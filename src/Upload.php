<?php
/**
 * 文件上传
 * @author        shuguang <5565907@qq.com>
 * @link          http://www.bagesoft.cn
 * @package       Library
 */
namespace shuguang;

use shuguang\lib\Local;
use shuguang\base\Config;

class Upload
{
    private static $file;

    /**
     * 获取上传的文件信息
     * @access public
     * @param  string   $name 名称
     * @return null|array|File
     */
    public static function file($name = '', $args = [])
    {
        try {
            $local = new Local();
            Config::init($args);
            $file = $local->file($name);
            if (is_array($file)) {
                foreach ($file as $key => $file) {
                    $fileinfo = $local->check($file, Config::args())->move(Config::args('root'));
                    if ($fileinfo) {
                        self::$file[] = $local->attrMap($fileinfo, Config::args());
                    }
                }
            } elseif (is_object($file)) {
                $fileinfo = $local->check($file, Config::args())->move(Config::args('root'));
                if (false == $fileinfo) {
                    return $file->getError();
                } else {
                    self::$file = $local->attrMap($fileinfo, Config::args());
                }
            }
            return self::$file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
