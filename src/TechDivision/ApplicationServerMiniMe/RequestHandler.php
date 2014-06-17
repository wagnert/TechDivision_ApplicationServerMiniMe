<?php

namespace TechDivision\ApplicationServerMiniMe;

class RequestHandler extends Thread
{

    protected $worker;
    protected $request;
    protected $response;
    protected $run;
    protected $application;

    public function __construct($application)
    {
        $this->run = true;
        $this->application = $application;
        $this->start();
    }

    protected function handleRequest($worker, $request, $response)
    {
        $this->worker = $worker;
        $this->request = $request;
        $this->response = $response;

        $this->notify();
    }

    public function run()
    {

        while ($this->run) {

            $this->wait();

            $worker = $this->worker;
            $application = $this->application;
            $request = $this->request;
            $response = $this->response;

            $sessionManager = $application->getSessionManager();

            $request->sessionManager = $sessionManager;

            $servlet = $application->lookup();
            $servlet->service($request, $response);

            $removedSessions = $sessionManager->collectGarbage();

            if ($removedSessions > 0) {
                echo 'REMOVED ' . $removedSessions . ' sessions [' . date('Y-m-d: H:i:s') . '] - Thread-ID: ' . $this->getThreadId() . PHP_EOL;
            }

            $worker->notify();
        }
    }
}