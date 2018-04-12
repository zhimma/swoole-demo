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
        $this->server = new swoole_server("0.0.0.0", 9501);
        // $server->on 设置事件回调
        $event = ['ManagerStart', 'WorkerStart', 'Start', 'Connect', 'Receive', 'Close', 'Task', 'Finish'];
        foreach ($event as $value) {
            $this->server->on($value, [$this, 'on' . $value]);
        }
        // $server->set 设置运行参数
        $this->server->set(
            [
                'worker_num'      => 4,   //一般设置为服务器CPU数的1-4倍
                'daemonize'       => false,  //以守护进程执行,
                'task_worker_num' => 2 //任务工作进程数量。注意：如果设置了此参数，则必须设置onTask回调函数和onFinish回调函数，否则程序无法运行
            ]
        );
        // $server->start启动服务器
        $this->server->start();
    }

    // master 进程启动后，fork出Manager进程，触发ManageStart
    public function onManagerStart($server)
    {
        echo "manage server start" . PHP_EOL;
    }

    // manager 进程启动后，fork出work进程
    public function onWorkerStart($server, $workerId)
    {
        if ($server->taskworker) {
            echo "task {$workerId} worker start" . PHP_EOL;
        } else {
            echo "worker {$workerId} start" . PHP_EOL;
        }
    }

    // worker 进程死掉后，触发WorkerStop
    public function onWorkerStop($server, $workerId)
    {
        echo "worker {$workerId} stop" . PHP_EOL;
    }

    // 主进程启动时触发的回调函数，即服务器启动自动调用
    public function onStart($server)
    {
        echo "server start" . PHP_EOL;
    }

    // 当有新的连接进入 触发
    public function onConnect($server, $fd)
    {
        echo "client {$fd} is connect" . PHP_EOL;
    }

    /**
     * 接收到数据时回调此函数  发生在worker进程中
     *
     * @param swoole_server $server     swoole_server对象
     * @param               $fd         tcp客户端链接的唯一标识符
     * @param               $from_id    tcp连接所在的Reactor线程ID
     * @param               $data       收到的数据内容
     *
     * @author mma5694@gmail.com
     * @date   2018年04月11日21:39:51
     */
    public function onReceive(swoole_server $server, $fd, $from_id, $data)
    {
        echo "received msg from client $fd,msg is : {$data}" . PHP_EOL;
        fwrite(STDOUT, "请回复消息：");
        $msg = trim(fgets(STDIN));
        $server->send($fd, $msg . ' from worker');

        // 较为耗时的任务 交给TaskWorker处理
        /*$data = json_encode(['fd' => $fd, 'data' => $data]);
        $server->task($data);*/
    }

    // 处理耗时任务
    public function onTask($server, $task_id, $from_id, $data)
    {
        fwrite(STDOUT, "请回复消息：");
        $msg = trim(fgets(STDIN));
        $result = json_decode($data, true);

        // onTask可以return，也可以echo，当return时会触发onFinish函数
        return json_encode(['fd' => $result['fd'], 'msg' => $msg . ' from task worker']);


    }

    // Task worker 完成后调用
    public function onFinish($server, $task_id, $data)
    {
        $result = json_decode($data, true);
        $server->send($result['fd'], $result['msg'], $task_id);
        //echo "Result: {$data}n";
    }

    public function onClose($server, $task_id)
    {
        echo "Task {$task_id} close" . PHP_EOL;
        //echo "Result: {$data}n";
    }

}

$server = new Server();