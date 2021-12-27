<?php

$newUrl = isset($_ENV['AUTH_COMPOSER_REPOSITORY']) ?
    $_ENV['AUTH_COMPOSER_REPOSITORY'] : 'https://auth-acf-composer-proxy.pivvenit.net/download';

$parsedNewUrl = parse_url($newUrl);

if ($parsedNewUrl === false) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Invalid new repository url';
    exit;
}

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

switch ($_SERVER['REQUEST_URI']) {
    case "/wordpress-plugin/packages.json":
        $newUrl = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-plugin/packages.json";
        break;
    case "/wordpress-muplugin/packages.json":
        $newUrl = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-muplugin/packages.json";
        break;
    case "/library/packages.json":
        $newUrl = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/library/packages.json";
        break;
    case "/wpackagist-plugin/packages.json":
        $newUrl = "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wpackagist-plugin/packages.json";
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo 'Unknown repository';
        exit;
}

$json = file_get_contents($newUrl);
if ($json === false) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'An error occurred while fetching the index';
    exit;
}
$data = json_decode($json);
foreach ($data->packages->{"advanced-custom-fields/advanced-custom-fields-pro"} as $key => $package) {
    $oldUrl = $package->dist->url;
    $oldQueryParameters = parse_url($oldUrl, PHP_URL_QUERY);
    if ($oldQueryParameters === false) {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'An error occurred while parsing the package url';
        exit;
    }
    $queryStringParameters = [];
    parse_str($oldQueryParameters, $queryStringParameters);
    $clonedParsedNewUrl = array_map(fn($o) => $o, $parsedNewUrl);
    $clonedParsedNewUrl['query'] = http_build_query(['t' => $queryStringParameters['t']]);
    $package->dist->url = buildUrl($clonedParsedNewUrl);
    $data->packages->{"advanced-custom-fields/advanced-custom-fields-pro"}->$key = $package;
}

echo json_encode($data, JSON_PRETTY_PRINT);

function buildUrl(array $parsedUrl)
{
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
    $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
    $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
    $pass = ($user || $pass) ? "$pass@" : '';
    $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
    $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
    $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}