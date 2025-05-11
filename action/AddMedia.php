<?php
    session_start();
    require "../Constants.php";
    require '../dvo/MAIN-DVO.php';
    require '../dao/MAIN-DAO.php';
    require '../Functions.class.php';
    
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

    require $_SERVER['DOCUMENT_ROOT'] . '/ValidateUser.php';

    $returnArray = $uploadedImg = [];
    $save = $error = $msg = $main_dvo->NID = '';
    $addAuthor = 0;
    $queryXML = str_replace("\'", "'", $_REQUEST["xmlData"]);
    $xml = simplexml_load_string(htmlspecialchars_decode($queryXML));
    error_log("xml  ".print_r($xml, 1));
    $result = $xml->xpath("//author");
    if(isset($result[0]) && $result[0] != null){
        $main_dvo->AUTHORNAME = $functions->sanitizeInput(trim($result[0]));
    }

    $returnArray = [];
    if (isset($_FILES["file"]) && is_countable($_FILES["file"])) {
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";

        $webPaths = [];
        $uploadStatus = []; // To store per-file success or error
        $maxFileSizeImage = 500 * 1024; // 500 KB
        $maxFileSizeOtherMedia = 1000 * 1024; // 1Mb
        $imageCount = $otherMediaCount = 0;
        $errorMsg = "You can upload up to four media files: a maximum of two images (each up to 500KB), and two audio/video/GIF files (each between 500KB and 1MB).";
        $date=date_create("now",timezone_open("Asia/Kolkata"));
        
        // Normalize inputs
        $main_dvo->FILEARR = (array)$_FILES['file']['name'];
        $fileTmpNames = (array)$_FILES['file']['tmp_name'];
        $fileErrors = (array)$_FILES['file']['error'];
        $fileSizes = (array)$_FILES['file']['size'];
        try {
            if (count($main_dvo->FILEARR) > 4){
                throw new Exception($errorMsg);
            }
 
            //Sanitize file names and get count of media group wise
            $returnData = $functions->validateFileNTypes($main_dvo, $CONST_TYPE_IMAGE, $CONST_TYPE_OTHER_MEDIA);
            $imageCount = $returnData[0];
            $otherMediaCount = $returnData[1];

            if($imageCount > 2 || $otherMediaCount > 2){
                throw new Exception($errorMsg);
            }

            for ($i = 0; $i < count($main_dvo->FILEARR); $i++) {
                $originalName = $main_dvo->FILEARR[$i];
                $fileName = basename($originalName);
                
                $fileName = date_format($date, "Ymd_His") . "_" . rand(1000, 9999) . "." . pathinfo($fileName, PATHINFO_EXTENSION);
                $targetFilePath = $targetDir . $fileName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                if (!in_array($fileType, $CONST_ALLOWED_TYPES)) { 
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "Invalid file type: $originalName"
                    ];
                    continue;
                }
                                                                
                if ($fileErrors[$i] !== 0) {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "Upload error (code ".$fileErrors[$i].") for: $originalName"
                    ];
                    continue;
                }

                if ( (in_array($fileType, $CONST_TYPE_IMAGE) && $fileSizes[$i] > $maxFileSizeImage) || (in_array($fileType, $CONST_TYPE_OTHER_MEDIA) && $fileSizes[$i] > $maxFileSizeOtherMedia)) {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "File too large : $originalName"
                    ];
                    continue;    
                }

                if(isset($_COOKIE['userId']) || isset($_SESSION['userId'])){
                    $main_dvo->NID = isset($_COOKIE['userId'])? $_COOKIE['userId'] : $_SESSION['userId'];
                }else if(empty($addAuthor) && isset($main_dvo->AUTHORNAME) && trim($main_dvo->AUTHORNAME) != ''){
                    $main_dvo->NID = $main_dao->addAuthor($main_dvo);
                    if(empty($main_dvo->NID)){
                        throw new Exception("Error occurred while adding user!");
                    }
                    $_SESSION['userId'] = $main_dvo->NID;
                    $_SESSION['author'] = $main_dvo->AUTHORNAME;

                    $addAuthor = 1;
                    $cookie_name = "userId";
                    $cookie_value = $main_dvo->NID;
                    setcookie($cookie_name, $cookie_value, time() + (3600 * 30), "/");//time is in seconds 
                }

                if (move_uploaded_file($fileTmpNames[$i], $targetFilePath)) {
                    array_push($uploadedImg, $originalName);
                    $webPaths[] = $webURL . "uploads/" . $fileName;
                } else {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "Failed to move uploaded file: $originalName"
                    ];
                }
            }

            foreach($webPaths as $i){
                $main_dvo->IMAGEURL = $i;
                $main_dao->addImage($main_dvo);
            }

            if(count($uploadedImg) > 0){
                // set form_submitted cookie only when he uploads all the 4 media
                if(isset($_COOKIE['media_count'])){
                    if(((int)$_COOKIE['media_count']+ (int)count($uploadedImg)) >= 4){
                        $cookie_name = "form_submitted";
                        $cookie_value = "yes";
                        setcookie($cookie_name, $cookie_value, time() + (3600 * 30), "/");
                    }
                    setcookie('media_count', ((int)$_COOKIE['media_count']+(int)count($uploadedImg)), time() + (3600 * 30), "/");
                    
                }else if($_SESSION['userId']){
                    $main_dvo->NID = $main_dvo->USERID;
                    $cookie_name = 'media_count';
                    $cookie_value = count($uploadedImg)+$countOfUploadedFiles;
                    setcookie($cookie_name, $cookie_value, time() + (3600 * 30), "/");
                }else{
                    $cookie_name = 'media_count';
                    $cookie_value = count($uploadedImg);
                    setcookie($cookie_name, $cookie_value, time() + (3600 * 30), "/");
                }

                $uploadStatus[] = [
                    'status' => 1,
                    'data' => implode(',',$uploadedImg),
                    'message' => "Successfully Uploaded file:"
                ];
            }

            $returnArray = [
                'status'=> 1,
                'data'=> $uploadStatus
            ];
        } catch (Exception $e) {
            $returnArray = [
                'status'=> 2,
                'message'=> $e->getMessage()
            ];
        }
        
    } else {
        $msg = 'File data not found/ some error occurred';
        $returnArray = [
            'status'=> 2,
            'message'=> $msg
        ];
    }

    echo json_encode($returnArray);
    exit();

        // &lt;?xml version='1.0'?&gt; &lt;query id='addPackageDetails'&gt;&lt;author&gt;name&lt;/author&gt;&lt;/query&gt;
?>