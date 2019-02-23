<?php

/**
 * MIME 类型
 * @author        shuguang <5565907@qq.com>
 * @link          http://www.bagesoft.cn
 */

namespace shuguang\base;

class Helper
{
    /**
     * 生成GUID
     * @param bool $opt 是否保留  {}
     */
    public static function guid($opt = true)
    {
        if (function_exists('com_create_guid')) {
            if ($opt) {return com_create_guid();} else {return trim(com_create_guid(), '{}');}
        } else {
            mt_srand((double) microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $left_curly = $opt ? chr(123) : '';
            $right_curly = $opt ? chr(125) : '';
            $uuid = $left_curly
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
                . $right_curly;
            return $uuid;
        }
    }
}
