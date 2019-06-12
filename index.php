<?php
include_once "php/server.php";

redirectHTTPSifNeeded();

if (initPage() == true)
    include "logged.php";
else
    include "not_logged.php";
?>
