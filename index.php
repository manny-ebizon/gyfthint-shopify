<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

require("vendor/autoload.php");
include('config.php');


if (ENV=="DEVELOPMENT" || true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else if (ENV=="PRODUCTION") {
    error_reporting(0);
}

// $maintenance="ON";

function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol.$domainName;
}

define( 'SITE_URL', siteURL() );

//FETCH SITE SETTINGS
// Create connection
$conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DATABASE);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$data['title'] = "Crayon Plus";

//Localization
function __($str,$echo=false){    
    if ($echo) {
        return $str;
    } else {
        echo $str;
    }
}


if(isset($maintenance)) {
    $fn="maintenance";    
    include('Controller/HomeController.php');
    include('Controller/Controller.php');
} else {
    include('router.php');
}

?>