<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:48
 */

namespace Classes;

class ExceptionHandler implements ExceptionHandlerInterface
{

    public function supports(\Exception $e)
    {
        return $e instanceof \Exception;
    }

    public static function handle(\Exception $e)
    {
        $msg  = get_class($e)." : ".($e->getMessage() ?? '') ." : ";
        $msg .= ($e->getCode() ?? '')."<br/>".($e->getFile() ?? '')
            .' : '.($e->getLine() ?? '' )."<br/>";
        foreach ($e->getTrace() as $k => $v ){
            if(gettype($v) === 'array'){
                foreach ($v as $ks => $ms){
                    if(gettype($ms) === 'array'){
                        $msg .= implode(',',$ms);
                    } else {
                        $msg .= ($ms ?? '').'<br/>';
                    }
                }
            } else {
                $msg .= $v;
            }
        }
        echo $msg;
        error_log($msg);
        return true;
    }

}
interface ExceptionHandlerInterface
{
    public function supports(\Exception $e);
    public static function handle(\Exception $e);
}
