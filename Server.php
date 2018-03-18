<?php

class Server
{
    private $serv;
    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1,   //一般设置为服务器CPU数的1-4倍
            'daemonize' => false,  //以守护进程执行
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'task_worker_num' => 8,  //task进程的数量
            "task_ipc_mode " => 3 ,  //使用消息队列通信，并设置为争抢模式
            //"log_file" => "log/taskqueueu.log" ,//日志
        ));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        // bind callback
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->start();
    }

    public function onConnect($serv , $fd)
    {
        echo $fd;
    }
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        //echo "Get Message From Client {$fd}:{$data}n";
        // send a task to task worker.
        file_put_contents('./client.txt',$data);
        $serv->task( $data );
    }
    public function onTask($serv,$task_id,$from_id, $data) {
        file_put_contents('./client.txt',$data);

    }
    public function onFinish($serv,$task_id, $data) {
        //echo "Task {$task_id} finishn";
        //echo "Result: {$data}n";
    }
    public function onClose($serv,$task_id) {
        echo "Task {$task_id} close";
        //echo "Result: {$data}n";
    }
}
$server = new Server();