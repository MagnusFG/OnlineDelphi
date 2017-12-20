<?php

// ----------------------------------------------------------------------------
// Funktion: mit Datenbank verbinden
// ----------------------------------------------------------------------------
function connect_database($UmfrageTyp) {
    if ($UmfrageTyp == 1) {
        $host = "localhost";   // Adresse des Datenbankservers, fast immer localhost
        $user = "root";            // Dein MySQL Benutzername
        $pass = "";            // Dein MySQL Passwort
        $dbase = "Umfragen";           // Name der Datenbank
    } elseif ($UmfrageTyp == 2) {
        $host = "localhost";   // Adresse des Datenbankservers, fast immer localhost
        $user = "root";            // Dein MySQL Benutzername
        $pass = "";            // Dein MySQL Passwort
        $dbase = "LiveUmfragen";           // Name der Datenbank
    } else {
        echo "Ungültiger Umfragetyp.";
    }

    $connection = new PDO("mysql:host=$host; dbname=$dbase", $user, $pass) OR DIE("Keine Verbindung zu der Datenbank moeglich.");
    
    return $connection;
}

// ----------------------------------------------------------------------------
// Funktion: Verbindung zu Datenbank trennen
// ----------------------------------------------------------------------------
function disconnect_database($connection) {

    $connection = null;
}

// ----------------------------------------------------------------------------
// Funktion: mit Login Datenbank verbinden
// ----------------------------------------------------------------------------
function connect_login() {

    $host = "localhost"; // Adresse des Datenbankservers
    $user = "root"; // MySQL Benutzername
    $pass = ""; // MySQL Passwort
    $dbase = "Login"; // Name der Datenbank

    $connection = new PDO("mysql:host=$host; dbname=$dbase", $user, $pass) OR DIE("Keine Verbindung zu der Datenbank moeglich.");

    // Verbindungs handle zurückgeben
    return $connection;
}

// ----------------------------------------------------------------------------
// Funktion: Verbindung zu Login Datenbank trennen
// ----------------------------------------------------------------------------
function disconnect_login($connection) {

    $connection = null;
}

?>