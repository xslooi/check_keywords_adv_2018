<?php
/**
 * 2018年最新版广告法禁用词检测程序接口
 * ---------------------------------------------------------
 * 使用说明
 * 1、输入网址或者网页源代码
 * 2、点击检测按钮则返回检测结果
 * ---------------------------------------------------------
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);  //不限制 执行时间
date_default_timezone_set('Asia/Shanghai');
header("content-Type: text/javascript; charset=utf-8"); //语言强制
header('Cache-Control:no-cache,must-revalidate');
header('Pragma:no-cache');

//定义根目录
define('WEB_ROOT', str_replace("\\", '/', dirname(__FILE__)) );
define('INPUT_DIR', WEB_ROOT . '/input/');
define('OUTPUT_DIR', WEB_ROOT . '/output/');
define('VENDOR_DIR', WEB_ROOT . '/vendor/');

//todo 环境检测
//1、PHP版本 默认大于5.3
//2、函数库检测：打开文件夹需要 system 函数


//======================================================================================================================
//文件说明区
//======================================================================================================================
$site = isset($_POST['site']) ? $_POST['site'] : '';
$source_code = isset($_POST['sc']) ? $_POST['sc'] : '';
$platform = isset($_POST['pf']) ? $_POST['pf'] : '';
$info = isset($_POST['info']) ? $_POST['info'] : '';


//======================================================================================================================
//操作逻辑区
//======================================================================================================================
$response_array = array(
    'state' => 1,
    'msg' => 'ok',
    'data' => 'formatCodeOk',
);


if(!empty($info)){
    get_info($info);
}


if(empty($source_code) && empty($site)){
    $response_array = array(
        'state' => -1,
        'msg' => '网址和源码必须填写一个！',
        'data' => '',
    );
    exit(json_encode($response_array));
}

//curl 下载网页内容
if(empty($source_code) && !empty($site)){
    $source_code = get_html_code($site);
}

//基本路由：
if(!empty($platform)){

    //转换源码
    code_convert($source_code);
    //过滤源码
    code_filter($source_code);

    $result = check_keywords($source_code, $platform);

    $response_array = array(
        'state' => 1,
        'msg' => 'ok',
        'data' => $result,
    );
    exit(json_encode($response_array));
}
else{
    $response_array = array(
        'state' => -1,
        'msg' => 'Not Found Platform :' . $platform,
        'data' => '',
    );
    exit(json_encode($response_array));
}

//======================================================================================================================
//函数库区
//======================================================================================================================
function check_keywords($souces_code, $platform=''){
    if(empty($souces_code)){
        return '';
    }

    $find_result = array();

    $ad_keywords = include 'ad_keywords.php';
    $keywords_normal = $ad_keywords['normal'];
    $keywords_industry = $ad_keywords['industry'];

    if(empty($platform)){
        echo 'Empty $platform';

    }else{
        switch($platform){
            case 'food':
                foreach($keywords_industry as $key=>$val){

                    if('01' == $key){

                        foreach($val['words'] as $item){
                            if(false !== mb_stripos($souces_code, $item)){
                                $find_result[] = array(
                                    'number' => $key,
                                    'key' => $val['key'],
                                    'word' => $item,
                                );
                            }
                        }
                    }

                }

                break;
            case 'cosmetics':
            foreach($keywords_industry as $key=>$val){

                if('02' == $key){

                    foreach($val['words'] as $item){
                        if(false !== mb_stripos($souces_code, $item)){
                            $find_result[] = array(
                                'number' => $key,
                                'key' => $val['key'],
                                'word' => $item,
                            );
                        }
                    }
                }

            }

            break;
            case 'medical':
            foreach($keywords_industry as $key=>$val){

                if('03' == $key){

                    foreach($val['words'] as $item){
                        if(false !== mb_stripos($souces_code, $item)){
                            $find_result[] = array(
                                'number' => $key,
                                'key' => $val['key'],
                                'word' => $item,
                            );
                        }
                    }
                }

            }

            break;
            case 'realty':

            foreach($keywords_industry as $key=>$val){

                if('04' == $key){

                    foreach($val['words'] as $item){
                        if(false !== mb_stripos($souces_code, $item)){
                            $find_result[] = array(
                                'number' => $key,
                                'key' => $val['key'],
                                'word' => $item,
                            );
                        }
                    }
                }

            }

            break;
            case 'normal':

                foreach($keywords_normal as $key=>$val){

                    foreach($val['words'] as $item){
                        if(false !== mb_stripos($souces_code, $item)){
                            $find_result[] = array(
                                'number' => $key,
                                'key' => $val['key'],
                                'word' => $item,
                            );
                        }
                    }
                }

                break;
            default: {
                echo 'default';
            }
        }
    }

//    TODO 匹配模式 ：先匹配长词，匹配完成后替换为空，再匹配短词

    return $find_result;
}


/**
 * 根据 行业 关键词 返回行业词性说明
 * @param $info
 */
function get_info($info){

    $ad_keywords = include 'ad_keywords.php';
    $keywords_normal = $ad_keywords['normal'];
    $keywords_industry = $ad_keywords['industry'];

    $response_array = array();

    switch($info){
        case 'food':
            $response_array = $keywords_industry['01']['info'];

            break;
        case 'cosmetics':
            $response_array = $keywords_industry['02']['info'];

            break;
        case 'medical':
            $response_array = $keywords_industry['03']['info'];

            break;
        case 'realty':
            $response_array = $keywords_industry['04']['info'];

            break;
        default: {
            echo 'default';
        }
    }


    $response_array = array(
        'state' => 1,
        'msg' => 'success',
        'data' => $response_array,
    );
    exit(json_encode($response_array));
}
/**
 * 文件编码转换GB2312 转换为 utf8
 * @param $html_body
 */
function code_convert(&$html_body){
    //TODO 暂时忽略 = 左右两边的空白字符

    if(30 > strlen($html_body)){
        return false;
    }

    //gb2312 转 utf8
    if(false !== stripos($html_body, 'charset="gb2312"', 20) || false !== stripos($html_body, 'charset=gb2312', 20)){
        $html_body = str_ireplace('charset="gb2312"', 'charset="utf-8"', $html_body);
        $html_body = str_ireplace('charset=gb2312', 'charset=utf-8', $html_body);
        $html_body = iconv("gb2312", "utf-8//IGNORE", $html_body);
    }

    //gbk 转 utf8
    if(false !== stripos($html_body, 'charset="gbk"', 20) || false !== stripos($html_body, 'charset=gbk', 20)){
        $html_body = str_ireplace('charset="gbk"', 'charset="utf-8"', $html_body);
        $html_body = str_ireplace('charset=gbk', 'charset=utf-8', $html_body);
        $html_body = iconv("gbk", "utf-8//IGNORE", $html_body);
    }
}

/**
 * 过滤源码如：js代码、css代码，属性英文等
 * @param $html_string
 */
function code_filter(&$html_string){
//    $html_string = strip_tags($html_string);
}


/**
 * 下载 指定URL 的 HTML 文档
 * @param $url
 * @return mixed
 */
function get_html_code($url){
    $ch = curl_init();
    $timeout = 10;
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
}

/**
 * 输出错误日志 如：文件为空、 indent 异常等
 * @param $data
 */
function log_record($data){
    $content = "\r\n-----------------------------------------------------------------\r\n";
    $content .= var_export($data, true);
    $content .= "\r\n-----------------------------------------------------------------\r\n";
    file_put_contents('log_record.log', $content, FILE_APPEND);
}