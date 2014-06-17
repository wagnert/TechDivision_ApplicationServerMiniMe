<?php

/**
 * TechDivision\ApplicationServerMiniMe\Application
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
 * Dummy application implementation for testing purposes.
 *
 * @category  Appserver
 * @package   TechDivision_ApplicationServerMiniMe
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 * @link      http://github.com/techdivision/TechDivision_ApplicationServerMiniMe
 */
class Application extends \Thread
{

    /**
     * Flag to keep application running.
     *
     * @var boolean
     */
    protected $run;

    /**
     * The dummy application name.
     *
     * @var string
     */
    protected $name;

    /**
     * The dummy application webapp path.
     *
     * @var string
     */
    protected $webappPath;

    /**
     * The session manager we want to use.
     *
     * @var \TechDivision\ServletEngine\SessionManager
     */
    protected $sessionManager;

    /**
     * The servlet we want to use.
     *
     * @var \TechDivision\Servlet\Servlet
     */
    protected $servlet;

    /**
     * Initializes the dummy application with the name and webapp path.
     *
     * @param string $name       The dummy application name
     * @param string $webappPath The dummy application webapp path
     *
     * @return void
     */
    public function __construct($name, $webappPath)
    {
        $this->name = $name;
        $this->webappPath = $webappPath;
        $this->run = true;
    }

    /**
     * Inject the session manager we want to use.
     *
     * @param \TechDivision\ServletEngine\SessionManager $sessionManager The session manager we want to use
     *
     * @return void
     */
    public function injectSessionManager($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Returns the session manager we use.
     *
     * @return \TechDivision\ServletEngine\SessionManager The session manager we use
     */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * Simulates the lookup for a servlet.
     *
     * @return \TechDivision\Servlet\Servlet The servlet we're looking for
     */
    public function lookup()
    {

        // create local references of instances we need to handle the request
        $name = $this->name;
        $webappPath = $this->webappPath;

        // log a message for which application we're looking up the servlet
        error_log("Now lookup servlet for app $name");

        // include the servlet definition
        require_once $webappPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        // return the servlet instance
        return $this->servlet;
    }

    /**
     * The applications run() method that creates the context we need to instanciate
     * servlets and register application specific autoloading functionality.
     *
     * @return void
     */
    public function run()
    {

        // create local references of instances we need to handle the request
        $name = $this->name;
        $webappPath = $this->webappPath;

        // include the servlet definition
        require_once $webappPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'Servlet.php';

        // initialize the servlet instance
        $this->servlet = new Servlet(10000);

        // we run forever
        while ($this->run) {
            $this->wait();
        }
    }
}
