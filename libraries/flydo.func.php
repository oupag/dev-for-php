<?php

/**
 * 公共函数
 * @author: Skiychan <dev@skiy.net>
 */

date_default_timezone_set('Asia/Shanghai'); //'Asia/Shanghai'   亚洲/上海 

if (!function_exists('post')) {
    function post($key = '') {
        $post_data = $_POST;
        if (empty($post_data)) {
            $post_str = file_get_contents("php://input");
            $post_data = json_decode($post_str, true);

            if (empty($post_data)) {
                parse_str($post_str, $post_data);
            }
        }

        if ($key === '') {
            return $post_data;
        }

        return isset($post_data[$key]) ? $post_data[$key] : '';
    }
}

if (!function_exists('get')) {
    function get($key = '') {
        if ($key === '') {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : '';
    }
}


if (!function_exists('post_get')) {
    function post_get($key) {
        return isset($_POST[$key]) ? $_POST[$key] :
            (isset($_GET[$key]) ? $_GET[$key] : '');
    }
}

if (!function_exists('get_post')) {
    function get_post($key) {
        return isset($_GET[$key]) ? $_GET[$key] :
            (isset($_POST[$key]) ? $_POST[$key] : '');
    }
}

if (!function_exists('file_exists_error')) {
    function file_exists_error($filePath) {
        if (!file_exists($filePath)) {
            header("http/1.1 404 not found");
            exit;
        }
    }
}

if (!function_exists('http_post_data')) {
    /**
     * HTTP json数据请求函数
     * @param $url
     * @param $data_string
     * @return mixed
     */
    function http_post_data($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);//设置等待时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        return $result;
    }
}

if (!function_exists('sign_encode')) {
    /**
     * 签名
     * $params 要签名的参数(array / string)
     * $filter 过滤的参数键名 array,
     * $mv     键值为空的值是否移除 BOOL
     * $sort   排序 (1 键名)
     * $return 返回数组值 TRUE, 默认不返回 FALSE
     * @return array|string
     */
    function sign_encode($params, $filter = array(), $mv = TRUE, $sort = 1, $return = FALSE) {
        $tmp = array();
        if (is_string($params)) {
            parse_str($params, $tmp);

            empty($tmp) || $params = $tmp;
        }

        if (empty($params) || !is_array($params)) {
            return '';
        }

        $result = array();
        foreach ($params as $key => $value) {
            if (in_array($key, $filter)) {
                continue;
            }

            if ($mv && $value === '') {
                continue;
            }

            $result[$key] = urldecode($value);
        }

        switch ($sort) {
            case 1:
                ksort($result);
                break;
        }

        if ($return) {
            return $result;
        }

        return urldecode(http_build_query($result));
    }

}

if (!function_exists('characet')) {
    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {

        if (!empty($data)) {
            $fileType = "UTF-8";
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }
}

if (!function_exists('checkEmpty')) {
    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }
}

if (!function_exists('formatPubKey')) {
    /**格式化公钥
     * $pubKey PKCS#1格式的公钥串
     * return pem格式公钥， 可以保存为.pem文件
     */
    function formatPubKey($pubKey) {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }
}

if (!function_exists('formatPriKey')) {
    /**格式化公钥
     * $priKey PKCS#1格式的私钥串
     * return pem格式私钥， 可以保存为.pem文件
     */
    function formatPriKey($priKey) {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END RSA PRIVATE KEY-----";
        return $fKey;
    }
}

if (!function_exists('sign')) {
    /**RSA签名
     * $data待签名数据
     * $priKey商户私钥
     * 签名用商户私钥
     * 使用MD5摘要算法
     * 最后的签名，需要用base64编码
     * return Sign签名
     */
    function sign($data, $priKey) {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
}

if (!function_exists('verify')) {
    /**RSA验签
     * $data待签名数据
     * $sign需要验签的签名
     * $pubKey爱贝公钥
     * 验签用爱贝公钥，摘要算法为MD5
     * return 验签是否通过 bool值
     */
    function verify($data, $sign, $pubKey) {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }
}

if (!function_exists('getSignContent')) {
    function getSignContent($params, $postCharset = "UTF-8") {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = characet($v, $postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }
}

if (!function_exists('getSignContentUrlencode')) {
//此方法对value做urlencode
    function getSignContentUrlencode($params, $postCharset = "UTF-8") {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = characet($v, $postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . urlencode($v);
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . urlencode($v);
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }
}

if (!function_exists('debugLog')) {
    /**
     * 全新的DEBUG工具
     */
    function debugLog($param, $clear = false, $logname = "request") {
        if (defined("DEBUG") && DEBUG === false) {
            return false;
        }

        defined("APP_ROOT_PATH") || define('APP_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

        is_string($param) || $param = var_export($param, TRUE);

        $logpath = $logname;
        if (in_array($logname, array('request', 'pay', 'sign', 'test')) ||
            strstr($logname, 'request') || strstr($logname, 'pay') || strstr($logname, 'sign') || strstr($logname, 'test')) {
            $logpath = APP_ROOT_PATH . "logs/" . date('Y-m-d') . '_' . $logname . ".log";
        }

        if ($clear) {
            file_put_contents($logpath, date('Y-m-d H:i:s') . ":\r\n" . $param);
        } else {
            file_put_contents($logpath, "\r\n" . date('Y-m-d H:i:s') . ":\r\n" . $param . "\r\n\r\n", FILE_APPEND);
        }
    }
}

if (!function_exists('paylog')) {
    /**
     * 支付日志
     */
    function paylog($param, $chan, $type) {
        defined("APP_ROOT_PATH") || define('APP_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
        $log_path = APP_ROOT_PATH . "paylogs/{$chan}_%s.log";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $log_path = "E:\pay_tmp_logs\{$chan}_%s.log";
        }

        switch ($type) {
            //回调请求数据
            case 1:
                $log_path = sprintf($log_path, 'params');
                is_string($param) || $param = var_export($param, TRUE);
                break;

            //发货请求参数
            case 2:
                $log_path = sprintf($log_path, 'request');
                is_string($param) || $param = var_export($param, TRUE);
                break;

            //发货请求结果
            case 3:
                $log_path = sprintf($log_path, 'response');
                $result = "未知";
                if ($param['result'] == 'failure') {
                    $result = '发货失败';
                } else if ($param['result'] == 'success') {
                    $result = '发货成功';
                }

                $param = "状态:{$result}, 订单号:{$param['notice_sn']}, 金额:{$param['amount']}元, user_id:{$param['user_id']}";
                break;

            default:
                exit;
        }

        file_put_contents($log_path, date('Y-m-d H:i:s') . ":\r\n" . $param . "\r\n\r\n", FILE_APPEND);
    }
}

if (!function_exists('xml2Array')) {
    /**
     * 将XML转为Array
     * @param $xml
     * @return mixed
     */
    function xml2Array($xml) {
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
}

if (!function_exists('buildRequestForm')) {
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $url 转跳页面
     * @param $para_temp 请求参数数组
     * @return 提交表单HTML文本
     * @return string
     */
    function buildRequestForm($url, $para_temp) {

        $sHtml = "<form id='jssubmit' name='htmlsubmit' action='" . $url . "' method='POST'>";
        foreach ($para_temp as $key => $val) {
            if (false === checkEmpty($val)) {
                //$val = $this->characet($val, $this->postCharset);
                $val = str_replace("'", "&apos;", $val);
                //$val = str_replace("\"","&quot;",$val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;'></form>";

        $sHtml = $sHtml . "<script>document.forms['htmlsubmit'].submit();</script>";

        return $sHtml;
    }
}

if (!function_exists('pageExecute')) {
    /**
     * 执行转跳页功能
     * @param $gateway_url 网关
     * @param $params 参数数组
     * @param string $httpmethod 请求方法 (默认POST)
     * @return 提交表单HTML文本|string
     */
    function pageExecute($gateway_url, $params, $httpmethod = "POST") {
        if ("GET" == $httpmethod) {
            //拼接GET请求串
            $requestUrl = $gateway_url . "?" . http_build_query($params);

            return $requestUrl;
        } else {
            //拼接表单字符串
            return buildRequestForm($gateway_url, $params);
        }
    }
}

if (!function_exists('ramdom_md5')) {
    /**
     * 生成随机的32位字符串/
     * @param string $string
     * @return string
     */
    function ramdom_md5($string = '') {
        //获取当前时间的微秒
        list($usec, $sec) = explode(' ', microtime());
        $microtime = ((float)$usec + (float)$sec);
        $microtime = str_replace('.', '', $microtime);

        //将微秒时间加长一个0-1000的随机变量
        for ($i = 0; $i < 19; $i++) {
            $microtime .= rand(0, 9);
        }

        $long_string = $string . $microtime;

        //md5加密后再base64编码
        $result = sha1(base64_encode(md5($long_string, true)));

        return $result;
    }
}

if (!function_exists('page_format')) {
    /**
     * 设置网页格式
     * @param string $type 格式
     */
    function page_format($type = 'html') {
        $formats = [
            'json' => 'application/json',
            'array' => 'application/json',
            'csv' => 'application/csv',
            'html' => 'text/html',
            'jsonp' => 'application/javascript',
            'php' => 'text/plain',
            'serialized' => 'application/vnd.php.serialized',
            'xml' => 'application/xml'
        ];

        $contentType = $formats[$type] ?: $formats['html'];
        header('Content-type: ' . $contentType);
    }
}

if (!function_exists('random_string')) {
    /**
     * 创建随机字符串
     * @param    string    type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
     * @param    int    长度
     * @return    string
     */
    function random_string($type = 'alnum', $len = 8) {
        switch ($type) {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
    }
}

if (!function_exists('rand_number')) {
    /**
     * 生成随机数，并且过滤对应值
     * @param $start 开始(含)
     * @param $end 结束(含)
     * @param array $filter 过滤数组值
     * @return bool|int
     */
    function rand_number($start, $end, $filter = array()) {
        //过滤值已填满时则错误
        if (($end - $start + 1) == count($filter)) {
            return false;
        }
        $num = rand($start, $end);
        if (in_array($num, $filter)) {
            return rand_number($start, $end, $filter);
        }
        return $num;
    }
}

if (!function_exists('str_json_encode')) {
    /**
     * api不支持中文转义的json结构
     * @param array $arr
     */
    function str_json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys($arr);
        $max_length = count($arr) - 1;

        if (($keys [0] === 0) && ($keys [$max_length] === $max_length)) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for ($i = 0; $i < count($keys); $i++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }

        foreach ($arr as $key => $value) {
            if (is_array($value)) { //Custom handling for arrays
                if ($is_list)
                    $parts[] = str_json_encode($value); /* :RECURSION: */
                else
                    $parts[] = '"' . $key . '":' . str_json_encode($value); /* :RECURSION: */
            } else {
                $str = '';
                if (!$is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string($value) && is_numeric($value) && $value < 2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes($value) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode(',', $parts);
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

}

if (!function_exists('http_post')) {
    /**
     * POST 请求
     * @param string $url 链接
     * @param array $param 参数
     * @param array $headers 用户头部信息
     * @param boolean $post_file 是否文件上传
     * @param array $use_cert 用户证书 (数组或字符串)
     * @param string $second 超时时间
     * @return string content
     */
    function http_post($url, $param, $headers = array(), $post_file = false, $use_cert = array(), $second = 30) {
        $oCurl = curl_init();
        //设置超时
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $second);

        //设置头
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (empty($param)) {
            $strPOST = array();
        } elseif (is_string($param)) {
            $strPOST = $param;
        } elseif ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        //设置header
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        //设置证书
        if (!empty($use_cert)) {
            $sslkey = '';
            if (is_string($use_cert)) {
                $sslcert = $use_cert;
            } else if (is_array($use_cert)) {
                $sslcert = $use_cert[0];
                empty($use_cert[1]) || $sslkey = $use_cert[1];
            }
            //第一种方法，cert 与 key 分别属于两个.pem文件
            //第二种方式，两个文件合成一个.pem文件
            curl_setopt($oCurl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($oCurl, CURLOPT_SSLCERT, $sslcert);
            //第一种方式
            if (!empty($sslkey)) {
                curl_setopt($oCurl, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($oCurl, CURLOPT_SSLKEY, $sslkey);
            }
        }
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
}

if (!function_exists('http_get')) {
    /**
     * GET 请求
     * @param string $url 链接
     * @param array $headers 用户头部信息
     * @return string content
     */
    function http_get($url, $headers = array()) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
}

if (!function_exists('server_ip')) {
// 不安全的获取 IP 方式，在开启CDN的时候，如果被人猜到真实 IP，则可以伪造。
    function server_ip() {
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $arr = array_filter(explode(',', $ip));
            $ip = end($arr);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return long2ip(ip2long($ip));
    }
}

if (!function_exists('client_ip')) {
    /**
     * 获取客户端IP
     * @return array|false|string
     */
    function client_ip() {
        //判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            //不允许就使用getenv获取
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        return $realip;
    }
}