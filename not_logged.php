<?php include_once "php/server.php" ?>
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


    <!-- jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
</head>

<body>
<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>
                <img src="icons/airplane.png" width="30" height="30" class="d-inline-block align-top" alt="">
                Benvenuto
            </h3>
            <strong>
                <img src="icons/airplane.png" width="30" height="30" class="d-inline-block align-top" alt="">
            </strong>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="#" id="register-btn">
                    <i class="fas fa-user-plus"></i>
                    Registrati
                </a>
            </li>
            <li>
                <a href="#" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </li>
        </ul>
        <ul class="list-unstyled actions">
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
            </div>

            <div class="body-right">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Prima di prenotare il tuo posto è necessario effettuare il login o creare un nuovo account</h4>
                <br><br>
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

<div class="popup"  id="login-popup" hidden>
    <div class="popup-content">
        <i class="fas fa-times close-popup" id="close-login"></i>
        <div class="popup-title">
            <img src="icons/airplane.png" alt="" width="80" height="80">
            <h3>TRN Airlines</h3>
        </div>


        <div class="form-group">
            <label for="loginEmail">Email</label>
            <input type="email" class="form-control" id="loginEmail" aria-describedby="emailHelp" placeholder="Inserisci la tua email" maxlength = "50">
            <p class="warning-field" id="warningEmail-log"></p>
        </div>
        <div class="form-group">
            <label for="loginPassword">Password</label>
            <input type="password" class="form-control" id="loginPassword" placeholder="Password">
            <p class="warning-field" id="warningPsw-log"></p>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="loginCheck">
            <label class="form-check-label" for="loginCheck">Ricordami</label>
        </div>
        <div>
            <p class="warning-field" id="warningDefault-log"></p>
        </div>
        <button class="btn btn-outline-primary" id="loginBtn">Login</button>
    </div>
</div>

<div class="popup" id="register-popup" hidden>
    <div class="popup-content">
        <i class="fas fa-times close-popup" id="close-register"></i>
        <div class="popup-title">
            <img src="icons/airplane.png" alt="" width="80" height="80">
            <h3>TRN Airlines</h3>
        </div>


        <!--<div class="form-group">
            <label for="registerName">Nome</label>
            <input type="text" class="form-control" id="registerName" placeholder="Inserisci il tuo nome">
            <p class="warning-field" id="warningName"></p>
        </div>-->
        <div class="form-group">
            <label for="registerEmail">Email</label>
            <input type="email" class="form-control" id="registerEmail" aria-describedby="emailHelp" placeholder="Inserisci la tua email" maxlength = "50">
            <p class="warning-field" id="warningEmail"></p>
        </div>
        <div class="form-group">
            <label for="registerPassword">Password</label>
            <input type="password" class="form-control" id="registerPassword" placeholder="Password">
            <p class="warning-field" id="warningPsw1"></p>
        </div>
        <div class="form-group">
            <label for="registerPassword2">Reinserisci password</label>
            <input type="password" class="form-control" id="registerPassword2" placeholder="Password">
            <p class="warning-field" id="warningPsw2"></p>
        </div>
        <div>
            <p class="warning-field" id="warningDefault"></p>
        </div>
        <button class="btn btn-outline-primary" id="registerBtn">Registrati</button>
    </div>
</div>

<!-- jQuery CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
</body>

</html>
