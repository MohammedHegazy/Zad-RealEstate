<?php

$transcript = 'C:/Users/USER/.cursor/projects/c-Users-USER-Desktop-Real-estat-ziadar-Real-estat-ziad-project-RealEstate-database/agent-transcripts/bea36a40-abbc-4d9b-ba50-3ff76aed2162/bea36a40-abbc-4d9b-ba50-3ff76aed2162.jsonl';
$outDir = dirname(__DIR__).'/_transcript_extract';
$pathFilter = $argv[1] ?? 'routes'; // routes | app | all

if (! is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

foreach (file($transcript) as $line) {
    $j = json_decode($line, true);
    if (! is_array($j)) {
        continue;
    }
    foreach ($j['message']['content'] ?? [] as $c) {
        if (($c['type'] ?? '') !== 'tool_use' || ($c['name'] ?? '') !== 'Write') {
            continue;
        }
        $path = str_replace('\\', '/', $c['input']['path'] ?? '');
        $pattern = $pathFilter === 'all'
            ? '#project-RealEstate_database/(.+)$#'
            : '#project-RealEstate_database/('.preg_quote($pathFilter, '#').'/.*)$#';
        if (! preg_match($pattern, $path, $m)) {
            continue;
        }
        $rel = $m[1]; // e.g. routes/api.php or app/Http/...
        $dest = $outDir.'/'.$rel;
        $dir = dirname($dest);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dest, $c['input']['contents']);
        echo "Wrote: $rel\n";
    }
}
