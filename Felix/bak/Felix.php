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
    protected $current_handler;
    protected $tag=false;
    public $DB;
    public $smarty;
    /**
     * 初始化
     * @return Felix
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
        Felix\Loader::addNameSpace('app', self::$app_path . '/');

    }

    //路由处理
    private function processRouteAction($server){
        $info=$server->currentRequest;
        $fhandler=new Felix\Handler;
        $fhandler->init($server,$this->config);
        $url=strtolower($info->meta['path']);
        $handlerAction="";
        $fclass="";
        if($url == "/"){ //首页处理
            $handlerFile=self::$app_path . '/handler/IndexHandler.php';
            if(!is_file($handlerFile)){
                $fhandler->httpError(404);
                $this->tag=false;
                return $this->tag;
            }
            $fclass='app\handler\\'."IndexHandler";
            $handlerAction="indexAction";
        }else{
            //是否为静态文件
            if($fhandler->doStaticRequest($info))
            {
                return $fhandler->response();
            }
            //动态路由处理
            $path = explode('/', trim($info->meta['path'], '/'));
            //print_r($path);
            if(count($path)<3){ //证明不是模块
                $cword=ucfirst($path[0]);
                $fclass='app\handler\\'."{$cword}Handler";
                $handlerFile=self::$app_path."/handler/{$cword}Handler.php";
                if(!is_file($handlerFile)){
                    $fhandler->httpError(404);
                    $this->tag=false;
                    return $this->tag;
                }
                if(!isset($path[1])){
                    $handlerAction="indexAction";
                }else {
                    $class_reflect = new ReflectionClass($fclass);
                    $action_name = strtolower($path[1] . "action");
                    foreach ($class_reflect->getMethods() as $method) {
                        $cMName = $method->getName();
                        $tmpName = strtolower($cMName);
                        if ($action_name == $tmpName) {
                            $handlerAction = $cMName;
                        }
                    }
                }
            }else{ //模块处理
                //0  是flag 标记
                $modName=strtolower($path[1]);
                $cword=ucfirst($path[2]);
                $fclass="app\\modules\\{$modName}\\"."{$cword}Handler";
                $handlerFile=self::$app_path."/modules/{$modName}/{$cword}Handler.php";
                if(!is_file($handlerFile)){
                    $fhandler->httpError(404);
                    $this->tag=false;
                    return $this->tag;
                }
                if(!isset($path[3])){
                    $handlerAction="indexAction";
                }else{
                    $class_reflect = new ReflectionClass($fclass);
                    $action_name=strtolower($path[3]."action");
                    foreach($class_reflect->getMethods() as $method){
                        $cMName=$method->getName();
                        $tmpName=strtolower($cMName);
                        if($action_name == $tmpName){
                            $handlerAction=$cMName;
                        }
                    }
                }
            }
        }
        if(!empty($handlerAction)){
            $handler=new $fclass;
            $handler->setLogger($server->log);
            $handler->smarty=$this->smarty;
            $handler->beforeAction($info,$this->config);
            $handler->$handlerAction();
            $this->tag=true;
        }else{
            $fhandler->httpError(404);
            $this->tag=false;
        }

        return $this->tag;


    }

    //运行http服务
    function runHttpServer($config = array(),$host = '0.0.0.0', $port = 9889)
    {
            $this->config=$config;

            if($config['redis']['enabled']){
                Felix\Database\FRedis::init($config['redis']);
            }
            //初始化模版引擎
            $this->smarty = new Smarty;
            $this->smarty->debugging = true;
            $this->smarty->caching = true;
            $this->smarty->cache_lifetime = 120;
            $this->smarty->template_dir = WEBPATH.'/apps/templates/';
            $this->smarty->compile_dir = WEBPATH.'/apps/templates/templates_c/';
            $this->smarty->cache_dir = WEBPATH.'/apps/templates//cache/';
            //启动http服务
            define('FELIX_SERVER', true);
//            $this->http_server=new Felix\Service\HttpService($config);
//            $this->http_server->setLogger(new \Felix\Log\FileLog($config["log"]));
//            $this->http_server->onRequest(function($server){
//                 $this->processRouteAction($server);
//            });
            $this->http_server=new Felix\Service\HttpServ($this->config);
            $this->http_server->smarty=$this->smarty;
            $this->http_server->setLogger(new \Felix\Log\FileLog($config["log"]));
            $this->http_server->run($host,$port);
    }

}