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

    private static $redis = [];


    private function __construct($dbNum, $host = '127.0.0.1', $port = '6379', $timeout = '0', $password = '')
    {
        try {
            if (!extension_loaded('redis')) {
                throw new Exception('the redis extension  not found');
            }
        } catch (Exception $e) {
            exit($e -> getMessage());
        }

        self::$redis[$dbNum] = new Redis();
        var_dump(self::$redis);
        die;
        try {
            self::$redis[$dbNum]->pconnect($host, $port);
            if ($password != '') {
                self::$redis[$dbNum]->auth($password);
            }
        } catch (Exception $e) {
            echo '错误代码：'.$e -> getCode().'错误信息'.$e -> getMessage();
        }
    }

    private function __clone(){}


    public static function getSingleInstance($dbNum = 0, $host, $port, $timeout = 2, $password = '')
    {
        try {
            if (isset(self::$_instance[$dbNum]) && self::$_instance[$dbNum]->redis[$dbNum]->Ping() == '+PONG') {
                return self::$_instance[$dbNum];
            }
        } catch (Exception $e) {

        }
        self::$_instance[$dbNum] = new self($host, $port, $timeout, $password);
        return self::$_instance[$dbNum];
    }


    protected static function set($dbNum, $key, $val)
    {
        var_dump(self::$redis[$dbNum] -> ping());
    }
}

$redis = RedisClass::getSingleInstance(0, '127.0.0.1', '6379');
$redis::set(0, 'a', 'c');
