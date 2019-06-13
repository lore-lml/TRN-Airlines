<?php
include_once 'user.php';
include_once 'seat.php';

define('host', 'localhost');
define('admin', 'root');
define('psw', '');
define('db', 'trnairlines');

global $_M;
global $_N;
global $_seatsMap;

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
    session_start();
    return isset($_SESSION['user']);
};

function connectDb(){
    $conn = mysqli_connect(host, admin, psw, db);
    return $conn;
};

function printSeatsGrid(){
    global $_M, $_N;

    $string = '<tr>
                 <th class="seat-grid-col"></th>';

    for($i = 0, $col = 'A'; $i < $_M; $i++, $col++){
        if($i == $_M/2)
            $string .= '<th class="seat-grid-middle"></th>';
        $string .= "<th>$col</th>";
    }
    $string .= "</tr>";

    echo $string;

    if(!isset($_SESSION['user'])){

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


                $string .= unavailableSeat($id);
            }
            $string .= '</tr>';
            echo $string;
        }
    }else{
        global $_seatsMap;

        $_seatsMap = getSeatMap();
        for($i = 0; $i < $_N; $i++) {
            $string = '<tr>
                            <th class="seat-grid-col">' . ($i + 1) . '</th>';
            for ($j = 0, $col = 'A'; $j < $_M; $j++, $col++) {
                if ($j == $_M / 2)
                    $string .= '<td class="seat-grid-middle"></td>';

                $id = "seat$col" . ($i + 1);
                $string .= '<td class="my-checkbox';
                if ($j == 0)
                    $string .= ' myborder-left';
                else if ($j == $_M - 1)
                    $string .= ' myborder-right';

                //Controllo se quel posto esiste nel db (Stato diverso da libero)
                if(!isset($_seatsMap[$id])){
                    $string .= freeSeat($id);
                    continue;
                }
                //Prendo lo stato e in base a quello visualizzo lo stile corretto del posto
                $state = $_seatsMap[$id]->{"getState"}();
                switch ($state){
                    case "bought":
                        $string .= boughtSeat($id);
                        break;
                    case "preordered":
                        $string .= preorderedSeat($id);
                        break;
                    default:
                        $string .= freeSeat($id);
                        break;
                }
            }
            $string .= '</tr>';
            echo $string;
        }
    }
}

function getSeatMap(): array{
    $seatsMap = array();
    $conn = connectDb();
    if(!$conn){
        echo "Errore connessione al database";
        return $seatsMap;
    }


    $sql = "SELECT seat_id, state, user_email FROM seats";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            $seat = new seat($row[0], $row[1], $row[2]);
            $seatMap[$row[0]] = $seat;
        }
    }

    mysqli_close($conn);
    return $seatsMap;
}

function unavailableSeat(string $id): string {
    return '" disabled state="unavailable"><input type="checkbox" disabled id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}

function freeSeat(string $id): string {
    return '"><input type="checkbox" id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}

function mySeat(string $id): string {
    return '"><input type="checkbox" id="'.$id.'" autocomplete="off" checked/>
                              <label for="'.$id.'"></label>
                            </td>';
}

function boughtSeat(string $id): string {
    return '" disabled state="bought"><input type="checkbox" disabled id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}

function preorderedSeat(string $id): string {
    return '" disabled state="preordered"><input type="checkbox" disabled id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}