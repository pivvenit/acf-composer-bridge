<?php

const INSTALLER_VERSION = "1";

function createPackage($tag, $keywords) {
    return [
        "name" => "advanced-custom-fields/advanced-custom-fields-pro",
        "description" => "Advanced Custom Fields PRO",
        "version" => $tag,
        "type" => "wpackagist-plugin",
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
        "keywords" => $keywords,
        "dist" => (object)[
            "type" => "zip",
            "url" => "https://connect.advancedcustomfields.com/index.php?p=pro&a=download&t={$tag}"
        ],
        "require" => (object)[
            "philippbaschke/acf-pro-installer" => "^".INSTALLER_VERSION
        ]
    ];
}

// The url to retrieve all available Advanced Custom Fields packages from
$response = file_get_contents("https://connect.advancedcustomfields.com/v2/plugins/get-info?p=pro");
$json = json_decode($response);

$data = [];
$versions = [];
    $versions['dev-master'] = createPackage($json->version, $json->tagged);
    $versions['latest'] = createPackage($json->version, $json->tagged);
    $versions[$json->version] = createPackage($json->version, $json->tagged);
foreach ($json->tags as $tag) {
    $versions[$tag] = createPackage($tag, $json->tagged);
}
$data['packages'] = (object)[
    "advanced-custom-fields/advanced-custom-fields-pro" => (object)$versions
];
$output = json_encode((object)$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$outputDir = __DIR__."/docs/v".INSTALLER_VERSION;
if (!is_dir($outputDir)) {
    mkdir($outputDir);
}
file_put_contents("{$outputDir}/packages.json", $output);