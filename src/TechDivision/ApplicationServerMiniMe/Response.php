<?php

namespace TechDivision\ApplicationServerMiniMe;

class Response extends Stackable
{

    public function __construct()
    {
        $this->head = array("HTTP/1.0 200 OK", "Content-Type: text/html", "Connection: close");
        $this->body = array();
    }
}