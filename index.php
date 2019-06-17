<?php
include_once "php/server.php";

redirectHTTPSifNeeded();
/*checkOrSetCookie();

if (checkInactivity())
    include "logged.php";
else
    include "not_logged.php";*/

if (isset($_COOKIE['user'])) {
    if (checkInactivity())
        include "logged.php";
    else
        include "not_logged.php";
}else{
    if (isset($_GET['cookiecheck'])) {
        if (isset($_COOKIE['user'])) {
            if (checkInactivity())
                include "logged.php";
            else
                include "not_logged.php";
        } else {
            redirect("cookies_disabled.html");
        }
    } else {
        setcookie("user", "enabled", time() + 3600);
        redirect("index.php", "cookiecheck", "1");
    }
}
