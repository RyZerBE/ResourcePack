<?php
$manifest = json_decode(file_get_contents("pack/manifest.json"), true);
$manifest["header"]["version"][2]++;
file_put_contents("pack/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
