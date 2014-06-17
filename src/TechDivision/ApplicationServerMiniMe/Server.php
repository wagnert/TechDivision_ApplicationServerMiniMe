<?php

/**
 * TechDivision\ApplicationServerMiniMe\Server
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

namespace TechDivision\ApplicationServerMiniMe;

/**
 * Dummy server implementation for testing purposes.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */
class Server extends \Thread
{

    /**
     * Flag to keep the server running.
     *
     * @var boolean
     */
    protected $run;

    /**
     * The array with the deployed dummy applications.
     *
     * @var array
     */
    protected $applications;

    /**
     * Initializes the server with the deployed dummy applications.
     *
     * @param array $applications The applications we want to deploy
     *
     * @return void
     */
    public function __construct($applications)
    {
        $this->applications = $applications;
    }

    /**
     * The servers run() method that opens the main socket and initializes
     * the workers that handles the requests.
     *
     * @return void
     */
    public function run()
    {

        // initialize the autoloader for this thread
        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        // create the main socket
        $socket = stream_socket_server("tcp://0.0.0.0:8111", $errno, $errstr);

        // create a local reference the the deployed applications
        $applications = $this->applications;

        // declare variables for workers/handlers
        $workers = array();
        $handlers = array();

        // we want to start 100 workers here
        for ($i = 0; $i < 100; $i++) {

            // create a handler for each application
            foreach ($applications as $application) {
                $handlers[$i][] = new RequestHandler($application);
            }

            // create a worker and pass master socket and handlers
            $workers[$i] = new ServerWorker($socket, $handlers[$i]);
            $workers[$i]->start();
        }

        // we run forever and make sure that we've enough servers
        while ($this->run) {

            // iterate over all servers and check if one has to be restarted
            for ($i = 0; $i < 100; $i++) {

                // if a worker has to be restarted
                if ($workers[$i]->shouldRestart()) {

                    // unset the worker and it's handlers
                    unset($workers[$i]);
                    unset($handlers[$i]);

                    // log a message
                    echo 'RESTART worker ...' . PHP_EOL;

                    // create a handler for each application
                    foreach ($applications as $application) {
                        $handlers[$i][] = new RequestHandler($application);
                    }

                    // create a worker and pass master socket and handlers
                    $workers[$i] = new ServerWorker($socket, $handlers);
                    $workers[$i]->start();

                    // log a message
                    echo 'RESTARTED worker ' . $workers[$i]->getThreadId() . PHP_EOL;
                }
            }

            // sleep for a second to reduce system load
            sleep(1);
        }
    }
}
