<?php

namespace Felix\Async\Client;


abstract class Base 
{   
    public function __construct($ip, $port, $data, $timeout)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->data = $data;
        $this->timeout = $timeout;
    }

    public function call(callable $callback) {}
}
