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
    $save = $error = $msg = '';
    $queryXML = str_replace("\'", "'", $_REQUEST["xmlData"]);
    $xml = simplexml_load_string(htmlspecialchars_decode($queryXML));
    
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
        $errorMsg = "You can upload up to four files; 2 images and 2 audio, video or gif files. Please refer to guildlines.";
        $date = date_create("now",timezone_open("Asia/Kolkata"));
        
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

            if($imageCount > $MAX_FILE_COUNT_FOR_EACH_TYPE || $otherMediaCount > $MAX_FILE_COUNT_FOR_EACH_TYPE){
                throw new Exception($errorMsg);
            }

            //Checking No. of uploaded files previously and No. of files submitted by user now
            if(count($dataOfUploadedFiles) > 0){
                // check Images
                if(isset($dataOfUploadedFiles[1]) && ($dataOfUploadedFiles[1] + $imageCount) > $MAX_FILE_COUNT_FOR_EACH_TYPE)
                    throw new Exception("You have already uploaded ". $dataOfUploadedFiles[1] ." image files. Now you can only upload ".($MAX_FILE_COUNT_FOR_EACH_TYPE - $dataOfUploadedFiles[1]) ." image files");
            
                //check Videos, audio
                if(isset($dataOfUploadedFiles[2]) && ($dataOfUploadedFiles[2] + $otherMediaCount) > $MAX_FILE_COUNT_FOR_EACH_TYPE)
                    throw new Exception("You have already uploaded ". $dataOfUploadedFiles[2] ." Audio, Video or GIF files. Now you can only upload ".($MAX_FILE_COUNT_FOR_EACH_TYPE - $dataOfUploadedFiles[2]) ." Audio, Video or GIF files");
            }

            //Validate uploaded media and upload.
            for ($i = 0; $i < count($main_dvo->FILEARR); $i++) {
                $originalName = $main_dvo->FILEARR[$i];
                $fileName = basename($originalName);
                
                //For unique name of file
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

                if ((in_array($fileType, $CONST_TYPE_IMAGE) && $fileSizes[$i] > $maxFileSizeImage) || (in_array($fileType, $CONST_TYPE_OTHER_MEDIA) && $fileSizes[$i] > $maxFileSizeOtherMedia)) {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "File too large : $originalName"
                    ];
                    continue;    
                }

                //if new user is submitting, add it into author table and use that Id to store the media.
               if(empty($main_dvo->USERID) && isset($main_dvo->AUTHORNAME) && trim($main_dvo->AUTHORNAME) != ''){
                    $main_dvo->USERID = $main_dao->addAuthor($main_dvo);
                    if(empty($main_dvo->USERID)){
                        throw new Exception("Error occurred while adding user!");
                    }
                    $_SESSION['userId'] = $main_dvo->USERID;
                    $_SESSION['author'] = $main_dvo->AUTHORNAME;

                    $cookie_name = "userId";
                    $cookie_value = $main_dvo->USERID;
                    setcookie($cookie_name, $cookie_value, time() + ($COOKIE_EXPIRY_TIME), "/");
                }

                if (move_uploaded_file($fileTmpNames[$i], $targetFilePath)) {
                    array_push($uploadedImg, $originalName);
                    $main_dvo->MEDIATYPE = in_array($fileType, $CONST_TYPE_IMAGE)? 1 : 0;
                    $main_dvo->MEDIATYPE = in_array($fileType, $CONST_TYPE_OTHER_MEDIA)? 2 : $main_dvo->MEDIATYPE;
                    $main_dvo->IMAGEURL = $webURL . "uploads/" . $fileName;
                    
                    //Store the URL in DB
                    $main_dao->addImage($main_dvo);
                } else {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "Failed to move uploaded file: $originalName"
                    ];
                }
            }

            if(count($uploadedImg) > 0){
                // set form_submitted cookie only when user uploads all the 4 media
                if(isset($_COOKIE['media_count'])){
                    if(((int)$_COOKIE['media_count'] + (int)count($uploadedImg)) >= 4){
                        $cookie_name = "form_submitted";
                        $cookie_value = "yes";
                        setcookie($cookie_name, $cookie_value, time() + ($COOKIE_EXPIRY_TIME), "/");
                    }
                    setcookie('media_count', ((int)$_COOKIE['media_count']+(int)count($uploadedImg)), time() + ($COOKIE_EXPIRY_TIME), "/");
                    
                }else if($_SESSION['userId']){
                    $cookie_name = 'media_count';
                    $cookie_value = count($uploadedImg);
                    if(isset($dataOfUploadedFiles[3]))
                        $cookie_value += $dataOfUploadedFiles[3];
                    setcookie($cookie_name, $cookie_value, time() + ($COOKIE_EXPIRY_TIME), "/");
                }else{
                    $cookie_name = 'media_count';
                    $cookie_value = count($uploadedImg);
                    setcookie($cookie_name, $cookie_value, time() + ($COOKIE_EXPIRY_TIME), "/");
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