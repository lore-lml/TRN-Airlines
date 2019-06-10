<?php
define('host', 'localhost');
define('admin', 'root');
define('psw', '');
define('db', 'trnairlines');
global $conn;
global $_M;
global $_N;
global $_logged;
function redirectHTTPSifNeeded(){
    if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on")
    {
        //Dice al browser di reindirizzarsi ad https
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
    return connectDb();
};

function connectDb(){
    global $conn;
    $conn = mysqli_connect(host, admin, psw, db);

    if(!$conn) return false;
    return true;
};

function printSeatsGrid(){
    global $conn, $_logged, $_M, $_N;
    if(!isset($conn))return;

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
                            <th class="seat-grid-col">>' .($i+1) .'</th>';
            for($j = 0, $col='A'; $j < $_M; $j++){
                if($j == $_M/2)
                    $string .= '<td class="seat-grid-middle"></td>';

                $string .= '<td class="my-checkbox" disabled id="seat'.$col.$j.'">
                                <input type="checkbox" id="myCheckbox1c" disabled />
                                <label for="myCheckbox1c"><img src="icons/free_seat.png" width="50" height="50" /></label>
                            </td>';
            }
            $string .= '</tr>';
            echo $string;
        }
    }
}