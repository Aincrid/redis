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

    ############################## 字符串 String #####################################

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

    /**
     * 判断键是否存在, redis 版本小于4.0, 只能判断一个, 大于4.0, 可以批量判断
     * @param $dbNum
     * @param $key
     * @return mixed
     */
    public function exists($dbNum, $key)
    {
        var_dump(self::$redis[$dbNum] -> info());
        return self::$redis[$dbNum] -> exists($key);
    }

    ############################## 字符串 String #####################################

    /**
     * 设置值
     * @param $dbNum
     * @param $key
     * @param $val
     * @return bool
     */
    public function set($dbNum, $key, $val)
    {

        $num = self::$redis[$dbNum]->set($key, $val);

        if ($num > 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     *  获取值
     * @param $dbNum
     * @param $key
     * @return bool
     */
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
//$redis -> set(0, 'a', 1);
//$redis -> set(0, 'b', 1);



var_dump($redis -> exists(0, ['as', 'cs', 'bs']));