<?php
require $_SERVER['DOCUMENT_ROOT'] . "/dao/ABSTRACT-DAO.php";
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

    function storeMedia($main_dvo){
        $returnVal = $default = 0;
        try {
            $query="INSERT INTO media (authid, url, type, height, width, mimetype, filesize) VALUES(?, ?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('isiiisi', $main_dvo->USERID, $main_dvo->IMAGEURL, $main_dvo->MEDIATYPE, $main_dvo->HEIGHT, $main_dvo->WIDTH, $main_dvo->MIMETYPE, $main_dvo->FILESIZE);
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
            $query="INSERT INTO author (name, ipaddress) VALUES(?,?)";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('ss', $main_dvo->AUTHORNAME, $main_dvo->IPADDR);
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
        $returnVal = $imageArr = [];
        try {
            $limit = "";
            if(isset($main_dvo->LIMIT))
                $limit = "LIMIT $main_dvo->LIMIT";
            $query = "SELECT id, url, height, width, mimetype, type FROM media WHERE status = 1 ORDER BY id DESC $limit";

            $stmt = $this->myslqi->prepare($query);
            $MEDIAURL = $NID = $HEIGHT = $WIDTH = $MIMETYPE = $TYPE = array(); 
            $CLASS = '';
            if ($stmt->execute()) {
                $stmt->bind_result($NID, $MEDIAURL, $HEIGHT, $WIDTH, $MIMETYPE, $TYPE);
                while ($stmt->fetch()) {
                    $CLASS = $WIDTH > $HEIGHT ? 'landscape':'portrait';
                    if($TYPE == 1){
                        array_push($imageArr, array('ID'=>$NID, 'MEDIA'=>$MEDIAURL, 'HEIGHT'=>$HEIGHT, 'WIDTH'=>$WIDTH, 'MIMETYPE'=>$MIMETYPE, 'CLASS'=>$CLASS, 'TYPE'=>$TYPE));
                    }else
                        array_push($returnVal, array('ID'=>$NID, 'MEDIA'=>$MEDIAURL, 'HEIGHT'=>$HEIGHT, 'WIDTH'=>$WIDTH, 'MIMETYPE'=>$MIMETYPE, 'CLASS'=>$CLASS, 'TYPE'=>$TYPE));
                }
            } else {
                $this->logError($this->myslqi->errno, $this->myslqi->error, 'getAllPackages');
            }
            $stmt->free_result();
            $stmt->close();
        } catch (Exception $e) {
            $this->logException($e);
        }
        return [$returnVal, $imageArr];
    }
   
    public function checkIfIPExists($main_dvo) {
        $returnVal = $count = 0;
        try {
            $query = "SELECT count(id) FROM author WHERE ipaddress = ? limit 1";
            $stmt = $this->myslqi->prepare($query);
            $stmt->bind_param('s', $main_dvo->IPADDR);

            if ($stmt->execute()) {
                $stmt->bind_result($count);
                if ($stmt->fetch()) {
                    $returnVal = $count;
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
   
    public function getCountOfUploadedFiles($main_dvo) {
        $returnVal = [];
        $count = $type = $totalCount = 0;
        try {
            $query = " SELECT count(id), type FROM media WHERE authid = ? GROUP BY type";
            $stmt = $this->myslqi->prepare($query);
            $stmt->bind_param('s', $main_dvo->USERID);

            if ($stmt->execute()) {
                $stmt->bind_result($count, $type);
                while ($stmt->fetch()) {
                    $returnVal[$type] = $count;
                    if(is_numeric($count))
                        $totalCount += $count;
                }
                $returnVal['3'] = $totalCount;
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