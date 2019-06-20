<?php
include_once "php/server.php";

redirectHTTPSifNeeded();

//CHECK COOKIES LOGIC
if (isset($_COOKIE['user'])) {
    if (checkInactivity(true))
        include "logged.php";
    else
        include "not_logged.php";
}else{
    if (isset($_GET['cookiecheck'])) {
        if (isset($_COOKIE['user'])) {
            if (checkInactivity(true))
                include "logged.php";
            else
                include "not_logged.php";
        } else {
            //echo $_COOKIE['user'];
            redirect("cookies_disabled.html","", "");
        }
    } else {
        setcookie("user", "enabled", time() + 3600);
        if(isset($_GET['msg'])){
            $cwd = preg_split('/\\\|\//', getcwd());
            header("Location: index.php?msg={$_GET['msg']}&cookiecheck=1");
            exit;
        }
        redirect("index.php", "cookiecheck", "1");
    }
}