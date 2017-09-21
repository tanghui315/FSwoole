<?php

namespace Felix\Async\Pool;

use Felix\Async\Pool\Pool;
use Felix\Async\Pool\Result;
use splQueue;

class MysqlPool extends Pool
{
    //splQueue
    protected $poolQueue;

    //splQueue
    protected $taskQueue;

    //最大连接数
    protected $maxPool = 50;

    //配置
    protected $config;

    //连接池资源
    protected $resources = [];

    protected $ableCount = 0;

    protected $timeout = 5;

    public function __construct($config)
    {
        $this->poolQueue = new splQueue();
        $this->taskQueue = new splQueue();
        $this->config=$config['database']['default'];
        $this->maxPool = $this->config['maxPool'];
        $this->timeout = $this->config['timeout'];

        $this->createResources();
    }

    //初始化连接数
    public function createResources()
    {
        $this->config = [
            'host' => $this->config['hostname'],
            'port' => $this->config['port'],
            'user' => $this->config['username'],
            'password' => $this->config['password'],
            'database' => $this->config['database'],
            'charset' => $this->config['char_set'],
            'timeout' => $this->timeout,
        ];

        for ($i = $this->ableCount; $i < $this->maxPool; $i++) { 
            $mysql = new \Swoole\MySQL;
            $mysql->connect($this->config, function(\Swoole\MySQL $mysql, $res) {
                if ($res) {
                    $this->put($mysql);
                } else {
                    $this->ableCount--;
                }
            });
            $this->ableCount++;
        }
    }

    public function doTask()
    {
        $resource = false;
        while (!$this->poolQueue->isEmpty()) {
            $resource = $this->poolQueue->dequeue();
            if (!isset($this->resources[spl_object_hash($resource)])) {
                $resource = false;
                continue;
            }
        }

        if (!$resource) {
            return;
        }

        //mysql连接超时了
        if ($resource->connected === false) {
            $this->remove($resource);
            return;
        }

        $task = $this->taskQueue->dequeue();
        $methd = $task['methd'];
        $callback = $task['callback'];
        $resource->$methd($task['parameters'], function(\Swoole\MySQL $mysql, $res) use ($callback) {
            if ($res === false) {
                call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                $this->release($mysql);
                return;
            }
            $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
            call_user_func_array($callback, array('response' => $result));
            //释放资源
            $this->release($mysql);
        });
    }

    /**
     * 关闭连接池
     */
    public function close()
    {
        foreach ($this->resources as $conn)
        {
            if ($conn->connected) {
                $conn->close();
            }
        }
    }
}
