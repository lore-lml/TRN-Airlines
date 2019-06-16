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

        //CONTROLLARE STO PEZZO
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
        mysqli_stmt_close($stmt);

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

function userExist($link, $email) : bool{
    $email = mysqli_real_escape_string($link, $email);

    $sql = "SELECT COUNT(*) AS cnt FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);

    if(!mysqli_stmt_execute($stmt))
        throw new mysqli_sql_exception("db_error");

    mysqli_stmt_bind_result($stmt, $cnt);
    mysqli_stmt_fetch($stmt);

    $response = true;
    if($cnt == null)
        $response = false;

    mysqli_stmt_close($stmt);
    return $response;
}

function preorderSeat(){
    global $result;
    session_start();

    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_SESSION['user'])){
            $result['cause'] = "session_expired";
            throw new Exception();
        }
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $email = $_SESSION['user']->{"getEmail"}();

        if(!userExist($conn, $email)){
            $result['cause'] = "invalid_email";
            throw new Exception();
        }

        if(!validateId($id)){
            $result['cause'] = "invalid_id";
            throw new Exception();
        }

        mysqli_autocommit($conn, false);
        //PROVO AD INSERIRE LA PRENOTAZIONE
        $sql = "INSERT INTO seats(seat_id, state, user_email) VALUES(?,'preordered',?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $id, $email);

        mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);

        //Se non funziona vuol dire che il posto è gia stato prenotato
        if($affected_rows == -1){
            //$err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);

            $sql = "SELECT state FROM seats WHERE seat_id = ? LIMIT 1 FOR UPDATE";
            $stmt1 = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt1, "s", $id);

            if(!mysqli_stmt_execute($stmt1)){
                //$err = mysqli_stmt_error($stmt1);
                $result['cause'] = "db_error";
                throw new Exception();
            }
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_row($res);
            //SE SIAMO QUI DENTRO ALLORA IL POSTO E' PREORDINATO O COMPRATO
            //SE IL POSTO E' COMPRATO LANCIA ECCEZIONE
            if(isset($row['state']) && $row['state'] === 'bought'){
                $result['cause']="already_bought";
                throw new Exception();
            }
            mysqli_stmt_close($stmt1);

            $sql = "UPDATE seats SET user_email = ?, state='preordered' WHERE seat_id = ?";
            $stmt2 = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt2, "ss", $email, $id);

            if(!mysqli_stmt_execute($stmt2)){
                //$err = mysqli_stmt_error($stmt2);
                $result['cause'] = "db_error";
                throw new Exception();
            }


            mysqli_stmt_close($stmt2);
        }

        $result['result'] = true;
    }catch (mysqli_sql_exception $e){
        $result['result'] = false;
        $result['cause'] = "db_error";
        mysqli_autocommit($conn, true);
    }catch (Exception $e){
        $result['result'] = false;
        if($result['cause'] !== "session_expired") {
            mysqli_rollback($conn);
            mysqli_autocommit($conn, true);
        }
    }
    mysqli_autocommit($conn, true);
    mysqli_close($conn);
    return json_encode($result);
}

function cancelSeat(){
    global $result;
    session_start();

    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_SESSION['user'])){
            $result['cause'] = "session_expired";
            throw new Exception();
        }
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $email = $_SESSION['user']->{"getEmail"}();

        if(!userExist($conn, $email)){
            $result['cause'] = "invalid_email";
            throw new Exception();
        }

        if(!validateId($id)){
            $result['cause'] = "invalid_id";
            throw new Exception();
        }

        //PROVO A CANCELLARE LA PRENOTAZIONE
        $sql = "DELETE FROM seats WHERE seat_id = ? AND user_email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $id, $email);

        mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if($affected_rows == 0){
            $result['cause'] = "not_your_seat";
            throw new Exception();
        }else if($affected_rows == -1){
            $result['cause'] = "db_error";
            throw new Exception();
        }

        $result['result'] = true;
    }catch (mysqli_sql_exception $e){
        $result['result'] = false;
        $result['cause'] = "db_error";
        mysqli_autocommit($conn, true);
    }catch (Exception $e){
        $result['result'] = false;
    }

    mysqli_close($conn);
    return json_encode($result);
}

function cancelPreorderedSeats(bool $sessionStart = true){
    global $result;
    if($sessionStart)
        session_start();

    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_SESSION['user'])){
            $result['cause'] = "session_expired";
            throw new Exception();
        }

        $email = $_SESSION['user']->{"getEmail"}();

        if(!userExist($conn, $email)){
            $result['cause'] = "invalid_email";
            throw new Exception();
        }

        $sql = "DELETE FROM seats WHERE state = 'preordered' AND user_email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if($affected_rows == -1){
            $result['cause'] = "db_error";
            throw new Exception();
        }

        $result['result'] = true;
    }catch (mysqli_sql_exception $e){
        $result['result'] = false;
        $result['cause'] = "db_error";
        mysqli_autocommit($conn, true);
    }catch (Exception $e){
        $result['result'] = false;
        if($result['cause'] !== "session_expired") {
            mysqli_rollback($conn);
            mysqli_autocommit($conn, true);
        }
    }

    mysqli_close($conn);
    return json_encode($result);
}

function buySeats(){
    global $result;
    session_start();

    $conn = connectDb();

    if(!$conn){
        $result['cause'] = "db_error";
        return json_encode($result);
    }

    try{
        if(!isset($_SESSION['user'])){
            $result['cause'] = "session_expired";
            throw new Exception();
        }

        $ids = array();
        $cnt = 0;
        //SANITIZZO GLI ID
        foreach($_POST['ids'] as $id){
            $ids[$cnt] = mysqli_real_escape_string($conn, $id);
            $cnt++;
        }

        $email = $_SESSION['user']->{"getEmail"}();

        if(!userExist($conn, $email)){
            $result['cause'] = "invalid_email";
            throw new Exception();
        }
        //CONVALIDO GLI ID
        foreach ($ids as $id)
            if(!validateId($id)){
                $result['cause'] = "invalid_id";
                throw new Exception();
            }
        unset($id);

        mysqli_autocommit($conn, false);
        //CERCO TUTTI I BIGLIETTI COMPRATI E CONTROLLO CHE I LORO ID NON SIANO PRESENTI IN QUELLI PASSATI
        $sql = "SELECT seat_id FROM seats WHERE user_email <> ? FOR UPDATE";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        if(!mysqli_stmt_execute($stmt)){
            $result['cause'] = "db_error";
            throw new Exception();
        }

        $res = mysqli_stmt_get_result($stmt);

        //CONTROLLO CHE I POSTI INSERITI NON SiANO GIA' STATI COMPRATI
        while($row = mysqli_fetch_row($res)){
            if(in_array($row[0], $ids, true)){
                $result['cause'] = "not_your_seat";
                throw new Exception();
            }
        }

        $queries = buildQueriesForBuying($ids,$email);
        if(!$queries){
            $result['cause'] = "no_ids_sent";
            throw new Exception();
        }
        foreach ($queries as $sql){
            $stmt = mysqli_prepare($conn,$sql);

            if(!mysqli_stmt_execute($stmt)){
                $result['cause'] = "db_error";
                throw new Exception();
            }

            if(mysqli_stmt_affected_rows($stmt) <= 0){
                $result['cause'] = "update_error";
                throw new Exception();
            }
        }


        $result['result'] = true;
    }catch (mysqli_sql_exception $e){
        $result['result'] = false;
        $result['cause'] = "db_error";
        mysqli_autocommit($conn, true);
    }catch (Exception $e){
        $result['result'] = false;
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);

        if($result['cause'] !== "session_expired") {
            if($result['cause'] === "not_your_seat"){
                cancelPreorderedSeats(false);
                unset($result);
                $result['result'] = false;
                $result['cause'] = "not_your_seat";
                $result['redirect'] = "index.php?msg=".$result['cause'];
            }
        }
    }

    mysqli_autocommit($conn, true);
    mysqli_close($conn);
    return json_encode($result);
}
function buildQueriesForBuying(array $ids, string $email): array {
    $cnt = sizeof($ids);
    if($cnt <= 0 ) return false;

    $cnt = 0;
    $queries = array();
    foreach ($ids as $id){
        $sql = "INSERT INTO seats(seat_id, state, user_email) VALUES('$id','bought','$email') "
            ."ON DUPLICATE KEY UPDATE state='bought', user_email = '$email'";
        $queries[$cnt++] = $sql;
    }

    return $queries;
}
function buildBuySeatsQuery(array $ids, string $email){
    $cnt = sizeof($ids);
    if($cnt <= 0 ) return false;


    $sql = "UPDATE seats SET state = 'bought', user_email = '$email'";
    $sql .= "WHERE ";
    for($i = 0; $i < $cnt; $i++){
        $id = $ids[$i];
        $sql .= "seat_id = '$id'";
        if($i !== $cnt-1)
            $sql .= " OR ";
    }

    return $sql;
}

function buildParamsBindList(&$stmt, string &$email, array &$ids): array{
    $param = array();
    $param[0] = $stmt;

    $types = "s";
    for($i = 0; $i < sizeof($ids); $i++)
        $types .= "s";
    $param[1] = &$types;
    $param[2] = $email;

    $cnt = 3;
    foreach($ids as $id)
        $param[$cnt++] = $id;

    return $param;
}