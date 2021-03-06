<?php
/**
 * Guild - Topic Daily Build System.
 *
 * @link       http://git.intra.weibo.com/huati/daily-build
 * @copyright  Copyright (c) 2009-2016 Weibo Inc. (http://weibo.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt   GPL License
 */

namespace Library\Util;

class Helper
{
    /**
     * Constant.
     */
    const BUILD_SUC = 'successfully';
    const BUILD_FAI = 'fail';
    
    /**
     * Throw messages to the terminal.
     *
     * @deprecated
     * @param $msg mixed
     */
    public static function console($msg)
    {
        if (APP_MODE == 'WEB') {
            return;
        }
        if (is_array($msg)) {
            var_dump($msg);
        } else {
            echo '[' . date('Y-m-d H:i:s', time()) . '] ' . $msg;
        }    
    }

    /**
     * Throw messages to the terminal in line.
     *
     * @deprecated
     * @param $msg mixed
     */
    public static function consoleLn($msg) {
        if (!is_array($msg)) {
            $msg .= "\n";
        }
        self::console($msg);
    }
    
    /**
     * Log.
     *
     * @param $filename string
     * @param $msg string
     */
    public static function logLn($filename, $msg) {
        self::consoleLn($msg);
        $path = dirname($filename);
        if (!is_dir($path)) {
            mkdir(iconv("UTF-8", "GBK", $path), 0777, true); 
            if (!chmod($path, 0777)) {
                die('unable to chmod.');
            }
        }
        error_log('[' . date('Y-m-d H:i:s', time()) . '] ' . $msg . "\n", 3, $filename);
    }

    /**
     * Set build result to the disk.
     *
     * @deprecated
     *
     * @param $msg string
     */
    public static function setBuildResult($msg = self::BUILD_SUC) {
        $buildResult =  Config::get("common.build.build_result");
        $fp = fopen($buildResult, "w");
        fwrite($fp, $msg);
        fclose($fp);
    }

    /**
     * Weather or not the current environment is debug mode.
     *
     * @return bool
     */
    public static function isDebug() 
    {
        if (DEBUG !== 'true') {
            return false;
        }
        return true;
    }

    /**
     * Change mode to files and directories.
     *
     * @param string @path
     * @param int $filemode
     * @param int $dirmode
     */
    public static function chmodr($path, $filemode, $dirmode) {
        if (is_dir($path) ) { 
            if (!chmod($path, $dirmode)) { 
                $dirmode_str=decoct($dirmode); 
                Helper::logLn(RUNTIME_LOG, "Failed applying filemode '$dirmode_str' on directory '$path'");
                Helper::logLn(RUNTIME_LOG, "  `-> the directory '$path' will be skipped from recursive chmod\n");
                return; 
            } 
            $dh = opendir($path); 
            while (($file = readdir($dh)) !== false) { 
                if ($file != '.' && $file != '..') {
                    $fullpath = $path.'/'.$file; 
                    self::chmodr($fullpath, $filemode,$dirmode); 
                } 
            } 
            closedir($dh); 
        } else { 
            if (is_link($path)) { 
                Helper::logLn(RUNTIME_LOG, "link '$path' is skipped\n");
                return; 
            } 
            if (!chmod($path, $filemode)) { 
                $filemode_str=decoct($filemode); 
                Helper::logLn(RUNTIME_LOG, "Failed applying filemode '$filemode_str' on file '$path'\n");
                return; 
            } 
        } 
    }
}
