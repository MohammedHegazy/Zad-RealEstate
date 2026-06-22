<?php

declare(strict_types=1);

$dir = dirname(__DIR__).'/app/Http/Controllers';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($it as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $c = file_get_contents($path);
    $n = preg_replace(
        "/('data' => (\$\w+)),(\s*\r?\n\s*'pagination' =>)/",
        "'data' => \$2->items(),\$3",
        $c
    );
    if ($n !== $c) {
        file_put_contents($path, $n);
        echo $path."\n";
    }
}
