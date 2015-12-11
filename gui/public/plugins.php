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

use iMSCP\Core\Application;
use iMSCP\Core\Plugin\Feature\RouteLogicProviderInterface;
use iMSCP\Core\Plugin\Feature\RoutesProviderInterface;
use iMSCP\Core\Plugin\PluginEvent;

require '../application.php';

/** @var \iMSCP\Core\Plugin\PluginManager $pluginManager */
$pluginManager = Application::getInstance()->getServiceManager()->get('PluginManager');

/** @var \Zend\Http\PhpEnvironment\Request $request */
$request = Application::getInstance()->getRequest();

$plugins = $pluginManager->getLoadedPlugins('Action');
$scriptPath = null;

if (empty($plugins)) {
    showNotFoundErrorPage();
}

$eventsManager = Application::getInstance()->getEventManager();

if (($urlComponents = parse_url($request->getServer('REQUEST_URI'))) === false) {
    throw new RuntimeException(sprintf('Could not parse URL: %s', $request->getServer('REQUEST_URI')));
}

$pluginEvent = new PluginEvent();
$responses = $eventsManager->trigger(PluginEvent::onBeforePluginsRoute, $pluginEvent);

if ($responses->stopped()) {
    showNotFoundErrorPage();
}

foreach ($plugins as $plugin) {
    if ($plugin instanceof RouteLogicProviderInterface && ($scriptPath = $plugin->route($urlComponents))) {
        break;
    }

    if ($plugin instanceof RoutesProviderInterface) {
        foreach ($plugin->getRoutes() as $pluginRoute => $pluginControllerPath) {
            if ($pluginRoute == $urlComponents['path']) {
                $scriptPath = $pluginControllerPath;
                $request->getServer()->set('SCRIPT_NAME', $pluginRoute);
                $_SERVER['SCRIPT_NAME'] = $pluginRoute;
                break 2;
            }
        }
    }
}

if (!$scriptPath) {
    showNotFoundErrorPage();
}

$pluginEvent->setParam('scriptPath', $scriptPath);
$eventsManager->trigger(PluginEvent::onAfterPluginsRoute, $pluginEvent, ['scriptPath' => $scriptPath]);
include $scriptPath;
