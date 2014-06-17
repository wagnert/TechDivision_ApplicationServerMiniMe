<?php

namespace TechDivision\ApplicationServerMiniMe;

class ServerWorker extends \Thread
{

    protected $socket;
    protected $handlers;
    protected $shouldRestart;

    public function __construct($socket, $handlers)
    {
        $this->socket = $socket;
        $this->handlers = $handlers;

        $this->shouldRestart = false;
    }

    public function run()
    {

        require APPSERVER_BP . '/app/code/vendor/autoload.php';

        $socket = $this->socket;
        $handlers = $this->handlers;

        $handle = 0;
        while ($handle < 100) {

            $client = stream_socket_accept($socket);

            if (is_resource($client)) {

                $line = '';

                $startLine = fgets($client);

                $messageHeaders = '';

                while ($line != "\r\n") {
                    $line = fgets($client);
                    $messageHeaders .= $line;
                }

                $request = new Request();

                list ($address, $port) = explode(':', stream_socket_get_name($client, true));

                $request->address = $address;
                $request->port = $port;

                $response = new Response();

                $handler = $handlers[rand(0, sizeof($handlers) - 1)];
                $handler->handleRequest($this, $request, $response);

                $this->wait();

                fwrite($client, $response->head);
                fwrite($client, "\r\n\r\n");
                fwrite($client, $response->body);

                stream_socket_shutdown($client, STREAM_SHUT_RDWR);

                $handle++;
            }
        }

        $this->shouldRestart = true;

        echo 'FINISHED worker ' . $this->getThreadId() . PHP_EOL;
    }

    public function shouldRestart()
    {
        return $this->shouldRestart;
    }
}