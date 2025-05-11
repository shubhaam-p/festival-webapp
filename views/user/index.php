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
    <title>Document</title>
	<link rel="stylesheet" href="<?php echo $webURL; ?>reckStatic/CSS/style.css">
	<link rel="stylesheet" href="<?php echo $webURL; ?>reckStatic/CSS/css2.css">
</head>
<body>
    <div class="container-fluid pb-5">
        <div class="container py-5">
            <div class="text-center mb-3">
                <h1 class="text-primary text-uppercase" style="letter-spacing: 5px;">Share your experience</h1>
                <p>
                    Your feedback is important to us, helps us to improve!
                </p>
            </div>                      
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-8 review-form-cont">
                    <div class="review-user-form" style="padding: 30px;">
                        <form name="uploadMediaForm" id="uploadMediaForm" onsubmit="return false">
                            <div class="form-row">
                                <div class="control-group">
                                    <Label for="authorName">Author Name</Label>
                                    <input type="text" class="form-control p-4" name="authorName" id="authorName" placeholder="Your name" size="50" value="<?php echo $author; ?>"/>
                                    <p class="help-block text-danger"></p>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="control-group">
                                     <label for="imageInput">
                                        <div>
                                            <div>
                                                <b>Media Upload Guidelines</b>
                                                <div>
                                                    <small>(Total Limit: Maximum of 4 media files)</small>
                                                </div>
                                            </div>
                                            <div class="row text-muted fs-small">
                                                <div class="col-6">
                                                    <div><b>Images:</b></div>
                                                    <ul>
                                                        <li>Up to 2 images allowed</li>
                                                        <li>Each image must be 500KB or smaller</li>
                                                    </ul>
                                                </div>
                                                <div class="col-6">
                                                    <div><b>Audio, Video, or GIF:</b></div>
                                                    <ul>
                                                        <li> Up to 2 files allowed</li>
                                                        <li>Each file must be 1MB or smaller</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                     </label>
                                    <input type="file" name="file[]" id="imageInput" accept="image/*,video/*,audio/*" multiple>
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
     <script src="<?php echo $webURL;?>reckStatic/js/UI-development.js"></script>
</body>
</html>