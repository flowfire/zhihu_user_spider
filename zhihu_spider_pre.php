<?php

const MYSQL_SERVER = "localhost";
const MYSQL_USER = "root";
const MYSQL_PASSWORD = "";

// 为知乎用户抓取信息新建数据库

$db = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);
$db->query('SET NAMES UTF8');

if ($db->connect_error) {
    echo $db->connect_error;
}

$sql1 = "DROP DATABASE IF EXISTS zhihu";
$sql2 = "CREATE DATABASE zhihu DEFAULT CHARACTER SET UTF8";


if ($db->query($sql1) && $db->query($sql2)) {

    unset($sql1);
    unset($sql2);

    $db->select_db("zhihu");

    // 创建用户信息的列表
    $sql = "CREATE TABLE zhihu_user(
        answer_count int(10) DEFAULT 0 COMMENT '回答数量',
        articles_count int(10) DEFAULT 0 COMMENT '文章数量',
        follower_count int(10) DEFAULT 0 COMMENT '专注者数量',
        gender int(1) NULL COMMENT '性别，-1男，1女',
        is_advertiser int(1) NULL COMMENT '大概是广告认证吧',
        is_followed int(1) NULL COMMENT '大概是他有没有关注你，DONTCARE',
        is_following int(1) NULL COMMENT '大概是你有没有关注他',
        is_org int(1) NULL COMMENT '大概是组织账号',
        name tinytext NULL COMMENT '姓名',
        type tinytext NULL COMMENT '类型',
        user_type tinytext NULL COMMENT '用户类型',
        avatar_url tinytext NULL COMMENT '小头像',
        avatar_url_template tinytext NULL COMMENT '不同尺寸头像',
        url tinytext NULL COMMENT 'url',
        url_token tinytext NULL COMMENT 'url_token',
        headline tinytext NULL COMMENT '个人简介',
        badge text NULL COMMENT '官方认证',
        id char(32) NOT NULL UNIQUE COMMENT 'ID'
    )DEFAULT CHARSET=UTF8";

    if ($db->query($sql)){
        echo "success";
    }else{
        echo $db->error;
    }

}else{
    echo $db->error;
}