<?php

const INSTALLER_VERSION = "2";

$types = ["wpackagist-plugin", "wordpress-plugin", "wpackagist-muplugin", "wordpress-muplugin", "library"];

function createPackage($tag, $alias = null, $type = "wpackagist-plugin") {
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
        "require" => (object)[
            "pivvenit/acf-pro-installer" => "^".INSTALLER_VERSION
        ]
    ];
}

// The url to retrieve all available Advanced Custom Fields packages from
$response = @file_get_contents("https://connect.advancedcustomfields.com/v2/plugins/get-info?p=pro");
if ($response === false) {
    echo "Error retrieving package information";
    die(1);
}
$json = json_decode($response);

foreach ($types as $type) {
    $data = [];
    $versions = [];
    $versions['dev-master'] = createPackage($json->version, 'dev-master', $type);
    $versions[$json->version] = createPackage($json->version,  null, $type);
    foreach ($json->versions as $version) {
        $versions[$version] = createPackage($version, null, $type);
    }
    $data['packages'] = (object)[
        "advanced-custom-fields/advanced-custom-fields-pro" => (object)$versions
    ];
    $output = json_encode((object)$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $outputDir = __DIR__."/composer/v".INSTALLER_VERSION;
    if (!is_dir($outputDir."/{$type}")) {
        mkdir($outputDir."/{$type}", 0777, true);
    }
    file_put_contents("{$outputDir}/{$type}/packages.json", $output);
    if ($type == "wpackagist-plugin") {
        file_put_contents("{$outputDir}/packages.json", $output);
    }
}
