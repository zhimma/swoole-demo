<?php
/**
 * @author 马雄飞 <mma5694@gmail.com>
 * @date   2018/4/11 下午9:32
 */

/**
 * tcp server
 */
class Server
{
    private $server;

    public function __construct()
    {
        // new swoole_server
        $this->server = new swoole_server("0.0.0.0", 9501, SWOOLE_BASE, SWOOLE_SOCK_UDP);
        // $server->on 设置事件回调
        $event = ['Packet', 'Task', 'Finish'];
        foreach ($event as $value) {
            $this->server->on($value, [$this, 'on' . $value]);
        }
        // $server->set 设置运行参数
        $this->server->set([
                               'worker_num'      => 4,   //一般设置为服务器CPU数的1-4倍
                               'daemonize'       => false,  //以守护进程执行,
                               'task_worker_num' => 4 //任务工作进程数量。注意：如果设置了此参数，则必须设置onTask回调函数和onFinish回调函数，否则程序无法运行
                           ]);
        // $server->start启动服务器
        $this->server->start();
    }

    // master 进程启动后，fork出Manager进程，触发ManageStart
    public function onManagerStart()
    {
        echo "manage server start" . PHP_EOL;
    }

    // manager 进程启动后，fork出work进程
    public function onWorkerStart($server, $workerId)
    {
        echo "worker {$workerId} start" . PHP_EOL;
    }

    // worker 进程死掉后，触发WorkerStop
    public function onWorkerStop($server, $workerId)
    {
        echo "worker {$workerId} stop" . PHP_EOL;
    }

    // UDP服务器与TCP服务器不同，UDP没有连接的概念。
    // 启动Server后，客户端无需Connect，直接可以向Server监听的9502端口发送数据包。
    // 对应的事件为onPacket。
    public function onPacket(swoole_server $server, $data, $clientInfo)
    {
        /* echo "received msg from client,msg is : {$data}" . PHP_EOL;
         fwrite(STDOUT, "请回复消息：");
         $msg = trim(fgets(STDIN));
         $server->sendTo($clientInfo['address'], $clientInfo['port'] , $msg . ' from worker');*/

        // 较为耗时的任务 交给TaskWorker处理
        $data = json_encode(['clientInfo' => $clientInfo, 'data' => $data]);
        $server->task($data);
    }

    // 处理耗时任务
    public function onTask($server, $task_id, $from_id, $data)
    {
        fwrite(STDOUT, "请回复消息：");
        $msg = trim(fgets(STDIN));
        $result = json_decode($data, true);

        // onTask可以return，也可以echo，当return时会触发onFinish函数
        return json_encode(['clientInfo' => $result['clientInfo'], 'data' => $msg . ' from task worker']);


    }

    // Task worker 完成后调用
    public function onFinish($server, $task_id, $data)
    {
        $result = json_decode($data, true);
        $server->sendTo($result['clientInfo']['address'], $result['clientInfo']['port'], $result['data']);
        //echo "Result: {$data}n";
    }

}

$server = new Server();