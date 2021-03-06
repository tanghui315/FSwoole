<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/11/7
 * Time: 11:14
 */

namespace Felix;
use \Felix\Async\Pool\MysqlPool;
use \Felix\Async\Pool\RedisPool;
use Felix\Handler\TaskContent;

class Handler{

    const DATE_FORMAT_HTTP = 'D, d-M-Y H:i:s T';
    protected $serv;
    protected $currentFd;

    public $config;
    public $query_builder =true;  //启用查询建立
    public $log;
    public $db;
    public $server;

    public $felix;
    public $content;



    function __construct($serv=null)
    {
        if($serv!=null){
            $this->server=$serv;
            $this->config=$serv->config;
            $this->log = $serv->log;
        }
        $this->content=new TaskContent($this);

    }

    /**
     * 设置Logger
     * @param $log
     */
    function setLogger($log)
    {
        $this->log = $log;
    }

    //异步任务
    function task($data){
        $taskData=array_merge($data,['handler'=>$this]);
        $this->server->task($taskData);
    }


    //加载同步类型数据库
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
    function loadModel($name,$rname='')
    {
        if(empty($name)){
            return false;

        }elseif (is_array($name))
        {
            foreach ($name as $key => $value)
            {
                is_int($key) ? $this->loadModel($value) : $this->loadModel($key, $value);
            }

            return $this;
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
            log_message("ModelErr",$path.$name." file not find");
            return false;
        }
        if(empty($path))
            $class="app\\models\\".$model."Model";
        else
            $class="app\\models\\".$path."\\". $model."Model";

        if(!empty($rname)){
            $this->$rname = new $class();
            $this->$rname->db=$this->db;
        }else{
            $this->$name = new $class();
            $this->$name->db=$this->db;
        }

        return true;
    }
    //加载mysql连接池
    function loadMysqlPool()
    {
      return  $this->felix->singleton('mysqlPool',function(){
            $mysqlPool = new MysqlPool($this->config);
            return $mysqlPool;
        });
    }
    //加载redis连接池
    function loadRedisPool()
    {
        return $this->felix->singleton("redisPool",function(){
            $redisPool =new RedisPool($this->config);
            return $redisPool;
        });
    }



    function onTask($serv,$task_id,$from_id,$data)
    {
        //任务处理
    }


    function onFinish($serv,$task_id, $data) {
        //任务结束
    }



}