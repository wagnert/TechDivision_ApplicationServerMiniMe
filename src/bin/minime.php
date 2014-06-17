#!/opt/appserver/bin/php
<?php

// bootstrap the application
require __DIR__ . '/../bootstrap.php';

use TechDivision\Storage\StackableStorage;
use TechDivision\ServletEngine\StandardSessionManager;
use TechDivision\ServletEngine\DefaultSessionSettings;
use TechDivision\ApplicationServerMiniMe\Server;
use TechDivision\ApplicationServerMiniMe\Application;

$sessionManager = new StandardSessionManager();
$sessionManager->injectSettings(new DefaultSessionSettings());

$webappPath = __DIR__ . '/../webapps';

$applications = array();
$applications[0] = new Application('app_01', $webappPath);
$applications[0]->injectSessionManager($sessionManager);
$applications[0]->start();

$applications[1] = new Application('app_02', $webappPath);
$applications[1]->injectSessionManager($sessionManager);
$applications[1]->start();

$server = new Server($applications);
$server->start();
$server->join();
