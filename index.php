<?php
include_once "php/server.php";

redirectHTTPSifNeeded();
checkOrSetCookie();

if (checkInactivity())
    include "logged.php";
else
    include "not_logged.php";