<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:30
 */

return [
    "swoole_server"=>[
        "max_request"=>200000,
        "worker_num"=>2,
        "keepalive"=>1,
        "task_worker_num"=>100,
        "daemonize"=>false,
        "pid_file"=>__DIR__."/http_server.pid",
    ],
    "web_server"=>[
        "document_root"=>__DIR__."/../",
        "static_dir"=>"static,",
        "static_ext"=>"js,jpg,gif,png,css,html",
        "keepalive"=>1,
        "gzip_open"=>1,
        "gzip_level"=>1,
        "expire_open"=>1,
        "expire_time"=>1800,

    ],
    "mysql"=>[
        'host' => '192.168.32.121',
        'port' => 3306,
        'dbname' => 'felix',
        'user' => 'root',
        'pass' => 'EV6ZeYVa'
    ],
    "route"=>[
        "/"=>"IndexHandler",
        "/info"=>"InfoHandler",
    ],
];