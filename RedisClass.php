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


    private function __construct($dbNum = 0, $host = '127.0.0.1', $port = '6379', $timeout = '0', $password = '')
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


    public static function getSingleInstance($host, $port, $timeout = 2, $password = '', $dbNum = 0)
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

    ############################## 通用 #####################################

    public function delete($key, $dbNum = 0)
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
    public function expire($key, $time, $dbNum = 0)
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
    public function expireAt($key, $timestamp, $dbNum = 0)
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
     * @param bool $isP 是否获取毫秒
     * @return bool
     */
    public function getExpireTime($key, $isP = false, $dbNum = 0)
    {
        if ((bool)$isP) {
            $time = self::$redis[$dbNum]->pttl($key);
        } else {
            $time = self::$redis[$dbNum]->ttl($key);
        }

        if ($time) {
            return $time;
        } else {
            return false;
        }
    }

    public function persist($key, $dbNum = 0)
    {
        return self::$redis[$dbNum]->persist($key);
    }

    /**
     * 判断键是否存在
     * @param $dbNum
     * @param $key
     * @return mixed
     */
    public function exists($key, $dbNum = 0)
    {
        return self::$redis[$dbNum]->exists($key);
    }

    /**
     *  获取该模式对应的键, * 表示所有键
     * @param string $pattern
     * @param int $dbNum
     * @return mixed
     */
    public function getKeys($pattern = '*', $dbNum = 0)
    {
        return self::$redis[$dbNum]->keys($pattern);
    }

    /**
     * @param null $it
     * @param string $pattern
     * @param int $count 每次便利$count, 不一定返回$count条
     * @param int $retry 是否重复scan
     * @param int $dbNum
     * @return array
     */
    public function scan($pattern = '*', $count = 50, $retry = 1, $dbNum = 0)
    {
        if ($retry) {

            self::$redis[$dbNum]->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);

        } else {

            // 仅scan一次
            self::$redis[$dbNum]->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);

        }
        $resultArr = [];
        $it = NULL;
        do {

            $resultArr = array_merge(self::$redis[$dbNum]->scan($it, $pattern, $count), $resultArr);

        } while ($it > 0);

        return $resultArr;
    }


    /**
     * @param $key
     * @param array $option 参数数组
     *  'by' => 'some_pattern_*',
     *  'limit' => array(0, 1),
     *  'get' => 'some_other_pattern_*' or an array of patterns, // 取出对应的键值
     *  'sort' => 'asc' or 'desc',
     *  'alpha' => TRUE, // 根据字母排序
     *  'store' => 'external-key' // 将排序后的结果保存到该键上
     * @param int $dbNum
     *  返回排序后的数组或数组的元素
     */
    public function sort($key, $option = [], $dbNum = 0)
    {
        return self::$redis[$dbNum] -> sort($key, $option);
    }




    ############################## 字符串 String #####################################

    /**
     * 设置值
     * @param $dbNum
     * @param $key
     * @param $val
     * @return bool
     */
    public function set($key, $val, $dbNum = 0)
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
    public function get($key, $dbNum = 0)
    {
        $string = self::$redis[$dbNum]->get($key);
        if ($string) {

            return self::$redis[$dbNum]->get($key);

        } else {

            return false;

        }

    }

    /**
     * 不存在的默认为0, 自增, 默认1
     * @param $key
     * @param int $length
     * @param int $dbNum
     * @param int 返回自增后的值
     */
    public function incrBy($key, $length = 1, $dbNum = 0)
    {
        return self::$redis[$dbNum]->incrBy($key, $length);
    }

    /**
     * 不存在的默认值为0, 再减$length
     * @param $key
     * @param float $length
     * @param int $dbNum
     * @return mixed  返回增加后的结果
     */
    public function incrByFloat($key, $length = 1.0, $dbNum = 0)
    {
        return self::$redis[$dbNum]->incrByFloat($key, $length);
    }

    /**
     * 不存在的默认为0, 减$length, 返回计算后的值
     * @param $key
     * @param int $length
     * @param int $dbNum
     * @param int 返回减后的值
     */
    public function decrBy($key, $length = 1, $dbNum = 0)
    {
        return self::$redis[$dbNum]->decrBy($key, $length);
    }

    /**
     * 不存在的默认值为0, 减$length, 返回计算后的值
     * @param $key
     * @param float $length
     * @param int $dbNum
     * @return mixed  返回减后的结果
     */
    public function decrByFloat($key, $length = 1.0, $dbNum = 0)
    {
        return self::$redis[$dbNum]->decrByFloat($key, $length);
    }

    /**
     * 键不存在的会自动创建, 然后赋值
     * @param $key
     * @param $string
     * @param int $dbNum
     * @return mixed 返回拼接后的字符串长度
     */
    public function append($key, $string, $dbNum = 0)
    {
        return self::$redis[$dbNum]->append($key, $string);
    }

    /**
     * 不存在的长度为0
     * @param $key
     * @param int $dbNum
     * @return int 字符串长度
     */
    public function strLen($key, $dbNum = 0)
    {
        return self::$redis[$dbNum]->strLen($key);
    }

    /**
     * 返回字符串从开始索引到结束索引的串, 可以是负数
     * @param $key
     * @param $start
     * @param $end
     * @param int $dbNum
     * @return mixed
     */
    public function getRange($key, $start, $end, $dbNum = 0)
    {
        return self::$redis[$dbNum]->getRange($key, $start, $end);
    }

    /**
     * 批量设置
     * @param array $key
     * @param $dbNum
     * @return bool
     */
    public function mSet($keyArray, $dbNum = 0)
    {
        return self::$redis[$dbNum]->mSet($keyArray);
    }

    /**
     * 批量获取值, 若不存在, 在键值位置显示false
     * @param array $key
     * @param int $dbNum
     * @return mixed
     */
    public function mGet($keyArray, $dbNum = 0)
    {
        return self::$redis[$dbNum]->mGet($keyArray);
    }

}
echo '<pre>';
$redis = RedisClass::getSingleInstance('127.0.0.1', '6379');
var_dump($redis -> getKeys());
var_dump($redis -> scan('a*', 3, 0));