<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 9:37
 */
require_once __DIR__ . '/Loader.php';
class Felix{

    public $app_path;
    public $config=[];
    protected $http_server;
    protected $ws_server;
    public  $redis;
    protected $current_handler;
    protected $tag=false;
    public $DB;
    public $smarty;
    private static $instance;
    public $instances;

    protected $onWorkStartServices = [
        'mysqlPool'=>'\Felix\Async\Pool\MysqlPool',
        //'redisPool'=>'\Felix\Async\Pool\RedisPool',
    ];
    /**
     * return single class
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)){
            self::$instance = new self;
        }

        return self::$instance;
    }
    /*
     * 服务预加载
     */
    public function initService()
    {
        $this->initSelf();
       // $this->registerProServices();
    }

    function init($config){
        $this->config=$config;
        if (!defined('DEBUG')) define('DEBUG', 'on');
        if (defined('WEBPATH'))
        {
            $this->app_path = WEBPATH . '/apps';
        }else{
            $this->app_path = __DIR__ . '/../apps';
        }
        define('APPSPATH', $this->app_path);
        Felix\Loader::addNameSpace('app', $this->app_path . '/');
    }

    public function initSelf()
    {
        self::$instance = $this;
    }

    /*
     * 预构建
     */
    public function registerProServices()
    {
        foreach ($this->onWorkStartServices as $key=>$service) {
            $this->singleton($key,function() use ($service){
                $obj = new $service($this->config);
                return $obj;
            });
        }
    }

    /*
    * 构建单例模式
    *
    */
    public function singleton($name, $callable = null)
    {
        if (!isset($this->instances[$name]) && $callable) {
            $this->instances[$name] = call_user_func($callable);
        }

        return isset($this->instances[$name]) ? $this->instances[$name] : null;
    }

    /*
     * 释放单例资源
     */
    public function release()
    {
        if(!empty($this->instances)){
            foreach($this->instances as $key=>$instance){
                if(in_array($key,['redis','mysql','redisPool', 'mysqlPool'])){
                    $instance->close();
                }else{
                    unset($instance);
                }
            }
        }
    }
    //运行http服务
    function runHttpServer()
    {
            if($this->config['smarty']) {
                //初始化模版引擎
                $this->smarty = new Smarty;
                $this->smarty->debugging = true;
                $this->smarty->caching = true;
                $this->smarty->cache_lifetime = 120;
                $this->smarty->template_dir = WEBPATH.'/apps/templates/';
                $this->smarty->compile_dir = WEBPATH.'/apps/templates/templates_c/';
                $this->smarty->cache_dir = WEBPATH.'/apps/templates//cache/';
            }
            //启动http服务
            define('FELIX_SERVER', true);
            $this->http_server=new Felix\Service\HttpServ($this);
            $this->http_server->smarty=$this->smarty;
            $this->http_server->app_path=$this->app_path;
            $this->http_server->setLogger(new \Felix\Log\FileLog($this->config["log"]));
            $this->http_server->run($this->config['listen']['host'],$this->config['listen']['port']);
    }

    //运行websocket服务
    function runWebSocket()
    {
        $this->ws_server= new Felix\Service\WebSocketServ($this);
        $this->ws_server->setLogger(new \Felix\Log\FileLog($this->config["log"]));
        $this->ws_server->run($this->config['listen']['host'],$this->config['listen']['port']);
    }

    //运行命令行处理
    function runCommand($parameter)
    {


        $cmd=new Felix\Service\CmdServ($this->config);
        $cmd->app_path=$this->app_path;
        $cmd->setLogger(new \Felix\Log\FileLog($this->config["log"]));
        $cmd->run($parameter);

    }

}