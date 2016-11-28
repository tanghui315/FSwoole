<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:23
 */
namespace Felix\Service;
use Felix;

class HttpService
{
    const HTTP_EOF = "\r\n\r\n";
    const ST_FINISH = 1; //完成，进入处理流程
    const ST_WAIT   = 2; //等待数据
    const ST_ERROR  = 3; //错误，丢弃此包
    const HTTP_HEAD_MAXLEN = 8192; //http头最大长度不得超过2k
    const POST_MAXSIZE=300000;
    protected $buffer_header = array();
    public  $currentRequest;
    public  $redis=null;
    protected $config = array(
        'dispatch_mode' => 3,
    );
    protected $_onRequest;
    protected $_onTask;
    public  $currentFd;
    protected $headerInfo;
    public $requests = array();
    /**
     * @var \swoole_server
     */
    public $serv;


    function __construct($config = array())
    {
        $this->config = $config;
        $this->parser = new Felix\Parser;
    }
    function setCharset($charset)
    {
        $this->charset = $charset;
    }

    function  onConnect($serv, $client_id, $from_id)
    {
        $this->currentFd=$client_id;
    }
    /**
     * @param \swoole_server $serv
     * @param $fd
     * @param $from_id
     */
    function onClose($serv, $client_id, $from_id)
    {
        $this->cleanBuffer($client_id);
        unset($this->requests[$client_id]);
    }

    function cleanBuffer($fd)
    {
        unset($this->requests[$fd], $this->buffer_header[$fd]);
    }


    function checkHeader($client_id, $http_data)
    {
        //新的连接
        if (!isset($this->requests[$client_id]))
        {
            if (!empty($this->buffer_header[$client_id]))
            {
                $http_data = $this->buffer_header[$client_id].$http_data;
            }
            //HTTP结束符
            $ret = strpos($http_data, self::HTTP_EOF);
            //没有找到EOF，继续等待数据
            if ($ret === false)
            {
                return false;
            }
            else
            {
                $this->buffer_header[$client_id] = '';
                $request = new Felix\Request;
                //GET没有body
                list($header, $request->body) = explode(self::HTTP_EOF, $http_data, 2);
                $request->header = \Felix\Parser::parseHeader($header);
                //使用head[0]保存额外的信息
                $request->meta = $request->header[0];
                unset($request->header[0]);
                //保存请求
                $this->requests[$client_id] = $request;
                //解析失败
                if ($request->header == false)
                {
                    print("parseHeader failed. header=".$header);
                    return false;
                }
            }
        }
        //POST请求需要合并数据
        else
        {
            $request = $this->requests[$client_id];
            $request->body .= $http_data;
        }
        return $request;
    }
    function checkPost(Felix\Request $request)
    {
        if (isset($request->header['Content-Length']))
        {
            //超过最大尺寸
            if (intval($request->header['Content-Length']) >self::POST_MAXSIZE)
            {
                print("checkPost failed. post_data is too long.");
                return self::ST_ERROR;
            }
            //不完整，继续等待数据
            if (intval($request->header['Content-Length']) > strlen($request->body))
            {
                return self::ST_WAIT;
            }
            //长度正确
            else
            {
                return self::ST_FINISH;
            }
        }
        print("checkPost fail. Not have Content-Length.");
        //POST请求没有Content-Length，丢弃此请求
        return self::ST_ERROR;
    }

    function checkData($client_id, $http_data)
    {
        if (isset($this->buffer_header[$client_id]))
        {
            $http_data = $this->buffer_header[$client_id].$http_data;
        }
        //检测头
        $request = $this->checkHeader($client_id, $http_data);
        //错误的http头
        if ($request === false)
        {
            $this->buffer_header[$client_id] = $http_data;
            //超过最大HTTP头限制了
            if (strlen($http_data) > self::HTTP_HEAD_MAXLEN)
            {
                print("http header is too long.");
                return self::ST_ERROR;
            }
            else
            {
                print("wait request data. fd={$client_id}");
                return self::ST_WAIT;
            }
        }
        //POST请求需要检测body是否完整
        if ($request->meta['method'] == 'POST')
        {
            return $this->checkPost($request);
        }
        //GET请求直接进入处理流程
        else
        {
            return self::ST_FINISH;
        }
    }

    /**
     * @param \swoole_server $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    function onReceive($serv, $client_id, $from_id, $data)
    {
        //检测request data完整性
        $ret = $this->checkData($client_id, $data);
        switch($ret)
        {
            //错误的请求
            case self::ST_ERROR;
                $this->server->close($client_id);
                return;
            //请求不完整，继续等待
            case self::ST_WAIT:
                return;
            default:
                break;
        }
        //完整的请求
        //开始处理


        $request = $this->requests[$client_id];

        $request->fd = $client_id;

        /**
         * Socket连接信息
         */
        $info = $serv->connection_info($client_id);
        $request->server['SWOOLE_CONNECTION_INFO'] = $info;
        $request->remote_ip = $info['remote_ip'];
        $request->remote_port = $info['remote_port'];
        /**
         * Server变量
         */
        $request->server['REQUEST_URI'] = $request->meta['uri'];
        $request->server['REMOTE_ADDR'] = $request->remote_ip;
        $request->server['REMOTE_PORT'] = $request->remote_port;
        $request->server['REQUEST_METHOD'] = $request->meta['method'];
        $request->server['REQUEST_TIME'] = $request->time;
        $request->server['SERVER_PROTOCOL'] = $request->meta['protocol'];
        if (!empty($request->meta['query']))
        {
            $_SERVER['QUERY_STRING'] = $request->meta['query'];
        }
        $this->parseRequest($request);
        $request->setGlobal();
        $this->currentRequest=$request;
        call_user_func($this->_onRequest, $this);

    }


    //异步任务
    function onTask($serv,$task_id,$from_id,$data)
    {
        call_user_func($this->_onTask,$serv,$task_id,$from_id,$data);
    }

    function setOnTask($callback)
    {
        $this->_onTask=$callback;
    }
    /**
     * 解析请求
     * @param $request
     * @return null
     */
    function parseRequest($request)
    {
        $url_info = parse_url($request->meta['uri']);
        $request->time = time();
        $request->meta['path'] = $url_info['path'];
        if (isset($url_info['fragment'])) $request->meta['fragment'] = $url_info['fragment'];
        if (isset($url_info['query']))
        {
            parse_str($url_info['query'], $request->get);
        }
        //POST请求,有http body
        if ($request->meta['method'] === 'POST')
        {
            $this->parser->parseBody($request);
        }
        //解析Cookies
        if (!empty($request->header['Cookie']))
        {
            $this->parser->parseCookie($request);
        }
    }

    function header($key, $value)
    {
        $this->headerInfo[$key] = $value;
    }

    static function parseCookie($strHeaders)
    {
        $result = array();
        $aHeaders = explode(';', $strHeaders);
        foreach ($aHeaders as $line)
        {
            list($k, $v) = explode('=', trim($line), 2);
            $result[$k] = urldecode($v);
        }
        return $result;
    }
    function onRequest($callback)
    {
        $this->_onRequest = $callback;
    }
    function config(array $config)
    {
        $this->config = $config;
    }
    function daemon()
    {
        $this->config['daemonize'] = 1;
    }
    function run($host = '0.0.0.0', $port = 9999)
    {
        register_shutdown_function(array($this, 'handleFatal'));
        $server = new \swoole_server($host, $port);
        $this->serv = $server;
        $server->on('Connect', array($this, 'onConnect'));
        $server->on('Receive', array($this, 'onReceive'));
        if(isset($this->config['task_worker_num'])){
            $server->on('Task',array($this,'onTask'));
        }
        $server->on('Close', array($this, 'onClose'));
        $server->set($this->config);
        $server->start();
    }

    /**
     * Fatal Error的捕获
     * @codeCoverageIgnore
     */
    public function handleFatal()
    {
        $error = error_get_last();
        if (!isset($error['type'])) return;
        switch ($error['type'])
        {
            case E_ERROR :
            case E_PARSE :
            case E_DEPRECATED:
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                break;
            default:
                return;
        }
        $message = $error['message'];
        $file = $error['file'];
        $line = $error['line'];
        $log = "$message ($file:$line)\nStack trace:\n";
        $trace = debug_backtrace();
        foreach ($trace as $i => $t)
        {
            if (!isset($t['file']))
            {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line']))
            {
                $t['line'] = 0;
            }
            if (!isset($t['function']))
            {
                $t['function'] = 'unknown';
            }
            $log .= "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) && is_object($t['object']))
            {
                $log .= get_class($t['object']) . '->';
            }
            $log .= "{$t['function']}()\n";
        }
        if (isset($_SERVER['REQUEST_URI']))
        {
            $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
        }
        error_log($log);
        //记录错误日志
        //$this->response($this->currentFd, $log);
    }

}
