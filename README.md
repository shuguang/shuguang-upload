!!本上传类库只支持bagecms项目使用，其它项目使用请自行删除base\Config::instance()中数据库配置读取部分

## 安装

> composer require shuguang/upload

## 使用

~~~
$file = \shuguang\Upload::file('file');

~~~