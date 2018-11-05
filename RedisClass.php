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
            exit($e->getMessage());
        }

        self::$redis[$dbNum] = new Redis();

        try {
            self::$redis[$dbNum]->pconnect($host, $port);
            if ($password != '') {
                self::$redis[$dbNum]->auth($password);
            }
        } catch (Exception $e) {
            echo '错误代码：' . $e->getCode() . '错误信息' . $e->getMessage();
        }
    }

    private function __clone()
    {
    }


    public static function getSingleInstance($dbNum = 0, $host, $port, $timeout = 2, $password = '')
    {
        try {
            if (isset(self::$_instance[$dbNum]) && self::$_instance[$dbNum]->redis[$dbNum]->Ping() == '+PONG') {
                return self::$_instance[$dbNum];
            }
        } catch (Exception $e) {

        }
        self::$_instance[$dbNum] = new self($dbNum, $host, $port, $timeout, $password);
        return self::$_instance[$dbNum];
    }

    ################### 通用操作 ################

    public function delete($dbNum, $key)
    {
        if (!self::$redis[$dbNum]->delete($key)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 设置时间段
     * @param $dbNum
     * @param $time int 过期时间段
     */
    public function expire($dbNum, $key, $time)
    {
        if (self::$redis[$dbNum]->expire($key, $time)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 设置过期时间戳
     * @param $dbNum
     * @param $key
     * @param $timestamp
     * @return bool
     */
    public function expireAt($dbNum, $key, $timestamp)
    {
        if (self::$redis[$dbNum]->expireAt($key, $timestamp)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $dbNum
     * @param $key
     * @param bool $isP  是否获取毫秒
     * @return bool
     */
    public function getExpireTime($dbNum, $key, $isP = false)
    {
        if((bool)$isP){
            $time = self::$redis[$dbNum] -> pttl($key);
        } else {
            $time = self::$redis[$dbNum] -> ttl($key);
        }

        if($time){
            return $time;
        }else{
            return false;
        }
    }

    public function set($dbNum, $key, $val)
    {

        $num = self::$redis[$dbNum]->set($key, $val);

        if ($num > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function get($dbNum, $key)
    {
        $string = self::$redis[$dbNum]->get($key);
        if ($string) {

            return self::$redis[$dbNum]->get($key);

        } else {

            return false;

        }

    }
}

$redis = RedisClass::getSingleInstance(0, '127.0.0.1', '6379');
echo $redis->get(0, 'a');
$redis -> expire(0, 'a', 3600);
echo '时间段'.$redis -> getExpireTime(0, 'a').'<br>';
//$redis -> expire(0, 'a', strtotime('+1day'));
//echo '时间戳'.$redis -> getExpireTime(0, 'a').'<br>';
//
//echo '毫秒'.$redis -> getExpireTime(0, 'a');