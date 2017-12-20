<?php
/**
 * INDEX - Part of OnlineDelphi project. This source file contains main page and 
 * the hmtl structur of the OnlineDelphi Project. Delphi Umfragen and Live 
 * Umfragen are shown using umfrage.php and liveumfrage.php.
 * @package OnlineDelphi
 * @author Felix Gräßer (IBMT, TU Dresden) <felix.graesser@tu-dresden.de>
 */
// benötigte Skripte einbinden
include('./login.php');
include('./poll.php');

// Session starten
session_start();

// aktueller Pfad zur Datei
$pfad = $_SERVER['SCRIPT_NAME'];

// Ausgabepuffer starten
ob_start();
?>

<!-- HTML -->
<html lang="en">
    <head>
        <!--Meta -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Title -->
        <title>OnlineDelphi</title>

        <!-- Bootstrap Core CSS -->
        <link href="../css/bootstrap.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="../css/custom.css" rel="stylesheet">

        <!-- Custom Fonts from Google -->
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    </head>

    <body>
        <!-- Navigation -->
        <nav id="siteNav" class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <!-- Logo and responsive Title -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">

                        <table width = 100% border = 0>
                            <colgroup>
                                <col align="left" width="60px">
                                <col align="left">
                            </colgroup>
                            <tr>
                                <!-- Logo -->
                                <td><img src="../images/Logo_ZEGV_small.png" width="50px"></td>

                                <!-- Titel -->
                                <td>Zentrum für Evidenzbasierte</br>
                                    Gesundheitsversorgung</td>
                            </tr>
                        </table>
                    </a>
                </div>

                <!-- Navigationsbar rechts -->
                <div class="collapse navbar-collapse" id="navbar">
                    <ul class="nav navbar-nav navbar-right">

                        <?php
                        if (!isset($_GET['action'])) {
                            echo "<li class=\"active\">";
                        } else {
                            echo "<li>";
                        }
                        ?>
                        <!-- About -->
                        <a href="index.php">About</a>
                        </li>

                        <!-- Dropdown Umfragetyp -->
                        <li class="dropdown">
                            <?php
                            if (isset($_GET['action']) && $_GET['action'] == 'umfragen') {
                                echo "<li class=\"active\">";
                            } else {
                                echo "<li>";
                            }
                            ?>                        
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Umfragen <span class="caret"></span></a>
                            <ul class="dropdown-menu" aria-labelledby="about-us">
                                <li><a href="index.php?action=umfragen">Delphi Umfragen</a></li>
                                <li><a href="index.php?action=live">Live Umfrage</a></li>
                            </ul>
                        </li>
                        </li>

                        <!-- Login -->
                        <?php if (!isset($_SESSION['login'])) { ?>
                            <?php
                            if (isset($_GET['action']) && $_GET['action'] == 'login') {
                                echo "<li class=\"active\">";
                            } else {
                                echo "<li>";
                            }
                            ?>
                            <a href = "index.php?action=login">Login</a>
                            </li>
                        <?php } else { ?>
                            <?php
                            if (isset($_GET['action']) && $_GET['action'] == 'logout') {
                                echo "<li class=\"active\">";
                            } else {
                                echo "<li>";
                            }
                            ?>
                            <a href="index.php?action=logout">Logout</a>
                            </li>
                        <?php } ?>

                        <!-- Impressum -->
                        <?php
                        if (isset($_GET['action']) && $_GET['action'] == 'impressum') {
                            echo "<li class=\"active\">";
                        } else {
                            echo "<li>";
                        }
                        ?>
                        <a href="index.php?action=impressum">Impressum</a>
                        </li>
                    </ul>

                </div>
            </div>
        </nav>

        <!-- keine action: Startseite -->
        <?php if (!isset($_GET['action'])) { ?>
            <a name="start"></a>
            <header>
                <div class="header-content">
                    <div class="container">

                        <!-- Startseite Bild -->
                        <img src="../images/about.png" alt="about picture" style="width:100%;"> 

                        <!-- Startseite Text -->
                        <h2>About</h2>
                        <p>Zweck der Umfrage. Informationen zur Umfrage.</p>

                        <!-- Startseite Button -->
                        <a href="index.php?action=umfragen" class="btn btn-primary btn-lg">Zu den Delphi Umfragen</a>
                        <a href="index.php?action=live" class="btn btn-primary btn-lg">Zu den Live Umfragen</a>
                    </div>
                </div>
            </header>
        <?php } ?>

        <!-- abhänging von action ... -->
        <?php
        if (isset($_GET['action'])) {
            $action = ($_GET['action']);

            // Delphi Umfragen
            if ($action == 'umfragen') {
                ?>

                <a name = "umfragen"></a>
                <section class = "content content-2">
                    <form method="post">

                        <h2>Delphi Umfragen</h2>

                        <?php
                        $umfrageTyp = 1;
                        $admin = 0;
                        if (isset($_SESSION['login'])) {
                            if ($_SESSION['loginAdmin'] == true) {
                                $admin = 1;
                            }
                        }

                        // Datenbank verbinden
                        $connection = connect_database($umfrageTyp);

                        // Umfragen auflisten                    
                        $umfragen = load_umfragen($connection, $umfrageTyp, $admin);
                        foreach ($umfragen as $umfrageId => $val) {
                            echo "</br>";

                            $umfrageTxt = $umfragen[$umfrageId][0];
                            $umfrageChart = $umfragen[$umfrageId][1];
                            $umfrageAktiv = $umfragen[$umfrageId][2];

                            // Eingabe verarbeiten
                            if (isset($_POST["umfrage$umfrageId"])) {
                                $_SESSION['umfrageId'] = $umfrageId;
                                $_SESSION['umfrageChart'] = $umfrageChart;
                                $_SESSION['umfrageTyp'] = $umfrageTyp;

                                $closed = !login_poll($connection, $umfrageId);
                                if ($closed == true) {
                                    if (isset($_SESSION['login'])) {
                                        header("Location: ./umfrage.php");
                                    } else {
                                        header("Location: ./index.php?action=login");
                                    }
                                } else {
                                    header("Location: ./umfrage.php");
                                }
                            }

                            // Umfrage aktiv oder Admin?
                            if ($umfrageAktiv == 1 || $admin == 1) {

                                // Umfrage Text
                                echo "<p>$umfrageTxt</p>";

                                // Wenn Admin, zeige Auswahlmöglichkeiten: aktivieren, Runde, ...
                                if ($admin) {
                                    $umfrageChart = select_status($connection, $umfrageId, $umfrageTyp, $umfrageChart);
                                }

                                // Umfrage zeigen Button
                                echo "<input class=\"btn btn-primary btn-lg\" name=\"umfrage{$umfrageId}\" type=\"submit\" value=\"Umfrage zeigen\"/></td>";
                            }

                            echo "</br>";
                            echo "</br>";
                            echo "</br>";
                        }
                        ?>
                    </form>
                </section>
                <?php
            }

            // Live Umfragen
            if ($action == 'live') {
                ?>

                <a name = "live"></a>
                <section class = "content content-2">
                    <form method="post">

                        <h2>Live Umfragen</h2>

                        <?php
                        $umfrageTyp = 2;
                        $admin = 0;
                        if (isset($_SESSION['login'])) {
                            if ($_SESSION['loginAdmin'] == true) {
                                $admin = 1;
                            }
                        }

                        // Datenbank verbinden
                        $connection = connect_database($umfrageTyp);

                        // Umfragen auflisten                    
                        $umfragen = load_umfragen($connection, $umfrageTyp, $admin);
                        foreach ($umfragen as $umfrageId => $val) {
                            echo "</br>";

                            $umfrageTxt = $umfragen[$umfrageId][0];
                            $umfrageChart = $umfragen[$umfrageId][1];
                            $umfrageAktiv = $umfragen[$umfrageId][2];

                            // Eingabe verarbeiten
                            if (isset($_POST["umfrage$umfrageId"])) {
                                $_SESSION['umfrageId'] = $umfrageId;
                                $_SESSION['umfrageChart'] = $umfrageChart;
                                $_SESSION['umfrageTyp'] = $umfrageTyp;

                                $closed = !login_poll($connection, $umfrageId);
                                if ($closed == true) {
                                    if (isset($_SESSION['login'])) {
                                        header("Location: ./liveumfrage.php");
                                    } else {
                                        header("Location: ./index.php?action=login");
                                    }
                                } else {
                                    header("Location: ./liveumfrage.php");
                                }
                            }

                            // Umfrage aktiv oder Admin?
                            if ($umfrageAktiv == 1 || $admin == 1) {

                                // Umfrage Ttext
                                echo "<p>$umfrageTxt</p>";

                                // Wenn Admin, zeige Auswahlmöglichkeiten: aktivieren, Runde, ...
                                if ($admin) {
                                    $umfrageChart = select_status($connection, $umfrageId, $umfrageTyp, $umfrageChart);
                                }

                                // Umfrage zeigen Button
                                echo "<input class=\"btn btn-primary btn-lg\" name=\"umfrage{$umfrageId}\" type=\"submit\" value=\"Umfrage zeigen\"/></td>";
                            }

                            echo "</br>";
                            echo "</br>";
                            echo "</br>";
                        }
                        ?>
                    </form>
                </section>
                <?php
            }

            // Login
            if ($action == 'login') {
                ?>

                <a name="login"></a>
                <section class="content content-2">
                    <div class="container">
                        <h2>Login</h2>
                        <p>Info zum Login.</p>
                        </br>

                        <?php
                        if (!isset($_SESSION['login'])) { // wenn keine Session vorhanden
                            if (isset($_POST['submit_login'])) { // wenn submit gedrueckt
                                // 
                                // Variablen definieren
                                $loginName = $_POST['log_user'];
                                $loginPass = $_POST['log_pass'];

                                // login prüfen
                                $message = check_login($loginName, $loginPass);

                                if (empty($message)) { // wenn keine error message
                                    // Session setzen und login namen uebernehmen
                                    $_SESSION['login'] = $_SERVER['REMOTE_ADDR'];
                                    $_SESSION['loginUserName'] = $loginName;
                                    $_SESSION['loginUserID'] = get_loginID($loginName);
                                    $_SESSION['loginAdmin'] = check_admin($_SESSION['loginUserID']);

                                    // Weiterleiten ..
                                    if (isset($_SESSION['umfrageId']) && isset($_SESSION['umfrageTyp'])) {
                                        // Umfrage
                                        if ((int) $_SESSION['umfrageTyp'] == 1) {
                                            header("Location: ./umfrage.php");
                                        } elseif ((int) $_SESSION['umfrageTyp'] == 2) {
                                            header("Location: ./liveumfrage.php");
                                        }
                                    } else {
                                        // Startseite
                                        header("Location: $pfad");
                                    }
                                } else {  // Fehlermeldung ausgeben wenn vorhanden
                                    echo "<p>$message<br/></p>";
                                }
                            }
                            ?>

                            <div class="login-form-1">
                                <form action="" method=post id="login-form" class="text-left">
                                    <div class="main-login-form">
                                        <div class="login-group">
                                            <div class="form-group">
                                                <label for="lg_username" class="sr-only">Username</label>
                                                <input type="text" class="form-control" id="lg_username" name="log_user" placeholder="username">
                                            </div>
                                            <div class="form-group">
                                                <label for="lg_password" class="sr-only">Password</label>
                                                <input type="password" class="form-control" id="lg_password" name="log_pass" placeholder="password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        </br>
                                        <input class="btn btn-primary btn-lg" name="submit_login" type="submit" value="Login" />
                                    </div>
                                </form>
                            </div>  
                            <?php
                        } else {
                            $loginName = $_SESSION['loginUserID'];
                            echo "<p>Sie sind eingelogged als $loginName</p>.";
                            echo "<a href=\"?action=logout\" class=\"btn btn-default btn-lg\">Logout</a>";
                        } // close isset Session login
                        ?>
                </section>
                <?php
            }

            // Impressum
            if ($action == 'impressum') {
                ?>
                <a name = "impressum"></a>
                <section class = "content content-2">
                    <h2>Impressum</h2>
                    <p>Zweck der Umfrage. Informationen zur Umfrage.</p>
                </section>

            </div>

            <?php
        }
        // Logout
        if ($action == 'logout') {
            session_unset();   // Sessionvariable loeschen	
            session_destroy();   // Session zerstoeren

            header("Location: $pfad"); // Weiterleitung => Login
        }
    }
    ?>

    <!--jQuery--> 
    <script src = "../js/jquery-1.11.3.min.js"></script>

    <!--Bootstrap Core JavaScript--> 
    <script src="../js/bootstrap.min.js"></script>

    <!--Plugin JavaScript--> 
    <script src="../js/jquery.easing.min.js"></script>

    <!--Custom Javascript--> 
    <script src="../js/custom.js"></script>

</body>
</html>
