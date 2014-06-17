<?php

namespace TechDivision\ApplicationServerMiniMe;

class Application extends Thread
{

    protected $name;
    protected $webappPath;
    protected $sessionManager;
    protected $servlet;
    protected $run;

    public function __construct($name, $webappPath)
    {
        $this->name = $name;
        $this->webappPath = $webappPath;
        $this->run = true;
    }

    public function injectSessionManager($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    public function lookup()
    {

        $name = $this->name;
        $webappPath = $this->webappPath;

        error_log("Now lookup servlet for app $name");

        require_once $webappPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        return $this->servlet;
    }

    public function run()
    {

        $name = $this->name;
        $webappPath = $this->webappPath;

        require_once $webappPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        $this->servlet = new Servlet(10000);

        while ($this->run) {
            $this->wait();
        }
    }
}