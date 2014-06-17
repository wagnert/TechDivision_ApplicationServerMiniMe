<?php

/**
 * TechDivision\ApplicationServerMiniMe\RequestHandler
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
 * Dummy request handler implementation for testing purposes.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */
class RequestHandler extends \Thread
{

    /**
     * Flag to keep request handler running.
     *
     * @var boolean
     */
    protected $run;

    /**
     * The worker that processes the request we've to handle.
     *
     * @var \TechDivision\ApplicationServerMiniMe\ServerWorker
     */
    protected $serverWorker;

    /**
     * A request instance we've to handle.
     *
     * @var \TechDivision\ApplicationServerMiniMe\Request
     */
    protected $request;

    /**
     * A response instance we've to handle.
     *
     * @var \TechDivision\ApplicationServerMiniMe\Response
     */
    protected $response;

    /**
     * The application instance this handler is bound to.
     *
     * @var \TechDivision\ApplicationServerMiniMe\Application
     */
    protected $application;

    /**
     * Initializes the request handler with the dummy application instance.
     *
     * @param \TechDivision\ApplicationServerMiniMe\Application $application The dummy application instance
     *
     * @return void
     */
    public function __construct($application)
    {

        // initialize the members
        $this->run = true;
        $this->application = $application;

        // start the request handler
        $this->start();
    }

    /**
     * Initializes the request handler with the dummy application instance.
     *
     * @param \TechDivision\ApplicationServerMiniMe\ServerWorker $serverWorker The worker that processes the request we've to handle
     * @param \TechDivision\ApplicationServerMiniMe\Request      $request      The request instance we've to handle.
     * @param \TechDivision\ApplicationServerMiniMe\Response     $response     The response instance we've to handle.
     *
     * @return void
     */
    protected function handleRequest($serverWorker, $request, $response)
    {

        // prepare the request variables
        $this->serverWorker = $serverWorker;
        $this->request = $request;
        $this->response = $response;

        // handle the request
        $this->notify();
    }

    /**
     * The applications run() method that creates the context we need to instanciate
     * servlets and register application specific autoloading functionality.
     *
     * @return void
     */
    public function run()
    {

        // initialize the autoloader for this thread
        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        // handle requests
        while ($this->run) {

            // wait until a request has to be handled
            $this->wait();

            // create local references of instances we need to handle the request
            $serverWorker = $this->serverWorker;
            $application = $this->application;
            $request = $this->request;
            $response = $this->response;

            // load the session manager
            $sessionManager = $application->getSessionManager();

            // add the session manager to the request
            $request->sessionManager = $sessionManager;

            // load the servlet, pass request/response and handle the request
            $servlet = $application->lookup();
            $servlet->service($request, $response);

            // collect garbage
            $removedSessions = $sessionManager->collectGarbage();

            // we want to know how many sessions has been deleted
            if ($removedSessions > 0) {
                echo 'REMOVED ' . $removedSessions . ' sessions [' . date('Y-m-d: H:i:s') . '] - Thread-ID: ' . $this->getThreadId() . PHP_EOL;
            }

            // notify the worker that the request has been handled
            $serverWorker->notify();
        }
    }
}
