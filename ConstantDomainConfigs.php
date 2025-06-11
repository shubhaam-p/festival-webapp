<?php
include_once "dotenv.php";
try {
    (new DotEnv(__DIR__ . '/.env'))->load();
    $PROTOCOL												= getenv('PROTOCOL');
    $DB_HOST												= getenv('DB_HOST');
    $DB_NAME												= getenv('DB_NAME');
    $DB_USER												= getenv('DB_USER');
    $DB_PASS												= getenv('DB_PASS');
    $DB_PORT												= getenv('DB_PORT');
    $FROM_EMAIL												= getenv('FROM_EMAIL');
    $TO_EMAIL												= getenv('TO_EMAIL');
    $EMAIL_PWD												= getenv('EMAIL_PWD');
    $SEND_EMAIL_FLAG												= getenv('SEND_EMAIL_FLAG');

    $CONST_PRODUCT_DOMAIN           = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : getenv('VALID_PRODUCT_DOMAIN'); // domain with fallback, will remove else part later
    $webURL                         = $PROTOCOL."://".$CONST_PRODUCT_DOMAIN."/" ; 
    error_log("webuelr ".$webURL);
}
catch (Exception $e) {
    //exception handling code goes here
		echo 'Message: ' .$e->getMessage(); exit();
}
?>