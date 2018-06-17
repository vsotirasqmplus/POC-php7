<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:48
 * License GPL-v3
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

echo <<<HEAD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>N+1 Batches within PHP 7 Class Autoload</title>
    </head>
    <body>
HEAD;
$query = 'select id,username, firstname, middlename,lastname 
from mdl_user where lastname like "%demi%" order by username limit 0,18';

# all records in one request, direct query
# $DB = new \Classes\Database();
# $recordset = $DB->query($query);
# echo \Classes\Database::tableRecordSet($recordset);
echo \Classes\Database::tableRecordSet((new \Classes\Database())->query($query));

# all records in batches, Object Instance Usage
$DB = new \Classes\Database();
$DB->setBatchQuery($query,10);
$next = true;
while ($next) {
    $recordset = $DB->getBatchNextPage();
    if ($next = ( isset($recordset) && is_object($recordset))) {
        switch (get_class($recordset)) {
            case 'mysqli_result':
                if ($recordset->num_rows > 0) {
                    echo \Classes\Database::tableRecordSet($recordset);
                }
                break;
        }
    }
}
# $a = new \Classes\Data();
echo <<<FOOTER
        <hr/>
    </body>
</html>
FOOTER;
