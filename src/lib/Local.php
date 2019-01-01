<?php
/**
 * 本地文件上传
 * @author        shuguang <5565907@qq.com>
 * @link          http://www.bagesoft.cn
 * @package       Library
 */
namespace shuguang\lib;

use shuguang\base\File;
use shuguang\Image;

class Local
{
    private $file;

    /**
     * 实体文件上传
     * @access public
     * @param  string   $name 名称
     * @return null|array|File
     */
    public function file($name = '')
    {
        if (empty($this->file)) {
            $this->file = isset($_FILES) ? $_FILES : [];
        }
        $files = $this->file;
        if (!empty($files)) {
            if (strpos($name, '.')) {
                list($name, $sub) = explode('.', $name);
            }
            // 处理上传文件
            $array = $this->dealFile($files, $name);
            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }
        return;
    }

    /**
     * 整理上传文件
     * @param  File $files 上传文件
     * @param  string $name  文件名
     * @return object
     */
    protected function dealFile($files, $name)
    {
        $array = [];
        foreach ($files as $key => $file) {
            if ($file instanceof File) {
                $array[$key] = $file;
            } elseif (is_array($file['name'])) {
                $item = [];
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($file['error'][$i] > 0) {
                        continue;
                    }
                    $temp['key'] = $key;
                    foreach ($keys as $_key) {
                        $temp[$_key] = $file[$_key][$i];
                    }
                    $item[] = (new File($temp['tmp_name']))->setUploadInfo($temp);
                }
                $array[$key] = $item;
            } else {
                if ($file['error'] > 0) {
                    continue;
                }
                $array[$key] = (new File($file['tmp_name']))->setUploadInfo($file);
            }
        }
        return $array;
    }

    /**
     * 参数检测
     * @param  object $file 文件
     * @param  array $args 参数
     * @return object
     */
    public function check($file, $args)
    {
        $validate = [];
        if (intval($args['size']) > 0) {
            $validate['size'] = $args['size'] * 1024;
        }
        if ($args['exts']) {
            $validate['ext'] = $args['exts'];
        }
        if ($args['mimes']) {
            $validate['type'] = $args['mimes'];
        }
        if (count($validate) > 1) {
            $file->validate($validate);
        }
        $file->rule($args['rule']);
        return $file;
    }

    /**
     * 字段映射
     * @param  object $file 文件
     * @return array
     */
    public function attrMap($file, $args)
    {
        $ltrim = './';
        $fileFmt = $this->pathFmt($file->getPathname(), $ltrim);
        $fileinfo = [
            'name' => $file->getInfo('name'),
            'type' => $file->getMime(),
            'size' => $file->getSize(),
            'ext' => $file->getExtension(),
            'savename' => $file->getFilename(),
            'savepath' => $this->pathFmt($file->getPath(), $ltrim),
            'file' => $fileFmt,
            'pathname' => $this->pathFmt($file->getPathName()),
            'fileurl' => \Yii::$app->request->hostInfo . \Yii::getAlias('@web') . '/' . $fileFmt,
            'fileserv' => $args['fileserv'] . '/' . $file->getSaveName(),
        ];
        //缩略图
        if ($args['thumb'] == 'Y' && in_array($file->getExtension(), ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
            $thumbSize = $args['thumbSize'] ? explode('x', $args['thumbSize']) : [300, 300];
            $thumbEx = explode('.', $file->getBasename());
            $thumbName = $thumbEx[0] . '_s.' . $thumbEx[1];
            $thumbFile = str_replace($file->getBasename(), $thumbName, $file->getRealPath());
            $image = Image::open($file)->thumb($thumbSize[0], $thumbSize[1])->save($thumbFile, null, $args['quality']);
            $fileinfo['thumb'] = str_replace($fileinfo['savename'], $thumbName, $fileinfo['file']);
            $fileinfo['thumburl'] = str_replace($fileinfo['savename'], $thumbName, $fileinfo['fileurl']);
            $fileinfo['thumbserv'] = $args['fileserv'] . '/' . str_replace($file->getFilename(), $thumbName, $file->getSaveName());
        }
        //水印
        if ($args['water'] && is_file($args['waterFile'])) {
            Image::open($file)->water($args['waterFile'], $args['waterPos'], $args['waterAlpha'])->save($file->getRealPath(), null, $args['quality']);
        }
        return $fileinfo;
    }

    /**
     * 路径格式化
     * @param  string $path 路径
     * @return string
     */
    public function pathFmt($path, $ltrim = '')
    {
        return str_replace('\\', '/', ltrim($path, $ltrim));
    }

    protected function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => 'upload File size exceeds the maximum value',
            2 => 'upload File size exceeds the maximum value',
            3 => 'only the portion of file is uploaded',
            4 => 'no file to uploaded',
            6 => 'upload temp dir not found',
            7 => 'file write error',
        ];
        $msg = $fileUploadErrors[$error];
        throw new \Exception($msg);
    }
}
