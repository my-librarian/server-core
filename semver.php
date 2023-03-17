<?php

error_reporting(0);

$bumpMethods = ['major', 'minor', 'patch'];

if (!(isset($argv[1]) && in_array($argv[1], $bumpMethods))) {

    echo "\n\033[0;31mError: Invalid version method\033[0m \n\n" .
        "Usage: php semver.php <major|minor|patch>\n";

    exit(1);
}

$filename = 'version.ini';
$defaultVersion = '0.0.0';
$bumpMethod = $argv[1];

if (file_exists($filename)) {
    $version = parse_ini_file($filename)['version'] ?: $defaultVersion;
} else {
    $version = $defaultVersion;
}

$version = explode('.', $version);
$bumpIndex = array_search($bumpMethod, $bumpMethods);

foreach ($version as $i => &$v) {
    if ($i === $bumpIndex) {
        $v += 1;
    } elseif ($i > $bumpIndex) {
        $v = 0;
    }
}

$version = join('.', $version);

echo "\033[0;35mBumped to version: $version\033[0m\n";

file_put_contents($filename, "version=$version");

if (is_dir('.git')) {
    echo "\n\033[0;36mCommitting changes and tagging it...\n";
    $tag = "\"v$version\"";
    exec("git add .");
    exec("git commit -m $tag");
    exec("git tag $tag");
    echo "\033[0;32mDone. \033[0m\n\n" .
        "Do\n" .
        "\t1.\t'git push <remote> <branch>' to push the changes and\n" .
        "\t2.\t'git push --tags' to push the tags to remote.\n";
}
