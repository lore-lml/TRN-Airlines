<?php
include_once "server.php";
include_once "user.php";
global $result;

$json = $_POST["method"]();
echo $json;

function registerUser(){
    global $result;
    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $result['cause'] = "no_email";
            throw new Exception();
        }else if(!isset($_POST['psw1'])){
            $result['cause'] = "no_psw1";
            throw new Exception();
        }else if(!validatePassword($_POST['psw1'])){
            $result['cause'] = "invalid_psw";
            throw new Exception();
        }else if(!isset($_POST['psw2'])){
            $result['cause'] = "no_psw2";
            throw new Exception();
        }else if(!isset($_POST['name'])){
            $result['cause'] = "empty_name";
            throw new Exception();
        }

        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $psw1 = $_POST['psw1'];
        $psw2 = $_POST['psw2'];

        if($psw1 != $psw2){
            $result['cause'] = "psw_mismatch";
            throw new Exception();
        }
        $psw1 = sha1($psw1);
        $sql = "INSERT INTO users(email, password, name) VALUES(?,?,?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $psw1, $name);

        //UTENTE GIA ESISTENTE
        if(!mysqli_stmt_execute($stmt)){
            $result['cause'] = "user_exist";
            throw new Exception();
        }

        session_start();
        $user = new user($email, $name);
        $_SESSION['user'] = $user;
        $result['result'] = true;
    }catch (Exception $e){
        $result['result'] = false;
    }

    mysqli_close($conn);
    return json_encode($result);
}

function login(){
    global $result;
    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $result['cause'] = "no_email";
            throw new Exception();
        }else if(!isset($_POST['psw'])){
            $result['cause'] = "no_psw";
            throw new Exception();
        }

        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $psw = sha1($_POST['psw']);

        $sql = "SELECT name, password FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);

        if(!mysqli_stmt_execute($stmt)){
            $result['cause'] = "user_exist";
            throw new Exception();
        }

        mysqli_stmt_bind_result($stmt, $name, $password);
        mysqli_stmt_fetch($stmt);
        if($name == null || $psw !== $password){
            $result['cause'] = "mismatch";
            throw new Exception();
        }

        session_start();
        $user = new user($email, $name);
        $_SESSION['user'] = $user;
        $result['result'] = true;
    }catch (Exception $e){
        $result['result'] = false;
    }

    mysqli_close($conn);
    return json_encode($result);
}

function validatePassword(string $psw): bool{
    $lower = false;
    $upperOrDigit = false;

    for($i = 0; $i < strlen($psw); $i++){
        $char = $psw{$i};
        if(is_numeric($char) || $char === strtoupper($char))
            $upperOrDigit = true;
        if($char === strtolower($char))
            $lower = true;

        if($lower && $upperOrDigit)
            return true;
    }

    return false;
}

function logout(){
    global $result;
    session_start();
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    $result["result"] = true;
    return json_encode($result);
}
