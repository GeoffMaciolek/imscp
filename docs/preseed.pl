#!/usr/bin/perl

# i-MSCP preseed.pl template file for installer preseeding
#
# See documentation at http://wiki.i-mscp.net/doku.php?id=start:preseeding
#
# Author: Laurent Declercq <l.declercq@nuxwin.com>
# Last update: 2016.08.03

%main::questions = (

    #
    ## i-MSCP server implementations
    #

    # FTP server
    # Possible values: proftpd, vsftpd
    FTPD_SERVER                         => 'proftpd',

    # Web server
    # Possible values: apache_itk, apache_fcgid, apache_php_fpm
    HTTPD_SERVER                        => 'apache_php_fpm',

    # DNS server
    # Possible values: bind, external_server
    NAMED_SERVER                        => 'bind',

    # SMTP server
    # Possible values: postfix
    MTA_SERVER                          => 'postfix',

    # POP/IMAP servers
    # Possible values: courier, dovecot
    PO_SERVER                           => 'dovecot',

    # SQL server
    # Please consult the docs/<distro>/packages-<distro>.xml file for available options.
    SQL_SERVER                          => 'mysql_5.5',

    #
    # System configuration
    #

    # Server hostname
    # Possible values: A fully qualified hostname name
    SERVER_HOSTNAME                     => 'host.domain.tld',

    # Primary server IP
    # Possible values: An already configured IPv4 or IPv6
    BASE_SERVER_IP                      => '192.168.1.110',

    # WAN IP
    # Only relevant if your primary IP is in private range (e.g. when your server is behind NAT).
    # You can force usage of a private IP by putting BASE_SERVER_IP IP value
    # Possible values: Ipv4 or IPv6
    BASE_SERVER_PUBLIC_IP               => '',

    #
    ## Control panel configuration
    #

    # Control panel domain
    # This is the domain name from which the control panel will be reachable
    # Possible values: A fully qualified domain name
    BASE_SERVER_VHOST                   => 'panel.domain.tld',

    # Control panel http port
    # Possible values: A port in range 1025-65535
    BASE_SERVER_VHOST_HTTP_PORT         => '8880',

    # Control panel https port
    # Only relevant if SSL is enabled for the control panel (see below)
    # Possible values: A port in range 1025-65535
    BASE_SERVER_VHOST_HTTPS_PORT        => '8443',

    # Enable or disable SSL
    # Possible values: yes, no
    PANEL_SSL_ENABLED                   => 'yes',

    # Whether or not a self-signed SSL cettificate must be used
    # Possible values: yes, no
    PANEL_SSL_SELFSIGNED_CERTIFICATE    => 'yes',

    # SSL private key path
    # Only relevant if you don't use a self-signed certificate
    PANEL_SSL_PRIVATE_KEY_PATH          => '',

    # SSL private key passphrase
    # Only relevant if your SSL private key is encrypted
    PANEL_SSL_PRIVATE_KEY_PASSPHRASE    => '',

    # SSL CA Bundle path
    # Only relevant if you don't use a self-signed certificate
    PANEL_SSL_CA_BUNDLE_PATH            => '',

    # SSL certificate path
    # Only relevant if you don't use a self-signed certificate
    PANEL_SSL_CERTIFICATE_PATH          => '',

    # Control panel default access mode
    # Only relevant if SSL is enabled
    # Possible values: http://, https://
    BASE_SERVER_VHOST_PREFIX            => 'http://',

    # Master administrator login
    ADMIN_LOGIN_NAME                    => 'admin',
    ADMIN_PASSWORD                      => '',

    # Master administrator email address
    # Possible value: A valid email address. Be aware that mails sent to local root user will be forwarded to that email.
    DEFAULT_ADMIN_ADDRESS               => '',

    # Database name
    DATABASE_NAME                       => 'imscp',

    # SQL user
    # Note that this SQL user must have full privileges on the SQL server. It is used to to connect to the i-MSCP
    # database and also to create/delete SQL users for your customers
    # Be aware that it is not allowed to use SQL root user
    # Only ASCII alphabet characters and numbers are allowed in password.
    DATABASE_USER                       => 'imscp_user',
    DATABASE_PASSWORD                   => '',

    # Database user host for SQL user created by i-MSCP
    # That is the host from which SQL users created by i-MSCP are allowed to connect to the SQL server
    # Possible values: A valid hostname or IP address
    DATABASE_USER_HOST                  => 'localhost',

    # Enable or disable prefix/suffix for SQL database names
    # Possible values: behind, infront, none
    MYSQL_PREFIX                        => 'none',

    #
    ## DNS server configuration
    #

    # DNS server mode
    # Only relevant with 'bind' server implementation
    # Possible values: master, slave
    BIND_MODE                           => 'master',

    # Allow to specify IP addresses of your primary DNS servers - Only relevant if you set BIND_MODE to 'slave'
    # Primary DNS server IP addresses
    # Only relevant with master mode
    # Possible value: 'no' or a list of IPv4/IPv6 each separated by semicolon or space
    PRIMARY_DNS                         => 'no',

    # Slave DNS server IP addresses
    # Only relevant with slave mode
    # Possible value: 'no' or a list of IPv4/IPv6 each separated by semicolon or space
    SECONDARY_DNS                       => 'no',

    # IPv6 support
    # Only relevant with 'bind' server implementation
    # Possible values: yes, no
    BIND_IPV6                           => 'no',

    # Local DNS resolver
    # Only relevant with 'bind' server implementation
    # Possible values: yes, no
    LOCAL_DNS_RESOLVER                  => 'yes',

    #
    ## FTP server configuration parameters
    #

    # FTP SQL user
    # Only ASCII alphabet characters and numbers are allowed in password.
    FTPD_SQL_USER                       => 'vftp_user',
    FTPD_SQL_PASSWORD                   => '',

    # Passive port range
    # Possible values: A valid port range in range 32768-60999
    FTPD_PASSIVE_PORT_RANGE             => '32768 60999',

    #
    ## SQL server configuration
    #

    # Databas hostname
    # Possible values: A valid hostname or IP address
    DATABASE_HOST                       => '127.0.0.1',

    # Database port
    # Note that port is used only for connections through TCP
    # Possible values: A valid port
    DATABASE_PORT                       => '3306',

    # SQL root user
    # Note: This user is only used while installation/reconfiguration
    # Only ASCII alphabet characters and numbers are allowed in password.
    SQL_ROOT_USER                       => 'root',
    SQL_ROOT_PASSWORD                   => '',

    #
    ## Courier, POP server configuration
    #

    # Authdaemon SQL user
    # Only ASCII alphabet characters and numbers are allowed in password.
    AUTHDAEMON_SQL_USER                 => 'authdaemon_user',
    AUTHDAEMON_SQL_PASSWORD             => '',

    # SASL SQL user
    # Only relevant with 'courier' server implementation
    # Only ASCII alphabet characters and numbers are allowed in password.
    SASL_SQL_USER                       => 'sasl_user',
    SASL_SQL_PASSWORD                   => '',

    # Dovecot SQL user
    # Only relevant with 'dovecot' server implementation
    # Only ASCII alphabet characters and numbers are allowed in password.
    DOVECOT_SQL_USER                    => 'dovecot_user',
    DOVECOT_SQL_PASSWORD                => '',

    #
    ## PHP configuration parameters
    #

    # PHP configuration level
    # Possible values: per_user, per_domain, per_site
    PHP_CONFIG_LEVEL                    => 'per_site',

    # PHP-FPM listen socket type
    # Only relevant with 'apache_php_fpm' sever implementation
    # Possible values: uds, tcp
    PHP_FPM_LISTEN_MODE                 => 'uds',

    # Timezone
    # Possible values: A valid timezone (see http://php.net/manual/en/timezones.php)
    TIMEZONE                            => 'UTC',

    #
    ## Backup configuration
    #

    # i-MSCP backup feature (database and configuration files)
    # Enable backup for i-MSCP
    # Possible values: yes, no
    BACKUP_IMSCP                        => 'yes',

    # Enable backup feature for customers
    # Possible values: yes, no
    BACKUP_DOMAINS                      => 'yes',

    #
    ## SSL configuration for FTP, IMAP/POP and SMTP services
    #

    # Enable or disable SSL
    # Possible values: yes, no
    SERVICES_SSL_ENABLED                => 'yes',

    # Whether or not a self-signed SSL certificate must be used
    # Only relevant if SSL is enabled
    # Possible values: yes, no
    SERVICES_SSL_SELFSIGNED_CERTIFICATE => 'yes',

    # SSL private key path
    # Only relevant if you don't use a self-signed SSL certificate
    # Possible values: Path to SSL private key
    SERVICES_SSL_PRIVATE_KEY_PATH       => '',

    # SSL private key passphrase
    # Only relevant if your SSL private key is encrypted
    # Possible values: SSL private key passphrase
    SERVICES_SSL_PRIVATE_KEY_PASSPHRASE => '',

    # SSL CA Bundle path
    # Only relevant if you don't use a self-signed certificate
    # Possible values: Path to the SSL CA Bundle
    SERVICES_SSL_CA_BUNDLE_PATH         => '',

    # SSL certificate path
    # Only relevant if you don't use a self-signed certificate
    # Possible values: Path to SSL certificate
    SERVICES_SSL_CERTIFICATE_PATH       => '',

    #
    ## i-MSCP packages configuration
    #

    # Webstats package
    # Enable or disable webstats package
    # Possible values: Awstats, No
    WEBSTATS_PACKAGES                   => 'Awstats',

    # FTP Web file manager packages
    # Possible values: Pydio (only if PHP < 7.0), Net2ftp and MonstaFTP
    FILEMANAGER_PACKAGE                 => 'MonstaFTP',

    # SQL user for PhpMyAdmin
    # Only ASCII alphabet characters and numbers are allowed in password.
    PHPMYADMIN_SQL_USER                 => 'pma_user',
    PHPMYADMIN_SQL_PASSWORD             => '',

    # Webmmail packages
    # Possible values: 'No' or a list of packages, each comma separated
    WEBMAIL_PACKAGES                    => 'RainLoop,Roundcube',

    # SQL user for Roundcube package
    # Only relevant if you use the Roundcube webmail package
    # Only ASCII alphabet characters and numbers are allowed in password.
    ROUNDCUBE_SQL_USER                  => 'roundcube_user',
    ROUNDCUBE_SQL_PASSWORD              => '',

    # SQL user for Rainloop package
    # Only relevant if you use the Rainloop webmail package
    # Only ASCII alphabet characters and numbers are allowed in password.
    RAINLOOP_SQL_USER                   => 'rainloop_user',
    RAINLOOP_SQL_PASSWORD               => '',

    # Anti-rootkits packages
    # Possible values: 'No' or a list of packages, each comma separated
    ANTI_ROOTKITS_PACKAGES              => 'Chkrootkit,Rkhunter'
);

1;
