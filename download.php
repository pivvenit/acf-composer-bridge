<?php

$downloadUrl = "https://connect.advancedcustomfields.com/v2/plugins/download";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="ACF Composer Repository"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You need to login';
    exit;
}

if ($_SERVER['PHP_AUTH_USER'] !== "licensekey") {
    header('WWW-Authenticate: Basic realm="ACF Composer Repository"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Invalid username, please use licensekey as username.';
    exit;
}

$getParameters = array_map(fn($o) => $o, $_GET);
$getParameters['p'] = 'pro';
$getParameters['k'] = $_SERVER['PHP_AUTH_PW'];
$downloadUrl = $downloadUrl."?".http_build_query($getParameters);
$file = @file_get_contents($downloadUrl);
if ($file === false) {
    header($http_response_header[0]);
    echo 'An error occurred while fetching the package';
    exit;
}
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=\"advanced-custom-fields-pro.{$getParameters['t']}.zip");
echo $file;