<?php
include_once 'user.php';
include_once 'seat.php';

define('host', 'localhost');
define('admin', 'root');
define('psw', '');
define('db', 'trnairlines');

global $_M;
global $_N;
global $_logged;
$_seats = array();

function redirectHTTPSifNeeded(){
    if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
    {
        //Dice al browser di reindirizzarsi ad https
        header('HTTP/1.1 301 Moved Permanently');
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
        //Evita che il resto dello script venga eseguito
        exit;
    }
};

function initPage(){
    global $_M, $_N, $_logged;

    $_M = 6;
    $_N = 10;
    $_logged = false;
    session_start();
    return isset($_SESSION['user']);
};

function connectDb(){
    $conn = mysqli_connect(host, admin, psw, db);
    return $conn;
};

function printSeatsGrid(){
    global $_logged, $_M, $_N;

    $string = '<tr>
                 <th class="seat-grid-col"></th>';

    for($i = 0, $col = 'A'; $i < $_M; $i++, $col++){
        if($i == $_M/2)
            $string .= '<th class="seat-grid-middle"></th>';
        $string .= "<th>$col</th>";
    }
    $string .= "</tr>";

    echo $string;

    if($_logged == false){

        for($i = 0; $i < $_N; $i++){
            $string =   '<tr>
                            <th class="seat-grid-col">' .($i+1) .'</th>';
            for($j = 0, $col='A'; $j < $_M; $j++, $col++){
                if($j == $_M/2)
                    $string .= '<td class="seat-grid-middle"></td>';

                $id = "seat$col".($i+1);

                $string .= '<td class="my-checkbox';
                if($j == 0)
                    $string .= ' myborder-left';
                else if($j == $_M-1)
                    $string .= ' myborder-right';


                $string .= '" disabled state="unavailable"><input type="checkbox" disabled id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
            }
            $string .= '</tr>';
            echo $string;
        }
    }
}