<?php

/**
 * TechDivision\ApplicationServerMiniMe\Servlet
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
 * Dummy servlet implementation for testing purposes.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */
class Servlet
{

    /**
     * The number of session we plan to create.
     *
     * @var integer
     */
    protected $offset;

    /**
     * Initializes the servlet with the number of sessions to be created.
     *
     * @param integer $offset The number of session we plan to create
     *
     * @return void
     */
    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Initializes the servlet with the number of sessions to be created.
     *
     * @param \TechDivision\ApplicationServerMiniMe\Request  $request  The request instance we want to handle
     * @param \TechDivision\ApplicationServerMiniMe\Response $response The response we can write to
     *
     * @return void
     */
    public function service($request, $response)
    {

        // log a message
        error_log("Now handle request with servlet " . __FILE__);

        // create a local reference to the session manager
        $sessionManager = $request->sessionManager;

        // simulate session handling
        $id = md5(rand(0, $this->offset));
        if ($session = $sessionManager->find($id)) {
            echo "FOUND session $id" . PHP_EOL;
            $session->putData('requests', rand(0, $this->offset));
        } else {
            $session = $sessionManager->create($id, 'test_session');
            $session->start();
            $session->putData('username', 'appsever');
            echo "CREATED session with $id" . PHP_EOL;
        }

        // prepare the response
        $body = $response->body;
        $body[] = "<html>";
        $body[] = "<head>";
        $body[] = "<title>Multithread Sockets PHP ({$request->address}:{$request->port})</title>";
        $body[] = "</head>";
        $body[] = "<body>";
        $body[] = "<pre>";
        $body[] = "Session-ID: $id";
        $body[] = "</pre>";
        $body[] = "</body>";
        $body[] = "</html>";
        $implodedBody = implode("\r\n", $body);
        $response->body = $implodedBody;

        // prepare the headers
        $head = $response->head;
        $head[] = sprintf("Content-Length: %d", strlen($implodedBody));
        $implodedHead = implode("\r\n", $head);
        $response->head = $implodedHead;
    }
}
