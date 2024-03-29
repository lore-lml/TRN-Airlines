<?php include_once "php/server.php";
include_once "php/user.php";?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="lore-lml">
    <title>TRN Airlines</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>
                <img src="icons/airplane.png" width="30" height="30" class="d-inline-block align-top" alt="">
                <?php
                    $user = $_SESSION['user'];
                    echo $user->{"getName"}();
                ?>
            </h3>
            <strong>
                <img src="icons/airplane.png" width="30" height="30" class="d-inline-block align-top" alt="">
            </strong>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="#" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
        <ul class="list-unstyled actions">
            <li>
                <a href="index.php" class="btn-action">
                    <i class="fas fa-redo-alt"></i>
                    Aggiorna
                </a>
            </li>
            <li>
                <a href="" class="btn-action" id="btn-cancelPreorderedSeats">
                    <i class="fas fa-trash-alt"></i>
                    Cancella Prenotazioni
                </a>
            </li>
            <li>
                <noscript class="no-js">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>JavaScript non è attivo:<br>Il sito non funzionerà correttamente finchè
                        non verrà riattivato dalle impostazioni del browser</p>
                </noscript>
            </li>
        </ul>
    </nav>

    <!-- Contenuto  -->
    <div id="content">

        <nav class="navbar navbar-expand-sm navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary">
                    <i class="fas fa-align-right"></i>
                </button>
                <h3 class="mr-auto">TRN Airlines</h3>
                <!--
                          <ul class="nav navbar-nav ml-auto">
                              <li class="nav-item active">
                                  <a class="nav-link" href="#">Page</a>
                              </li>
                              <li class="nav-item">
                                  <a class="nav-link" href="#">Page</a>
                              </li>
                          </ul>
                          -->
                <h5 id="error-field"><?php printError() ?></h5>
                <h5 id="success-field"><?php printSuccess() ?></h5>
            </div>
        </nav>

        <h3>Prenota facilmente i tuoi posti preferiti per il volo</h3>
        <div class="body">

            <table class="seat-grid">
                <?php
                printSeatsGrid();
                ?>
            </table>

            <div class="body-center">
                <div class="legend">
                    <div class="legend-item">
                        <img src="icons/free_seat.png" alt="" width="50" height="50">
                        <p>Posto libero</p>
                    </div>
                    <div class="legend-item">
                        <img src="icons/preordered_seat.png" alt="" width="50" height="50">
                        <p>Posto prenotato</p>
                    </div>
                    <div class="legend-item">
                        <img src="icons/my_seat.png" alt="" width="50" height="50">
                        <p>Il tuo posto</p>
                    </div>
                    <div class="legend-item">
                        <img src="icons/bought_seat.png" alt="" width="50" height="50">
                        <p>Posto acquistato</p>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-block" id="btn-compra">Acquista</button>
            </div>

            <div class="body-right logged">
                <i class="fas fa-plane-departure" id="seat-summary"></i>
                <h4>Riepilogo posti</h4>
                <div class="summary">
                    <p id="totalSeat"><strong>Posti Totali: <span><?php echo COL*ROW;?></span>
                        </strong></p>
                    <div class="summary-item">
                        <p>Posti acquistati: </p>
                        <div class="progress">
                            <div class="progress-bar bg-red" role="progressbar" <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                $boughtPerc = $_numberOfSeatsPerState['bought%'];
                                echo "style='width: $boughtPerc%;' aria-valuenow='$boughtPerc'"?> aria-valuemin="0" aria-valuemax="100">
                                <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                echo $_numberOfSeatsPerState['bought'];?>
                            </div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <p>Posti prenotati: </p>
                        <div class="progress">
                            <div class="progress-bar bg-orange" role="progressbar" <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                $preorderedPerc = $_numberOfSeatsPerState['preordered%'];
                                echo "style='width: $preorderedPerc%;' aria-valuenow='$preorderedPerc'"?> aria-valuemin="0" aria-valuemax="100">
                                <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                echo $_numberOfSeatsPerState['preordered'];?>
                            </div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <p>Posti liberi:</p>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                $freePerc = $_numberOfSeatsPerState['free%'];
                                echo "style='width: $freePerc%;' aria-valuenow='$freePerc'"?> aria-valuemin="0" aria-valuemax="100">
                                <?php global $_numberOfSeatsPerState;
                                calcSeatsNumberPerState();
                                echo $_numberOfSeatsPerState['free'];?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

<script type="text/javascript" src="js/script.js"></script>
</body>
</html>

