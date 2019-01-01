<?php
namespace shuguang\base;

use bagesoft\models\Config as MConfig;

class Config
{
    //用户输入参数
    private $file = [];
    private static $instance = null;

    //默认参数
    private static $def = [
        'mimes' => '', //允许上传的文件MiMe类型
        'size' => 0, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png', //允许上传的文件后缀
        'root' => './uploads', //保存根路径
        'replace' => true, //存在同名是否覆盖
        'quality' => 100, //存在同名是否覆盖
        'rule' => 'Ymd', //目录及命名规则
        'thumb' => 'Y', //是否保存缩略图
        'thumbSize' => '200x300', //缩略图尺寸
        'water' => 'Y', //是否打水印
        'waterFile' => './static/watermark.png', //水印文件,
        'waterAlpha' => 100, //水印透明度
        'waterPos' => 9, //水印位置
        'fileserv' => '', //文件服务器
    ];

    /**
     * 数据库配置实例
     * @return array
     */
    private static function instance()
    {
        if (null == self::$instance) {
            $arr = [];
            $data = MConfig::find()->where('m=:m', ['m' => 'upload'])->all();
            foreach ($data as $key => $row) {
                $arr[$row->var] = $row->val;
            }
            self::$instance = $arr;
        }
        return self::$instance;
    }

    /**
     * 初始化参数
     * @param  array  $args 自定义参数
     * @return array
     */
    public static function init($args = [])
    {
        $dbconf = self::instance();
        self::$def['size'] = $args['size'] ? $args['size'] : $dbconf['upload_max_size'];
        self::$def['exts'] = $args['exts'] ? $args['exts'] : $dbconf['upload_allow_ext'];
        self::$def['root'] = $args['root'] ? $args['root'] : $dbconf['upload_root'];
        self::$def['water'] = $args['water'] ? $args['size'] : $dbconf['upload_water'];
        self::$def['waterFile'] = $args['waterFile'] ? $args['waterFile'] : $dbconf['upload_water_file'];
        self::$def['waterAlpha'] = $args['waterAlpha'] ? $args['waterAlpha'] : $dbconf['upload_water_alpha'];
        self::$def['waterPos'] = $args['waterPos'] ? $args['waterPos'] : $dbconf['upload_water_pos'];
        self::$def['thumb'] = $args['thumb'] ? $args['thumb'] : $dbconf['upload_thumb'] ;
        self::$def['thumbSize'] = $args['thumbSize'] ? $args['thumbSize'] : $dbconf['upload_thumb_size'];
        self::$def['quality'] = $args['quality'] ? $args['quality'] : $dbconf['upload_quality'];
        self::$def['rule'] = $args['rule'] ? $args['rule'] : $dbconf['upload_rule'];
        self::$def['fileserv'] = $args['fileserv'] ? $args['fileserv'] : $dbconf['upload_fileserv'];
    }

    /**
     * 获取参数
     * @param  string $attr 字段
     * @return string|array
     */
    public static function args($attr = '')
    {
        if ($attr) {
            return self::$def[$attr];
        } else {
            return self::$def;
        }
    }

}
