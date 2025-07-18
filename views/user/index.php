<?php
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Constants.php';
    require $_SERVER['DOCUMENT_ROOT'].'/Functions.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/dao/MAIN-DAO.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/dvo/MAIN-DVO.php';

    $functions = new Functions();
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();

    $adminAccess = 0;
    $author = "";
    if( !empty($_REQUEST['admin'] ) && $_REQUEST['admin']>0 ){
        $admin = (int) trim($_REQUEST['admin']);
        $adminAccess = 1;
    }

    require $_SERVER['DOCUMENT_ROOT'] . '/ValidateUser.php';
    if(isset($_SESSION['author']))
        $author = $_SESSION['author'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
	<link rel="stylesheet" href="<?php echo $webURL; ?>reckStatic/css/style.css?ver=<?=$VER?>">
</head>
<body>
    <div class="my-3"> 
        <div class="review-form-cont" id="form-content">
            <div class="form-row mb-3">
                <h1 class="text-primary text-uppercase titleForm"> ANGKOR, WHAT?!</h1>
                <h6 class="textGreyColor line subtitleForm">Join us in creating an inconclusive anthology of the <span class="font-weight-semi-bold">Angkor Photo Festival & Workshops </span></h6>
            </div>
            <div class="review-user-form">
                <form name="uploadMediaForm" id="uploadMediaForm" onsubmit="return false">
                    <div class="form-row">
                        <div class="control-group">
                            <div class="label-field">
                                <input type="text" class="form-control" name="authorName" id="authorName" placeholder="Preferred Name"  value="<?php echo $author; ?>"/>
                                <div class="tooltip2">
                                    <img src="<?php echo $webURL; ?>reckStatic/images/qmark.png" alt="info" class="qmark-icon">
                                    <span class="tooltiptext">
                                        Optional
                                    </span>
                                </div>
                            </div>
                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="control-group">
                            <label for="imageInput" id="fileLabel" class="file-label">
                                Share Something
                            </label>
                            <span id="fileCountText" class="line">0 files selected</span>
                            <div class="tooltip2">
                                <img src="<?php echo $webURL; ?>reckStatic/images/qmark.png" alt="info" class="qmark-icon">
                                <span class="tooltiptext tooltipbigtext">
                                    <div class="">
                                        <div>
                                            <b>Media Upload Guidelines</b>
                                        </div>
                                        <div class="row fs-small">
                                            <div class="col-6">
                                                <div class="category"><b>Images:</b></div>
                                                <ul>
                                                    <li>Maximum No. 2</li>
                                                    <li>File size: 500KB or less</li>
                                                    <li>Formats accepted: 'jpg', 'jpeg', 'png'</li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <div><b>Audio, Video, or GIF:</b></div>
                                                <ul>
                                                    <li>Maximum No. 2</li>
                                                    <li>File size: 1MB or less</li>
                                                    <li>Formats accepted: 'gif', 'mp3', 'mp4'</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <label id="imageInput-error" class="error" for="imageInput"></label>
                            <input class="form-control" type="file" name="mediaFile" id="imageInput" accept="image/*,video/*,audio/*">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="control-group">
                            <div class="label-field">
                                <input type="text" class="form-control" name="caption" id="caption" placeholder="Caption"/>
                                <div class="tooltip2">
                                    <img src="<?php echo $webURL; ?>reckStatic/images/qmark.png" alt="info" class="qmark-icon">
                                    <span class="tooltiptext">
                                        Optional
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-left">
                        <button class="button-1" type="submit" id="submitFormButton">Submit</button>
                    </div>

                    <input type="hidden" name="adminAccess" id="adminAccess" value="<?=$adminAccess?>">
                </form>
                <div class="submit-response-msg text-left"></div>
            </div>
        </div>
    </div>

    <script>
        const webURL = "<?php echo $webURL;?>";
        const fileInput = document.getElementById('imageInput');
        const fileLabel = document.getElementById('fileLabel');
        const fileCountText = document.getElementById('fileCountText');
        const formContent = document.getElementById('form-content');
        const imageInputerror = document.getElementById('imageInput-error');
        const thankYouScreen = <?php echo $thankYouScreen?>;
    
        if(thankYouScreen == 1){
            formContent.innerHTML='<div class="form-row mb-3 border-thankyou"><h1>You\'ve already responded.</h1></div>';
        }

        fileInput.addEventListener('change', () => {
        const count = fileInput.files.length;
    
        if (count === 0) {
            fileCountText.textContent = "0 files selected";
        } else if (count === 1) {
            imageInputerror.textContent = "";
            let fileName = fileInput.files[0].name;
            fileName = fileName.length > 10? fileName.slice(0, 10)+'...': fileName ;
            fileCountText.textContent = fileName;
        } 
        });

    </script>
     <!-- JavaScript Libraries -->
    <script src="<?php echo $webURL;?>reckStatic/js/jquery-3.4.1.min.js"></script>
    <script src="<?php echo $webURL;?>reckStatic/js/jquery.validate.min.js"> </script> 
    <script src="<?php echo $webURL;?>reckStatic/js/bootstrap.bundle.min.js"></script>


    <!-- custom JS -->
     <script src="<?php echo $webURL;?>reckStatic/js/common.js?ver=<?=$VER?>"></script>
     <script src="<?php echo $webURL;?>reckStatic/js/UI-development.js?ver=<?=$VER?>"></script>
</body>
</html>