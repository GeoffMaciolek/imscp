=head1 NAME

 iMSCP::Provider::NetworkInterface::Adapter::Debian - Debian network interface provider adapter

=cut

# i-MSCP - internet Multi Server Control Panel
# Copyright (C) 2010-2015 by Laurent Declercq <l.declercq@nuxwin.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

package iMSCP::Provider::NetworkInterface::Adapter::Debian;

use strict;
use warnings;
use Carp;
use iMSCP::Execute;
use iMSCP::File;
use iMSCP::Net;
use iMSCP::TemplateParser;
use parent 'iMSCP::Provider::NetworkInterface::Adapter::Abstract';

# Commands used in that package
my %commands = (
	'ifup' => '/sbin/ifup',
	'ifdown' => '/sbin/ifdown'
);

#  Network interface configuration file for ifup and ifdown
my $interfacesFilePath = '/etc/network/interfaces';

=head1 DESCRIPTION

 Debian network interface provider adapter.

=head1 PUBLIC METHODS

=over 4

=item addIpAddr(\%data)

 Add an IP address

 Param hash \%data IP address data:
   id: int IP address unique identifier
   ip_card: Network card to which the IP address must be added
   ip_address: string Either an IPv4 or IPv6 address
   netmask: OPTIONAL string Netmask (default: auto)
   broadcast: OPTIONAL string Broadcast (default: auto)
   gateway: OPTIONAL string Gateway (default: auto)
 Return iMSCP::Provider::Setting::Network::Interface, die on failure

=cut

sub addIpAddr
{
	my ($self, $data) = @_;

	$data = { } unless defined $data && ref $data eq 'HASH';
	defined $data->{$_} or croak(sprintf('The %s parameter is not defined', $_)) for qw/ id ip_card ip_address /;
	$data->{'id'} =~ /^\d+$/ or croak('id parameter must be an integer');
	$self->{'net'}->isKnownDevice($data->{'ip_card'}) or croak(sprintf(
		'The %s network interface is unknown', $data->{'ip_card'}
	));
	$self->{'net'}->isValidAddr($data->{'ip_address'}) or croak(sprintf(
		'The %s IP address is not valid', $data->{'ip_address'}
	));
	$data->{'id'} += 1000;
	$data->{'netmask'} = $self->{'net'}->getAddrVersion($data->{'ip_address'}) eq 'ipv4' ? '255.255.255.255' : '64';
	# TODO guess netmask broadcast and gateway if not defined

	# Make sure that the network device is UP
	$self->{'net'}->upDevice($data->{'ip_card'}) unless $self->{'net'}->isDeviceUp($data->{'ip_card'});
	$self->_updateInterfaces('add', $data);

	# If the IP is already configured we skip the configuration step
	unless($self->{'net'}->isKnownAddr($data->{'ip_address'})) {
		my ($stdout, $stderr);
		execute("$commands{'ifup'} --force $data->{'ip_card'}:$data->{'id'}", \$stdout, \$stderr) == 0 or die(sprintf(
			'Could not bring up the %s network interface', $stderr || 'Unknown error'
		));

		$self->{'net'}->resetInstance();
	}

	0;
}

=item removeIpAddr(\%data)

 Remove an IP address

 Param hash \%data IP address data:
   id: int IP address unique identifier
   ip_card: string Network card from which the IP address must be removed
   ip_address: string Either an IPv4 or IPv6 address
 Return iMSCP::Provider::Setting::Network::Interface, die on failure

=cut

sub removeIpAddr
{
	my ($self, $data) = @_;

	$data = { } unless defined $data && ref $data eq 'HASH';
	defined $data->{$_} or croak(sprintf('The %s parameter is not defined', $_)) for qw/ id ip_card ip_address /;
	$data->{'id'} =~ /^\d+$/ or croak('id parameter must be an integer');
	$data->{'id'} += 1000;
	$self->{'net'}->isKnownDevice($data->{'ip_card'}) or croak(sprintf(
		'The %s network interface is unknown', $data->{'ip_card'}
	));

	# Process only if the IP address is configured
	if($self->{'net'}->isKnownAddr($data->{'ip_address'})) {
		my ($stdout, $stderr);
		execute("$commands{'ifdown'} --force $data->{'ip_card'}:$data->{'id'}", \$stdout, \$stderr) == 0 or die(sprintf(
			'Could not bring down the %s network interface', $stderr || 'Unknown error'
		));

		$self->{'net'}->resetInstance();
	}

	$self->_updateInterfaces('remove', $data);
}

=back

=head1 PRIVATE METHODS

=over 4

=item _init()

 Initialize instance

 Return iMSCP::Provider::NetworkInterface::Adapter::Debian

=cut

sub _init
{
	my $self = shift;

	$self->{'net'} = iMSCP::Net->getInstance();
	$self;
}

=item _updateInterfaces($action, \%data)

 Add or remove IP address in the network interfaces file.

 Param string $action Action to perform (add|remove)
 Param string $data Template data
 Return int 0 on success, die on failure

=cut

sub _updateInterfaces
{
	my ($self, $action, $data) = @_;

	my $file = iMSCP::File->new( filename => $interfacesFilePath );
	$file->copyFile($interfacesFilePath . '.bak'); # backup the current working file

	# In any case we remove the entry if any
	my $fileContent = $file->get();
	$fileContent = iMSCP::TemplateParser::replaceBloc(
		"\n# i-MSCP [$data->{'ip_card'}:$data->{'id'}] entry BEGIN\n",
		"# i-MSCP [$data->{'ip_card'}:$data->{'id'}] entry ENDING\n",
		'',
		$fileContent
	);

	# If the IP is already configured we skip the configuration step
	if($action eq 'add' && ! $self->{'net'}->isKnownAddr($data->{'ip_address'})) {
		my $normalizedAddr = $self->{'net'}->normalizeAddr($data->{'ip_address'});

		# Add IP only if the IP has not been already added manually by the administrator
		if($fileContent !~ /(?:address|ip\s+addr.*?)\s+(?:$data->{'ip_address'}|$normalizedAddr)/) {
			$fileContent .= iMSCP::TemplateParser::process(
				{
					id => $data->{'id'},
					ip_card => $data->{'ip_card'},
					addr_family => $self->{'net'}->getAddrVersion($normalizedAddr) eq 'ipv4' ? 'inet' : 'inet6',
					address => $normalizedAddr,
					netmask => $data->{'netmask'},
				},
				<<TPL

# i-MSCP [{ip_card}:{id}] entry BEGIN
auto eth0:{id}
iface eth0:{id} {addr_family} static
        address {address}
        netmask {netmask}
# i-MSCP [{ip_card}:{id}] entry ENDING
TPL
			);
		}
	}

	$file->set($fileContent);
	$file->save();
}

=back

=head1 AUTHOR

 Laurent Declercq <l.declercq@nuxwin.com>

=cut

1;
__END__
