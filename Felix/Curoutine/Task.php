<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/8/3
 * Time: 下午12:02
 */
class Task
{
    public $container;

    protected $taskId;

    protected $coStack;

    protected $coroutine;

    protected $exception = null;

    protected $sendValue = null;

    /**
     * @param int $taskId
     * @param obj $container
     * @param obj Generator $coroutine
     */
    public function __construct($taskId, $container, \Generator $coroutine)
    {
        $this->taskId = $taskId;
        $this->container = $container;
        $this->coroutine = $coroutine;
        $this->coStack = new \SplStack();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * 获取task id
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * setException  设置异常处理
     * @param $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * 协程调度
     */
    public function run()
    {
        while (true) {
            try {
                if ($this->exception) {
                    $this->coroutine->throw($this->exception);
                    $this->exception = null;
                    continue;
                }

                $value = $this->coroutine->current();

                //如果是coroutine，入栈
                if ($value instanceof \Generator) {
                    $this->coStack->push($this->coroutine);
                    $this->coroutine = $value;
                    continue;
                }

                //如果为null，而且栈不为空，出栈
                if (is_null($value) && !$this->coStack->isEmpty()) {
                    $this->coroutine = $this->coStack->pop();
                    $this->coroutine->send($this->sendValue);
                    continue;
                }

                //如果是系统调用
//                if ($value instanceof SysCall || is_subclass_of($value, SysCall::class)) {
//                    call_user_func($value, $this);
//                    return;
//                }
//
//                //如果是异步IO
//                if ($value instanceof \Group\Async\Client\Base || is_subclass_of($value, \Group\Async\Client\Base::class)) {
//                    $this->coStack->push($this->coroutine);
//                    $value->call(array($this, 'callback'));
//                    return;
//                }

                if ($this->coStack->isEmpty()) {
                    return;
                }

                $this->coroutine = $this->coStack->pop();
                $this->coroutine->send($value);
                //\Log::info($this->taskId.__METHOD__ . " values  pop and send", [__CLASS__]);

            } catch (\Exception $e) {
                if ($this->coStack->isEmpty()) {
//                    $swooleHttpResponse = $this->container->getSwooleResponse();
//                    if ($swooleHttpResponse) {
//                        $exception = new \Group\Handlers\ExceptionsHandler($this->container);
//                        $error = $exception->handleException($e);
//                        $response = new \Response($error, 500);
//                        $swooleHttpResponse->status($response->getStatusCode());
//                        $swooleHttpResponse->end($response->getContent());
//                        return;
//                    } else {
//                        //此时上层已经无法catch了
//                        throw $e;
//                    }
                }

                $this->coroutine = $this->coStack->pop();
                $this->exception = $e;
            }
        }
    }

    public function callback($response, $error = null, $calltime = 0)
    {
        $this->coroutine = $this->coStack->pop();
        $callbackData = array('response' => $response, 'error' => $error, 'calltime' => $calltime);
        $this->send($callbackData);
        $this->run();
    }

    public function send($sendValue) {
        $this->sendValue = $sendValue;
        return $this->coroutine->send($sendValue);
    }

    public function isFinished()
    {
        return !$this->coroutine->valid();
    }

    public function getCoroutine()
    {
        return $this->coroutine;
    }
}
