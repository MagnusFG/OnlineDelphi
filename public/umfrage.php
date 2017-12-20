<?php

/**
 * UMFRAGE - Part of OnlineDelphi project. This source file contains the hmtl 
 * structur of the Delphi Umfrage and shows questions, answers and 
 * result charts using poll.php.
 * @package OnlineDelphi
 * @author Felix Gräßer (IBMT, TU Dresden) <felix.graesser@tu-dresden.de>
 */

include('./poll.php');

session_start();
?>

<html lang="en">
    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

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
        
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="../js/bootstrap.min.js"></script>

        <!-- Header -->
        <section>
            <div class="intro-umfrage">        
            </div>
        </section>

        <!-- umfrage laden -->
        <section class="content content-umfrage">
            <?php
            $umfrageTyp = 1;

// Benutzer ID
            if (isset($_SESSION['loginUserID'])) { // angemeldeter Benutzer?
                $loginUserID = $_SESSION['loginUserID'];
            } else {
                $loginUserID = 0;
            }

// Datenbank verbinden
            $connection = connect_database($umfrageTyp);

// neuen Benutzer für Umfrage anlegen
            add_user($connection, $loginUserID);

// Umfrage zeigen
            if (isset($_SESSION['umfrageId']) AND isset($_SESSION['umfrageChart'])) {
                show_poll($connection, (int) $_SESSION['umfrageId'], $umfrageTyp, (int) $_SESSION['umfrageChart']);
            } else {
                echo "<p class=\"lead text-muted\">Ungültige Eingabe.</p>";
            }

// Datenbankverbindung trennen
            disconnect_database($connection);
            ?>

        </section>
    </body>
</html>