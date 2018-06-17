<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:48
 * License GPL-v3
 *
 * Description of Config
 * This class is loading defaults from an INI file
 *
 * @author vasileios
 */

namespace Classes;

function my_error_handler($e, $str, $file, $line, $trace): callable
{
    # echo $e->getMessage();
    error_log("<pre>$str [$e] <br/>$file:$line</pre>");
    #print_r($trace);
    # echo '</pre>';
    /*    foreach ($trace as $msg) {
            echo "$msg<br/>";
        };*/
    return true;
}

function my_exception_handler(\Throwable $e)
{
    $msg = (get_class($e) ?? '') . '<br/>';
    $msg .= '<pre>' . $e->getMessage() . '</pre>';
    ob_start();
    debug_print_backtrace();
    $o = ob_get_clean();
    $o = str_replace(',', "\n", $o);
    $msg .= $o;
    error_log($msg);
    echo $msg;
}

set_error_handler(array('\Classes\ErrorHandler', 'handle'));
set_exception_handler(array('\Classes\ExceptionHandler', 'handle'));


class Config
{

    private static $CFG = null;

    //put your code here
    function __construct()
    {
        self::init();
    }

    public function __get($param)
    {
        return self::$db[$param] ?? null;
    }

    public static function getConfig()
    {
        if (!isset(self::$CFG)) {
            self::init();
        }
        return self::$CFG;
    }

    public static function init($config_file = '')
    {
        error_reporting(E_ALL);
        /* @var $ini_set int */
        ini_set('display_errors', 1);
        if (file_exists($config_file)) {
            self::$CFG = parse_ini_file($config_file, true);
        } else if (file_exists('config/.configuration.ini')) {
            self::$CFG = parse_ini_file('config/.configuration.ini', true);
        }
        #if(ini_get('display_errors')=="1"){ var_dump(self::$CFG); }
    }

}
