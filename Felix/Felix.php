<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 9:37
 */
require_once __DIR__ . '/Loader.php';
class Felix{

    static public $felix;
    static public $app_path;
    protected $config=array();
    protected $http_server;
    public  $redis;
    protected $tag=false;
    /**
     * 初始化
     * @return Swoole
     */
    static function getInstance()
    {
        if (!self::$felix)
        {
            self::$felix = new Felix;
        }
        return self::$felix;
    }

    private function __construct()
    {
        if (!defined('DEBUG')) define('DEBUG', 'on');
        if (defined('WEBPATH'))
        {
            self::$app_path = WEBPATH . '/apps';
        }else{
            self::$app_path = __DIR__ . '/../apps';
        }
        define('APPSPATH', self::$app_path);
        Felix\Loader::addNameSpace('App', self::$app_path . '/');

    }

    /**
     * 压缩内容
     * @return null
     */
    function gzip()
    {
        //不要在文件中加入UTF-8 BOM头
        //ob_end_clean();
        ob_start("ob_gzhandler");
        #是否开启压缩
        if (function_exists('ob_gzhandler'))
        {
            ob_start('ob_gzhandler');
        }
        else
        {
            ob_start();
        }
    }
    //路由处理
    private function processRoute($server,$routeConfig,$webserverConfig){
        $info=$server->currentRequest;
        var_dump($info);
        $fhandler=new Felix\Handler;
        $fhandler->init($server,$webserverConfig);
        if(!empty($routeConfig)){
            $url=strtolower($info->meta['path']);
            if(!isset($routeConfig[$url])){
                //判断是否为静态文件
                if($fhandler->doStaticRequest($info))
                {
                    return $fhandler->response();
                }else{
                    $fhandler->httpError(404);
                    return $this->tag;
                }

            }
            $class_name=$routeConfig[$url];

            $handlerFile=self::$app_path . '/Handler/' . $class_name . '.php';
            var_dump($handlerFile);
            //判断是否为动态处理文件
            if(is_file($handlerFile)){
                $fclass='App\Handler\\'.$class_name;
                $handler=new $fclass;
                $handler->beforeAction($info,$webserverConfig);
                $server->setOnTask($handler->onTask);
               // $handler->init($server);
                if($info->meta['method']=="GET"){
                    $handler->get();
                }elseif($info->meta['method']=="POST"){
                    $handler->post();
                }
                $this->tag=true;
            }else{
                $fhandler->httpError(404);
            }
        }else{
            $fhandler->httpError(404);
        }

        return $this->tag;
    }

    //运行http服务
    function runHttpServer($config = array(),$host = '0.0.0.0', $port = 9898)
    {
            $this->config=$config;
            //初始化数据库连接
            Felix\Database\MysqlDb::init($config['mysql']);
            $this->http_server=new Felix\Service\HttpService($config["swoole_server"]);
            $this->http_server->onRequest(function($server){
                 $this->processRoute($server,$this->config["route"],$this->config["web_server"]);
            });
            $this->http_server->run($host,$port);
    }

}