<?php
// Front controller untuk deploy di subfolder /emadrasah (DocumentRoot ada di folder induk).
// Memberi tahu Laravel bahwa base path-nya /emadrasah, bukan /emadrasah/public.
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/emadrasah/index.php';
$_SERVER['PHP_SELF'] = '/emadrasah/index.php';
require __DIR__.'/public/index.php';
