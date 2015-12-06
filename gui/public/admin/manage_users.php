<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2015 by
 * i-MSCP - internet Multi Server Control Panel. All Rights Reserved.
 */

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptStart);

check_login('admin');

$cfg = \iMSCP\Core\Application::getInstance()->getConfig();
$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->define_dynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'admin/manage_users.tpl',
    'page_message' => 'layout',
    'admin_message' => 'page',
    'admin_list' => 'page',
    'admin_item' => 'admin_list',
    'admin_delete_link' => 'admin_item',
    'rsl_message' => 'page',
    'rsl_list' => 'page',
    'rsl_item' => 'rsl_list',
    'usr_message' => 'page',
    'search_form' => 'page',
    'usr_list' => 'page',
    'usr_item' => 'usr_list',
    'domain_status_change' => 'usr_item',
    'domain_status_nochange' => 'usr_item',
    'user_details' => 'usr_list',
    'usr_status_reload_true' => 'usr_item',
    'usr_status_reload_false' => 'usr_item',
    'usr_delete_show' => 'usr_item',
    'usr_delete_link' => 'usr_item',
    'icon' => 'usr_item',
    'scroll_prev_gray' => 'page',
    'scroll_prev' => 'page',
    'scroll_next_gray' => 'page',
    'scroll_next' => 'page'
]);
$tpl->assign([
    'TR_PAGE_TITLE' => tr('Admin / Users / Overview'),
    'TR_NEXT' => tr('Next'),
    'TR_PREVIOUS' => tr('Previous')
]);

if (isset($_POST['details']) && !empty($_POST['details'])) {
    $_SESSION['details'] = $_POST['details'];
} else {
    if (!isset($_SESSION['details'])) {
        $_SESSION['details'] = "hide";
    }
}

if (isset($_SESSION['user_added'])) {
    unset($_SESSION['user_added']);
    set_page_message(tr('Customer successfully scheduled for addition.'), 'success');
} elseif (isset($_SESSION['reseller_added'])) {
    unset($_SESSION['reseller_added']);
    set_page_message(tr('Reseller successfully added.'), 'success');
} elseif (isset($_SESSION['user_updated'])) {
    unset($_SESSION['user_updated']);
    set_page_message(tr('Customer account successfully updated.'), 'success');
} elseif (isset($_SESSION['user_deleted'])) {
    unset($_SESSION['user_deleted']);
    set_page_message(tr('Customer successfully scheduled for deletion.'), 'success');
} elseif (isset($_SESSION['email_updated'])) {
    unset($_SESSION['email_updated']);
    set_page_message(tr('Email successfully updated.'), 'success');
} elseif (isset($_SESSION['hdomain'])) {
    unset($_SESSION['hdomain']);
    set_page_message(tr('The reseller you want to remove has one or more customers accounts.<br>Remove them first.'), 'error');
}

if (!isset($cfg['HOSTING_PLANS_LEVEL']) || strtolower($cfg['HOSTING_PLANS_LEVEL']) !== 'admin') {
    $tpl->assign('EDIT_OPTION', '');
}

generateNavigation($tpl);
get_admin_manage_users($tpl);
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptEnd, null, [
    'templateEngine' => $tpl
]);
$tpl->prnt();

unsetMessages();
