<?php

header ( "Content-type: text/plain" );
echo "#!gpxe\n";

#GET VARS
$MANUFACTURER=$_GET["manufacturer"];
$PRODUCT=$_GET["product"];
$IP=$_GET["ip"];
$MAC=$_GET["mac"];

$proto = "https";
// Comment out/remove the following if strictly using HTTPS
if (!isset($_SERVER["HTTPS"]))
  $proto = "http";

// This assigns the host that gPXE should use using the most logical variables
if ( $_SERVER["HTTP_HOST"] != "" ) {
  $host=$_SERVER["HTTP_HOST"];
} else {
  if ( $_SERVER["SERVER_NAME"] != 0) {
	$host=$_SERVER["SERVER_NAME"];
  } else {
	$host=$_SERVER["SERVER_ADDR"];
  }
}

// Comment out/remove the following if you are running on a standard port
if (!((! isset($_SERVER["HTTPS"]) ) && ($_SERVER["SERVER_PORT"] == 80))
  && !(isset($_SERVER["HTTPS"]) && ($_SERVER["SERVER_PORT"] == 443)) ){
    if (strrpos($host, ":") == FALSE)
      $host=$host.":".$_SERVER["SERVER_PORT"];
}

$uri=$_SERVER["REQUEST_URI"];
$dir=substr ( $uri, 0, strrpos ($uri, "/") + 1);

echo "#!gpxe\n";
echo "imgfree\n";
/*echo "login\n";*/
$url="bootcfg.php?mac=$MAC&manufacturer=".preg_replace('/\s+/', '',$MANUFACTURER)."&product=".preg_replace('/\s+/', '',$PRODUCT)."&ip=$IP";
echo "set 209:string ".$url."\n";
echo "set 210:string ".
     $proto."://\${username:uristring}:\${password:uristring}@".
     $host.$dir."\n";
echo "chain \${210:string}pxe/pxelinux.0\n";
?>
