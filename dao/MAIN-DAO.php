<?php
require $_SERVER['DOCUMENT_ROOT'] . "/dao/ABSTRACT-DAO.php";
// include_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

Class MAIN_DAO extends AbstractDAO{

    public $rows_per_page = 10; //Number of records to display per page
    public $total_rows = 0; //Total number of rows returned by the query
    public $links_per_page = 5; //Number of links to display per page
    public $page = 1;
    public $max_pages = 0;
    public $offset = 0;
    public $startnum = 1;
    public $endnum = 0;
    public $pagination = 'ON';

    function employerLoginAuth($main_dvo):int{
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

    function storeMedia($main_dvo):int{
        $returnVal = $default = 0;
        try {
            $query="INSERT INTO media (authid, url, caption, type, height, width, mimetype, filesize) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('issiiisi', $main_dvo->USERID, $main_dvo->IMAGEURL, $main_dvo->CAPTION, $main_dvo->MEDIATYPE, $main_dvo->HEIGHT, $main_dvo->WIDTH, $main_dvo->MIMETYPE, $main_dvo->FILESIZE);
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

    function addAuthor($main_dvo):int{
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

    public function getMedia($main_dvo):array {
        $returnVal = [];
        try {
            $limit = $offset = "";
            if(isset($main_dvo->LIMIT))
                $limit = "LIMIT $main_dvo->LIMIT";

            if(isset($main_dvo->PAGINATION) && !empty($main_dvo->OFFSET))
                $offset = "OFFSET $main_dvo->OFFSET";
            $query = "SELECT id, url, height, width, mimetype, type, caption FROM media WHERE status = 1 ORDER BY id DESC $limit $offset";
            $stmt = $this->myslqi->prepare($query);
            $MEDIAURL = $NID = $HEIGHT = $WIDTH = $MIMETYPE = $TYPE = $CAPTION = array(); 
            $CLASS = '';
            if ($stmt->execute()) {
                $stmt->bind_result($NID, $MEDIAURL, $HEIGHT, $WIDTH, $MIMETYPE, $TYPE, $CAPTION);
                while ($stmt->fetch()) {
                    $CLASS = $WIDTH > $HEIGHT ? 'landscape':'portrait';
                    array_push($returnVal, array('ID'=>$NID, 'MEDIA'=>$MEDIAURL, 'HEIGHT'=>$HEIGHT, 'WIDTH'=>$WIDTH, 'MIMETYPE'=>$MIMETYPE, 'CLASS'=>$CLASS, 'TYPE'=>$TYPE, 'CAPTION'=>$CAPTION));
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
   
    public function checkIfIPExistsNGetUser($main_dvo):array {
        $returnVal = []; 
        $count = $id = 0;
        try {
            $query = "SELECT count(id), id FROM author WHERE ipaddress = ? limit 1";
            $stmt = $this->myslqi->prepare($query);
            $stmt->bind_param('s', $main_dvo->IPADDR);

            if ($stmt->execute()) {
                $stmt->bind_result($count, $id);
                if ($stmt->fetch()) {
                    $returnVal[0] = $count;
                    $returnVal[1] = $id;
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
   
    //Used to track no of files uploaded by user
    public function getCountOfUploadedFiles($main_dvo):array {
        $returnVal = [];
        $count = $type = $totalCount = 0;
        try {
            $query = "SELECT count(id), type FROM media WHERE authid = ? GROUP BY type";
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

    //Used to confirm that media file exists before deleting it
    public function getMediaById($main_dvo):array {
        $returnVal = [];
        try {
            $query = "SELECT id, url FROM media WHERE id = ? AND status = 1";
            $stmt = $this->myslqi->prepare($query);
            $stmt->bind_param('i', $main_dvo->UNIQUEID);

            $NID = $MEDIAURL = '';
            if ($stmt->execute()) {
                $stmt->bind_result($NID, $MEDIAURL);
                if ($stmt->fetch()) {
                    $returnVal['ID'] = $NID;
                    $returnVal['MEDIA'] = $MEDIAURL;
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

     //status = (3-delete)
    function changeStatus($main_dvo, $status = 0){
        $returnVal = 0;
        try {
            $query="UPDATE media SET status = ?, updatedat = NOW() WHERE id = ?";
            try {
                $stmt = $this->myslqi->prepare($query);
                $stmt->bind_param('ii', $status ,$main_dvo->UNIQUEID);
            } catch (\Throwable $th) {
                $this->logException($th);
                return $returnVal;
            }

            if ($stmt->execute()) {   
                $returnVal = 1;
            }
            $stmt->free_result();
            $stmt->close();
        } catch (Exception $e) {
            $this->logException($e);
            $returnVal = 0;
        }
        return $returnVal;
    }


    //For pagination
    function paginate() {
        $returnVal = $count = 0;
        try {
            $totalRowCountQuery = "SELECT count(id) FROM media WHERE status = 1";
            $stmt = $this->myslqi->prepare($totalRowCountQuery);
            if ($stmt->execute()) {
                $stmt->bind_result($count);
                while ($stmt->fetch()) {
                    $this->total_rows = $count;
                    
                    //Max number of pages
                    $this->max_pages = ceil($this->total_rows / $this->rows_per_page);
                    if ($this->links_per_page > $this->max_pages) {
                        $this->links_per_page = $this->max_pages;
                    }

                    //Check the page value just in case someone is trying to input an aribitrary value
                    if ($this->page > $this->max_pages || $this->page <= 0) {
                        $this->page = 1;
                    }
            
                    //Calculate Offset
                    $this->offset = $this->rows_per_page * ($this->page - 1);
                }
            } else {
                $this->logError($this->myslqi->errno, $this->myslqi->error, 'paginate');
                $this->logException($this->myslqi->error);
                return  0;
            }
        }catch (Exception $e) {
            $this->logException($e);
            $returnVal = 0;
        }
        return $returnVal;
    }

    function renderFirst($str, $tag = 'First') {
        if ($this->total_rows == 0)
            return FALSE;
        if ($this->page != 1) {

            if ($this->page == 1) {
                $page = 1;
                $str = str_replace('~pagenum~', $page, $str);

                return "<li class='page-item active'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            } else {
                $page = 1;
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            }
        }
    }

    function renderLast($str, $tag = 'Last') {
        if ($this->total_rows == 0)
            return FALSE;
        if ($this->page != $this->max_pages) {
            if ($this->page == $this->max_pages) {
                $page = 1;
    
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item active'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            } else {
                $page = $this->max_pages;
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item'><a class='page-link' onclick='$str' href='javascript:void(0)'>$tag</a></li>";
            }
        }
    }

    function renderNext($str, $tag = 'Next') {
        if ($this->total_rows == 0)
            return FALSE;
        if ($this->page != $this->max_pages) {
            if ($this->page < $this->max_pages) {
                $page = $this->page + 1;
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            } else {
                return "<li class='page-item active'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            }
        }
    }

    function renderPrev($str, $tag = 'Prev') {
        if ($this->total_rows == 0)
            return FALSE;
        if ($this->page != 1) {
            if ($this->page > 1) {
                $page = $this->page - 1;
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            } else {
                $page = $this->page - 1;
                $str = str_replace('~pagenum~', $page, $str);
                return "<li class='page-item active'> <a class='page-link' onclick='$str' href='javascript:void(0);'>$tag</a></li>";
            }
        }
    }

    function renderNav($str, $prefix = "<li class='page-item'>", $suffix = "</li>") {
        if ($this->total_rows == 0)
            return FALSE;
        $batch = ceil($this->page / $this->links_per_page);
        $end = $batch * $this->links_per_page;

        if ($end > $this->max_pages) {
            $end = $this->max_pages;
        }
        $start = $end - $this->links_per_page + 1;
        $links = '';
        $tempstr = $str;
        for ($i = $start; $i <= $end; $i++) {
            $page = $i;
            $str = str_replace('~pagenum~', $page, $tempstr);
            if ($i == $this->page) {
                $links .= "<li class='page-item active'><a class='page-link' href='javascript:void(0);'' >$i</a></li>";
            } else {
                $links .= " $prefix <a class='page-link' onclick='$str' href='javascript:void(0);'> $i</a>$suffix ";
            }
        }
        return $links;
    }

    function renderFullNav($str) {
        $this->paginate();
        if ($this->total_rows > $this->rows_per_page) {
            // error_log("first -- ".$this->renderFirst($str));
            // error_log("Prev -- ".$this->renderPrev($str));
            // error_log("Nav -- ".$this->renderNav($str));
            // error_log("Last -- ".$this->renderLast($str));
            // error_log("Next -- ".$this->renderNext($str));
            
            return $this->renderPrev($str) . $this->renderNav($str) . $this->renderNext($str);
        }
    }
    //For pagination
}
?>