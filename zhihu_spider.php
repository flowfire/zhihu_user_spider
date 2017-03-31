<?php
set_time_limit(0);
$startTime = microtime(true) * 1000;

// 设置从哪个用户开始抓取。
const START_USER = "flowfire";

// 维护一个用户队列，按照队列来抓取。
$userList = Array();
array_push($userList, START_USER);

// 维护一个用户列表，如果用户已经被抓取过则不再抓取。
$userTokens = Array();
array_push($userTokens, START_USER);

// COOKIE 存放位置
const COOKIE = '';

// HTTPHEADER ，理论上不需要修改
const HEADER = Array(
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Encoding: gzip, deflate, sdch, br',
    'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4',
    'Cache-Control: max-age=0',
    'Connection: keep-alive',
    'DNT: 1',
    'Host: www.zhihu.com',
    'Upgrade-Insecure-Requests: 1',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.110 Safari/537.36',
);

// 抓取 url
function getUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, HEADER);
    curl_setopt($ch, CURLOPT_COOKIE, COOKIE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'\\CA\\cacert.pem');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    return curl_exec($ch);
}

// 一页一页抓取用户信息并合并到一起
function getUser($token = START_USER) {
    $url = "https://www.zhihu.com/api/v4/members/$token/followees?offset=0&limit=20";
    $datas = Array();
    $getPage = function($next = NULL) use ($url,&$datas,&$getPage) {
        if ($next == NULL) {
            $next = $url;
        }
        $curlReturn = getUrl($next);
        $curlReturn = json_decode($curlReturn, true);
        $datas = array_merge($datas, $curlReturn['data']);
        if ($curlReturn['paging']['is_end']) return;
        $next = str_replace("http://", "https://", $curlReturn['paging']['next']);
        $getPage($next);
    };
    $getPage();
    return $datas;
}

// 处理抓取到的用户数据
function saveData($datas){
    global $db;
    while ($data = array_shift($datas)) {
        $keys = [];
        $values = [];
        foreach ($data as $key => $value){
            if ($key === 'badge'){
                $value = json_encode($value);
            }
            $value = '\''.addslashes($value).'\'';
            array_push($keys,$key);
            array_push($values,$value);
        }
        $keys = '('.implode(',',$keys).')';
        $values = '('.implode(',',$values).')';
        $sql = "INSERT INTO zhihu_user $keys VALUES $values";
        @$db->query($sql);

    }
}

// 连接数据库
const MYSQL_SERVER = "localhost";
const MYSQL_USER = "root";
const MYSQL_PASSWORD = "";
$db = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$db->query('SET NAMES UTF8');
$db->select_db('zhihu');


//循环处理用户
while ($user = array_shift($userList)){
    $datas = getUser($user);
    saveData($datas);
    foreach ($datas as $key => $data){
        $token = $data['url_token'];
        if (!in_array($token, $userTokens)){
            array_push($userList, $token);
            array_push($userTokens, $token);
        }
    }
}

// 结束时输出时间
$endTime = microtime(true) * 1000;
echo "执行时间：" , $endTime - $startTime , "ms";
