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
 * Get domain alias data
 *
 * @access private
 * @param int $domainAliasId Subdomain unique identifier
 * @return array Domain alias data. If any error occurs FALSE is returned
 */
function _reseller_getAliasData($domainAliasId)
{
    static $domainAliasData = null;

    if (null === $domainAliasData) {
        $query = "
            SELECT
                alias_name, url_forward AS forward_url, type_forward AS forward_type
            FROM
                domain_aliasses
            INNER JOIN
                domain USING(domain_id)
            INNER JOIN
                admin ON(admin_id = domain_admin_id)
            WHERE
                alias_id = ?
            AND
                alias_status = ?
            AND
                created_by = ?
        ";
        $stmt = exec_query($query, [$domainAliasId, 'ok', $_SESSION['user_id']]);

        if (!$stmt->rowCount()) {
            return false;
        }

        $domainAliasData = $stmt->fetch(PDO::FETCH_ASSOC);
        $domainAliasData['alias_name_utf8'] = decode_idna($domainAliasData['alias_name']);
    }

    return $domainAliasData;
}

/**
 * Generate page
 *
 * @param $tpl \iMSCP\Core\Template\TemplateEngine
 * @return void
 */
function reseller_generatePage($tpl)
{
    if (isset($_GET['id'])) {
        $domainAliasId = clean_input($_GET['id']);

        if (!($domainAliasData = _reseller_getAliasData($domainAliasId))) {
            showBadRequestErrorPage();
        }

        if (empty($_POST)) {
            if ($domainAliasData['forward_url'] != 'no') {
                $urlForwarding = true;
                $uri = new Zend\Uri\Uri($domainAliasData['forward_url']);
                $forwardUrlScheme = $uri->getScheme();
                $forwardUrl = substr($uri->toString(), strlen($forwardUrlScheme) + 3);
                $forwardType = $domainAliasData['forward_type'];
            } else {
                $urlForwarding = false;
                $forwardUrlScheme = 'http://';
                $forwardUrl = '';
                $forwardType = '302';
            }
        } else {
            $urlForwarding = (isset($_POST['url_forwarding']) && $_POST['url_forwarding'] == 'yes') ? true : false;
            $forwardUrlScheme = (isset($_POST['forward_url_scheme'])) ? $_POST['forward_url_scheme'] : 'http://';
            $forwardUrl = isset($_POST['forward_url']) ? $_POST['forward_url'] : '';
            $forwardType = isset($_POST['forward_type']) && in_array($_POST['forward_type'], ['301', '302', '303', '307'], true)
                ? $_POST['forward_type'] : '302';
        }

        $cfg = \iMSCP\Core\Application::getInstance()->getConfig();
        $checked = $cfg['HTML_CHECKED'];
        $selected = $cfg['HTML_SELECTED'];
        $tpl->assign([
            'DOMAIN_ALIAS_ID' => $domainAliasId,
            'DOMAIN_ALIAS_NAME' => tohtml($domainAliasData['alias_name_utf8']),
            'FORWARD_URL_YES' => ($urlForwarding) ? $checked : '',
            'FORWARD_URL_NO' => ($urlForwarding) ? '' : $checked,
            'HTTP_YES' => ($forwardUrlScheme == 'http://') ? $selected : '',
            'HTTPS_YES' => ($forwardUrlScheme == 'https://') ? $selected : '',
            'FTP_YES' => ($forwardUrlScheme == 'ftp://') ? $selected : '',
            'FORWARD_URL' => tohtml(decode_idna($forwardUrl)),
            'FORWARD_TYPE_301' => ($forwardType == '301') ? $checked : '',
            'FORWARD_TYPE_302' => ($forwardType == '302') ? $checked : '',
            'FORWARD_TYPE_303' => ($forwardType == '303') ? $checked : '',
            'FORWARD_TYPE_307' => ($forwardType == '307') ? $checked : ''
        ]);
    } else {
        showBadRequestErrorPage();
    }
}

/**
 * Edit domain alias
 *
 * @return bool TRUE on success, FALSE on failure
 */
function reseller_editDomainAlias()
{
    if (isset($_GET['id'])) {
        $domainAliasId = clean_input($_GET['id']);

        if (($domainAliasData = _reseller_getAliasData($domainAliasId))) {
            $forwardUrl = 'no';
            $forwardType = 'null';

            if (
                isset($_POST['url_forwarding']) && $_POST['url_forwarding'] == 'yes' &&
                isset($_POST['forward_type']) && in_array($_POST['forward_type'], ['301', '302', '303', '307'], true)
            ) {
                if (isset($_POST['forward_url_scheme']) && isset($_POST['forward_url'])) {
                    $forwardUrl = clean_input($_POST['forward_url_scheme']) . clean_input($_POST['forward_url']);
                    $forwardType = clean_input($_POST['forward_type']);

                    try {
                        try {
                            $uri = new Zend\Uri\Uri($forwardUrl);
                        } catch (InvalidArgumentException $e) {
                            throw new InvalidArgumentException(tr('Forward URL %s is not valid.', "<strong>$forwardUrl</strong>"));
                        }

                        $uri->setHost(encode_idna($uri->getHost()));
                        $uriPath = rtrim(preg_replace('#/+#', '/', $uri->getPath()), '/') . '/'; // normalize path
                        $uri->setPath($uriPath);

                        if ($uri->getHost() == $domainAliasData['alias_name'] && $uri->getPath() == '/') {
                            throw new InvalidArgumentException(
                                tr('Forward URL %s is not valid.', "<strong>$forwardUrl</strong>") . ' ' .
                                tr('Domain alias %s cannot be forwarded on itself.', "<strong>{$domainAliasData['alias_name_utf8']}</strong>")
                            );
                        }

                        $forwardUrl = $uri->toString();
                    } catch (Exception $e) {
                        set_page_message($e->getMessage(), 'error');
                        return false;
                    }
                } else {
                    showBadRequestErrorPage();
                    exit;
                }
            }

            \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
                \iMSCP\Core\Events::onBeforeEditDomainAlias, null, [
                'domainAliasId' => $domainAliasId,
                'domainAliasName' => $domainAliasData['alias_name']
            ]);

            exec_query(
                'UPDATE `domain_aliasses` SET `url_forward` = ?, `type_forward` = ?, `alias_status` = ? WHERE `alias_id` = ?',
                [$forwardUrl, $forwardType, 'tochange', $domainAliasId]
            );

            \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
                \iMSCP\Core\Events::onAfterEditDomainAlias, null, [
                'domainAliasId' => $domainAliasId,
                'domainAliasName' => $domainAliasData['alias_name']
            ]);

            send_request();
            write_log("{$_SESSION['user_logged']}: scheduled update of domain alias: {$domainAliasData['alias_name_utf8']}.", E_USER_NOTICE);
        } else {
            showBadRequestErrorPage();
        }
    } else {
        showBadRequestErrorPage();
    }

    return true;
}

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
    \iMSCP\Core\Events::onResellerScriptStart, \iMSCP\Core\Application::getInstance()->getApplicationEvent()
);

check_login('reseller');
(resellerHasFeature('domain_aliases') && resellerHasCustomers()) or showBadRequestErrorPage();

if (!empty($_POST) && reseller_editDomainAlias()) {
    set_page_message(tr('Domain alias successfully scheduled for update.'), 'success');
    redirectTo('alias.php');
} else {
    $tpl = new \iMSCP\Core\Template\TemplateEngine();
    $tpl->defineDynamic([
        'layout' => 'shared/layouts/ui.tpl',
        'page' => 'reseller/alias_edit.tpl',
        'page_message' => 'layout'
    ]);
    $tpl->assign([
        'TR_PAGE_TITLE' => tr('Reseller / Domains / Edit Domain Alias'),
        'TR_DOMAIN_ALIAS' => tr('Domain alias'),
        'TR_DOMAIN_ALIAS_NAME' => tr('Domain alias name'),
        'TR_URL_FORWARDING' => tr('URL forwarding'),
        'TR_FORWARD_TO_URL' => tr('Forward to URL'),
        'TR_URL_FORWARDING_TOOLTIP' => tr('Allows to forward any request made to this domain alias to a specific URL.'),
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
        'TR_UPDATE' => tr('Update'),
        'TR_CANCEL' => tr('Cancel')
    ]);

    generateNavigation($tpl);
    reseller_generatePage($tpl);
    generatePageMessage($tpl);

    $tpl->parse('LAYOUT_CONTENT', 'page');
    \iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onResellerScriptEnd, null, [
        'templateEngine' => $tpl
    ]);
    $tpl->prnt();
    unsetMessages();
}
