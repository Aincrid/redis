<?php
/**
 * Created by PhpStorm.
 * User: kirito
 * Date: 2018/11/5
 * Time: 10:04
 */

class RedisClass
{
    private static $_instance = [];

    private static $dbNum = 0;

    private $redisException = '';

    private $redis = [];


    private function __construct($host = '127.0.0.1', $port = '6379', $timeout = '0', $password = '')
    {
        try {
            if (!extension_loaded('redis')) {
                throw new Exception('the redis extension  not found');
            }
        } catch (Exception $e) {
            exit($e -> getMessage());
        }

        $this->redis[self::$dbNum] = new Redis();
        try {
            $this->redis[self::$dbNum]->pconnect($host, $port);
            if ($password != '') {
                $this->redis[self::$dbNum]->auth($password);
            }
        } catch (Exception $e) {
            echo '错误代码：'.$e -> getCode().'错误信息'.$e -> getMessage();
        }
    }

    private function __clone(){}


    public static function getSingleInstance($host, $port, $timeout = 2, $password = '')
    {
        self::$dbNum++;
        try {
            if (isset(self::$_instance[self::$dbNum]) && self::$_instance[self::$dbNum]->redis[self::$dbNum]->Ping() == '+PONG') {
                return self::$_instance[self::$dbNum];
            }
        } catch (Exception $e) {

        }
        self::$_instance[self::$dbNum] = new self($host, $port, $timeout, $password);
        return self::$_instance[self::$dbNum];
    }
}

$redis = RedisClass::getSingleInstance('127.0.0.1', '6379');
echo '<pre>';
var_dump($redis);

$redis = new Redis();

try {
    $redis -> connect('127.0.0.1', '6379');
}catch(Exception $e){
    echo $e -> getMessage();
}
var_dump($redis -> ping());
