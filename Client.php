<?php

class Client
{
    /**
     * client handle
     *
     * @var swoole_client
     */
    protected $client;
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
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        //set 设置客户端参数
//        $this->client->set([]);
        //on 注册异步事件回调函数
        $eventArray = ['connect', 'error', 'receive', 'close'];
        foreach ($eventArray as $value) {
            $this->client->on($value, array($this, $value));
        }
        $this->client->connect("127.0.0.1", 9501);
    }

    public function connect()
    {
        echo "connect \n";
        $this->client->send('hello swool');
    }
    public function error($cli)
    {
        print_r($cli);
    }

    public function receive($cli , $data = '')
    {
        if(empty($data)){
            $this->close();
        }else {
            echo "received : $data\n";
            sleep(1);
            $this->client->send("hello \n");
        }
    }

    public function close()
    {
        $this->client->close();
        echo 'close' . "\n";
    }
}

$client = new Client();
