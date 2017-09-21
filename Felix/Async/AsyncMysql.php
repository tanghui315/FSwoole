<?php

namespace Felix\Async;

use Config;
use \Felix\Async\Pool\MysqlProxy;
use \Felix\Async\Client\Mysql;
use \Felix\SysCall;
use \Felix\Task;

class AsyncMysql
{   
    protected static $timeout = 1;

    protected static $userPool = true;

    public static function setTimeout($timeout)
    {
        self::$timeout = $timeout;
    }

   public static function getHandler() {
        return new SysCall(function(Task $task){
            $task->send($task->getHandler());
            $task->run();
        });
    }

    public static function query($sql, $userPool = true)
    {
        $handler = (yield self::getHandler());
        if ($userPool && self::$userPool) {
            $pool =$handler->loadMysqlPool();
            $mysql = new MysqlProxy($pool);
        } else {
            $timeout = self::$timeout;
            $mysql = $handler->singleton('mysql', function() use ($timeout,$handler) {
                $mysql = new Mysql($handler->config);
                $mysql->setTimeout($timeout);
                return $mysql;
            });
        }

        $mysql->query($sql);
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        }

        yield false;
    }

    public static function begin()
    {   
        self::$userPool = false;
        $res = (yield self::query('begin', false));
        yield $res;
    }

    public static function commit()
    {
        $res = (yield self::query('commit', false));
        self::$userPool = true;
        yield $res;
    }

    public static function rollback()
    {
        $res = (yield self::query('rollback', false));
        self::$userPool = true;
        yield $res;
    }
}
