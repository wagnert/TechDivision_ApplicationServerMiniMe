<?php

namespace TechDivision\ApplicationServerMiniMe;

class Server extends \Thread
{

    protected $applications;

    public function __construct($applications)
    {
        $this->applications = $applications;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = stream_socket_server("tcp://0.0.0.0:8111", $errno, $errstr);

        $applications = $this->applications;
        $workers = array();
        $handlers = array();

        for ($i = 0; $i < 100; $i++) {

            foreach ($applications as $application) {
                $handlers[$i][] = new RequestHandler($application);
            }

            $workers[$i] = new ServerWorker($socket, $handlers[$i]);
            $workers[$i]->start();
        }

        while (true) {

            for ($i = 0; $i < 100; $i++) {

                if ($workers[$i]->shouldRestart()) {

                    unset($workers[$i]);
                    unset($handlers[$i]);

                    echo 'RESTART worker ...' . PHP_EOL;

                    foreach ($applications as $application) {
                        $handlers[$i][] = new RequestHandler($application);
                    }

                    $workers[$i] = new ServerWorker($socket, $handlers);
                    $workers[$i]->start();

                    echo 'RESTARTED worker ' . $workers[$i]->getThreadId() . PHP_EOL;
                }
            }
        }
    }
}