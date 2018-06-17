<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 15/06/18
 * Time: 15:48
 * License GPL-v3
 *
 * Description of Database
 *
 * This class provides PHP 7 connectivity to MySQL 5.7
 * It provides full query response, and paginated record sets for a query,
 * giving a default solution for the N+1 access model
 *
 * @author vasileios
 */

namespace Classes;

class Database {

    const MAX_PAGE = 5000;
    const BATCH_SIZE = 20;

    private $DbConn = null;
    private $DbQuery = null;

    public function __construct() {
        $this->initialise();
    }

    private function setBatchSizeConfig() {
        if (!isset($this->DbQuery['BatchSizeConfig'])) {
            $conf = \Classes\Config::getConfig();
            $mbsc = (int) ($conf['database_server_one']['maxBatch'] ?? self::MAX_PAGE );
            $bsc = (int) ($conf['database_server_one']['batch'] ?? self::BATCH_SIZE);
            $this->DbQuery['BatchSizeConfig'] = (0 < $bsc && $bsc <= $mbsc) ? $bsc : self::BATCH_SIZE;
        }
    }

    private function initialise() {
        $this->DbQuery = array();
        if (!isset($this->DbConn)) {
            $this->connection();
        }
        $this->setBatchSizeConfig();
        $this->DbQuery['BatchSize'] = $this->DbQuery['BatchSizeConfig'];
        $this->DbQuery['BatchPage'] = 0;
        $this->DbQuery['BatchQuery'] = '';
        $this->DbQuery['BatchEnd'] = null;
        $this->DbQuery['BatchQueryChecked'] = false;
    }

    private function connection() {
        if (is_null($this->DbConn)) {
            $conf = \Classes\Config::getConfig();
            try {
                switch ($conf['database_server_one']['type']) {
                    case 'mysqli':
                        # var_dump($conf);
                        $this->DbConn = new \mysqli($conf['database_server_one']['host']
                            , $conf['database_server_one']['user']
                            , $conf['database_server_one']['password']
                            , $conf['database_server_one']['database']);
                        if(! $this->DbConn){
                            throw new \Exception("Database connection refused\n");
                        }
                        break;
                    # feel free to add more database support here
                }
            } catch (\Exception $ex) {
                if (ini_get('display_errors') === "1") {
                    echo $ex->getMessage();
                    error_log($ex->getMessage());
                }
            } catch (\Error $error){
                if (ini_get('display_errors') === "1") {
                    error_log($error);
                    echo ($error);
                }
            }
        }
        return $this->DbConn;
    }

    public function setBatchQuery($query, $batchSize = null) {
        $this->connection();
        if (!$this->DbQuery['BatchQueryChecked']) {
            $this->initialise();
            $limit_pos = strripos($query, 'limit');
            if ($limit_pos > -1) {
                $query = substr($query, 0, $limit_pos);
            }
            $this->DbQuery['BatchQueryChecked'] = true;
        }
        $this->DbQuery['BatchQuery'] = $query;
        $this->setBatchSize(( (int) $batchSize) > 0 ? (int) $batchSize : $this->DbQuery['BatchSize'] );
    }

    public function query($query) {
        $return = null;
        try {
            if(!isset($this->DbConn)){
                $this->connection();
            }
            $return = is_object($this->DbConn) ? $this->DbConn->query($query) : null;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        return $return;
    }

    public function resetBatchQuery() {
        $this->DbQuery['BatchQuery'] = '';
        $this->DbQuery['BatchPage'] = 0;
        $this->DbQuery['BatchEnd'] = null;
        $this->DbQuery['BatchQueryChecked'] = false;
    }

    public function getBatchNextPage() {
        $return = null;
        $rec_count = null;
        if($this->connection()){
            try {
                if ($this->DbQuery['BatchQuery'] > '' && !$this->DbQuery['BatchEnd']) {
                    $sql = $this->DbQuery['BatchQuery'] . ' LIMIT '
                        . (string) ($this->DbQuery['BatchPage'] * $this->DbQuery['BatchSize'])
                        . ',' . (string) ($this->DbQuery['BatchSize']);
                    $return = $this->DbConn->query($sql);
                    if (is_object($return) && get_class($return) == 'mysqli_result') {
                        $rec_count = ($return->num_rows);
                    }
                    if ($rec_count == $this->DbQuery['BatchSize']) {
                        $this->DbQuery['BatchPage'] ++;
                        $this->DbQuery['BatchEnd'] = false;
                    } else {
                        $this->DbQuery['BatchEnd'] = true;
                    }
                }
            } catch (\Error $error){
                var_dump($error);
            } catch (\Exception $exception){
                var_dump($exception);
            }
        }
        return $return;
    }

    public function setBatchSize(int $batchsize) {

        if ($batchsize > 0 && $batchsize <= self::MAX_PAGE) {
            $this->DbQuery['BatchSize'] = $batchsize;
            $this->DbQuery['BatchEnd'] = null;
            $this->DbQuery['BatchPage'] = 0;
            $this->DbQuery['BatchQueryChecked'] = false;
        } else {
            echo 'Error: Batch size out of bounds<br/>';
        }
        # echo "New batch size is ",$this->DbQuery['BatchSize']," <br/>";
    }

    public static function tableRecordSet($recordset): string {
        $return = '';
        if (gettype($recordset) == 'object') {
            $tablestyle = '<table style="table-border: 1px solid black;border-collapse: collapse; "><thead><tr>';
            $th_style = "<th style='border:2px solid red;padding: 2px 5px; background-color: lightcyan; font-variant: small-caps; '>";
            $td_style = "<td style='display: table-cell; border: 1px solid blue; padding: 2px 5px;background-color: lightyellow; '>";
            switch (get_class($recordset)) {
                case 'mysqli_result':
                    $fields = $recordset->fetch_fields();
                    $return .= $tablestyle;
                    foreach ($fields as $field) {
                        $return .= "$th_style$field->orgname</th>";
                    }
                    $return .= "</th></thead><tbody><tr>";
                    foreach ($recordset as $record) {
                        foreach ($record as $value) {
                            $return .= "$td_style$value</td>";
                        }
                        $return .= '</tr><tr>';
                    }
                    $return .= '</tr></tbody></table>';
                    break;
            }
        }
        return $return;
    }

    public function resetConnection() {
        $this->DbConn = null;
    }
}
