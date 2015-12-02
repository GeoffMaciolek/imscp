<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 * Copyright (C) 2010-2015 by Laurent Declercq <l.declercq@nuxwin.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

return [
	// isp logos path
	'ISP_LOGO_PATH' => '/ispLogos',

	'HTML_CHECKED' => ' checked="checked"',
	'HTML_DISABLED' => ' disabled="disabled"',
	'HTML_READONLY' => ' readonly="readonly"',
	'HTML_SELECTED' => ' selected="selected"',

	// User initial lang
	'USER_INITIAL_LANG' => 'auto',

	// Session timeout
	'SESSION_TIMEOUT' => 30,

	// SQL related settings
	'MAX_SQL_DATABASE_LENGTH' => 64,
	'MAX_SQL_USER_LENGTH' => 16,
	'MAX_SQL_PASS_LENGTH' => 32,

	// Captcha image width
	'LOSTPASSWORD_CAPTCHA_WIDTH' => 276,

	// Captcha image high
	'LOSTPASSWORD_CAPTCHA_HEIGHT' => 30,

	// Captcha background color
	'LOSTPASSWORD_CAPTCHA_BGCOLOR' => [176, 222, 245],

	// Captcha text color
	'LOSTPASSWORD_CAPTCHA_TEXTCOLOR' => [1, 53, 920],

	// Captcha ttf fontfiles (have to be under compatible open source license)
	'LOSTPASSWORD_CAPTCHA_FONTS' => [
		'FreeMono.ttf',
		'FreeMonoBold.ttf',
		'FreeMonoBoldOblique.ttf',
		'FreeMonoOblique.ttf',
		'FreeSans.ttf',
		'FreeSansBold.ttf',
		'FreeSansBoldOblique.ttf',
		'FreeSansOblique.ttf',
		'FreeSerif.ttf',
		'FreeSerifBold.ttf',
		'FreeSerifBoldItalic.ttf',
		'FreeSerifItalic.ttf'
	],

	/**
	 * The following settings can be overridden via the control panel - (admin/settings.php)
	 * The value below are those used by default
	 */

	// Domain rows pagination
	'DOMAIN_ROWS_PER_PAGE' => 10,

	// admin    : hosting plans are available only in admin level, the reseller cannot make custom changes
	// reseller : hosting plans are available only in reseller level
	'HOSTING_PLANS_LEVEL' => 10,

	// Enable or disable support system
	'IMSCP_SUPPORT_SYSTEM' => 1,

	// Enable or disable lost password support
	'LOSTPASSWORD' => 1,

	// Uniqkeytimeout in minutes
	'LOSTPASSWORD_TIMEOUT' => 30,

	// Enable or disable bruteforce detection
	'BRUTEFORCE' => 1,

	// Blocktime in minutes
	'BRUTEFORCE_BLOCK_TIME' => 30,

	// Max login before block
	'BRUTEFORCE_MAX_LOGIN' => 3,

	// Max login attempts before forced to wait
	'BRUTEFORCE_MAX_ATTEMPTS_BEFORE_WAIT' => 2,

	// Max captcha failed attempts before block
	'BRUTEFORCE_MAX_CAPTCHA' => 5,

	// Enable or disable time between logins
	'BRUTEFORCE_BETWEEN' => 1,

	// Time between logins in seconds
	'BRUTEFORCE_BETWEEN_TIME' => 30,

	// Enable or disable maintenance mode
	// 1: Maintenance mode enabled
	// 0: Maintenance mode disabled
	'MAINTENANCEMODE' => 0,

	// Minimum password chars
	'PASSWD_CHARS' => 6,

	// Enable or disable strong passwords
	// 1: Strong password not allowed
	// 0: Strong password allowed
	'PASSWD_STRONG' => 1,

	/**
	 * Logging Mailer default level (messages sent to DEFAULT_ADMIN_ADDRESS)
	 *
	 * E_USER_NOTICE: common operations (normal work flow)
	 * E_USER_WARNING: Operations that may be related to a problem
	 * E_USER_ERROR: Errors for which the admin should pay attention
	 *
	 * Note: PHP's E_USER_* constants are used for simplicity.
	 */
	'LOG_LEVEL' => E_USER_WARNING,

	// Creation of webmaster, postmaster and abuse forwarders when
	'CREATE_DEFAULT_EMAIL_ADDRESSES' => 1,

	// Count default email accounts (abuse, postmaster, webmaster) in user limit
	// 1: default email accounts are counted
	// 0: default email accounts are NOT counted
	'COUNT_DEFAULT_EMAIL_ADDRESSES' => 1,

	// Use hard mail suspension when suspending a domain:
	// 1: email accounts are hard suspended (completely unreachable)
	// 0: email accounts are soft suspended (passwords are modified so user can't access the accounts)
	'HARD_MAIL_SUSPENSION' => 1,

	// Prevent external login (i.e. check for valid local referer) separated in admin, reseller and client.
	// This option allows to use external login scripts
	//
	// 1: prevent external login, check for referer, more secure
	// 0: allow external login, do not check for referer, less security (risky)
	'PREVENT_EXTERNAL_LOGIN_ADMIN' => 1,
	'PREVENT_EXTERNAL_LOGIN_RESELLER' => 1,
	'PREVENT_EXTERNAL_LOGIN_CLIENT' => 1,

	// Automatic search for new version
	'CHECK_FOR_UPDATES' => false,
	'ENABLE_SSL' => false,

	// Server traffic settings
	'SERVER_TRAFFIC_LIMIT' => 0,
	'SERVER_TRAFFIC_WARN' => 0,

	// Paths appended to the default PHP open_basedir directive of customers
	'PHPINI_OPEN_BASEDIR' => ''
];
