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
 * Functions
 */

/**
 * Generates database sql users list
 *
 * @access private
 * @param iMSCP\Core\Template\TemplateEngine $tpl Template engine
 * @param int $databaseId Database unique identifier
 * @return void
 */
function _client_generateDatabaseSqlUserList($tpl, $databaseId)
{
    $stmt = exec_query(
        'SELECT sqlu_id, sqlu_name, sqlu_host FROM sql_user WHERE sqld_id = ? ORDER BY sqlu_name', $databaseId
    );

    if (!$stmt->rowCount()) {
        $tpl->assign('SQL_USERS_LIST', '');
    } else {
        $tpl->assign('SQL_USERS_LIST', '');
        $tpl->assign(
            [
                'TR_DB_USER' => 'User',
                'TR_DB_USER_HOST' => 'Host',
                'TR_DB_USER_HOST_TOOLTIP' => tr('Host from which SQL user is allowed to connect to SQL server')
            ]
        );

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sqlUserName = $row['sqlu_name'];
            $tpl->assign([
                'DB_USER' => tohtml($sqlUserName),
                'DB_USER_HOST' => tohtml(decode_idna($row['sqlu_host'])),
                'DB_USER_JS' => tojs($sqlUserName),
                'USER_ID' => $row['sqlu_id']
            ]);
            $tpl->parse('SQL_USERS_LIST', '.sql_users_list');
        }
    }
}

/**
 * Generates databases list
 *
 * @param iMSCP\Core\Template\TemplateEngine $tpl Template engine
 * @param int $domainId Domain unique identifier
 * @return void
 */
function client_databasesList($tpl, $domainId)
{
    $stmt = exec_query('SELECT sqld_id, sqld_name FROM sql_database WHERE domain_id = ? ORDER BY sqld_name', $domainId);

    if (!$stmt->rowCount()) {
        set_page_message(tr('You do not have databases.'), 'static_info');
        $tpl->assign('SQL_DATABASES_USERS_LIST', '');
    } else {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tpl->assign([
                'DB_ID' => $row['sqld_id'],
                'DB_NAME' => tohtml($row['sqld_name']),
                'DB_NAME_JS' => tojs($row['sqld_name'])
            ]);

            _client_generateDatabaseSqlUserList($tpl, $row['sqld_id']);
            $tpl->parse('SQL_DATABASES_LIST', '.sql_databases_list');
        }
    }
}

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptStart);

check_login('user');
customerHasFeature('sql') or showBadRequestErrorPage();

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->defineDynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'client/sql_manage.tpl',
    'page_message' => 'layout',
    'sql_databases_users_list' => 'page',
    'sql_databases_list' => 'sql_databases_users_list',
    'sql_users_list' => 'sql_databases_list'
]);
$tpl->assign([
    'TR_PAGE_TITLE' => tr('Client / Databases / Overview'),
    'TR_MANAGE_SQL' => tr('Manage SQL'),
    'TR_DELETE' => tr('Delete'),
    'TR_DATABASE' => tr('Database Name and Users'),
    'TR_CHANGE_PASSWORD' => tr('Update password'),
    'TR_ACTIONS' => tr('Actions'),
    'TR_DATABASE_USERS' => tr('Database users'),
    'TR_ADD_USER' => tr('Add SQL user'),
    'TR_DATABASE_MESSAGE_DELETE' => tr("This database will be permanently deleted. This process cannot be recovered. All users linked to this database will also be deleted if not linked to another database. Are you sure you want to delete the '%s' database?", '%s'),
    'TR_USER_MESSAGE_DELETE' => tr("Are you sure you want delete the %s SQL user?", '%s')
]);

generateNavigation($tpl);
client_databasesList($tpl, get_domain_default_props($_SESSION['user_id'])['domain_id']);
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onClientScriptEnd, null, [
    'templateEngine' => $tpl
]);
$tpl->prnt();

unsetMessages();
