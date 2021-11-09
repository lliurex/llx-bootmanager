<?php
header ( "Content-type: text/plain" );

#GET VARS
if (isset($_GET["manufacturer"])){
    $MANUFACTURER=$_GET["manufacturer"];
}else{
    $MANUFACTURER="";
}
if (isset($_GET["product"])){
    $PRODUCT=$_GET["product"];
}else{
    $PRODUCT="";
}
if (isset($_GET["ip"])){
    $IP=$_GET["ip"];
}else{
    $IP="";
}
if (isset($_GET["mac"])){
    $MAC=$_GET["mac"];
}else{
    $MAC="";
}
if (isset($_GET["platform"])){
    $PLATFORM=$_GET["platform"];
}else{
    $PLATFORM="";
}

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

echo "#!ipxe\n";
/*echo "login\n";*/
$url="../../bootcfg.php?mac=$MAC&manufacturer=".preg_replace('/\s+/', '',$MANUFACTURER)."&product=".preg_replace('/\s+/', '',$PRODUCT)."&ip=$IP&platform=$PLATFORM";
echo "set 209:string ".$url."\n";
if ($PLATFORM == "efi"){
    echo "set 210:string ".$proto."://\${username:uristring}:\${password:uristring}@".$host.$dir."pxe/efi/"."\n";
    echo "chain \${210:string}syslinux.efi\n";
}else{
    echo "set 210:string ".$proto."://\${username:uristring}:\${password:uristring}@".$host.$dir."pxe/bios/"."\n";
    echo "chain \${210:string}pxelinux.0\n";
}
?>
