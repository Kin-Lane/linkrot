<?php
include "/var/www/html/system/common.php";

//Primary Site URL
$Site_URL = $_SERVER['HTTP_HOST'];
$Full_Site_URL = "http://" . $Site_URL;
$Full_Site_Secure_URL = "https://" . $Site_URL;

// Password Encryption Key
$Salt = "evangelize!!";

$awsAccessKey = "09VXKKVP882KS7F74D02";
$awsSecretKey = "uj+430Xa3uNVsdvXYbKvH5du7mNPLslb8jL74BPJ";
$awsSiteBucket = "kinlane-productions";
$awsRootURL = "http://kinlane-productions.s3.amazonaws.com/";

$dbserver = "laneworks-2.cjgvjastiugl.us-east-1.rds.amazonaws.com";;
$dbname = "linkrot";
$dbuser = "kinlane";
$dbpassword = "ap1stack!";

$three_scale_provider_key = "9c72d79253c63772cc2a81d4e4bd07f8";

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$API = "api.laneworks.net";
$APISecret = "I like beer!";
$APIKey = encrypt($API,$APISecret);
?>

