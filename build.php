<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

const INSTALLER_VERSION = "2";
$repoVersions = [2,3];

$types = ["wpackagist-plugin", "wordpress-plugin", "wpackagist-muplugin", "wordpress-muplugin", "library"];

function createPackage($tag, $alias = null, $type = "wpackagist-plugin", $repoVersion = 2) {
    $dependencies = [
        "pivvenit/acf-pro-installer" => getInstallerVersion($tag)
    ];
    if ($repoVersion == 3) {
        $dependencies["composer/installers"] ="^1.0 || ^2.0";
    }
    $downloadurl = getDownloadUrl($tag);
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
        "support" => (object)[
            "docs" => "https://www.advancedcustomfields.com/resources/",
            "forum" => "https://support.advancedcustomfields.com/topics/"
        ],
        "homepage" => "https://www.advancedcustomfields.com/",
        "keywords" => "acf, advanced, custom, field, fields, form, repeater, content",
        "dist" => (object)[
            "type" => "zip",
            "url" => "{$downloadurl}{$tag}"
        ],
        "require" => (object)$dependencies
    ];
}

/**
 * @return string
 */
function getInstallerVersion($tag): string
{
    $semver = getSemver($tag);
    ['major' => $major, 'minor' => $minor, 'patch' => $patch] = $semver;
    if (((int)$major == 5 && (int)$minor < 8) || ((int)$major == 5 && (int)$minor == 8 && (int)$patch < 8)) {
        return "^" . INSTALLER_VERSION;
    }
    return "^2.4.0 || ^3.0";
}

/**
 * @param $tag
 * @return string
 */
function getDownloadUrl($tag): string
{
    $semver = getSemver($tag);
    $downloadurl = "https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&t=";
    ['major' => $major, 'minor' => $minor, 'patch' => $patch] = $semver;
    if (((int)$major == 5 && (int)$minor < 8) || ((int)$major == 5 && (int)$minor == 8 && (int)$patch < 8)) {
        $downloadurl = "https://connect.advancedcustomfields.com/index.php?p=pro&a=download&t=";
    }
    return $downloadurl;
}

/**
 * @param $tag
 * @return array
 */
function getSemver($tag): array
{
    $matches = [];
    preg_match('/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.*(?P<patch>0|[1-9]\d*)?(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/m',
        $tag, $matches);
    if (!array_key_exists('patch', $matches)) {
        $matches['patch'] = 0;
    }
    return $matches;
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
