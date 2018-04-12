<?php
/**
 * @author 马雄飞 <mma5694@gmail.com>
 * @date   2018/4/12 下午10:54
 */

/**
 *
 * mysql server
 */
class Server
{
    private $db;

    public function __construct()
    {
        $config = [
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'password' => '',
            'database' => 'swoole'
        ];
        $this->db = new swoole_mysql();
        $this->db->connect($config, [$this, 'onConnect']);
    }

    public function onConnect($db, $result)
    {
        if ($result === false) {
            var_dump($db->connect_errno, $db->connect_error);
        }
        $sql = 'select * from user limit 1';
        //query方法就是执行sql的方法
        //第一个参数是sql, 第二个参数是回调函数
        $db->query($sql, function ($db, $result) {
            /*
            *  执行失败，$result为false
            *  error属性获得错误信息，errno属性获得错误码
            */
            if ($result === false) {
                var_dump($db->error, $db->errno);
            } else if ($result === true) {
                /*
                 * 执行成功
                 * SQL为非查询语句(inset,update,delete）$result为true
                 * affected_rows 为影响的行数，insert_id 获得
                 */
                var_dump($db->affected_rows, $db->insert_id);
            } else {
                /*
                 * 执行成功，SQL为select语句，$result为结果数组
                 */
                var_dump($result);
            }
            //关闭mysql连接
            $db->close();
        });
    }
}
$server = new Server();