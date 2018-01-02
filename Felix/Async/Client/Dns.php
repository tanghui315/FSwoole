<?php

namespace Felix\Async\Client;

class Dns extends Base
{
    protected $domain;

    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    public function call(callable $callback)
    {
        swoole_async_dns_lookup($this->domain, function($host, $ip) use ($callback) {
            call_user_func_array($callback, array('response' => ['host' => $host, 'ip' => $ip], 'error' => null, 'calltime' => 0));
        });
    }
}