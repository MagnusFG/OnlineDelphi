<?php

/**
 * LOGIN - Part of OnlineDelphi project. This source file contains all functions
 * for login and user management.
 * @package OnlineDelphi
 * @author Felix Gräßer (IBMT, TU Dresden) <felix.graesser@tu-dresden.de>
 */
// ----------------------------------------------------------------------------
// Funktion: Login ID für Benutzername zurückgegeben
// ----------------------------------------------------------------------------
function get_loginID($login_name) {

    // Variablen definieren
    $loginUserID = 0;
    
    // variablen vorbereiten
    $login_name = strip_tags($login_name);

    // mit Login Datenbank verbinden
    $connection = connect_login();

    // Login ID aus Datenbank lesen
    $qryLoginUser = $connection->prepare("SELECT Login_ID FROM tblLogin WHERE LoginUsername_txt = :name");
    $qryLoginUser->execute(array(':name' => $login_name));
    while ($rowLoginUser = $qryLoginUser->fetch(PDO::FETCH_OBJ)) {
        $loginUserID = $rowLoginUser->Login_ID;
        echo $loginUserID;
    }

    // Verbindung zu Login Datenbank trennen
    disconnect_login($connection);

    // Login ID zurückgeben
    return $loginUserID;
}

// ----------------------------------------------------------------------------
// Funktion: Loginname und Passwort prüfen
// ----------------------------------------------------------------------------
function check_login($login_name, $login_pass) {

    // Variablen definieren
    $message = null;
    
    // Variablen vorbereiten
    $login_name = strip_tags($login_name);
    $login_pass = strip_tags($login_pass);    

    // mit Login Datenbank verbinden
    $connection = connect_login();

    // Pruefen ob Loginname angegeben wurde
    if (empty($login_name) OR empty($login_pass)) {
        disconnect_login($connection);
        return $message .= 'Bitte geben Sie ihren Usernamen und das Passwort ein.';
    }

    // Logindaten prüfen
    $qryLogin = $connection->prepare("SELECT LoginPassword_txt, LoginUsername_txt FROM tblLogin WHERE LoginUsername_txt = :name");
    $qryLogin->execute(array(':name' => $login_name));
    $rowLogin = $qryLogin->fetch(PDO::FETCH_OBJ);

// TODO: alle Passwörter hashen
    $stored_pass = $rowLogin->LoginPassword_txt;
//    $stored_pass = password_hash($stored_pass, PASSWORD_DEFAULT);

    if (empty($rowLogin) OR ! password_verify($login_pass, $stored_pass)) {
        disconnect_login($connection);
        return $message .= 'Zugriff verweigert.';
    }

    // Verbindung zu Login Datenbank trennen
    disconnect_login($connection);

    // Fehler Flage zurückgeben
    return $message;
}

// ----------------------------------------------------------------------------
// Funktion: Prüfen ob Loginname Administratorrechte besitzt
// ----------------------------------------------------------------------------
function check_admin($login_id) {

    // Variablen definieren
    $status = 0;

    // mit Login Datenbank verbinden
    $connection = connect_login();

    // Adminstrator Status lesen
    $qryLogin = $connection->prepare("SELECT LoginAdmin_bln FROM tblLogin WHERE Login_ID = :name");
    $qryLogin->execute(array(':name' => $login_id));
    $rowLogin = $qryLogin->fetch(PDO::FETCH_OBJ);
    $status = $rowLogin->LoginAdmin_bln;

    // Verbindung zu Login Datenbank trennen
    disconnect_login($connection);

    // Adminstrator Status zurückgeben
    return $status;
}

?>