<?php

include '../db/DBConnection.php';

abstract class AbstractDAO
{
    //
    public $errorMsg = array();
    public $exceptionMsg = array();
    public $customer_array = array();
    public $connection = '';
    public $myslqi = '';
    public $totalRowCountQuery;
    public $php_self;
    public $rows_per_page = 10; //Number of records to display per page
    public $total_rows = 0; //Total number of rows returned by the query
    public $links_per_page = 5; //Number of links to display per page
    public $page = 1;
    public $max_pages = 0;
    public $offset = 0;
    public $startnum = 1;
    public $endnum = 0;
    public $pagination = 'ON'; //Default pagination is ON
    
    
    /**
     * [[Description]]
     * @private
     */
    public function __construct() {
        $this->connection = DBConnection::getInstance();
        $this->myslqi = $this->connection->getMySQLIConnection();
    }

    /**
     * [[Description]]
     * @param [[Type]] $e [[Description]]
     */
    public function logException($e) {
        try {
            //array_push($this->exceptionMsg, $e->getMessage());
            $this->exceptionMsg[] = $e->getMessage();
            error_log("Error in DAO :: ".$e->getMessage());
        } catch (Exception $e1) {
            
        }
    }
    
    /**
     * [[Description]]
     * @param [[Type]] $errcd        [[Description]]
     * @param [[Type]] $errmsg       [[Description]]
     * @param [[Type]] $functionName [[Description]]
     */
    public function logError($errcd, $errmsg, $functionName) {
        try {
            $this->errCd = $errcd;
            //array_push($this->errorMsg, $errcd . ':[' . $functionName . ']' . $errmsg);
            $this->errorMsg = "$errcd . ':' . $functionName . '' . $errmsg";
            //echo $errcd . ':[' . $functionName . ']' . $errmsg;
            $this->errorMsg = $errmsg;
            $this->funcName = $functionName;
        } catch (Exception $e1) {
            
        }
    }
     
    /**
     * [[Description]]
     * @param  [[Type]] [$default = false] [[Description]]
     * @return boolean  [[Description]]
     */
    public function autoCommit($default = false) {
        mysqli_autocommit($this->myslqi, $default);
        return true;
    }

    /**
     * [[Description]]
     * @return boolean [[Description]]
     */
    public function mysqliCommit() {
        mysqli_commit($this->myslqi);
        return true;
    }

    /**
     * [[Description]]
     * @return boolean [[Description]]
     */
    public function mysqliRollBack() {
        mysqli_rollback($this->myslqi);
        return true;
    }
    
    /**
     * [[Description]]
     * @return boolean [[Description]]
     */
    public function closeMySQLIConnection() {
        $this->connection->closeMySQLIConnection();
        return true;
    }
    
}
?>