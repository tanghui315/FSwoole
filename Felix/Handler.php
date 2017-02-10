<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/11/7
 * Time: 11:14
 */

namespace Felix;

class Handler{
    protected $charset = 'utf-8';
    protected $HttpStatus = array(
        200 => 'OK',
        404 => 'Not Found',
    );
    const DATE_FORMAT_HTTP = 'D, d-M-Y H:i:s T';
    static $serv;
    static $currentFd;
    static $keepalive=false;
    static $static_dir;
    static $static_ext;
    static $document_root;
    static $gzip=false;
    static $expire=false;
    public $config=[];
    public $query_builder =true;  //启用查询建立
    public $head;
    public $cookie;
    public $body;
    public $log;
    public $smarty;
    public $db;
    public $http_protocol = 'HTTP/1.1';
    public $http_status = 200;
    public $request;
    public $response;
    static $HTTP_HEADERS = array(
        100 => "100 Continue",
        101 => "101 Switching Protocols",
        200 => "200 OK",
        201 => "201 Created",
        204 => "204 No Content",
        206 => "206 Partial Content",
        300 => "300 Multiple Choices",
        301 => "301 Moved Permanently",
        302 => "302 Found",
        303 => "303 See Other",
        304 => "304 Not Modified",
        307 => "307 Temporary Redirect",
        400 => "400 Bad Request",
        401 => "401 Unauthorized",
        403 => "403 Forbidden",
        404 => "404 Not Found",
        405 => "405 Method Not Allowed",
        406 => "406 Not Acceptable",
        408 => "408 Request Timeout",
        410 => "410 Gone",
        413 => "413 Request Entity Too Large",
        414 => "414 Request URI Too Long",
        415 => "415 Unsupported Media Type",
        416 => "416 Requested Range Not Satisfiable",
        417 => "417 Expectation Failed",
        500 => "500 Internal Server Error",
        501 => "501 Method Not Implemented",
        503 => "503 Service Unavailable",
        506 => "506 Variant Also Negotiates",
    );

    public $mime_types = array(
        'jpg' => 'image/jpeg',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bin' => 'application/octet-stream',
        'js' => 'application/javascript',
        'css' => 'text/css',
        'html' => 'text/html',
        'xml' => 'text/xml',
        'tar' => 'application/x-tar',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pdf' => 'application/pdf',
        'swf' => 'application/x-shockwave-flash',
        'zip' => 'application/x-zip-compressed',
        'gzip' => 'papplication/gzip',
        'woff' => 'application/x-woff',
        'svg' => 'image/svg+xml',
    );

    function init($server,$config)
    {
        $config=$config['web_server'];
        self::$serv=$server->serv;
        self::$currentFd=$server->currentFd;
        if(isset($config['document_root'])){
            self::$document_root=$config['document_root'];
        }
        if(isset($config['keepalive'])){
            self::$keepalive=true;
        }
        if(isset($config['gzip_open'])){
            self::$gzip=true;
        }
        if(isset($config['expire_open'])){
            self::$expire=true;
        }

        self::$static_dir = array_flip(explode(',', $config['static_dir']));
        self::$static_ext = array_flip(explode(',', $config['static_ext']));

    }

    function initE($server,$config)
    {
        $config=$config['web_server'];
        self::$serv=$server;
        if(isset($config['document_root'])){
            self::$document_root=$config['document_root'];
        }
        if(isset($config['keepalive'])){
            self::$keepalive=true;
        }
        if(isset($config['gzip_open'])){
            self::$gzip=true;
        }
        if(isset($config['expire_open'])){
            self::$expire=true;
        }

        self::$static_dir = array_flip(explode(',', $config['static_dir']));
        self::$static_ext = array_flip(explode(',', $config['static_ext']));

    }
    /**
     * 设置Logger
     * @param $log
     */
    function setLogger($log)
    {
        $this->log = $log;
    }

    //请求开始
    function beforeAction($request,$config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    //请求结束
    function afterAction()
    {

    }


    //异步任务
    function task($data){
        $taskData=array_merge($data,['handler'=>$this]);
        self::$serv->task($taskData);
    }


    function setcookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null)
    {
        $this->cookie[] = ['name'=>$name,'value'=>$value,'expire'=>$expire,'path'=>$path,'domain'=>$domain,'secure'=>$secure,'httponly'=>$httponly];
    }

    function setHttpStatus($code)
    {
        $this->head[0] = $this->http_protocol.' '.self::$HTTP_HEADERS[$code];
        $this->http_status = $code;
    }


    //静态文件处理 ，用于HttpServ
    function doStaticRequestE($request)
    {
        $path = explode('/', trim($request->server['path_info'], '/'));
        //print_r($path);
        //扩展名
        $request->ext_name = $ext_name = \Felix\Helper::getFileExt($request->server['path_info']);

        /* 是否静态目录 */
        if (isset(self::$static_dir[$path[0]]) or isset(self::$static_dir[$ext_name]))
        {
            $path = self::$document_root . $request->server['path_info'];
            echo $path;
            if (is_file($path))
            {
                $read_file = true;
                if (self::$expire)
                {
                    $expire = intval(isset($this->config['web_server']['expire_time'])?$this->config['web_server']['expire_time']:1800);
                    $fstat = stat($path);
                    //过期控制信息
                    if (isset($request->header['If-Modified-Since']))
                    {
                        $lastModifiedSince = strtotime($request->header['If-Modified-Since']);

                        if ($lastModifiedSince and $fstat['mtime'] <= $lastModifiedSince)
                        {
                            //不需要读文件了
                            $read_file = false;
                            $this->setHttpStatus(304);
                        }
                    }
                    else
                    {
                        $this->head['Cache-Control'] = "max-age={$expire}";
                        $this->head['Pragma'] = "max-age={$expire}";
                        $this->head['Last-Modified'] = date(self::DATE_FORMAT_HTTP, $fstat['mtime']);
                        $this->head['Expires'] = "max-age={$expire}";
                    }
                }
                $ext_name = \Felix\Helper::getFileExt($request->server['path_info']);
                if($read_file)
                {
                    $this->head['Content-Type'] = $this->mime_types[$ext_name];
                    $this->body = file_get_contents($path);
                }else{
                    //校验头
                    $this->head['Content-Type'] = $this->mime_types[$ext_name];
                }
                $this->response();
                return 1;
            }
            else
            {
                $this->httpError(404);
                return -1;
            }
        }

        return -2;

    }


    //加载数据库
    function loadDB($active_group="",$return = FALSE){

        $dataBaseConfig=$this->config['database'];
        require_once(BASEPATH.'Felix/Database/DB.php');

        if ($return === TRUE)
        {
            return DB($dataBaseConfig, $this->query_builder,$active_group);
        }

        $this->db=& DB($dataBaseConfig, $this->query_builder,$active_group);

    }

    // 加载模型

    function loadModel($name)
    {
        if(empty($name)){
            return false;
        }
        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($name, '/')) !== FALSE)
        {
            // The path is in front of the last slash
            $path = substr($name, 0, ++$last_slash);

            // And the model name behind it
            $name = substr($name, $last_slash);
        }

        if(empty($this->db))
        {
            $this->loadDB();
        }

        $model = ucfirst($name);
        $filePath =BASEPATH."apps/models/".$path.$model."Model.php";
        if(!file_exists($filePath)){
            log_message("modelErr",$path.$name." file not find");
            return false;
        }
        if(empty($path))
            $class="app\\models\\".$model."Model";
        else
            $class="app\\models\\".$path."\\". $model."Model";

        $this->$name = new $class();
        $this->$name->db=$this->db;
        return true;
    }


    function response($content="",$isjson=false)
    {
        if(!empty($content)){
            $this->body=$content;
        }
        if (!isset($this->head['Date']))
        {
            $this->response->header('Date',gmdate("D, d M Y H:i:s T"));
        }
        if($isjson)
        {
            $this->response->header('Content-Type','application/json;charset='.$this->charset);
            self::$gzip=false;
        }else{
            self::$gzip=true;
        }

        if (!isset($this->head['Connection']))
        {
            //keepalive
            if (self::$keepalive and (isset($this->request->header['connection']) and strtolower($this->request->header['connection']) == 'keep-alive'))
            {
                $this->response->header('KeepAlive','on');
                $this->response->header('Connection','keep-alive');
            }
            else
            {
                $this->response->header('KeepAlive','off');
                $this->response->header('Connection','close');
            }
        }

        $this->response->status($this->http_status);
        if (!isset($this->head['Server']))
        {
            $this->response->header('Server','felix-2.0');
        }
        if (!isset($this->head['Content-Type']))
        {
            $this->response->header('Content-Type','text/html; charset='.$this->charset);
        }else{
            $this->response->header('Content-Type',$this->head['Content-Type']);
        }

        if (!empty($this->cookie) and is_array($this->cookie))
        {
            foreach($this->cookie as $v)
            {
                $this->response->cookie($v['name'],$v['value'],$v['expire'],$v['path'],$v['domain'],$v['secure'],$v['httponly']);
            }
        }
        if (self::$gzip)
        {
           // $this->response->header('Content-Encoding','deflate');
            $this->response->gzip(1);
        }

        $this->response->end($this->body);
        $this->afterAction();
        return;

    }

    function httpError($code,$content=null)
    {
        $this->setHttpStatus($code);
        $this->head['Content-Type'] = 'text/html';
        if(empty($content))
        {
            $this->response("<h1>Page Not Found</h1><hr />Felix Web Server ");
        }else{
            $this->response($content);
        }

    }

    //模版呈现
    function render($src,$data)
    {
        $this->smarty->assign($data);
        $output=$this->smarty->fetch($src);
        $this->response($output);
    }

    function onTask($serv,$task_id,$from_id,$data)
    {
        //任务处理
    }


    function onFinish($serv,$task_id, $data) {
        //任务结束
    }



}