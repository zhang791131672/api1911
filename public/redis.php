<?php
$redis=new Redis();
$redis->connect('127.0.0.1',6379);
$redis->auth('密码');
$redis->set('name','zhangsan');
$name=$redis->get('name');
echo $name;
