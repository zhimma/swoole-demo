<?php

class Server
{
    private $server;

    public function __construct()
    {
        //new swoole_server
        $this->server = new swoole_server("0.0.0.0", 9501);
        //$server->on 设置事件回调
        $event = ['Connect', 'Receive', 'Task', 'Finish', 'Close'];
        foreach ($event as $value) {
            $this->server->on($value, array($this, 'on' . $value));
        }
        //$server->set 设置运行参数
        $this->server->set(array(
            'worker_num'      => 1,   //一般设置为服务器CPU数的1-4倍
            'daemonize'       => false,  //以守护进程执行
            'max_request'     => 10000,
            'dispatch_mode'   => 2,
            'task_worker_num' => 8,  //task进程的数量
            "task_ipc_mode "  => 3,  //使用消息队列通信，并设置为争抢模式
            //"log_file" => "log/taskqueueu.log" ,//日志
        ));
        //$server->start启动服务器
        $this->server->start();
    }

    public function onConnect($server, $fd)
    {
        echo $fd;
    }

    public function onReceive(swoole_server $server, $fd, $from_id, $data)
    {
        $server->task($data);
    }

    public function onTask($server, $task_id, $from_id, $data)
    {
        echo $data;
    }

    public function onFinish($server, $task_id, $data)
    {
        //echo "Task {$task_id} finishn";
        //echo "Result: {$data}n";
    }

    public function onClose($server, $task_id)
    {
        echo "Task {$task_id} close";
        //echo "Result: {$data}n";
    }

    public function onStart()
    {
        echo 111;
    }

    public function onWorkerStart()
    {
        echo 222;
    }

    public function onTaskerStart()
    {
        echo 333;
    }
}

$server = new Server();