<?php
include "ABSTRACT-DAO.php";
// include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

Class MAIN_DAO extends AbstractDAO{

    function employerLoginAuth($main_dvo){
        $returnVal = 0;
        $aid = $DTPASSWORD = $password = $userId = "";
        try {
            $qprechkq="SELECT createdon, nstatus FROM adminlogin WHERE pwd = SHA2(?, 256) AND emlid = ?";
            $qprechk=$this->myslqi->prepare($qprechkq);
            $qprechk->bind_param('ss', $main_dvo->STRUSERNAME, $main_dvo->STRBINPASSWORD);

            if ($qprechk->execute()) {
                
            $qprechk->bind_result($aid, $DTPASSWORD);
                if ($qprechk->fetch()) {
                   $main_dvo->ADMINID = $aid;
                   $main_dvo->DTPASSWORD = $DTPASSWORD;
            }
            }

            $qprechk->free_result();
            $qprechk->close();
            $query1 = "SELECT COUNT(1) FROM adminlogin WHERE pwd = SHA2(?, 256) AND emlid = ?";
            $stmt2 = $this->myslqi->prepare($query1);
            $stmt2->bind_param('ss', $password, $userId);

            $userId = $main_dvo->STRUSERNAME;
            $password = $main_dvo->STRBINPASSWORD;

            if ($stmt2->execute()) {
                $stmt2->bind_result($count);
                if ($stmt2->fetch()) {
                    $returnVal = $count;
                } else {
                    $returnVal = 0;
                    $this->logError($this->myslqi->errno, $this->myslqi->error, 'employerLoginAuth');
                }
            } else {
                $returnVal = 0;
                $this->logError($this->myslqi->errno, $this->myslqi->error, 'employerLoginAuth');
            }
        } catch (Exception $e) {
            $this->logException($e);
            $returnVal = 0;
        }
        return $returnVal;

    }

    function addImage($main_dvo){
        $returnVal = 0;
        try {
            $query="INSERT INTO image (authid, image) VALUES(?, ?)";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('is', $main_dvo->NID, $main_dvo->IMAGEURL);
            } catch (\Throwable $th) {
                $this->logException($th);
                return $returnVal;
            }

            if ($stmt->execute()) 
                $returnVal = 1;
            $stmt->free_result();
            $stmt->close();
        } catch (Exception $e) {
            $this->logException($e);
            $returnVal = 0;
        }
        return $returnVal;
    }

    function addAuthor($main_dvo){
        $returnVal = 0;
        try {
            $query="INSERT INTO author (name) VALUES(?)";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('s', $main_dvo->AUTHORNAME);
            } catch (\Throwable $th) {
                $this->logException($th);
                return $returnVal;
            }
            if ($stmt->execute()) {
                if(isset($stmt->insert_id))
                    $returnVal = $stmt->insert_id;
            }
            $stmt->free_result();
            $stmt->close();
        } catch (Exception $e) {
            $this->logException($e);
            $returnVal = 0;
        }
        return $returnVal;
    }

    public function getImages($main_dvo) {
        $returnVal = [];
        try {
            $limit = "";
            if(isset($main_dvo->LIMIT))
                $limit = "LIMIT $main_dvo->LIMIT";
            $query = "SELECT id, image FROM image WHERE status = 1 ORDER BY id DESC $limit";

            $stmt = $this->myslqi->prepare($query);
            $IMAGEURL = $NID = array(); 

            if ($stmt->execute()) {
                $stmt->bind_result($NID, $IMAGEURL);
                while ($stmt->fetch()) {
                    array_push($returnVal, array('ID'=>$NID, 'IMAGE'=>$IMAGEURL));
                }
            } else {
                $this->logError($this->myslqi->errno, $this->myslqi->error, 'getAllPackages');
            }
            $stmt->free_result();
            $stmt->close();
        } catch (Exception $e) {
            $this->logException($e);
        }
        return $returnVal;
    }
}
?>