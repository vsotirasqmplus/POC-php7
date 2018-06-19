<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:48
 * License GPL-v3
 */

require_once $_SERVER['DOCUMENT_ROOT'].
    DIRECTORY_SEPARATOR.
    ( (explode(DIRECTORY_SEPARATOR,$_SERVER['REQUEST_URI'])[1]) ?? '.').
    DIRECTORY_SEPARATOR.'setup.php';

echo <<<HEAD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>N+1 Batches within PHP 7 Class Autoload</title>
    </head>
    <body>
HEAD;
# echo $_SERVER['DOCUMENT_ROOT'],'<BR/>';
$query = 'select id,username, firstname, middlename,lastname 
from mdl_user where lastname like "%kala%" order by username limit 0,18';

# all records in one request, direct query
# $DB = new \Classes\Database();
# $recordset = $DB->query($query);
# echo \Classes\Database::tableRecordSet($recordset);
# or as on line
# echo \Classes\Database::tableRecordSet((new \Classes\Database())->query($query));

# all records in batches, Object Instance Usage
$DB = new \Classes\Database();
$DB->setBatchQuery($query);
$next = true;
while ($next) {
    $recordset = $DB->getBatchNextPage();
    if ($next = ( isset($recordset) && is_object($recordset))) {
        switch (get_class($recordset)) {
            case 'mysqli_result':
                if ($recordset->num_rows > 0) {
                    echo \Classes\Database::tableRecordSet($recordset);
                    flush();
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
