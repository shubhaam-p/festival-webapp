<?php
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Constants.php';
    require $_SERVER['DOCUMENT_ROOT'].'/Functions.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/dao/MAIN-DAO.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/dvo/MAIN-DVO.php';

    $functions = new Functions();
    $main_dvo = new MAIN_DVO();
    $main_dao = new MAIN_DAO();

    require $_SERVER['DOCUMENT_ROOT'] . '/ValidateUser.php';
    $author = "";
    if(isset($_SESSION['author']))
        $author = $_SESSION['author'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
	<link rel="stylesheet" href="<?php echo $webURL; ?>reckStatic/CSS/style.css?ver=<?=$VER?>">
	<link rel="stylesheet" href="<?php echo $webURL; ?>reckStatic/CSS/css2.css?ver=<?=$VER?>">
</head>
<body>
    <div class="container-fluid pb-5">
        <div class="container py-5">
            <div class="text-center mb-3">
                <h1 class="text-primary text-uppercase" style="letter-spacing: 5px;">ANGKOR, WHAT?</h1>
                <h6>Us in creating an inconclusive anthology of the Angkor Photo Festival & Workshops</h6>
            </div>                      
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-8 review-form-cont">
                    <div class="review-user-form" style="padding: 30px;">
                        <form name="uploadMediaForm" id="uploadMediaForm" onsubmit="return false">
                            <div class="form-row">
                                <div class="control-group">
                                    <label for="authorName" class="">Author Name </label>
                                    <div class="tooltip2">
                                        <img src="<?php echo $webURL; ?>reckStatic/images/qmark.png" alt="info" class="qmark-icon">
                                        <span class="tooltiptext">
                                            Write name here
                                        </span>
                                    </div>
                                    <input type="text" class="form-control p-4" name="authorName" id="authorName" placeholder="Name..." value="<?php echo $author; ?>"/>
                                    <p class="help-block text-danger"></p>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="control-group">
                                    <label for="imageInput">Upload media</label>
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
                                                            <li>Formats accepted: <b>'jpg', 'jpeg', 'png'</b></li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-6">
                                                        <div><b>Audio, Video, or GIF:</b></div>
                                                        <ul>
                                                            <li>Maximum No. 2</li>
                                                            <li>File size: 1MB or less</li>
                                                            <li>Formats accepted: <b>'gif', 'mp3', 'mp4'</b></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                    <input class="form-control" type="file" name="file[]" id="imageInput" accept="image/*,video/*,audio/*">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="control-group">
                                    <label for="caption" class="">Caption</label>
                                    <div class="tooltip2">
                                        <img src="<?php echo $webURL; ?>reckStatic/images/qmark.png" alt="info" class="qmark-icon">
                                        <span class="tooltiptext">
                                            Write caption here
                                        </span>
                                    </div>
                                    <input type="text" class="form-control p-4" name="caption" id="caption" placeholder="Caption..."/>
                                </div>
                            </div>

                            <div class="text-left">
                                <button class="btn btn-primary py-3 px-4" type="submit" id="submitFormButton">Submit</button>
                            </div>
                        </form>
                        <div class="submit-response-msg text-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const webURL = "<?php echo $webURL;?>";

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