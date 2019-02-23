<?php
/**
 * Raw Data
 * @author        shuguang <5565907@qq.com>
 * @link          http://www.bagesoft.cn
 * @package       Library
 */

namespace shuguang\lib;

use shuguang\base\Helper;
use shuguang\base\Config;
use shuguang\base\Mime;

class Raw
{
    private $error; //错误信息
    private $src; //原始内容
    private $data; //数据
    private $mime; //Mime
    private $suffix; //扩展名
    private $rule = 'Ym'; //目录生成规则
    private $type; //数据类型

    /**
     * 移动文件
     * @param  string $file 文件内容
     * @param  string $name 文件名
     * @param  array $args 参数
     * @return 
     */
    public function move($file, $name,$args )
    {
        Config::init($args);
        $conf = Config::args();
        if (substr($file, 0, 5) == 'data:' && strpos($file, 'base64,') !== false) {
            preg_match('/^data:(.*);base64,(.*)/i', $file, $match);
            $this->type = 'base64';
        } else if (substr($file, 0, 5) == 'data:') {
            preg_match('/^data:()(.*)/i', $file, $match);
            $this->type = 'data';
        }
        $this->src = $match[0];
        $this->mime = $match[1];
        $this->data = $match[2];
        $this->rule = $conf['rule'];
        if (false == $this->test($conf, $name)) {
            return false;
        }
        $root = rtrim($conf['root'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $saveName = $this->buildSaveName($name);
        $filename = $root . $saveName;
        // 检测目录
        if (false === $this->testPath(dirname($filename))) {
            return false;
        }
        file_put_contents($filename, base64_decode($this->data));
        return true;
    }

    /**
     * 合法性检测
     * @param  array $conf 配置参数
     * @return boolean
     */
    private function test($conf)
    {
        if (false == $this->testData()
            || (isset($conf['size']) && $conf['size'] > 0 && !$this->testSize($conf['size']))
            || (isset($conf['exts']) && !$this->testExt($conf))
        ) {
            return false;
        }
        return true;
    }

    /**
     * 检测内容
     * @return boolean
     */
    private function testData()
    {
        if (empty($this->data)) {
            $this->error = 'source is empty!';
            return false;
        }
        return true;
    }

    /**
     * 获取保存文件名
     * @param  string  $savename    保存的文件名 默认自动生成
     * @return string
     */
    protected function buildSaveName($savename)
    {
        $path = $this->autoPath();
        if ($savename) {
            $savename = $path . $savename;
        } elseif ($this->suffix) {
            $savename = $path . Helper::guid(false) . '.' . $this->suffix;
        }else{
            $savename = $path . Helper::guid(false) ;
        }
        return $savename;
    }

    /**
     * 自动生成目录名
     * @return string
     */
    protected function autoPath()
    {
        if ($this->rule instanceof \Closure) {
            $path = call_user_func_array($this->rule, [$this]);
        } else {
            switch ($this->rule) {
                case 'Ymd':
                    $path = date('Y/m/d') . DIRECTORY_SEPARATOR;
                    break;
                case 'Ym':
                    $path = date('Y/m') . DIRECTORY_SEPARATOR;
                    break;
                case 'Y':
                    $path = date('Y') . DIRECTORY_SEPARATOR;
                    break;
                case 'date':
                    $path = date('Ymd') . DIRECTORY_SEPARATOR;
                    break;
                default:
                    if (is_callable($this->rule)) {
                        $path = call_user_func($this->rule);
                    } else {
                        $path = date('Ymd') . DIRECTORY_SEPARATOR;
                    }
            }
        }
        return $path;
    }

    /**
     * 检测扩展名
     * @param  array $conf 配置参数
     * @return boolean
     */
    private function testExt($conf)
    {
        $suffix = array_search($this->mime, Mime::$list);
        if ($this->type == 'data' || ($this->type == 'base64' && $suffix && in_array($suffix, explode(',', $conf['exts'])))) {
            $this->suffix = $suffix;
            return true;
        } else {
            $this->error = 'extensions to upload is not allowed';
            return false;
        }
    }

    /**
     * 检测大小
     * @param  int $size 大小
     * @return boolean
     */
    private function testSize($size)
    {
        $data = base64_decode($this->file);
        if (strlen($data) > $size) {
            $this->error = 'filesize not match';
            return false;
        }
        return true;
    }

    /**
     * 检测目录
     * @param  string $path 目录
     * @return boolean
     */
    private function testPath($path)
    {
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, 0755, true)) {
            return true;
        }
        $this->error = 'directory {:path} creation failed';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
