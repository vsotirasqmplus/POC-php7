<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:27
 */

namespace Classes;


class ErrorHandler
{
    private $err, $msg, $file, $line, $trace;

    /**
     * ErrorHandler constructor.
     * @param $e
     * @param $str
     * @param $file
     * @param $line
     * @param $trace
     */
    public function __construct($str, $file, $line, $trace)
    {
        $this->msg = $str;
#        $this->e = $e;
        $this->file = $file;
        $this->line = $line;
        $this->trace = $trace;
        # echo $e->getMessage();
        error_log("<pre>$str <br/>$file:$line</pre>");
        #print_r($trace);
        # echo '</pre>';
        /*    foreach ($trace as $msg) {
                echo "$msg<br/>";
            };*/
        echo $str,' ',$file,' ',$line,'<br/>';
        return true;
    }

    /**
     * @param $errno
     * @param $errmsg
     * @param $filename
     * @param $line
     * @param $vars
     * @return bool|void
     */
    public static function handle($errno, $errmsg, $filename,
                                  $line, $vars)
    {
        $self = new self($errmsg, $filename, $line, $vars);
        switch ($errno) {
            case E_USER_ERROR:
                return $self->handleError();
            case E_USER_WARNING:
            case E_WARNING:
                return $self->handleWarning();
            case E_USER_NOTICE:
            case E_NOTICE:
                return $self->handleNotice();
            default:
                return false;
        }
    }

    /**
     *
     */
    public function handleError()
    {
        ob_start();
        debug_print_backtrace();
        $backtrace = ob_get_flush();
        $body = <<<EOT
A fatal error occured in the application:
Message:   {$this->message}
File:      {$this->filename}
Line:      {$this->line}
Backtrace:
{$backtrace}
EOT;
        error_log($body, 1, 'sysadmin@example.com',
            "Fatal error occurred\n");
        exit(1);
    }

    /**
     * @return bool
     */
    public function handleWarning()
    {
        $body = <<<EOT
An environmental error occured in the application, and may 
 indicate a potential larger issue:
Message:   {$this->msg}
File:      {$this->file}
Line:      {$this->line}
EOT;
        return error_log($body, 1, 'sysadmin@example.com',
            "Subject: Non-fatal error occurred");
    }

    /**
     * @return bool
     */
    public function handleNotice()
    {
        $file = $file ?? 'null';
        $body = <<<EOT
A NOTICE was raised with the following information:
Message:   {$this->msg}
File:      {$this->file}
Line:      {$this->line}
EOT;
        $body = date('[Y-m-d H:i:s] ') . $body . "\n";
        return error_log($body, 1/*, $this->_noticeLog ?? ""*/);
    }
}
