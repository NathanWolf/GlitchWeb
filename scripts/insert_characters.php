<?php

if (PHP_SAPI !== 'cli') {
    die('What are you doing?');
}

if (count($argv) < 2) {
    die("Usage: insert_characters <character folder>\n");
}

require_once '../data/GlitchDatabase.class.php';

$db = new \com\elmakers\glitch\GlitchDatabase();
$characters = $db->getCharacters();

$characterFolder = $argv[1];

function endsWith($haystack, $needle){
    $length = strlen($needle);
    return (substr($haystack, -$length) === $needle);
}

$iterator = new DirectoryIterator($characterFolder);
foreach ($iterator as $fileInfo) {
    if ($fileInfo->isDot()) continue;
    if ($fileInfo->isDir()) continue;
    $filename = $fileInfo->getFilename();
    if (!endsWith($filename, '.png')) continue;
    $base = basename($filename);
    $filename = $fileInfo->getPathname();
    $info = pathinfo($filename);
    $characterId = basename($filename, '.' . $info['extension']);
    if (!isset($characters[$characterId])) {
        $name = str_replace('_', ' ', $characterId);
        $name = ucwords($name);
        echo "INSERT INTO persona (id, first_name, portrait) values ('$characterId', '$name', '{\"offset\":[0.5,0.5]}');\n";
    }
}