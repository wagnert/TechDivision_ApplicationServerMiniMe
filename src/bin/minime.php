#!/opt/appserver/bin/php
<?php

/**
 * bin/minime.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */

require __DIR__ . '/../../../../../../../bootstrap.php';

use TechDivision\Storage\StackableStorage;
use TechDivision\ServletEngine\StandardSessionManager;
use TechDivision\ServletEngine\DefaultSessionSettings;
use TechDivision\ApplicationServerMiniMe\Server;
use TechDivision\ApplicationServerMiniMe\Application;

// initialize the session manager
$sessionManager = new StandardSessionManager();
$sessionManager->injectSettings(new DefaultSessionSettings());

// prepare the path to the dummy web applications
$webappPath = __DIR__ . '/../webapps';

// create the array with the applications
$applications = array();
$applications[0] = new Application('app_01', $webappPath);
$applications[0]->injectSessionManager($sessionManager);
$applications[0]->start();
$applications[1] = new Application('app_02', $webappPath);
$applications[1]->injectSessionManager($sessionManager);
$applications[1]->start();

// start the applications
$server = new Server($applications);
$server->start();
$server->join();
