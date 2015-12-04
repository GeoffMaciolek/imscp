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

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->define_dynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'admin/server_status.tpl',
    'page_message' => 'layout',
    'service_status' => 'page'
]);

$tpl->assign([
    'TR_PAGE_TITLE' => tr('Admin / General / Services Status'),
    'TR_SERVICE' => tr('Service Name'),
    'TR_IP' => tr('IP Address'),
    'TR_PORT' => tr('Port'),
    'TR_STATUS' => tr('Status'),
    'TR_SERVER_STATUS' => tr('Server status')
]);

\iMSCP\Core\Application::getInstance()->getEventManager()->attach('onGetJsTranslations', function ($e) {
    /** @var $e \Zend\EventManager\Event */
    $e->getParam('translations')->core['dataTable'] = getDataTablesPluginTranslations(false);
});

generateNavigation($tpl);
generatePageMessage($tpl);

$running = tr('UP');
$down = tr('DOWN');
$services = new \iMSCP\Core\SystemServices();

foreach ($services as $service) {
    if ($services->isVisible()) {
        $serviceState = $services->isRunning();
        $ip = $services->getIp();
        $tpl->assign([
            'SERVICE' => tohtml($services->getName()),
            'IP' => ($ip === '0.0.0.0') ? tr('Any') : tohtml($ip),
            'PORT' => tohtml($services->getPort()),
            'STATUS' => $serviceState ? "<b>$running</b>" : $down,
            'CLASS' => $serviceState ? 'up' : 'down'
        ]);
        $tpl->parse('SERVICE_STATUS', '.service_status');
    }
}

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptEnd, [
    'templateEngine' => $tpl
]);
$tpl->prnt();
