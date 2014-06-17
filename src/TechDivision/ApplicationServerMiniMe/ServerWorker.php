<?php

/**
 * TechDivision\ApplicationServerMiniMe\ServerWorker
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
 * Dummy worker implementation for testing purposes.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */
class ServerWorker extends \Thread
{

    /**
     * The master socket we listen to.
     *
     * @return resource
     */
    protected $socket;

    /**
     * The array with the request handlers.
     *
     * @return array
     */
    protected $handlers;

    /**
     * The flag if we've finished request processing.
     *
     * @return boolean
     */
    protected $shouldRestart;

    /**
     * Initializes the server with the deployed dummy applications.
     *
     * @param resource $socket   The applications we want to deploy
     * @param array    $handlers The applications we want to deploy
     *
     * @return void
     */
    public function __construct($socket, $handlers)
    {
        // initialize the socket/handlers
        $this->socket = $socket;
        $this->handlers = $handlers;

        // we don't want to restart now
        $this->shouldRestart = false;
    }

    /**
     * The workers run() method that handles the requests if a new
     * incoming connection has been accepted.
     *
     * @return void
     */
    public function run()
    {

        // initialize the autoloader for this thread
        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        // create local references of instances we need to handle the request
        $socket = $this->socket;
        $handlers = $this->handlers;

        // we handle 100 requests before we stop working
        $handle = 0;
        while ($handle < 100) {

            // wait for a incoming client connection
            $client = stream_socket_accept($socket);

            // if we have a new connection
            if (is_resource($client)) {

                // declare variables
                $line = '';
                $messageHeaders = '';

                // simulate request handling
                $startLine = fgets($client);
                while ($line != "\r\n") {
                    $line = fgets($client);
                    $messageHeaders .= $line;
                }

                // we wan't to know who we are talking with
                list ($address, $port) = explode(':', stream_socket_get_name($client, true));

                // create a new request instance
                $request = new Request();
                $request->address = $address;
                $request->port = $port;

                // create a new response instance
                $response = new Response();

                // load the handler
                $handler = $handlers[rand(0, sizeof($handlers) - 1)];
                $handler->handleRequest($this, $request, $response);

                // wait until request has been handled
                $this->wait();

                // write the response back to the client
                fwrite($client, $response->head);
                fwrite($client, "\r\n\r\n");
                fwrite($client, $response->body);

                // shutdown the client
                stream_socket_shutdown($client, STREAM_SHUT_RDWR);

                // raise the request counter
                $handle++;
            }
        }

        // we've finished request handling now
        $this->shouldRestart = true;

        // write a message to the console
        echo 'FINISHED worker ' . $this->getThreadId() . PHP_EOL;
    }

    /**
     * Returns the flag that this worker has to be restarted now.
     *
     * @return boolean TRUE if this worker has been finished, else FALSE
     */
    public function shouldRestart()
    {
        return $this->shouldRestart;
    }
}
