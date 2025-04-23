<?php
/**
 * Description of DBConnection
 *

 */
   require 'db_conf.php';
class DBConnection {
    private $dbConnection;
    private $dbSelect;
    private $dbMysqlIConn;
    private static $_singleton;
    public $__enable_profiler = false;
    public $calling_function = '';

    public function getConnection(){
        if(!$this->dbConnection)
        {
            try{
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                $this->dbConnection=mysql_connect(AAConf::get_databaseURL(),
                        AAConf::get_databaseUName(),AAConf::get_databasePWord());
            }catch(Exception $e){
                echo "ERR101 : Error processing request\n";
            }
        }
        return $this->dbConnection;
    }
    
    public function getSelectedDB(){
        if(!$this->dbSelect)
        {
            try{
                $this->dbSelect=mysql_select_db(AAConf::get_databaseName(),
                $this->dbConnection);
            }catch(Exception $e){
                echo "ERR102 : Error processing request\n";
            }
        }
        return $this->dbSelect;
    }
    public function closeConnection() {
        if($this->dbConnection){
            try{
                mysql_close($this->dbConnection);
                $this->dbConnection=null;
            }catch(Exception $e){
                echo "ERR103 : Error processing request\n";
            }
        }
    }

    public function getMySQLIConnection($database='default'){
        if(!$this->dbMysqlIConn){
            try{
                if($database==AAConf::get_databaseName1()) {
                    $this->dbMysqlIConn = new mysqli(AAConf::get_databaseHost(),AAConf::get_databaseUName(),AAConf::get_databasePWord(),AAConf::get_databaseName1(),AAConf::get_databasePort());
                } else {
                    $this->dbMysqlIConn = new mysqli(AAConf::get_databaseHost(),AAConf::get_databaseUName(),AAConf::get_databasePWord(),AAConf::get_databaseName(),AAConf::get_databasePort());
                    error_log(AAConf::get_databaseHost() ." -- ". AAConf::get_databaseUName()." -- ".AAConf::get_databasePWord()." -- ".AAConf::get_databaseName()." -- ".AAConf::get_databasePort());
                }
            }catch(Exception $e){
                echo "ERR104 : Error processing request\n";
            }
        }
        $this->enable_profiler();
        return $this->dbMysqlIConn;
    }

    public function closeMySQLIConnection(){
        if($this->dbMysqlIConn){
            try{                
                $this->getProfilerData($this->dbMysqlIConn);  
                $this->dbMysqlIConn->close();
            }catch(Exception $e){
                echo "ERR105 : Error processing request\n";
            }
        }
    }

    public static function getInstance() {
        if(!self::$_singleton) {
            self::$_singleton = new DBConnection();
        }
        return self::$_singleton;
    }
    
    
    public function enable_profiler(){
        if($this->dbMysqlIConn && $this->__enable_profiler){
            $query0 = 'set profiling=1';
            $stmt0 = $this->dbMysqlIConn->prepare($query0);
            if ($stmt0->execute()) {
               // echo '*** <br />';
            }
        }
        return false;
    }
    
    
    public function getProfilerData($connp1){
        if($connp1 && $this->__enable_profiler){
            $query = 'show profiles';
            $stmt12 = $connp1->prepare($query);//$this->dbMysqlIConn->prepare($query);
            if(is_object($stmt12)){
                
                if ($stmt12->execute()) {
                    $stmt12->bind_result($id, $time, $query);
                    //print_R($stmt12);
                    $queries = array();
                    ///print 'before while';
                    $cnt = 0;
                    while($stmt12->fetch()) {
                        $queries[$cnt]['time'] = $time . '( '.round($time, 4) * 1000 .' ms )';
                        $queries[$cnt]['query'] = $query;
                        $queries[$cnt]['id'] = $id;
                        if($this->calling_function){
                            $queries[$cnt]['calling_func'] = $this->calling_function;
                        }
                        $cnt++;
                    }
                    print '<pre>';
                    print_R($queries);
                    print '</pre>';
                    return true;

                }
            }   
        }
    }

}
?>