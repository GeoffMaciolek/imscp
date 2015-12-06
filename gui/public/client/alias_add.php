<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 * Copyright (C) 2010-2015 by i-MSCP Team
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

/***********************************************************************************************************************
 * Functions
 */

/**
 * Get domains list
 *
 * @return array Domains list
 */
function _client_getDomainsList()
{
    static $domainsList = null;

    if (null === $domainsList) {
        $mainDmnProps = get_domain_default_props($_SESSION['user_id']);

        $domainsList = [
            [
                'name' => $mainDmnProps['domain_name'],
                'id' => $mainDmnProps['domain_id'],
                'type' => 'dmn',
                'mount_point' => '/'
            ]
        ];

        $query = "
            SELECT
                CONCAT(t1.subdomain_name, '.', t2.domain_name) AS name,
                t1.subdomain_mount AS mount_point
            FROM
                subdomain AS t1
            INNER JOIN
                domain AS t2 USING(domain_id)
            WHERE
                t1.domain_id = :domain_id
            AND
                t1.subdomain_status = :status_ok
            UNION
            SELECT
                alias_name AS name, alias_mount AS mount_point
            FROM
                domain_aliasses
            WHERE
                domain_id = :domain_id
            AND
                alias_status = :status_ok
            UNION
            SELECT
                CONCAT(t1.subdomain_alias_name, '.', t2.alias_name) AS name,
                t1.subdomain_alias_mount AS mount_point
            FROM
                subdomain_alias AS t1
            INNER JOIN
                domain_aliasses AS t2 USING(alias_id)
            WHERE
                t2.domain_id = :domain_id
            AND
                subdomain_alias_status = :status_ok
        ";
        $stmt = exec_query($query, ['domain_id' => $mainDmnProps['domain_id'], 'status_ok' => 'ok']);

        if ($stmt->rowCount()) {
            $domainsList = array_merge($domainsList, $stmt->fetchAll(PDO::FETCH_ASSOC));
            usort($domainsList, function ($a, $b) {
                return strnatcmp(decode_idna($a['name']), decode_idna($b['name']));
            });
        }
    }

    return $domainsList;
}

/**
 * Generate page
 *
 * @param $tpl iMSCP\Core\Template\TemplateEngine
 * @return void
 */
function client_generatePage($tpl)
{
    $cfg = \iMSCP\Core\Application::getInstance()->getConfig();
    $checked = $cfg['HTML_CHECKED'];
    $selected = $cfg['HTML_SELECTED'];
    $forwardType = isset($_POST['forward_type']) && in_array($_POST['forward_type'], ['301', '302', '303', '307'], true) ? $_POST['forward_type'] : '302';
    $tpl->assign([
            'DOMAIN_ALIAS_NAME' => (isset($_POST['domain_alias_name'])) ? tohtml($_POST['domain_alias_name']) : '',
            'SHARED_MOUNT_POINT_YES' => (isset($_POST['shared_mount_point']) && $_POST['shared_mount_point'] == 'yes') ? $checked : '',
            'SHARED_MOUNT_POINT_NO' => (isset($_POST['shared_mount_point']) && $_POST['shared_mount_point'] == 'yes') ? '' : $checked,
            'FORWARD_URL_YES' => (isset($_POST['url_forwarding']) && $_POST['url_forwarding'] == 'yes') ? $checked : '',
            'FORWARD_URL_NO' => (isset($_POST['url_forwarding']) && $_POST['url_forwarding'] == 'yes') ? '' : $checked,
            'HTTP_YES' => (isset($_POST['forward_url_scheme']) && $_POST['forward_url_scheme'] == 'http://') ? $selected : '',
            'HTTPS_YES' => (isset($_POST['forward_url_scheme']) && $_POST['forward_url_scheme'] == 'https://') ? $selected : '',
            'FTP_YES' => (isset($_POST['forward_url_scheme']) && $_POST['forward_url_scheme'] == 'ftp://') ? $selected : '',
            'FORWARD_URL' => (isset($_POST['forward_url'])) ? tohtml(decode_idna($_POST['forward_url'])) : '',
            'FORWARD_TYPE_301' => ($forwardType == '301') ? $checked : '',
            'FORWARD_TYPE_302' => ($forwardType == '302') ? $checked : '',
            'FORWARD_TYPE_303' => ($forwardType == '303') ? $checked : '',
            'FORWARD_TYPE_307' => ($forwardType == '307') ? $checked : ''
        ]
    );

    $domainList = _client_getDomainsList();

    foreach ($domainList as $domain) {
        $tpl->assign([
            'DOMAIN_NAME' => tohtml($domain['name']),
            'DOMAIN_NAME_UNICODE' => tohtml(decode_idna($domain['name'])),
            'SHARED_MOUNT_POINT_DOMAIN_SELECTED' => (
                isset($_POST['shared_mount_point_domain']) && $_POST['shared_mount_point_domain'] == $domain['name']
            ) ? $selected : ''
        ]);
        $tpl->parse('SHARED_MOUNT_POINT_DOMAIN', '.shared_mount_point_domain');
    }
}

/**
 * Add new domain alias
 *
 * @return bool TRUE on success, FALSE on failure
 */
function client_addDomainAlias()
{
    if (empty($_POST['domain_alias_name'])) {
        set_page_message(tr('You must enter a domain alias name.'), 'error');
        return false;
    }

    $domainAliasName = clean_input(strtolower($_POST['domain_alias_name']));
    global $dmnNameValidationErrMsg;

    if (!isValidDomainName($domainAliasName)) {
        set_page_message($dmnNameValidationErrMsg, 'error');
        return false;
    }

    // www is considered as an alias of the domain alias
    while (strpos($domainAliasName, 'www.') !== false) {
        $domainAliasName = substr($domainAliasName, 4);
    }

    if (imscp_domain_exists($domainAliasName, $_SESSION['user_created_by'])) {
        set_page_message(tr('Domain %s is unavailable.', "<strong>$domainAliasName</strong>"), 'error');
        return false;
    }

    $domainAliasNameAscii = encode_idna($domainAliasName);
    // Set default mount point
    $mountPoint = "/$domainAliasNameAscii";

    // Check for shared mount point option
    if (isset($_POST['shared_mount_point']) && $_POST['shared_mount_point'] == 'yes') { // We are safe here
        if (isset($_POST['shared_mount_point_domain'])) {
            $sharedMountPointDomain = clean_input($_POST['shared_mount_point_domain']);
            $domainList = _client_getDomainsList();

            // Get shared mount point
            foreach ($domainList as $domain) {
                if ($domain['name'] == $sharedMountPointDomain) {
                    $mountPoint = $domain['mount_point'];
                }
            }
        } else {
            showBadRequestErrorPage();
        }
    }

    // Check for URL forwarding option
    $forwardUrl = 'no';
    $forwardType = null;

    if (
        isset($_POST['url_forwarding']) && $_POST['url_forwarding'] == 'yes' &&
        isset($_POST['forward_type']) && in_array($_POST['forward_type'], ['301', '302', '303', '307'], true)
    ) {
        if (isset($_POST['forward_url_scheme']) && isset($_POST['forward_url'])) {
            $forwardUrl = clean_input($_POST['forward_url_scheme']) . clean_input($_POST['forward_url']);
            $forwardType = clean_input($_POST['forward_type']);

            try {
                try {
                    $uri = new \Zend\Uri\Uri($forwardUrl);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(tr('Forward URL %s is not valid.', "<strong>$forwardUrl</strong>"));
                }

                $uri->setHost(encode_idna($uri->getHost()));
                $uriPath = rtrim(preg_replace('#/+#', '/', $uri->getPath()), '/') . '/'; // normalize path
                $uri->setPath($uriPath);

                if ($uri->getHost() == $domainAliasNameAscii && $uri->getPath() == '/') {
                    throw new InvalidArgumentException(
                        tr('Forward URL %s is not valid.', "<strong>$forwardUrl</strong>") . ' ' .
                        tr('Domain alias %s cannot be forwarded on itself.', "<strong>$domainAliasName</strong>")
                    );
                }

                $forwardUrl = $uri->toString();
            } catch (Exception $e) {
                set_page_message($e->getMessage(), 'error');
                return false;
            }
        } else {
            showBadRequestErrorPage();
        }
    }

    $mainDmnProps = get_domain_default_props($_SESSION['user_id']);
    $domainId = $mainDmnProps['domain_id'];

    /** @var \Doctrine\DBAL\Connection $db */
    $db = \iMSCP\Core\Application::getInstance()->getServiceManager()->get('Database');

    \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onBeforeAddDomainAlias, null, [
        'domainId' => $domainId,
        'domainAliasName' => $domainAliasNameAscii
    ]);

    exec_query(
        '
            INSERT INTO `domain_aliasses` (
                `domain_id`, `alias_name`, `alias_mount`, `alias_status`, `alias_ip_id`, `url_forward`, `type_forward`
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?
            )
        ',
        [
            $domainId, $domainAliasNameAscii, $mountPoint, 'ordered', $mainDmnProps['domain_ip_id'], $forwardUrl,
            $forwardType
        ]
    );

    \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAfterAddDomainAlias, null, [
        'domainId' => $domainId,
        'domainAliasName' => $domainAliasNameAscii,
        'domainAliasId' => $db->lastInsertId()
    ]);

    send_alias_order_email($domainAliasName); // // Notify the reseller
    write_log("{$_SESSION['user_logged']}: ordered new domain alias: $domainAliasName.", E_USER_NOTICE);
    set_page_message(tr('Domain alias successfully ordered.'), 'success');
    return true;
}

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptStart);
check_login('user');
customerHasFeature('domain_aliases') or showBadRequestErrorPage();

$mainDmnProps = get_domain_default_props($_SESSION['user_id']);
$domainAliasesCount = get_domain_running_als_cnt($mainDmnProps['domain_id']);

if ($mainDmnProps['domain_alias_limit'] != 0 && $domainAliasesCount >= $mainDmnProps['domain_alias_limit']) {
    set_page_message(tr('You have reached the maximum number of domain aliasses allowed by your subscription.'), 'warning');
    redirectTo('domains_manage.php');
} elseif (!empty($_POST) && client_addDomainAlias()) {
    redirectTo('domains_manage.php');
} else {
    $tpl = new \iMSCP\Core\Template\TemplateEngine();
    $tpl->define_dynamic([
        'layout' => 'shared/layouts/ui.tpl',
        'page' => 'client/alias_add.tpl',
        'page_message' => 'layout',
        'shared_mount_point_domain' => 'page'
    ]);
    $tpl->assign([
        'TR_PAGE_TITLE' => tr('Client / Domains / Add Domain Alias'),
        'TR_DOMAIN_ALIAS' => tr('Domain alias'),
        'TR_DOMAIN_ALIAS_NAME' => tr('Domain alias name'),
        'TR_DOMAIN_ALIAS_NAME_TOOLTIP' => tr("You must omit 'www'. It will be added automatically."),
        'TR_SHARED_MOUNT_POINT' => tr('Shared mount point'),
        'TR_SHARED_MOUNT_POINT_TOOLTIP' => tr('Allows to share the mount point of another domain.'),
        'TR_URL_FORWARDING' => tr('URL forwarding'),
        'TR_URL_FORWARDING_TOOLTIP' => tr('Allows to forward any request made to this domain alias to a specific URL. Be aware that when this option is in use, no Web folder is created for the domain alias.'),
        'TR_FORWARD_TO_URL' => tr('Forward to URL'),
        'TR_YES' => tr('Yes'),
        'TR_NO' => tr('No'),
        'TR_HTTP' => 'http://',
        'TR_HTTPS' => 'https://',
        'TR_FTP' => 'ftp://',
        'TR_FORWARD_TYPE' => tr('Forward type'),
        'TR_301' => '301',
        'TR_302' => '302',
        'TR_303' => '303',
        'TR_307' => '307',
        'TR_ADD' => tr('Add'),
        'TR_CANCEL' => tr('Cancel')
    ]);

    generateNavigation($tpl);
    client_generatePage($tpl);
    generatePageMessage($tpl);

    $tpl->parse('LAYOUT_CONTENT', 'page');
    \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptEnd, null, [
            'templateEngine' => $tpl]
    );
    $tpl->prnt();

    unsetMessages();
}
