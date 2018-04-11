<?php
class client
{
    private $client;
    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_UDP,SWOOLE_SOCK_ASYNC);
        //$client->on 设置事件回调
        $event = [ 'Connect', 'Receive', 'Close' , 'Error'];
        foreach ($event as $value) {
            $this->client->on($value, [$this, 'on' . $value]);
        }

        $this->client->connect('127.0.0.1' , '9501' ,1);
    }
    // 客户端连接成功
    public function onConnect($client)
    {
        fwrite(STDOUT, "请输入消息：");
        swoole_event_add(STDOUT,function(){
            fwrite(STDOUT, "请输入消息：");
            $msg = trim(fgets(STDIN));
            // 发送给消息到服务端
            $this->client->send( $msg );
        });
    }
    // 接收到服务端消息后触发的函数
    public function onReceive($client , $data)
    {
        // 接收服务器消息
        echo "received msg is : {$data}" .PHP_EOL;
    }
    // 服务器断开链接后触发的函数
    public function onClose($client){
        echo "server closed";

    }
    public function onError($client)
    {
        echo "has something wrong with server";
    }
}
$client = new Client();

