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
require 'module/Core/src/Functions/Tickets.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptStart);

check_login('admin');

if (!hasTicketSystem()) {
    redirectTo('index.php');
} elseif (isset($_GET['ticket_id']) && !empty($_GET['ticket_id'])) {
    closeTicket(intval($_GET['ticket_id']));
}

if (isset($_GET['psi'])) {
    $start = intval($_GET['psi']);
} else {
    $start = 0;
}

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->defineDynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'admin/ticket_system.tpl',
    'page_message' => 'layout',
    'tickets_list' => 'page',
    'tickets_item' => 'tickets_list',
    'scroll_prev_gray' => 'page',
    'scroll_prev' => 'page',
    'scroll_next_gray' => 'page',
    'scroll_next' => 'page'
]);
$tpl->assign([
    'TR_PAGE_TITLE' => tr(' Admin / Support / Open Tickets'),
    'TR_TICKET_STATUS' => tr('Status'),
    'TR_TICKET_FROM' => tr('From'),
    'TR_TICKET_SUBJECT' => tr('Subject'),
    'TR_TICKET_URGENCY' => tr('Priority'),
    'TR_TICKET_LAST_ANSWER_DATE' => tr('Last reply date'),
    'TR_TICKET_ACTIONS' => tr('Actions'),
    'TR_TICKET_DELETE' => tr('Delete'),
    'TR_TICKET_CLOSE' => tr('Close'),
    'TR_TICKET_READ_LINK' => tr('Read ticket'),
    'TR_TICKET_DELETE_LINK' => tr('Delete ticket'),
    'TR_TICKET_CLOSE_LINK' => tr('Close ticket'),
    'TR_TICKET_DELETE_ALL' => tr('Delete all tickets'),
    'TR_TICKETS_DELETE_MESSAGE' => tr("Are you sure you want to delete the '%s' ticket?", '%s'),
    'TR_TICKETS_DELETE_ALL_MESSAGE' => tr('Are you sure you want to delete all tickets?'),
    'TR_PREVIOUS' => tr('Previous'),
    'TR_NEXT' => tr('Next')
]);

$cfg = \iMSCP\Core\Application::getInstance()->getConfig();

generateNavigation($tpl);
generateTicketList($tpl, $_SESSION['user_id'], $start, $cfg['DOMAIN_ROWS_PER_PAGE'], 'admin', 'open');
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptEnd, null, [
    'templateEngine' => $tpl
]);
$tpl->prnt();

unsetMessages();
