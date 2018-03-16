<?php

class Client
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
    }

    public function connect()
    {
        file_put_contents('./connect.txt','connect');
        if (!$this->client->connect("127.0.0.1", 9501, 1)) {
            echo "Connect Error";
        }
        $this->client->send('it\'s  from client');
    }
}

$client = new Client();
$client->connect();