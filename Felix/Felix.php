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
    public $app_path;
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
            $this->app_path = WEBPATH . '/apps';
        }else{
            $this->app_path = __DIR__ . '/../apps';
        }
        define('APPSPATH', $this->app_path);
        Felix\Loader::addNameSpace('app', $this->app_path . '/');

    }

    //运行http服务
    function runHttpServer($config = array(),$host = '0.0.0.0', $port = 9889)
    {
            $this->config=$config;

            if($this->config['redis']['enabled']){
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
            $this->http_server=new Felix\Service\HttpServ($this->config);
            $this->http_server->smarty=$this->smarty;
            $this->http_server->app_path=$this->app_path;
            $this->http_server->setLogger(new \Felix\Log\FileLog($config["log"]));
            $this->http_server->run($host,$port);
    }

    //运行命令行处理
    function runCommand($parameter,$config = array())
    {
        $this->config=$config;

        if($this->config['redis']['enabled']){
            Felix\Database\FRedis::init($config['redis']);
        }

        $cmd=new Felix\Service\CmdServ($this->config);
        $cmd->app_path=$this->app_path;
        $cmd->setLogger(new \Felix\Log\FileLog($config["log"]));
        $cmd->run($parameter);

    }

}