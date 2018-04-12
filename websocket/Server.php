<?php
/**
 * @author 马雄飞 <xiongfei.ma@pactera.com>
 * @date   2018/4/11 下午9:32
 */

/**
 * tcp server
 */
class Server
{
    private $server;
    protected $clientIds = [];

    public function __construct()
    {
        // new swoole_server
        $this->server = new swoole_websocket_server("0.0.0.0", 9501);
        // $server->on 设置事件回调
        $event = ['open', 'message', 'close'];
        foreach ($event as $value) {
            $this->server->on($value, [$this, 'on' . ucfirst($value)]);
        }
        // $server->start启动服务器
        $this->server->start();
    }

    // master 进程启动后，fork出Manager进程，触发ManageStart
    public function onOpen(swoole_websocket_server $server, $request)
    {
        echo "client {$request->fd} connected" . PHP_EOL;
        array_push($this->clientIds, $request->fd);
    }

    // manager 进程启动后，fork出work进程
    public function onMessage($server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}" . PHP_EOL;
        fwrite(STDOUT, "请回复消息：");
        $msg = trim(fgets(STDIN));
        foreach ($this->clientIds as $client) {
            $server->push($client, $msg . PHP_EOL);
        }
    }

    // worker 进程死掉后，触发WorkerStop
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed" . PHP_EOL;
    }

}

$server = new Server();
