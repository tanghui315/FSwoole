<?php

namespace Felix\Async;

use \Felix\Async\Client\Redis;
use \Felix\Async\Pool\RedisProxy;

class AsyncRedis
{   
    protected static $timeout = 1;

    protected static $usePool = true;

    public static function setTimeout($timeout)
    {
        self::$timeout = $timeout;
    }

    public static function enablePool($status)
    {
        self::$usePool = boolval($status);
    }

    /**
     * static call
     *
     * @param  method
     * @param  parameters
     * @return void
     */
    public static function __callStatic($method, $parameters)
    {
        $handler = (yield \Felix\Helper::getHandler());
        if (self::$usePool) {
            $pool = $handler->loadRedisPool();
            $redis = new RedisProxy($pool);
        } else {
            $timeout = self::$timeout;
            $redis = $handler->singleton('redis', function() use ($timeout) {
                $redis = new Redis();
                $redis->setTimeout($timeout);
                return $redis;
            });
            
        }

        $redis->setMethod($method);
        $redis->setParameters($parameters);
        $res = (yield $redis);
        if ($res && $res['response']) {
            yield $res['response'];
        }

        yield false;
    }
}
