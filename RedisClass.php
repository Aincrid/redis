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

    ########################### 链表 list###########################

    /**
     * 左进, 成功true, 失败false
     * @param $key
     * @param $val
     * @return bool
     */
    public function lPush($key, ...$val)
    {
        array_unshift($val, $key);
        return (bool)call_user_func_array(array(self::$redis, 'lPush'), $val);
    }

    /**
     * 只能向存在的链表中添加，否则false
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    public function lPushx($key, string $val)
    {
        return (bool)self::$redis->lPushx($key, $val);
    }

    /**
     * 右进
     * @param $key
     * @param array $val
     * @return bool
     */
    public function rPush($key, ...$val)
    {
        array_unshift($val, $key);
        return (bool)call_user_func_array(array(self::$redis, 'rPush'), $val);
    }

    /**
     *  右进, 只能针对存在的list, 否则false
     * @param $key
     * @param $val
     * @return bool
     */
    public function rPushx($key, string $val)
    {
        return (bool)self::$redis->rPushx($key, $val);
    }

    /**
     * 将左侧的第一个从list中移除, 并返回
     * @param $key
     * @return string
     */
    public function lPop($key)
    {
        return self::$redis->lPop($key);
    }

    /**
     * 将右侧的第一个从list中移除, 并返回
     * @param $key
     * @return string
     */
    public function rPop($key)
    {
        return self::$redis->rPop($key);
    }

    /**
     * lpop的阻塞版本, 当给定列表内没有任何元素可供弹出的时候，连接将被 BLPOP 命令阻塞，直到等待超时或发现可弹出元素为止。
     * @param array $key
     * @param int $timeout
     * @return array
     */
    public function blPop(array $key, int $timeout = 10)
    {
        return self::$redis->blPop($key, $timeout);
    }

    /**
     * rPop的阻塞版本, 当给定列表内没有任何元素可供弹出的时候，连接将被 BLPOP 命令阻塞，直到等待超时或发现可弹出元素为止。
     * @param array $key
     * @param int $timeout
     * @return array
     */
    public function brPop(array $key, int $timeout = 10)
    {
        return self::$redis->brPop($key, $timeout);
    }

    /**
     * 将list $srcKey最后一个元素弹出，添加到dst第一个元素， 并返回该元素
     * @param $srcKey 来源list
     * @param $dstKey 目的list
     * @return string 被操作的值
     */
    public function rPopLPush($srcKey, $dstKey)
    {
        return self::$redis->rpoplpush($srcKey, $dstKey);
    }

    /**
     * 阻塞版本
     * @param $srcKey
     * @param $dstKey
     * @param int $timeout
     * @return string
     */
    public function bRPopLPush($srcKey, $dstKey, $timeout = 10)
    {
        return self::$redis->brpoplpush($srcKey, $dstKey, $timeout);
    }

    /**
     * 返回索引处的值, 索引从0开始, 也可使用负数-1表示最后一个
     * @param string $key
     * @param int $index
     * @return String|false if is not exist
     */
    public function lIndex(string $key, int $index)
    {
        return self::$redis->lIndex($key, $index);
    }

    /**
     * 表示在$pivot(若有多个$pivot，只在第一个之前或之后)之前或之后添加一个值，成功返回list中元素的数量, 否则返回false
     * @param string $key
     * @param int $isBefore
     * @param string $pivot
     * @param $value
     * @return int
     */
    public function lInsert(string $key, $isBefore = 1, string $pivot, $value)
    {
        if ($isBefore) {
            $position = Redis::BEFORE;
        } else {
            $position = Redis::AFTER;
        }
        $result = self::$redis->lInsert($key, $position, $pivot, $value);
        if($result <= 0){
            return false;
        }
        return $result;
    }

    /**
     * 返回list元素数量, key不存在返回0, key不是list,返回false
     * @param $key
     * @return int|false
     */
    public function lLen($key)
    {
        return self::$redis -> lLen($key);
    }

    /**
     * 返回数组
     * @param $key
     * @param $start 0开始 可以负数
     * @param $end
     * @return array
     */
    public function lRange($key, $start, $end)
    {
        return self::$redis -> lRange($key, $start, $end);
    }

    /**
     * 根据参数 count 的值，移除列表中与参数 value 相等的元素, 返回移除元素数量, key不是list返回false
     * @param $key
     * @param $value
     * @param $count
     * count > 0 : 从表头开始向表尾搜索，移除与 value 相等的元素，数量为 count 。
     * count < 0 : 从表尾开始向表头搜索，移除与 value 相等的元素，数量为 count 的绝对值。
     * count = 0 : 移除表中所有与 value 相等的值
     * @return int
     */
    public function lRemove($key, $value, $count = 1)
    {
        return self::$redis -> lRem($key, $value, $count);
    }

    /**
     * 设置$index处的值, 成功true, 失败false
     * @param $key
     * @param $index
     * @param $val
     * @return bool
     */
    public function lSet($key, $index, $val)
    {
        return self::$redis -> lSet($key, $index, $val);
    }

    /**
     * 让列表只保留$start到$stop之间的元素, 只要start大于列表最大长度或大于stop, 列表就会被清空
     * @param $key
     * @param $start 可以为负数
     * @param $stop 可以为负数
     * @return array
     */
    public function lTrim($key, $start, $stop)
    {
        return self::$redis -> lTrim($key, $start, $stop);
    }



    ############################# 集合 set ##################################

    /**
     * 向集合中添加多个元素
     * @param $key
     * @param array $val
     * @return mixed
     */
    public function sAdd($key, ...$val)
    {
        array_unshift($val, $key);
        var_dump($val);
        return call_user_func_array(array(self::$redis, 'sAdd'), $val);
    }

    /**
     * 返回集合元素的数量, 不存在返回0
     * @param $key
     * @return int
     */
    public function sCard($key)
    {
        return self::$redis -> sCard($key);
    }

    /**
     * 给出的是第一个集合中有而其他集合没有的元素集合
     * @param mixed ...$key
     * @return mixed
     */
    public function sDiff(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sDiff'), $key);
    }

    /**
     * 求差集并保存所给的第一个集合中, 若该集合已存在会覆盖, 返回元素数量
     * @param mixed ...$key
     * @return mixed
     */
    public function sDiffStore(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sDiffStore'), $key);
    }

    /**
     * 返回交集
     * @param mixed ...$key
     * @return mixed
     */
    public function sInter(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sInter'), $key);
    }

    /**
     * 求交集并保存所给的第一个集合中, 若该集合已存在会覆盖, 返回元素数量
     * @param mixed ...$key
     * @return mixed
     */
    public function sInterStore(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sInterStore'), $key);
    }

    /**
     * 判断元素是否在该集合中, 在返回true, 否则false
     * @param $key
     * @param $val
     * @return bool
     */
    public function sIsMember($key, $val)
    {
        return self::$redis -> sIsMember($key, $val);
    }

    /**
     * 返回该集合中所有元素，不存在为空集合
     * @param $key
     * @return array
     */
    public function sMembers($key)
    {
        return self::$redis -> sMembers($key);
    }

    /**
     * 将$srcKey中的member 挪到 $dstKey中, 成功true, 失败false
     * @param $srcKey
     * @param $dstKey
     * @param $member
     * @return bool
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return self::$redis -> sMove($srcKey, $dstKey, $member);
    }

    /**
     * 从集合中随机去除一个或多个元素, 并返回该元素
     * @param $key
     * @param $count 随机出来的数量, $count > 集合元素数量, 会全部出来
     * @return string
     */
    public function sPop($key, $count = 1)
    {
        return self::$redis -> sPop($key, $count);
    }

    /**
     * 从集合中随机出来$count 个元素, 并不从集合中移除
     * @param $key
     * @param $count
     * @return array|string
     */
    public function sRandMember($key, $count = 1)
    {
        return self::$redis -> sRandMember($key, $count);
    }

    /**
     * 将多个元素从集合中移除
     * @param $key
     * @param mixed ...$member
     */
    public function sRemove($key, ...$member)
    {
        array_unshift($member, $key);
        return call_user_func_array(array(self::$redis, 'sRemove'), $member);
    }

    /**
     * 返回多个集合的并集
     * @param mixed ...$key
     * @return mixed
     */
    public function sUnion(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sUnion'), $key);
    }

    /**
     * 返回并集的数量, 没有键返回false
     * @param $storeKey
     * @param mixed ...$key
     * @return mixed
     */
    public function sUnionStore(...$key)
    {
        return call_user_func_array(array(self::$redis, 'sUnionStore'), $key);
    }

    /**
     *  同scan
     * @param $key
     * @param $pattern
     * @param $count
     * @return array
     */
    public function sScan($key, $pattern, $count)
    {
        self::$redis -> setOption(REDIS::OPT_SCAN, REDIS::SCAN_RETRY);
        $it = 0;
        $resultArr = [];
        do{

            $result = self::$redis -> sScan($key, $it, $pattern, $count);

            if(is_array($result)){
                $resultArr = array_merge($result, $resultArr);
            }

        }while($it > 0);
        var_dump($resultArr);
        return $resultArr;
    }





    ####################### 有序集合 sorted sets #############################

    /**
     * 向有序集合中添加元素
     * @param $key
     * @param $score
     * @param $value
     * @return int
     */
    public function zAdd($key, $score, $value)
    {
        return self::$redis -> zAdd($key, $score, $value);
    }

    /**
     * 返回有序集合的元素数量
     * @param $key
     * @return int
     */
    public function zCard($key)
    {
        return self::$redis -> zCard($key);
    }

    /**
     * 返回$key中  分数>= $min 并且分数 <= $max 元素的数量
     * @param $key
     * @param $min
     * @param $max
     * @return int
     */
    public function zCount($key, $min, $max)
    {
        return self::$redis -> zCount($key, $min, $max);
    }


    /**
     * 对有序集合中$members分数加$incrScore, 返回增加后的分数, key不存在或member不再key中,直接创建
     * @param $key
     * @param $member
     * @param float $incrScore
     * @return float
     */
    public function zIncrBy($key, $member, float $incrScore)
    {
        return self::$redis -> zIncrBy($key, $incrScore, $member);
    }

    /**
     * 计算两个有序集合的交集, ['a'=>0, 'b'=>'1', 'c']
     * @param $dstKey
     * @param array $zSetArray
     * @param array $weightArray 权重, 每个集合元素的分数*自己的权重
     * @param string $aggregate  有MIN, MAX, SUM 三个选项, 若是MIN, 则从交集中选出乘上权重后最小的分数
     * @return int
     */
    public function zInter($dstKey, array $zSetArray, array $weightArray = null, $aggregate = 'SUM')
    {
        return self::$redis -> zinter($dstKey, $zSetArray, $weightArray, $aggregate);
    }

    /**
     * 计算有序集合的并集
     * @param $dstKey
     * @param array $zSetArray
     * @param array|null $weightArray
     * @param string $aggregate
     * @return int
     */
    public function zUnion($dstKey, array $zSetArray, array $weightArray = null, $aggregate = 'SUM')
    {
        return self::$redis -> zUnion($dstKey, $zSetArray, $weightArray, $aggregate);
    }

    /**
     * 获取一定长度的有序集合
     * @param $key
     * @param $start
     * @param $end
     * @param bool $withScores
     * @return array
     */
    public function zRange($key, $start, $end, $withScores = true)
    {
        return self::$redis ->zRange($key, $start, $end, $withScores);
    }

    /**
     * 根据分数范围获取元素, 正序, 若给定$max < $min 则返回空数组
     * @param $key
     * @param $min
     * @param $max
     * @param array $options  withscores:true limit=>array($offset, $limit)
     * @return array
     */
    public function zRangeByScore($key, $min, $max, array $options)
    {
        return self::$redis -> zRangeByScore($key, $min, $max, $options);
    }

    /**
     * 若给定max < min , 返回空数组
     * @param $key
     * @param $max
     * @param $min
     * @param array $options
     * @return array
     */
    public function zRevRangeByScore($key, $max, $min, array $options)
    {
        return self::$redis -> zRevRangeByScore($key, $max, $min, $options);
    }

    /**
     * 返回该元素在有序集合中的排名(按分数从小到大, 最小为0)
     * @param $key
     * @param $member
     * @return int
     */
    public function zRank($key, $member)
    {
        return self::$redis -> zrank($key, $member);
    }

    /**
     * 返回该元素在有序集合中的排名(按分数从大到小, 最大为0)
     * @param $key
     * @param $member
     * @return int
     */
    public function zRevRank($key, $member)
    {
        return self::$redis -> zrank($key, $member);
    }

    /**
     * 删除有序集合中的元素
     * @param $key
     * @param mixed ...$members
     * @return mixed
     */
    public function zRem($key, ...$members)
    {
        array_unshift($members, $key);
        return call_user_func_array(array(self::$redis, 'zrem'), $members);
    }

    /**
     *  删除集合中分数 >= min <= max 中的元素
     * @param $key
     * @param $min
     * @param $max
     * @return int 删除的数量
     */
    public function zRemRangeByScore($key, $min, $max)
    {
        return self::$redis -> zRemRangeByScore($key, $min, $max);
    }

    /**
     * 删除集合中排名 >= min <= max的元素
     * @param $key
     * @param $min
     * @param $max
     * @return int
     */
    public function zRemRangeByRank($key, $min, $max)
    {
        return self::$redis -> zRemRangeByRank($key, $min, $max);
    }

    /**
     * 迭代寻找元素
     * @param $key
     * @param $pattern
     * @param $count
     * @return array
     */
    public function zScan($key, $pattern, $count)
    {
        $it = null;
        $resultArray = array();

        do{
            $result = self::$redis -> zScan($key, $it, $pattern, $count);

            if(is_array($result)){
                $resultArray = array_merge($resultArray, $result);
            }
        }while($it > 0);

        return $resultArray;
    }
















}

echo '<pre>';
$redis = RedisClass::getSingleInstance('127.0.0.1', '6379');
echo 'set<br>';
//var_dump($redis -> sPop('set'));
//var_dump($redis -> sRandMember('set', 2));
//var_dump($redis -> sRemove('set2', 'c', 'd'));
//var_dump($redis -> sUnion('set', 'set2'));
var_dump($redis -> sScan('set', '*', 10));