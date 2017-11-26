<?php




class CFileHelper
{

    
    public static function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    
    public static function copyDirectory($src, $dst, $options = array())
    {
        $fileTypes = array();
        $exclude = array();
        $level = -1;
        extract($options);
        if (!is_dir($dst))
            self::mkdir($dst, $options, true);

        self::copyDirectoryRecursive($src, $dst, '', $fileTypes, $exclude, $level, $options);
    }

    
    protected static function applyFilters($list, $options)
    {
        $pregs = array();
        $pregsName = array();

        $excludePregs = array();
        $excludePregsName = array();

        extract($options['filters']);

        $res = array();
        foreach ($list as $item) {
            $include = false || (empty($pregs) && empty($pregsName));
            $exclude = false;

            
            $name = pathinfo($item, PATHINFO_BASENAME);
            foreach ($pregs as $preg) {
                if (preg_match($preg, $item)) {
                    $include = true;
                }
            }
            foreach ($pregsName as $pregName) {
                if (preg_match($pregName, $name)) {
                    $include = true;
                }
            }
                        foreach ($excludePregs as $exludePreg) {
                if (preg_match($exludePreg, $item)) {
                    $exclude = true;
                }
            }
            foreach ($excludePregsName as $exludePregName) {
                if (preg_match($exludePregName, $name)) {
                    $exclude = true;
                }
            }
            if ($include && !$exclude) {
                $res[] = $item;
            }
        }

        return $res;
    }

    
    public static function findFiles($dir, $options = array())
    {
        $fileTypes = array();
        $exclude = array();
        $level = -1;
        extract($options);
        $list = self::findFilesRecursive($dir, '', $fileTypes, $exclude, $level);
        sort($list);

        if (isset($options['filters'])) {
            $list = self::applyFilters($list, $options);
        }

        return $list;
    }

    
    public static function findDirs($dir, $options = array())
    {
        $fileTypes = array();
        $exclude = array();
        $level = -1;
        extract($options);
        $list = self::findDirsRecursive($dir, '', $fileTypes, $exclude, $level);
        sort($list);

        if (isset($options['filters'])) {
            $list = self::applyFilters($list, $options);
        }

        return $list;
    }

    
    protected static function copyDirectoryRecursive($src, $dst, $base,
            $fileTypes, $exclude, $level, $options)
    {
        if (!is_dir($dst))
            self::mkdir($dst, $options, false);

        $folder = opendir($src);
        while (($file = readdir($folder)) !== false) {
            if ($file === '.' || $file === '..')
                continue;
            $path = $src . DIRECTORY_SEPARATOR . $file;
            $isFile = is_file($path);

            $copyPath = true;
            if (isset($options['pathMatchCallback']) && $options['pathMatchCallback']) {
                $callback = $options['pathMatchCallback'];
                $copyPath = call_user_func_array($callback, array($path));
            };

            if ($copyPath && self::validatePath($base, $file, $isFile, $fileTypes, $exclude)) {
                if ($isFile) {
                    copy($path, $dst . DIRECTORY_SEPARATOR . $file);
                    if (isset($options['newFileMode']))
                        chmod($dst . DIRECTORY_SEPARATOR . $file, $options['newFileMode']);
                }
                elseif ($level)
                    self::copyDirectoryRecursive($path, $dst . DIRECTORY_SEPARATOR . $file, $base . '/' . $file, $fileTypes, $exclude, $level - 1, $options);
            }
        }
        closedir($folder);
    }

    
    protected static function findFilesRecursive($dir, $base, $fileTypes,
            $exclude, $level)
    {
        $list = array();
        if (!is_dir($dir)){
            trigger_error("Dir can'n open by path {$dir}", E_USER_NOTICE);
            return $list;
        }            
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..')
                continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $isFile = is_file($path);
            if (self::validatePath($base, $file, $isFile, $fileTypes, $exclude)) {
                if ($isFile)
                    $list[] = str_replace('\\', '/', $path);
                elseif ($level)
                    $list = array_merge($list, self::findFilesRecursive($path, $base . '/' . $file, $fileTypes, $exclude, $level - 1));
            }
        }
        closedir($handle);
        return $list;
    }

    
    protected static function findDirsRecursive($dir, $base, $fileTypes,
            $exclude, $level)
    {
        if (!is_dir($dir)) {
            return array();
        }
        $list = array();
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..')
                continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $isFile = is_file($path);
            if (self::validatePath($base, $file, $isFile, $fileTypes, $exclude)) {
                if ($isFile) {
                                    } elseif ($level) {
                    $list[] = str_replace('\\', '/', $path);
                    $list = array_merge($list, self::findDirsRecursive($path, $base . '/' . $file, $fileTypes, $exclude, $level - 1));
                }
            }
        }
        closedir($handle);
        return $list;
    }

    
    protected static function validatePath($base, $file, $isFile, $fileTypes,
            $exclude)
    {
        foreach ($exclude as $e) {
            if ($file === $e || strpos($base . '/' . $file, $e) === 0)
                return false;
        }
        if (!$isFile || empty($fileTypes))
            return true;
        if (($type = pathinfo($file, PATHINFO_EXTENSION)) !== '')
            return in_array($type, $fileTypes);
        else
            return false;
    }

    
    public static function getMimeType($file, $magicFile = null,
            $checkExtension = true)
    {
        if (function_exists('finfo_open')) {
            $options = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            $info = $magicFile === null ? finfo_open($options) : finfo_open($options, $magicFile);

            if ($info && ($result = finfo_file($info, $file)) !== false)
                return $result;
        }

        if (function_exists('mime_content_type') && ($result = mime_content_type($file)) !== false)
            return $result;

        return $checkExtension ? self::getMimeTypeByExtension($file) : null;
    }

    
    public static function getMimeTypeByExtension($file, $magicFile = null)
    {
        static $extensions, $customExtensions = array();
        if ($magicFile === null && $extensions === null)
            $extensions = require(Yii::getPathOfAlias('system.utils.mimeTypes') . '.php');
        elseif ($magicFile !== null && !isset($customExtensions[$magicFile]))
            $customExtensions[$magicFile] = require($magicFile);
        if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
            $ext = strtolower($ext);
            if ($magicFile === null && isset($extensions[$ext]))
                return $extensions[$ext];
            elseif ($magicFile !== null && isset($customExtensions[$magicFile][$ext]))
                return $customExtensions[$magicFile][$ext];
        }
        return null;
    }

    
    public static function mkdir($dst, array $options, $recursive)
    {
        $prevDir = dirname($dst);
        if ($recursive && !is_dir($dst) && !is_dir($prevDir))
            self::mkdir(dirname($dst), $options, true);

        $mode = isset($options['newDirMode']) ? $options['newDirMode'] : 0777;
        $res = true;
        if (!is_dir($dst)){
            $res = mkdir($dst, $mode);
        }
        chmod($dst, $mode);
        return $res;
    }

}
