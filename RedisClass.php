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

    private static $redis = '';


    private function __construct($host = '127.0.0.1', $port = '6379', $timeout = '0', $password = '')
    {
        try {
            if (!extension_loaded('redis')) {
                throw new Exception('the redis extension  not found');
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }


        self::$redis = self::$redis ? self::$redis : new Redis();

        try {
            self::$redis->pconnect($host, $port);
            if ($password != '') {
                self::$redis->auth($password);
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
        self::$_instance[$dbNum] = new self($host, $port, $timeout, $password);
        return self::$_instance[$dbNum];
    }

    ############################## 通用 #####################################

    public function delete($key, $dbNum = 0)
    {
        if (!self::$redis->delete($key)) {
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
    public function expire($key, $time)
    {
        if (self::$redis->expire($key, $time)) {
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
    public function expireAt($key, $timestamp)
    {
        if (self::$redis->expireAt($key, $timestamp)) {
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
    public function getExpireTime($key, $isP = false)
    {
        if ((bool)$isP) {
            $time = self::$redis->pttl($key);
        } else {
            $time = self::$redis->ttl($key);
        }

        if ($time) {
            return $time;
        } else {
            return false;
        }
    }

    public function persist($key)
    {
        return self::$redis->persist($key);
    }

    /**
     * 判断键是否存在
     * @param $dbNum
     * @param $key
     * @return mixed
     */
    public function exists($key)
    {
        return self::$redis->exists($key);
    }

    /**
     *  获取该模式对应的键, * 表示所有键
     * @param string $pattern
     * @param int $dbNum
     * @return mixed
     */
    public function getKeys($pattern = '*')
    {
        return self::$redis->keys($pattern);
    }

    /**
     * @param null $it
     * @param string $pattern
     * @param int $count 每次便利$count, 不一定返回$count条
     * @param int $retry 是否重复scan
     * @param int $dbNum
     * @return array
     */
    public function scan($pattern = '*', $count = 50, $retry = 1)
    {
        if ($retry) {

            self::$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);

        } else {

            // 仅scan一次
            self::$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);

        }
        $resultArr = [];
        $it = NULL;
        do {
            $arr = self::$redis->scan($it, $pattern, $count);
            if (is_array($arr)) {
                $resultArr = array_merge($arr, $resultArr);
            }

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
     *  返回排序后的数组或数组的元素 链表 集合
     */
    public function sort($key, $option = [])
    {
        return self::$redis->sort($key, $option);
    }

    /**
     * 修改键名
     * @param $key
     * @param $newKey
     * @param int $dbNum
     * @return bool
     */
    public function rename($key, $newKey)
    {
        return self::$redis->rename($key, $newKey);
    }

    /**
     * 随机返回一个键
     * @param int $dbNum
     * @return mixed
     */
    public function random($dbNum = 0)
    {
        return self::$redis->randomKey();
    }




    ############################## 字符串 String #####################################

    /**
     * 设置值
     * @param $dbNum
     * @param $key
     * @param $val
     * @return bool
     */
    public function set($key, $val)
    {

        $num = self::$redis->set($key, $val);

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
    public function get($key)
    {
        $string = self::$redis->get($key);
        if ($string) {

            return self::$redis->get($key);

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
    public function incrBy($key, $length = 1)
    {
        return self::$redis->incrBy($key, $length);
    }

    /**
     * 不存在的默认值为0, 再减$length
     * @param $key
     * @param float $length
     * @param int $dbNum
     * @return mixed  返回增加后的结果
     */
    public function incrByFloat($key, $length = 1.0)
    {
        return self::$redis->incrByFloat($key, $length);
    }

    /**
     * 不存在的默认为0, 减$length, 返回计算后的值
     * @param $key
     * @param int $length
     * @param int $dbNum
     * @param int 返回减后的值
     */
    public function decrBy($key, $length = 1)
    {
        return self::$redis->decrBy($key, $length);
    }

    /**
     * 不存在的默认值为0, 减$length, 返回计算后的值
     * @param $key
     * @param float $length
     * @param int $dbNum
     * @return mixed  返回减后的结果
     */
    public function decrByFloat($key, $length = 1.0)
    {
        return self::$redis->decrByFloat($key, $length);
    }

    /**
     * 键不存在的会自动创建, 然后赋值
     * @param $key
     * @param $string
     * @param int $dbNum
     * @return mixed 返回拼接后的字符串长度
     */
    public function append($key, $string)
    {
        return self::$redis->append($key, $string);
    }

    /**
     * 不存在的长度为0
     * @param $key
     * @param int $dbNum
     * @return int 字符串长度
     */
    public function strLen($key)
    {
        return self::$redis->strLen($key);
    }

    /**
     * 返回字符串从开始索引到结束索引的串, 可以是负数
     * @param $key
     * @param $start
     * @param $end
     * @param int $dbNum
     * @return mixed
     */
    public function getRange($key, $start, $end)
    {
        return self::$redis->getRange($key, $start, $end);
    }

    /**
     * 批量设置
     * @param array $key
     * @param $dbNum
     * @return bool
     */
    public function mSet($keyArray)
    {
        return self::$redis->mSet($keyArray);
    }

    /**
     * 批量获取值, 若不存在, 在键值位置显示false
     * @param array $key
     * @param int $dbNum
     * @return mixed
     */
    public function mGet($keyArray)
    {
        return self::$redis->mGet($keyArray);
    }


    ################################# hash 哈希 ###################################

    /**
     * 添加或修改hash值
     * @param $table
     * @param $key
     * @param $val
     * @param int $dbNum
     * @return bool
     */
    public function hSet($table, $key, $val)
    {
        self::$redis->hSet($table, $key, $val);
        $value = self::$redis->hGet($table, $key);

        if ($val === $value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除hash
     * @param $table
     * @param $key
     * @param int $dbNum
     * @return bool
     */
    public function hDel($table, $key)
    {
        return (bool)self::$redis->hDel($table, $key);
    }

    /**
     *  返回值， 没有是false
     * @param $table
     * @param $key
     * @param int $dbNum
     * @return mixed
     */
    public function hGet($table, $key)
    {
        return self::$redis->hGet($table, $key);
    }

    /**
     * 获得hash表所有元素和值, 不存在返回？？
     * @param $table
     * @param $dbNum
     * @return mixed
     */
    public function hGetAll($table)
    {
        return self::$redis->hGetAll($table);
    }

    /**
     * 判断hash表是否有该键
     * @param $table
     * @param $key
     * @param int $dbNum
     * @return bool
     */
    public function hExists($table, $key)
    {
        return self::$redis->hExists($table, $key);
    }

    /**
     * 返回新值
     * @param $table
     * @param $key
     * @param $val
     * @param int $dbNum
     * @return mixed
     */
    public function hIncrBy($table, $key, int $val)
    {
        return self::$redis->hIncrBy($table, $key, (int)$val);
    }

    /**
     * 返回新值
     * @param $table
     * @param $key
     * @param float $val
     * @param int $dbNum
     * @return mixed
     */
    public function hIncrByFloat($table, $key, float $val)
    {
        return self::$redis->hIncrByFloat($table, $key, $val);
    }

    /**
     * 返回hash表中所有键
     * @param $table
     * @param int $dbNum
     * @return array
     */
    public function hKeys($table)
    {
        return self::$redis->hKeys($table);
    }

    /**
     * 返回hash所有值
     * @param $table
     * @return array
     */
    public function hVals($table)
    {
        return self::$redis->hVals($table);
    }

    /**
     * 返回符合条件的关联数组, 包括键和值
     * @param $table
     * @param $pattern
     * @param int $length
     */
    public function hScan($table, $pattern, $length = 50)
    {
        $it = NULL;

        $resultArray = [];

        self::$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);

        do {
            $result = self::$redis->hScan($table, $it, $pattern, $length);

            if (is_array($result)) {
                $resultArray = array_merge($resultArray, $result);
            }

        } while ($it > 0);

        return $resultArray;
    }


    /**
     * 返回哈希表长度, 不存在返回false
     * @param $table
     * @return int|bool
     */
    public function hLen($table)
    {
        return self::$redis->hLen($table);
    }

    /**
     * 获取值的长度, 键不存在返回false
     * @param $table
     * @param $key
     * @return mixed
     */
    public function hStrLen($table, $key)
    {
        if (self::$redis->hExists($table, $key)) {
            return self::$redis->hStrLen($table, $key);
        } else {
            return false;
        }
    }

}

echo '<pre>';
$redis = RedisClass::getSingleInstance('127.0.0.1', '6379');
echo 'hScan<br>';
var_dump($redis->hScan('tao', 'b*'));
var_dump($redis->hScan('tao', 'a*'));
var_dump($redis->hStrLen('tao', 'e'));
var_dump($redis->hExists('tao', 'b'));