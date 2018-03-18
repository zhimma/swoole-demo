<?php

class Client
{
    private $client;
    //swoole的版本号
    public $swoole_version = SWOOLE_VERSION;

    public function __construct()
    {
        //SWOOLE_SOCK_TCP 创建tcp socket
        //SWOOLE_SOCK_TCP6 创建tcp ipv6 socket
        //SWOOLE_SOCK_UDP 创建udp socket
        //SWOOLE_SOCK_UDP6 创建udp ipv6 socket
        //SWOOLE_SOCK_SYNC 同步客户端
        //SWOOLE_SOCK_ASYNC 异步客户端
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);

        //set 设置客户端参数
        $this->client->set([]);
        //on 注册异步时间回调函数
        $eventArray = ['connect', 'error', 'receive', 'close'];
        $this->client->on();
    }

    public function connect()
    {
        if (!$this->client->connect("127.0.0.1", 9501, 1)) {
            echo "Connect Error";
        }
        $this->client->send('it\'s  from client');
    }
}

$client = new Client();
$client->connect();