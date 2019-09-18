<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

const INSTALLER_VERSION = "2";
$repoVersions = [2,3];

$types = ["wpackagist-plugin", "wordpress-plugin", "wpackagist-muplugin", "wordpress-muplugin", "library"];

function createPackage($tag, $alias = null, $type = "wpackagist-plugin", $repoVersion = 2) {
    $dependencies = [
        "pivvenit/acf-pro-installer" => "^".INSTALLER_VERSION
    ];
    if ($repoVersion == 3) {
        $dependencies["composer/installers"] ="~1.0";
    }
    return [
        "name" => "advanced-custom-fields/advanced-custom-fields-pro",
        "description" => "Advanced Custom Fields PRO",
        "version" => $alias ?? $tag,
        "type" => $type,
        "license" => "GPL-2.0-or-later",
        "authors" => [
            (object)[
                "name" => "Elliot Condon",
                "homepage" => "http://www.elliotcondon.com",
                "role" => "Maintainer"
            ],
            (object)[
                "name" => "PivvenIT",
                "homepage" => "https://pivvenit.nl",
                "role" => "Composer Repository maintainer"
            ]
        ],
        "homepage" => "https://www.advancedcustomfields.com/",
        "keywords" => "acf, advanced, custom, field, fields, form, repeater, content",
        "dist" => (object)[
            "type" => "zip",
            "url" => "https://connect.advancedcustomfields.com/index.php?p=pro&a=download&t={$tag}"
        ],
        "require" => (object)$dependencies
    ];
}

// The url to retrieve all available Advanced Custom Fields packages from
$response = @file_get_contents("https://connect.advancedcustomfields.com/v2/plugins/get-info?p=pro");
if ($response === false) {
    echo "Error retrieving package information";
    die(1);
}
$json = json_decode($response);

foreach ($repoVersions as $repoVersion) {
    foreach ($types as $type) {
        $data = [];
        $versions = [];
        $versions['dev-master'] = createPackage($json->version, 'dev-master', $type, $repoVersion);
        $versions[$json->version] = createPackage($json->version, null, $type, $repoVersion);
        $availableVersions = $json->versions ?? [];
        foreach ($availableVersions as $version) {
            $versions[$version] = createPackage($version, null, $type, $repoVersion);
        }
        if (!is_array($availableVersions) || empty($availableVersions) || count($versions) == 2) {
            echo "The list of packages is empty, probably the API has changed, not updating repository";
            die(1);
        }
        $data['packages'] = (object)[
            "advanced-custom-fields/advanced-custom-fields-pro" => (object)$versions
        ];
        $output = json_encode((object)$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $outputDir = __DIR__ . "/composer/v" . $repoVersion;
        if (!is_dir($outputDir . "/{$type}")) {
            mkdir($outputDir . "/{$type}", 0777, true);
        }
        file_put_contents("{$outputDir}/{$type}/packages.json", $output);
        if ($type == "wpackagist-plugin") {
            file_put_contents("{$outputDir}/packages.json", $output);
        }
    }
}
