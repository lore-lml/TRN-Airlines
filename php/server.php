<?php
include_once 'user.php';
include_once 'seat.php';

define('host', 'localhost');
define('admin', 'root');
define('psw', '');
define('db', 'trnairlines');
define('COL', 6);
define('ROW', 10);
define('INACTIVITY_TIME', 120);

global $_seatsMap;
global $_numberOfSeatsPerState;

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

function redirect(string $resource, string $key = "msg", string $value = ""){
    if($value === "")
        $key = "";
    else
        $key ="?$key=";

    $cwd = preg_split('/\\\|\//', getcwd());
    header("Location: https://" . $_SERVER["HTTP_HOST"] ."/".$cwd[sizeof($cwd)-1]. "/$resource$key$value");
    //header("Location: https://localhost/TRN-Airlines/index.php?msg=not_your_seat");
    //exit;
}

function connectDb(){
    $conn = mysqli_connect(host, admin, psw, db);
    return $conn;
};

function saveUserSession(user $user){
    session_start();
    $_SESSION['user'] = $user;
    $_SESSION['time'] = time();
}

function destroyUserSession(bool $session_start = true){
    if($session_start)
        session_start();
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600*24,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

function checkInactivity(bool $redirect = true) : bool {
    session_start();
    $t=time();
    $inactivity=0;
    $new=false;

    if (isset($_SESSION['time'])){
        $t0=$_SESSION['time'];
        $inactivity=($t-$t0);
    } else
        $new=true;

    if($new || $inactivity > INACTIVITY_TIME){
        destroyUserSession(false);
        if(!$new && $redirect)
            redirect("index.php", "msg", "session_expired");
        return false;
    }

    $_SESSION['time'] = time();
    return true;

}

function printError(){
    if(isset($_GET['msg'])){
        switch ($_GET['msg']) {
            case "not_your_seat":
                echo "Uno dei tuoi posti è stato prenotato o acquistato da qualcun altro";
                break;
            case "session_expired":
                echo "La tua sessione è scaduta per inattività";
        }
    }
}

function printSeatsGrid(){
    global $_seatsMap;

    $string = '<tr>
                 <th class="seat-grid-col"></th>';

    for($i = 0, $col = 'A'; $i < COL; $i++, $col++){
        if($i == COL/2)
            $string .= '<th class="seat-grid-middle"></th>';
        $string .= "<th>$col</th>";
    }
    $string .= "</tr>";

    echo $string;

    $_seatsMap = getSeatMap();

    $disabled = !isset($_SESSION['user']);
    for($i = 0; $i < ROW; $i++) {
        $string = '<tr>
                        <th class="seat-grid-col">' . ($i + 1) . '</th>';
        for ($j = 0, $col = 'A'; $j < COL; $j++, $col++) {
            if ($j == COL / 2)
                $string .= '<td class="seat-grid-middle"></td>';

            $id = "seat_$col"."_" . ($i + 1);
            $string .= '<td class="my-checkbox';
            if ($j == 0)
                $string .= ' myborder-left';
            else if ($j == COL - 1)
                $string .= ' myborder-right';

            //Controllo se quel posto esiste nel db (Stato diverso da libero)
            if(!isset($_seatsMap[$id])){
                $string .= freeSeat($id, $disabled);
                continue;
            }
            //Prendo lo stato e in base a quello visualizzo lo stile corretto del posto
            $state = $_seatsMap[$id]->{"getState"}();
            switch ($state){
                case "bought":
                    $string .= boughtSeat($id);
                    break;
                case "preordered":
                    if(isset($_SESSION['user']) &&
                        $_seatsMap[$id]->{"getUserEmail"}() === $_SESSION['user']->{"getEmail"}())
                        $string .= mySeat($id);
                    else
                        $string .= preorderedSeat($id, $disabled);
                    break;
                default:
                    $string .= freeSeat($id, $disabled);
                    break;
            }
        }
        $string .= '</tr>';
        echo $string;
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
            $seat = new seat($row['seat_id'], $row['state'], $row['user_email']);
            $seatsMap[$row['seat_id']] = $seat;
        }
    }

    mysqli_close($conn);
    return $seatsMap;
}

function validateId(string $id){

    $results = preg_split("/_/", $id);
    if(sizeof($results) != 3)
        return false;

    $col = ord($results[1]);
    $row = $results[2];

    $col -= ord('A');
    if($col > COL || $col < 0 )
        return false;
    if($row > ROW || $row < 0)
        return false;

    return true;
}

/*function unavailableSeat(string $id): string {
    return '" disabled state="unavailable"><input type="checkbox" disabled id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}*/

function freeSeat(string $id, bool $disabled): string {
    if($disabled){
        return '"><input type="checkbox" id="'.$id.'" disabled autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
    }
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

function preorderedSeat(string $id, bool $disabled): string {
    if($disabled)
        return '" state="preordered"><input type="checkbox" id="'.$id.'" disabled autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';

    return '" state="preordered"><input type="checkbox" id="'.$id.'" autocomplete="off"/>
                              <label for="'.$id.'"></label>
                            </td>';
}

function calcSeatsNumberPerState(){
    global $_seatsMap, $_numberOfSeatsPerState;
    if(!isset($_seatsMap))
        throw new LogicException("You must call printSeatsGrid() before");
    if(isset($_numberOfSeatsPerState))
        return;
    $bought = 0;
    $preordered = 0;
    foreach($_seatsMap as $key => $value){
        $state = $value->{"getState"}();

        if($state === "bought")
            $bought++;
        else if($state === "preordered")
            $preordered++;
    }

    $total = COL*ROW;
    $free = $total - sizeof($_seatsMap);
    $_numberOfSeatsPerState['free'] = $free;
    $_numberOfSeatsPerState['bought'] = $bought;
    $_numberOfSeatsPerState['preordered'] = $preordered;

    //INSERISCO 2% come minima percentuale per non azzerare completamente la progress bar e far vedere il numero
    //Per questioni di grafica
    if($total != 0){
        $_numberOfSeatsPerState['free%'] = $free/$total == 0 ? 2 : $free/$total*100;
        $_numberOfSeatsPerState['bought%'] = $bought/$total == 0 ? 2 : $bought/$total*100;
        $_numberOfSeatsPerState['preordered%'] = $preordered/$total == 0 ? 2 : $preordered/$total*100;
    }else{
        $_numberOfSeatsPerState['free%'] = 2;
        $_numberOfSeatsPerState['bought%'] = 2;
        $_numberOfSeatsPerState['preordered%'] = 2;
    }
}