<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 18/06/18
 * Time: 09:42
 * this file should be required in any page to give the ability to make class autoloading available
 * and through that to have access to common configuration and error and exception handling
 */
function my_class_autoload($class) {
    $class = str_replace('\\', '/', $class);
    # echo "Loading $class ...<br/>";
    if (file_exists($class . '.php')) {
        require_once $class . '.php';
    } else {
        throw new \Exception('Class file not found');
    }
}
// Use default autoload implementation
spl_autoload_extensions(".php, .inc");
spl_autoload_register('my_class_autoload');

// Add your class dir to include path
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');
