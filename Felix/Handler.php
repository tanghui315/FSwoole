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

    public $head;
    public $cookie;
    public $body;

    public $http_protocol = 'HTTP/1.1';
    public $http_status = 200;
    public $request;
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

    //请求开始
    function beforeAction($request,$config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    //请求结束
    function afterAction()
    {
        if (!self::$keepalive or $this->head['Connection'] == 'close')
        {
            self::$serv->close($this->request->fd);
        }
        $this->request->unsetGlobal();
        //清空request缓存区
       // unset($this->requests[$request->fd]);
        unset($request);
    }

    /**
     * 设置Http头信息
     * @param $key
     * @param $value
     */
    function setHeader($key,$value)
    {
        $this->head[$key] = $value;
    }


    /**
     * 添加http header
     * @param $header
     */
    function addHeaders(array $header)
    {
        $this->head = array_merge($this->head, $header);
    }

    function getHeader($fastcgi = false)
    {
        $out = '';
        if ($fastcgi)
        {
            $out .= 'Status: '.$this->http_status.' '.self::$HTTP_HEADERS[$this->http_status]."\r\n";
        }
        else
        {
            //Protocol
            if (isset($this->head[0]))
            {
                $out .= $this->head[0]."\r\n";
                unset($this->head[0]);
            }
            else
            {
                $out = "HTTP/1.1 200 OK\r\n";
            }
        }
        //fill header
        if (!isset($this->head['Server']))
        {
            $this->head['Server'] = "felix-2.0";
        }
        if (!isset($this->head['Content-Type']))
        {
            $this->head['Content-Type'] = 'text/html; charset='.$this->charset;
        }
        if (!isset($this->head['Content-Length']))
        {
            $this->head['Content-Length'] = strlen($this->body);
        }
        //Headers
        foreach($this->head as $k=>$v)
        {
            $out .= $k.': '.$v."\r\n";
        }
        //Cookies
        if (!empty($this->cookie) and is_array($this->cookie))
        {
            foreach($this->cookie as $v)
            {
                $out .= "Set-Cookie: $v\r\n";
            }
        }
        //End
        $out .= "\r\n";
        return $out;
    }

    function noCache()
    {
        $this->head['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
        $this->head['Pragma'] = 'no-cache';
    }

    /**
     * 设置COOKIE
     * @param $name
     * @param null $value
     * @param null $expire
     * @param string $path
     * @param null $domain
     * @param null $secure
     * @param null $httponly
     */
    function setcookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null)
    {
        if ($value == null)
        {
            $value = 'deleted';
        }
        $cookie = "$name=$value";
        if ($expire)
        {
            $cookie .= "; expires=" . date("D, d-M-Y H:i:s T", $expire);
        }
        if ($path)
        {
            $cookie .= "; path=$path";
        }
        if ($secure)
        {
            $cookie .= "; secure";
        }
        if ($domain)
        {
            $cookie .= "; domain=$domain";
        }
        if ($httponly)
        {
            $cookie .= '; httponly';
        }
        $this->cookie[] = $cookie;
    }

    function setHttpStatus($code)
    {
        $this->head[0] = $this->http_protocol.' '.self::$HTTP_HEADERS[$code];
        $this->http_status = $code;
    }

    function doStaticRequest($request)
    {
        $path = explode('/', trim($request->meta['path'], '/'));
        //扩展名
        $request->ext_name = $ext_name = \Felix\Helper::getFileExt($request->meta['path']);

        /* 是否静态目录 */
        if (isset(self::$static_dir[$path[0]]) or isset(self::$static_dir[$ext_name]))
        {
            return $this->processStatic($request);
        }
        return false;
    }


    /**
     * 处理静态请求
     */
    function processStatic($request)
    {
        $path = self::$document_root . $request->meta['path'];
        if (is_file($path))
        {
            $read_file = true;
            if (self::$expire)
            {
                $expire = intval(isset($this->config['expire_time'])?$this->config['expire_time']:1800);
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
            $ext_name = \Felix\Helper::getFileExt($request->meta['path']);
            if($read_file)
            {
                $this->head['Content-Type'] = $this->mime_types[$ext_name];
                $this->body = file_get_contents($path);
            }else{
                //校验头
                $this->head['Content-Type'] = $this->mime_types[$ext_name];
            }
            return true;
        }
        else
        {
            return false;
        }
    }
  /*  function response($respData, $code = 200)
    {

        $headerInfo['Content-Type'] = 'text/html; charset='.$this->charset;

        $response = implode("\r\n", array(
            'HTTP/1.1 '.$code.' '.$this->HttpStatus[$code],
            'Cache-Control: must-revalidate,no-cache',
            'Content-Language: zh-CN',
            'Server: felix-2.0',
            'Content-Type: '.$headerInfo['Content-Type'],
            'Content-Length: ' . strlen($respData),
            '',
            $respData));
       // var_dump($this->serv);
//        var_dump($this->currentFd);
//        return false;
        $ret=self::$serv->send(self::$currentFd, $response);
        self::$serv->close(self::$currentFd);
        return $ret;
    }*/

    function response($content="")
    {
        if(!empty($content)){
            $this->body=$content;
        }

        if (!isset($this->head['Date']))
        {
            $this->head['Date'] = gmdate("D, d M Y H:i:s T");
        }
        if (!isset($this->head['Connection']))
        {
            //keepalive
            if (self::$keepalive and (isset($this->request->header['Connection']) and strtolower($this->request->header['Connection']) == 'keep-alive'))
            {
                $this->head['KeepAlive'] = 'on';
                $this->head['Connection'] = 'keep-alive';
            }
            else
            {
                $this->head['KeepAlive'] = 'off';
                $this->head['Connection'] = 'close';
            }
        }

        //过期命中
        if (self::$expire and $this->http_status == 304)
        {
            $out = $this->getHeader();
            return self::$serv->send(self::$currentFd, $out);
        }

        //压缩
        if (self::$gzip)
        {
            $this->head['Content-Encoding'] = 'deflate';
            $this->body = \gzdeflate($this->body, isset($this->config['gzip_level'])?$this->config['gzip_level']:1);
        }
       //var_dump($this->getHeader());
        $out = $this->getHeader().$this->body;
        $ret = self::$serv->send(self::$currentFd, $out);
        $this->head['Connection']="close";
        $this->afterAction();
        return $ret;

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

    function onTask($serv,$task_id,$from_id,$data)
    {
        //任务处理
    }


}