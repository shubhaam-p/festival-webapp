<?php
    session_start();
    $returnArray = $uploadedImg = [];

    if (isset($_FILES["file"]) && is_countable($_FILES["file"]) && count($_FILES)>1) {
          $returnArray = [
            'status'=> 2,
            'message'=> 'Please upload one file at a time.'
        ];
        echo json_encode($returnArray);
        exit();
    }

    require "../Constants.php";
    require '../dvo/MAIN-DVO.php';
    require '../dao/MAIN-DAO.php';
    require '../Functions.class.php';
    
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

    $save = $error = $msg = $videoFileCount = $adminAccess = $key = '';
    $queryXML = str_replace("\'", "'", $_REQUEST["xmlData"]);
    $xml = simplexml_load_string(htmlspecialchars_decode($queryXML));
    
    $result = $xml->xpath("//admin");
    if(isset($result[0]) && $result[0] != null){
        $key = $adminAccess = (int) $functions->sanitizeInput(trim($result[0]));
    }
    $result = $xml->xpath("//author");
    if(isset($result[0]) && $result[0] != null){
        $main_dvo->AUTHORNAME = $functions->sanitizeInput(trim($result[0]));
    }

    $result = $xml->xpath("//caption");
    if(isset($result[0]) && $result[0] != null){
        $main_dvo->CAPTION = $functions->sanitizeInput(trim($result[0]));
    }
    
    $result = isset($_POST["videoFileCount"])?$_POST["videoFileCount"]:0;
    if(!empty($result[0]) && $result[0] !== ''){
        $videoFileCount = $functions->sanitizeInput(trim($result[0]));
    }

    require $_SERVER['DOCUMENT_ROOT'] . '/ValidateUser.php';

    $videoDimArr = [];
    $videoFileCount = isset($videoFileCount) && ($videoFileCount > 0) ? $videoFileCount : 0;
    for($i = 0; $i < $videoFileCount; $i++){
        $width = isset($_POST["width_$i"])? $_POST["width_$i"] : 0;
        $height = isset($_POST["height_$i"])? $_POST["height_$i"] : 0;
        $duration = isset($_POST["duration_$i"])? $_POST["duration_$i"] : 0;
        $videoDimArr[$i] = ['width'=>$width, 'height'=>$height, 'duration'=>$duration];
    }

    $returnArray = [];
    if (isset($_FILES["file"]) && is_countable($_FILES["file"])) {
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";

        $webPaths = [];
        $uploadStatus = []; // To store per-file success or error
        $maxFileSizeImage = $MAX_FILE_SIZE_IMAGE;
        $maxFileSizeOtherMedia = $MAX_FILE_SIZE_OTHER_MEDIA;
        $imageCount = $otherMediaCount = 0;
        $errorMsg = "You can upload up to four files; 2 images and 2 audio, video or gif files. Please refer to guildlines.";
        $date = date_create("now",timezone_open("Asia/Kolkata"));
        
        // Normalize inputs
        $main_dvo->FILEARR = (array)$_FILES['file']['name'];
        $main_dvo->TEMPFNAME = $fileTmpNames = (array)$_FILES['file']['tmp_name'];
        $fileErrors = (array)$_FILES['file']['error'];
        $fileSizes = (array)$_FILES['file']['size'];
        try {
            if (count($main_dvo->FILEARR) > 4){
                throw new Exception($errorMsg);
            }
 
            //Sanitize file names and get count of media group wise
            $returnData = $functions->validateFileNTypes($main_dvo, $CONST_MIME_TYPE_IMAGE, $CONST_MIME_TYPE_OTHER_MEDIA);
            $imageCount = $returnData[0];
            $otherMediaCount = $returnData[1];
            $MIMEArr = $returnData[2];
            $notAllowedMediaArr = $returnData[3];
            $notAllowedFileNoArr = array_keys($notAllowedMediaArr);

            if($imageCount > $MAX_FILE_COUNT_FOR_EACH_TYPE || $otherMediaCount > $MAX_FILE_COUNT_FOR_EACH_TYPE){
                throw new Exception($errorMsg);
            }

            //Checking No. of uploaded files previously and No. of files submitted by user now
            // $remainingLimit - ** As we are uploading media one by one, this wont be used
            if(count($dataOfUploadedFiles) > 0){
                // check Images
                if(isset($dataOfUploadedFiles[1]) && ($dataOfUploadedFiles[1] + $imageCount) > $MAX_FILE_COUNT_FOR_EACH_TYPE){
                    $msg = "You have already uploaded 2 image files. You can try to upload an audio,video or gif file instead.";
                    throw new Exception($msg);
                }
                //check Videos, audio
                if(isset($dataOfUploadedFiles[2]) && ($dataOfUploadedFiles[2] + $otherMediaCount) > $MAX_FILE_COUNT_FOR_EACH_TYPE){
                    $msg = "You have already uploaded 2 audio video or gif files. You can try to upload an image file instead.";
                    // $msg = "You have already uploaded ". $dataOfUploadedFiles[2] ." Audio, Video or GIF files. ";
                    // $remainingLimit = $MAX_FILE_COUNT_FOR_EACH_TYPE - $dataOfUploadedFiles[2];
                    // if($remainingLimit > 0)
                    //     $msg .="Now you can only upload ".($remainingLimit) ." Audio or Video or GIF files";
                    // else
                    //     $msg .="You can not upload this type of file";

                    throw new Exception($msg);
                }
            }

            //Validate uploaded media and upload.
            for ($i = 0; $i < count($main_dvo->FILEARR); $i++) {
                $storeStatus = 0;
                $originalName = $main_dvo->FILEARR[$i];
                $fileName = basename($originalName);
                //To get MIME type
                $main_dvo->MIMETYPE = $MIMEArr[$i];

                //For unique name of file
                $fileName = date_format($date, "Ymd_His") . "_" . rand(1000, 9999) . "." . pathinfo($fileName, PATHINFO_EXTENSION);
                $targetFilePath = $targetDir . $fileName;

                //Check for invalid file types
                if (in_array($i, $notAllowedFileNoArr)) { 
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "if you have not already, you may upload audio,video or gif files/image files  ",
                        'MIME type' => $notAllowedMediaArr[$i]  
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

                if (empty($adminAccess) && ((in_array($main_dvo->MIMETYPE, $CONST_MIME_TYPE_IMAGE) && $fileSizes[$i] > $maxFileSizeImage) || (in_array($main_dvo->MIMETYPE, $CONST_MIME_TYPE_OTHER_MEDIA) && $fileSizes[$i] > $maxFileSizeOtherMedia))) {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "File too large : $originalName"
                    ];
                    continue;    
                }

                //***Based on user cheking the type of file he has added - so new user must be added to DB***
                //if new user is submitting, add it into author table and use that Id to store the media.
               if(empty($main_dvo->USERID)){
                    //create dummy name 
                    if(empty($main_dvo->AUTHORNAME))
                        $main_dvo->AUTHORNAME = 'dummyUser_'.date('d_m_Y_H_i_s');

                    $main_dvo->USERID = $main_dao->addAuthor($main_dvo);
                    if(empty($main_dvo->USERID)){
                        throw new Exception("Error occurred processing the request!");//Error while adding the user
                    }
                    //Empty the name, if its dummy user name
                    if(preg_match('/dummyUser_/i', $main_dvo->AUTHORNAME)){
                        $main_dvo->AUTHORNAME = '';
                    }
                    $_SESSION['userId'] = $main_dvo->USERID;
                    $_SESSION['author'] = $main_dvo->AUTHORNAME;

                    $cookie_name = "userId";
                    $cookie_value = $main_dvo->USERID;
                    setcookie($cookie_name, $cookie_value, time() + ($COOKIE_EXPIRY_TIME), "/");
                }

                if (move_uploaded_file($fileTmpNames[$i], $targetFilePath)) {
                    $main_dvo->MEDIATYPE = in_array($main_dvo->MIMETYPE, $CONST_MIME_TYPE_IMAGE)? 1 : 0;
                    $main_dvo->MEDIATYPE = in_array($main_dvo->MIMETYPE, $CONST_MIME_TYPE_OTHER_MEDIA)? 2 : $main_dvo->MEDIATYPE;
                    $main_dvo->IMAGEURL = $webURL . "uploads/" . $fileName;

                    //To get file size
                    $main_dvo->FILESIZE = $fileSizes[$i]; // In bytes
                    // $main_dvo->FILESIZE = round($fileSize / 1024 / 1024, 2); // In MB
                    $videoDim = isset($videoDimArr[$i]) ? $videoDimArr[$i]:[];
                    $mediaDimensions = $functions->getMediaDimensions($targetFilePath, $main_dvo->MIMETYPE, $videoDim);
                    $whatType = $mediaDimensions[0];
                    $dimensions = $mediaDimensions[1];
                    if($whatType == 1){
                        $main_dvo->HEIGHT = isset($dimensions[0])?$dimensions[0]:0;
                        $main_dvo->WIDTH = isset($dimensions[1])?$dimensions[1]:0;
                    }elseif($whatType == 2){
                        $main_dvo->HEIGHT = isset($dimensions['height'])?$dimensions['height']:0;
                        $main_dvo->WIDTH = isset($dimensions['width'])?$dimensions['width']:0;
                        // $duration = isset($dimensions['duration'])?$dimensions['duration']:0;
                    }
                    //Store the URL in DB
                    $storeStatus = $main_dao->storeMedia($main_dvo);
                    if(empty($storeStatus)){
                        $uploadStatus[] = [
                            'file' => $originalName,
                            'status' => 2,
                            'message' => "Error occurred while processing this file : $originalName"
                        ];
                    }else
                        array_push($uploadedImg, $originalName);

                } else {
                    $uploadStatus[] = [
                        'file' => $originalName,
                        'status' => 2,
                        'message' => "Failed to process uploaded file: $originalName"
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
                    
                }else if(isset($_SESSION['userId'])){
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
                    'message' => "Successfully Uploaded file"
                ];
            }

            //This block is not needed as there is upload status for every file
            // else{
            //     $uploadStatus[] = [
            //         'status' => 2,
            //         'message' => "Error occurred while processing the request!"
            //     ];
            // }

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