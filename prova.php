<?php
//var_dump($_SERVER);

$cwd = "ciao\ciao";
$cwd = preg_split('/\\\|\//', $cwd);
foreach ($cwd as $c)
    echo "$c\n";
