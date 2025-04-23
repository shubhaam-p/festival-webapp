<?php
    include "../Constants.php";
    include '../dvo/MAIN-DVO.php';
    include '../dao/MAIN-DAO.php';
    include '../Functions.class.php';

    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();
    $functions = new Functions();

// error_log("xmlData ".print_r($_REQUEST["xmlData"],1)." -- ".__DIR__ );
    $returnArray = $uploadedImg = [];
    $save = $error = $msg = $main_dvo->NID = '';
    $addAuthor = 0;
    $queryXML = str_replace("\'", "'", $_REQUEST["xmlData"]);
    $xml = simplexml_load_string(htmlspecialchars_decode($queryXML));

    $result = $xml->xpath("//author");
    if(isset($result[0]) && $result[0] != null){
        $main_dvo->AUTHORNAME = $functions->sanitizeInput(trim($result[0]));
    }

    // echo "Autho $main_dvo->AUTHORNAME";
    $maxFileSize = 500 * 1024; // 500 KB in bytes

    if (isset($_FILES["file"]) && is_countable($_FILES["file"])) {
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        $webPaths = [];
        $uploadStatus = []; // To store per-file success or error
        $maxFileSize = 500 * 1024; // 500 KB
    
        // Normalize inputs
        $fileNames = (array)$_FILES['file']['name'];
        $fileTmpNames = (array)$_FILES['file']['tmp_name'];
        $fileErrors = (array)$_FILES['file']['error'];
        $fileSizes = (array)$_FILES['file']['size'];
        // error_log("file count ".count($fileNames));
        for ($i = 0; $i < min(count($fileNames), 3); $i++) {
            $originalName = $fileNames[$i];
            $fileName = basename($originalName);
            $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
            $fileName = time() . "_" . rand(1000, 9999) . "_" . $fileName;
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            error_log("file type $originalName -- ".$fileType);
            if (!in_array($fileType, $allowedTypes)) {
                $uploadStatus[] = [
                    'file' => $originalName,
                    'status' => 'error',
                    'message' => "Invalid file type: $originalName"
                ];
                continue;
            }

            if ($fileErrors[$i] !== 0) {
                $uploadStatus[] = [
                    'file' => $originalName,
                    'status' => 'error',
                    'message' => "Upload error (code {$fileErrors[$i]}) for: $originalName"
                ];
                continue;
            }

            error_log("file size $originalName".$fileSizes[$i]);
            if ($fileSizes[$i] > $maxFileSize) {
                $uploadStatus[] = [
                    'file' => $originalName,
                    'status' => 'error',
                    'message' => "File too large (max 500 KB): $originalName"
                ];
                continue;    
            }

            if (move_uploaded_file($fileTmpNames[$i], $targetFilePath)) {
                // error_log("file 2 ".$targetFilePath);
                array_push($uploadedImg, $i+1);
                if(empty($addAuthor) && isset($main_dvo->AUTHORNAME) && trim($main_dvo->AUTHORNAME) != ''){
                    $main_dvo->NID = $main_dao->addAuthor($main_dvo);
                    $addAuthor = 1;
                }

                $webPaths[] = $webURL . "uploads/" . $fileName;
            } else {
                $uploadStatus[] = [
                    'file' => $originalName,
                    'status' => 'error',
                    'message' => "Failed to move uploaded file: $originalName"
                ];
            }

        }

        foreach($webPaths as $i){
            $main_dvo->IMAGEURL = $i;
            $main_dao->addImage($main_dvo);
        }
        error_log("array ".print_r($uploadedImg,1));
        if(count($uploadedImg)>0){
            $uploadStatus[] = [
                'status' => 'sucess',
                'image' => implode(',',$uploadedImg),
                'message' => "Successfully Uploaded file:"
            ];
        }
        $main_dvo->IMAGEPATH = $webPaths;
        echo json_encode($uploadStatus);
        error_log("pahys ".print_r($main_dvo->IMAGEPATH,1));
    } else {
        $msg = 'File data not found/ some error occurred';
    }

    // echo $msg;
    exit();

        // &lt;?xml version='1.0'?&gt; &lt;query id='addPackageDetails'&gt;&lt;author&gt;name&lt;/author&gt;&lt;/query&gt;
?>