<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/9/20
 * Time: 下午3:06
 */
namespace Felix;

class SysCall {

    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task)
    {
        return call_user_func($this->callback, $task);
    }
}