<?php

/**
 * POLL - Part of OnlineDelphi project. This source file contains all functions
 * for showing questions, answers and result charts. Furhtermore, all functions
 * for database handling are part of this file, i.e. loading and saving data.
 * @package OnlineDelphi
 * @author Felix Gräßer (IBMT, TU Dresden) <felix.graesser@tu-dresden.de>
 */
// Datenbank-Verbindung einbinden
include('../config/config.inc.php');
require '../includes/class.phpmailer.php';
require '../includes/class.smtp.php';

// ----------------------------------------------------------------------------
// Funktion: Umfrage ausgeben
// ----------------------------------------------------------------------------
function show_poll($connection, $umfrage, $umfrageTyp, $chart) {

// Variablen definieren
    $user = $_SESSION['user'];
    $answersRead = array();
    $answersWrite = array();
    $fehler = array();
    $dontSave = false;
    $umfrageValid = false;

// Umfrage gültig?
    if ($umfrage > 0 && $umfrage < 100) {
        $qryUmfragen = $connection->prepare("SELECT UmfragenText_txt FROM tblUmfragen WHERE Umfragen_ID = :name");
        $qryUmfragen->execute(array(':name' => $umfrage));
        $rowUmfragen = $qryUmfragen->fetch(PDO::FETCH_OBJ);
        if (isset($rowUmfragen->UmfragenText_txt)) {
            $umfrageValid = true;
        }
    }

// Umfrage ausgeben 
    if (is_int($umfrage) AND $umfrageValid) { // Wenn Umfrage gültig, Umfrage ausgeben ...
// Lese Anzahl Blöcke in Umfrage
        $qryBlock = $connection->prepare("SELECT FragenSelBlock_int FROM tblFragenSel WHERE FragenSelUmfragen_fkey = :name AND FragenSelAktiv_bln = '1' ORDER BY FragenSelBlock_int DESC");
        $qryBlock->execute(array(':name' => $umfrage));
        $rowBlock = $qryBlock->fetch(PDO::FETCH_OBJ);
        if (isset($rowBlock->FragenSelBlock_int)) {
            $numBlock = $rowBlock->FragenSelBlock_int;
        } else {
            $numBlock = 0;
        }

// Block Counter initialisieren
        if (!isset($_SESSION['block'])) {
            $_SESSION['block'] = 1;
            $block = $_SESSION['block'];
        } else {
            $block = $_SESSION['block'];
        }

// Button Eingabe verarbeiten
        if (isset($_POST['submit_weiter'])) { // wenn weiter gedrueckt, ...
// ... Daten speichern
            $answersWrite = store_data($connection, $umfrage, $block, $user, $chart);

// ... wenn keine Fehler, Block Counter inkrementieren
            $fehler = get_fehler($connection, $answersWrite, $umfrage, $block);
            if (get_numfehler($fehler) > 0) {
                $block = $block;
            } else {
                $block ++;
            }
        }

        if (isset($_POST['submit_zurueck'])) { // wenn zurück gedrueckt, ...
// ... Daten speichern
            store_data($connection, $umfrage, $block, $user, $chart);

// ... wenn nicht erster Block, Block Counter dekrementieren
            if ($block == 1) {
                $dontSave = true;
                $_POST['submit_close'] = 1;
            } else {
                $block --;
            }
        }

        if (isset($_POST['submit_close'])) { // Wenn schliessen gedrueckt, ...
// ... Block Counter zurücksetzen
            unset($_SESSION['block']);

// ... Mail versenden und Startseite
            if ($umfrageTyp == 1) { // Delphi Umfrage
                if (!$dontSave) {
                    send_mail($connection, $umfrage, $umfrageTyp, $user, $chart);
                }
                header("Location: ./index.php?action=umfragen");
            } else if ($umfrageTyp == 2) { // Live Umfrage
// ... Mail versenden
                if (!$chart && !$dontSave) {
                    send_mail($connection, $umfrage, $umfrageTyp, $user, $chart);
                }
                header("Location: ./index.php?action=live");
            } else { // sonst
                header("Location: ./index.php");
            }
        } else { // Wenn nicht schliessen gedrückt, ...
// ... Daten laden
            if ($umfrageTyp == 1) {
                $answersRead = load_data($connection, $umfrage, $block, $user, $chart);
            }
        }

// Block anzeigen
        echo "<form class=\"questionblock\" action=\"\" id = \"formID\" method=\"post\">\n";

// Titel anzeigen
        $qryUmfragen = $connection->prepare("SELECT UmfragenText_txt FROM tblUmfragen WHERE Umfragen_ID = :name");
        $qryUmfragen->execute(array(':name' => $umfrage));
        $rowUmfragen = $qryUmfragen->fetch(PDO::FETCH_OBJ);
        $str = $rowUmfragen->UmfragenText_txt;
        echo "<h2>$str</h2>";

// Fragen oder Information anzeigen        
        if ($numBlock == 0) { // wenn keine Fragen in Block, ...
// ... Benachrichtigung keine Fragen in Umfrage
            echo "<div class=\"alert alert-success\" role=\"alert\">";
            echo "<strong>Keine Fragen.</strong> Diese Umfrage enthält keine Fragen.";
            echo "</div>";
        } else if ($block > $numBlock) { // wenn Ende der Umfrage erreicht, ...
// ... Benachrichtigung Ende der Umfrage
            echo "<div class=\"alert alert-success\" role=\"alert\">";
            echo "<strong>Ende der Umfragen.</strong> Vielen Dank für die Teilnahme.";
            echo "</div>";
        } else { // wenn Ende der Umfrage noch nicht erreicht, ...
// ... Progressbar anzeigen
            if ($numBlock > 1) {
                $progress = round(($block) / $numBlock * 100);
                echo "<div class = \"progress\">";
                echo "<div class = \"progress-bar\" role = \"progressbar\" aria-valuenow = \"$progress\" aria-valuemin = \"0\" aria-valuemax = \"100\" style = \"width: $progress%;\">";
                echo "$progress%";
                echo "</div>";
                echo "</div >";
            }
// ... Fragen, Antworten und Charts ausgeben
            $_SESSION['answer'] = 0;
            $qryFrage = $connection->prepare("SELECT FragenSel_ID, FragenText_txt, FragenSelTyp_int, FragenSelAktiv_bln, FragenSelChart_bln FROM tblFragenSel INNER JOIN tblFragen ON tblFragenSel.FragenSelFragen_fkey = tblFragen.Fragen_ID WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = '1'");
            $qryFrage->execute(array(':name1' => $umfrage, ':name2' => $block));
            while ($rowFrage = $qryFrage->fetch(PDO::FETCH_OBJ)) { // while Fragen ausgeben
                echo "<div class=\"panel panel-default\">";

// Frage ausgeben
                echo "<div class=\"panel-heading\">";
                show_question($rowFrage->FragenText_txt);
                echo "</div>";

// Chart ausgeben
                if ($rowFrage->FragenSelChart_bln && $chart) {
                    echo "<div class=\"panel-body\">";
                    show_chart($connection, $rowFrage->FragenSel_ID, $rowFrage->FragenSelTyp_int, $umfrageTyp, $user);
                    echo "</div>";
                }

// Antworten ausgeben
                if ($umfrageTyp == 1) { // Delphi Umfrage
                    if ($rowFrage->FragenSelAktiv_bln) {
                        echo "<div class=\"panel-body\">";
                        $numAnswers = show_answers($connection, $rowFrage->FragenSel_ID, $rowFrage->FragenSelTyp_int, $answersRead);
                        echo "</div>";
                    }
                } else if ($umfrageTyp == 2) { // Live Umfrage
                    if ($rowFrage->FragenSelAktiv_bln && !$chart) {
                        echo "<div class=\"panel-body\">";
                        $numAnswers = show_answers($connection, $rowFrage->FragenSel_ID, $rowFrage->FragenSelTyp_int, $answersRead);
                        echo "</div>";
                    } else {
                        $dontSave = true;
                    }
                }
                echo "</div>";

// Warnung ausgeben wenn Fehler
                if (get_numfehler($fehler) > 0) {
                    show_fehler($fehler, $rowFrage->FragenSel_ID, $rowFrage->FragenSelTyp_int);
                }
            } // while ($rowFrage = mysql_fetch_object($qryFrage)) {
        } // if ($numBlock == 0) {
//
// Navigationsbuttons ausgeben
        echo "</br>";
        echo "</br>";
        echo "<table width = 100% border = 0>";
        echo "<tr>";

        if ($dontSave) {
            echo "<td align=\"center\"><input class=\"btn btn-primary btn-lg\" name=\"submit_close\" type=\"submit\" value=\"schliessen\"/></td>";
        } else {
            if ($block > $numBlock) {
                echo "<td align=\"left\"><input class=\"btn btn-primary btn-lg\" name=\"submit_zurueck\" type=\"submit\" value=\"zurück\"/></td>";
                echo "<td align=\"right\"><input class=\"btn btn-primary btn-lg\" name=\"submit_close\" type=\"submit\" value=\"schliessen\"/></td>";
            } else {
                echo "<td align=\"left\"><input class=\"btn btn-primary btn-lg\" name=\"submit_zurueck\" type=\"submit\" value=\"zurück\"/></td>";
                echo "<td align=\"right\"><input class=\"btn btn-primary btn-lg\" name=\"submit_weiter\" type=\"submit\" value=\"weiter\"/></td>";
            }
        }

        echo "</tr>";
        echo "</table>";
        echo "</form>";
        echo "</br>";
        echo "</br>";
    } else { // Wenn Umfrage gültig, Fehlermeldung ausgeben ...
        echo "<p class=\"lead text-muted\">Ungültige Umfrage.</p>";
        unset($_SESSION['block']);
    } // if (is_int($umfrage) AND $umfrageValid) {
// Block Counter aktualisieren
    if (isset($_SESSION['block'])) {
        $_SESSION['block'] = $block;
    }
}

// ----------------------------------------------------------------------------
// Funktion: Login erfordert?
// ----------------------------------------------------------------------------
function login_poll($connection, $poll_id) {
    $umfrageoffen = 0;

// lese Umfrage offen flag aus Datenbank
    $info = $connection->prepare("SELECT UmfragenOffen_bln FROM tblUmfragen WHERE Umfragen_ID = :name");
    $info->execute(array(':name' => $poll_id));
    $row = $info->fetch(PDO::FETCH_OBJ);
    $umfrageoffen = $row->UmfragenOffen_bln;

// Flag zurückgeben
    return $umfrageoffen;
}

// ----------------------------------------------------------------------------
// Funktion: Anzahl Umfragen
// ----------------------------------------------------------------------------
function get_poll($connection) {
// lese Anzahl Umfragen aus Datenbank
    $abfrage = $connection->query("SELECT IDPoll FROM tblPoll ORDER BY IDPoll DESC");
    $total = $abfrage->rowCount();

// Anzahl Umfragen zurückgeben
    return $total;
}

// ----------------------------------------------------------------------------
// Funktion: Frage ausgeben
// ----------------------------------------------------------------------------
function show_question($frageText) {
    echo $frageText;
}

// ----------------------------------------------------------------------------
// Funktion: Chart ausgeben
// ----------------------------------------------------------------------------
function show_chart($connection, $fragenID, $fragenTyp, $umfrageTyp, $user) {

    if ($fragenTyp == 4 || $fragenTyp == 10) { // Fragetyp: Slider oder Numeric
// lade Parameter
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
        $idAntwort = $rowAntwort->AntwortenSel_ID;
        $minAntwort = (double) $rowAntwort->AntwortenText_txt;
        $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
        $maxAntwort = (double) $rowAntwort->AntwortenText_txt;
        $stepAntwort = 1;
        if ($fragenTyp == 4) { // Fragetyp: Slider
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $stepAntwort = (double) $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $einheitAntwort = $rowAntwort->AntwortenText_txt;
        }

// Anzahl Bars definieren
        $numBars = round(($maxAntwort - $minAntwort) / $stepAntwort, 0);
        if ($numBars > 8) {
            $numBars = 8;
            $stepAntwort = ($maxAntwort - $minAntwort) / $numBars;
        }

// Bins defnieren (Histogramm)
        $widths = range($minAntwort, $maxAntwort, $stepAntwort);
        $bins = array();
        foreach ($widths as $key => $val) {
            if (!isset($widths[$key + 1]))
                break;
            $bins[] = round($val, 1) . '-' . round($widths[$key + 1], 1);
        }
        $flotHistogram = array_fill_keys($bins, 0);
//        print_r($bins);
//        print_r($widths);
//        
// sortiere Daten in Bins (Histogramm)
        $histogram = array();
        $userKey = null;
        $numDataTot = 0;
        $qryData = $connection->prepare("SELECT DataWert_dbl, DataUser_fkey FROM tblData WHERE DataAntwortenSel_fkey = :name");
        $qryData->execute(array(':name' => $idAntwort));
        while ($rowData = $qryData->fetch(PDO::FETCH_OBJ)) { // while Data
            for ($key = array_search($minAntwort, $widths); $key < array_search($maxAntwort, $widths); $key++) { // gehe durch Bins, ...
                if ($rowData->DataWert_dbl >= $widths[$key] && $rowData->DataWert_dbl < $widths[$key + 1]) { // ... finde richtigen Bin
                    if (!isset($histogram[$key])) {
                        $histogram[$key] = array();
                    }
                    $flotHistogram[$bins[$key]] ++;
                    $histogram[$key][] = $rowData->DataWert_dbl;
                    $numDataTot ++;

// aktiven Nutzer
                    if ($rowData->DataUser_fkey == $user) {
                        $userKey = $bins[$key];
                        $userData = $rowData->DataWert_dbl;
                    }
                }
            }
            $key = array_search($maxAntwort, $widths) - 1;
            if ($rowData->DataWert_dbl >= $widths[$key] && $rowData->DataWert_dbl <= $widths[$key + 1]) { // ... letzer bin
                if (!isset($histogram[$key])) {
                    $histogram[$key] = array();
                }
                $flotHistogram[$bins[$key]] ++;
                $histogram[$key][] = $rowData->DataWert_dbl;
                $numDataTot ++;

// aktiver Nutzer
                if ($rowData->DataUser_fkey == $user) {
                    $userKey = $bins[$key];
                    $userData = $rowData->DataWert_dbl;
                }
            }
        }
//        print_r($flotHistogram);
//  Ausgabe
        if ($numDataTot > 0) { // Daten vorhanden
            $divBars = 90 / $numBars;
            echo "<div style = \"text-align:center;\">";
            echo "<div style = \"width:5%; float:left; color:#FFFFFF\">";
            echo "placeholder";
            echo "</div>";
            foreach ($flotHistogram as $key => $bar) {

//  Bars zeigen und aktiven Nutzer markieren
                echo "<div style = \"width:$divBars%; float:left;\">";
                $numData = 100 * round($bar / $numDataTot, 2);
                if ($userKey == $key) {
                    echo "<ul class=\"barGraph\"><li style=\"height: $numData%; background: #AA001E;\"></li></ul>";
                } else {
                    echo "<ul class=\"barGraph\"><li style=\"height: $numData%;\"></li></ul>";
                }

// Zahl (Prozent) ausgeben
                echo "<p class=\"lead text-muted\">$numData%</p>";

// Bin Bezeichnung ausgeben
                if ($umfrageTyp == 1 || $umfrageTyp == 2) {
                    echo $key;
                }

// Wert aus letzter Runde ausgeben
                if (($umfrageTyp == 1 || $umfrageTyp == 2) && $userKey == $key) {
                    echo "<p>($userData)</p>";
                }

                echo "</div>";
            }
            echo "<div style = \"width:5%; float:left;\">";
            echo "</div>";

            echo "</div>";
        } else { // keine Daten vorhanden
// Augabe keine Daten vorhanden
            echo "<p class=\"lead text-muted\">Keine Daten vorhanden.</p>";
        }
    } elseif ($fragenTyp == 7) { // Fragetyp: Relevanz
// Anzahl Antwortmöglichkeiten
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        $numProzent = 95 / $numAntwort;

// Variablen anlegen
        $answers = array();
        $answersCnt = array();
        $answersText = array();
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {
            $answersText[$rowAntwort->AntwortenSel_ID] = $rowAntwort->AntwortenText_txt;
            for ($i = 1; $i <= $numAntwort; $i++) {
                $answers[$i][$rowAntwort->AntwortenSel_ID] = 0;
                $answersCnt[$i] = 0;
            }
        }

// Antworten lesen        
        $numDataTot = 0;
        $answersUser = array();
        $qryAntwort = $connection->prepare("SELECT Data_ID, DataUser_fkey, DataAntwortenSel_fkey, DataWert_dbl FROM tblData INNER JOIN tblAntwortenSel ON tblAntwortenSel.AntwortenSel_ID = tblData.DataAntwortenSel_fkey WHERE DataFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1 ORDER BY DataWert_dbl ASC");
        $qryAntwort->execute(array(':name' => $fragenID));
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {
            $answers[$rowAntwort->DataWert_dbl][$rowAntwort->DataAntwortenSel_fkey] += 1;
            $answersCnt[$rowAntwort->DataWert_dbl] += 1;
            $numDataTot += 1;
            if ($rowAntwort->DataUser_fkey == $user) {
                $answersUser[$rowAntwort->DataWert_dbl][$rowAntwort->DataAntwortenSel_fkey] = 1;
            }
        }

//  Ausgabe
        if ($numDataTot > 0) {
            echo "<div style = \"color: white; width:5%; float:left;\">";
            echo "o";
            echo "</div>";
            foreach ($answersText as $i => $data) {
                echo "<div style = \"width:$numProzent%; float:left; text-align:center;\">";
                echo $data;
                echo "</div>";
            }
            foreach ($answers as $i => $data) {
                echo "<div style = \"margin: 10px 0px; width:5%; float:left;\">";
                echo "$i.";
                echo "</div>";
                foreach ($answers[$i] as $j => $data) {
                    if (isset($answers[$i][$j])) {
                        $numData = round(100 * $answers[$i][$j] / $answersCnt[$i], 0);
                    } else {
                        $numData = 0;
                    }
                    if (isset($answersUser[$i][$j])) {
                        echo "<div style = \"width:$numProzent%; float:left; text-align:center; background: #AA001E; border-radius: 4px;\">";
                        echo "<p class=\"lead text-muted\" style= \"color: white; margin: 10px;\">$numData%</p>";
                        echo "</div>";
                    } else {
                        echo "<div style = \"width:$numProzent%; float:left; text-align:center;\">";
                        echo "<p class=\"lead text-muted\" style= \"margin: 10px;\">$numData%</p>";
                        echo "</div>";
                    }
                }
            }
        } else {
// Augabe keine Daten vorhanden
            echo "<p class=\"lead text-muted\">Keine Daten vorhanden.</p>";
        }
    } elseif ($fragenTyp == 8) { // Fragetyp: Priorisierung 100
// Anzahl Antwortmöglichkeiten
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelAktiv_bln, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        $numProzent = 100 / $numAntwort;

// Antworten lesen
        $answers = array();
        $answersCnt = array();
        $answersSelf = array();
        $numDataTot = 0;
        $qryAntwort = $connection->prepare("SELECT Data_ID, DataAntwortenSel_fkey, DataWert_dbl, DataUser_fkey FROM tblData INNER JOIN tblAntwortenSel ON tblAntwortenSel.AntwortenSel_ID = tblData.DataAntwortenSel_fkey WHERE DataFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {
            if (!isset($answers[$rowAntwort->DataAntwortenSel_fkey])) {
                $answers[$rowAntwort->DataAntwortenSel_fkey] = $rowAntwort->DataWert_dbl;
            } else {
                $answers[$rowAntwort->DataAntwortenSel_fkey] += $rowAntwort->DataWert_dbl;
            }
            if (!isset($answersCnt[$rowAntwort->DataAntwortenSel_fkey])) {
                $answersCnt[$rowAntwort->DataAntwortenSel_fkey] = 1;
            } else {
                $answersCnt[$rowAntwort->DataAntwortenSel_fkey] += 1;
            }
// aktiver Nutzer
            if ($rowAntwort->DataUser_fkey == $user) {
                $answersSelf[$rowAntwort->DataAntwortenSel_fkey] = $rowAntwort->DataWert_dbl;
            }
            $numDataTot += 1;
        }
        $answersCntTot = max($answersCnt);

//  Ausgabe
        if ($numDataTot > 0) { // Daten vorhanden
            echo "<div style = \"text-align:center;\">";

            foreach ($answers as $j => $data) {

// Bars zeigen und mittelwert anzeigen
                echo "<div style = \"width:$numProzent%; float:left;\">";
//                $numData = round($answers[$j] / $answersCnt[$j], 0);
                $numData = round($answers[$j] / $answersCntTot, 0);
                echo "<ul class=\"barGraph\"><li style=\"height: $numData%; background: #AA001E;\"></li></ul>";

// Zahl (Durchschnitt) ausgeben
                echo "<p class=\"lead text-muted\">&Oslash: $numData</p>";

// Wert aus letzter Runde ausgeben
                if (isset($answersSelf[$j])) {
                    echo "<p>($answersSelf[$j])</p>";
                } else {
                    echo "<p>(0)</p>";
                }

// Antworttext 
                $qryAntwort = $connection->prepare("SELECT AntwortenText_txt FROM tblAntworten INNER JOIN tblAntwortenSel ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSel_ID = :name AND AntwortenSelAktiv_bln = 1");
                $qryAntwort->execute(array(':name' => $j));
                $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
                echo $rowAntwort->AntwortenText_txt;

                echo "</div>";
            }
            echo "</div>";
        } else { // keine Daten vorhanden
// Augabe keine Daten vorhanden
            echo "<p class=\"lead text-muted\">Keine Daten vorhanden.</p>";
        }
    } elseif ($fragenTyp == 9) { // Fragetyp: Textfeld
// keine Ausgabe
    } else { // Fragetyp: sonstige
// Anzahl Antwortmöglichkeiten
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelAktiv_bln FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        $numProzent = 100 / $numAntwort;

// Anzahl Antworten gesamt
        $qryAntwort = $connection->prepare("SELECT Data_ID FROM tblData INNER JOIN tblAntwortenSel ON tblAntwortenSel.AntwortenSel_ID = tblData.DataAntwortenSel_fkey WHERE DataFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1 AND DataWert_dbl = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numDataTot = $qryAntwort->rowCount();

//  Ausgabe
        if ($numDataTot > 0) { // Daten vorhanden
            echo "<div style = \"text-align:center;\">";
            $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelAktiv_bln, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
            $qryAntwort->execute(array(':name' => $fragenID));

            while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {

//  Bars zeigen und aktiven Nutzer markieren
                echo "<div style = \"width:$numProzent%; float:left;\">";
                $qryData = $connection->prepare("SELECT Data_ID FROM tblData WHERE DataAntwortenSel_fkey = :name AND DataWert_dbl = 1");
                $qryData->execute(array(':name' => $rowAntwort->AntwortenSel_ID));
                $numData = 100 * round($qryData->rowCount() / $numDataTot, 3);

                $qryUser = $connection->prepare("SELECT Data_ID FROM tblData WHERE DataAntwortenSel_fkey = :name1 AND DataUser_fkey = :name2 AND DataWert_dbl = 1");
                $qryUser->execute(array(':name1' => $rowAntwort->AntwortenSel_ID, ':name2' => $user));
                if ($qryUser->rowCount() > 0) {
                    echo "<ul class=\"barGraph\"><li style=\"height: $numData%; background: #AA001E;\"></li></ul>";
                } else {
                    echo "<ul class=\"barGraph\"><li style=\"height: $numData%;\"></li></ul>";
                }

// Zahl (Prozent) ausgeben
                echo "<p class=\"lead text-muted\">$numData%</p>";

// Antworttext ausgeben
                if ($fragenTyp == 1 OR $fragenTyp == 3 OR $fragenTyp == 5 || $umfrageTyp == 2) {
                    echo $rowAntwort->AntwortenText_txt;
                }
                echo "</div>";
            }
            echo "</div>";
        } else { // keine Daten vorhanden
// Augabe keine Daten vorhanden
            echo "<p class=\"lead text-muted\">Keine Daten vorhanden.</p>";
        }
    } // Fragetyp
}

// ----------------------------------------------------------------------------
// Funktion: Antworten ausgeben
// ----------------------------------------------------------------------------
function show_answers($connection, $fragenID, $fragenTyp, $ansRead) {

    if ($fragenTyp == 1) { // Fragetyp: Radio untereinander
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelOther_bln, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        echo "<div id=\"accordion\">";
        echo "<div class=\"panel\">";
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) { // while Antworten ausgeben
//            echo $rowAntwort->IDAntworten;
//            echo $rowFrage->IDFragen;
//            print_r($ansRead);
            $checked = get_checked($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID);
                        
            if ($rowAntwort->AntwortenSelOther_bln == 1) {
                echo "<p><input type=\"radio\" data-toggle=\"collapse\" data-parent=\"#accordion\" data-target=\"#other\" name=\"answers{$fragenID}[0]\" value=\"" . $rowAntwort->AntwortenSel_ID . "\" $checked/>&nbsp;&nbsp;$rowAntwort->AntwortenText_txt</p>";
                
                $value = get_value($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID, NULL);
                if (!is_string($value)) {
                    $value = NULL;
                    echo "<div id=\"other\" class=\"collapse\">";
                } else {
                    echo "<div id=\"other\" class=\"collapse in\">";
                }                
                echo "<div style = \"text-align:center;\">";
                echo "<textarea class = \"form-control form-textarea\" name=\"answers{$fragenID}[1]\" rows = 1  placeholder=\"other\" maxlength = 255>$value</textarea>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<p><input type=\"radio\" data-toggle=\"collapse\" data-parent=\"#accordion\" data-target=\"#all{$rowAntwort->AntwortenSel_ID}\" name=\"answers{$fragenID}[0]\" value=\"" . $rowAntwort->AntwortenSel_ID . "\" $checked/>&nbsp;&nbsp;$rowAntwort->AntwortenText_txt</p>";
                echo "<div id=\"all{$rowAntwort->AntwortenSel_ID}\" class=\"collapse\">";
                echo "</div>";
            }
        } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) {
        echo "</div>";
        echo "</div>";
    }

    if ($fragenTyp == 2) { // Fragetyp: Radio nebeneinander
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        if ($numAntwort > 0) { // Antworten vorhanden
            $numProzent = 100 / $numAntwort;

            echo "<div style = \"text-align:center;\">";
            $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
            $qryAntwort->execute(array(':name' => $fragenID));
            while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) { // while Antworten ausgeben
                echo "<div style = \"width:$numProzent%; margin:10px 0 10px 0; float:left;\">";
                echo "$rowAntwort->AntwortenText_txt<br>";
                $checked = get_checked($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID);
                echo "<input type=\"radio\" name=\"answers{$fragenID}[]\" value=\"" . $rowAntwort->AntwortenSel_ID . "\" $checked/>";
                echo "</div>";
            } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) {
            echo "</div>";
        } // if ($numAntwort > 0) {
    }

    if ($fragenTyp == 3) { // Fragetyp: Dropdown
        echo "<select name=\"answers{$fragenID}[]\" border = \"0\" class = \"table\">";
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        echo "<option disabled selected value></option>";
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) { // while Antworten ausgeben
            $selected = get_selected($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID);
            echo "<option $selected value=\"$rowAntwort->AntwortenSel_ID\">" . $rowAntwort->AntwortenText_txt . "</option>";
        } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) {
        echo "</select>";
    }

    if ($fragenTyp == 4) { // Fragetyp: Slider
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        if ($numAntwort >= 4) {
// Parameter definieren
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $minAntwort = $rowAntwort->AntwortenText_txt;
            $idAntwort = $rowAntwort->AntwortenSel_ID;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $maxAntwort = $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $stepAntwort = $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $einheitAntwort = $rowAntwort->AntwortenText_txt;
//$value = get_value($ansRead, $fragenID, $idAntwort, $minAntwort);
            $value = get_value($ansRead, $fragenID, $idAntwort, NULL);

// Ausgabe
            echo "<div style = \"width:5%; float:left; margin:10px 0 10px 0; line-height: 34px; text-align:center;\">";
            echo "$minAntwort";
            echo "</div>";
            echo "<div style = \"width:90%; float:left;\">";
            echo "<input type=\"range\" id=\"rangeID\" name=\"answers{$fragenID}[$idAntwort]\" class=\"form-control\" min=\"$minAntwort\" max=\"$maxAntwort\" step=\"$stepAntwort\" value=\"$value\" onchange=\"showValue(this.value)\" />";
            echo "</div>";
            echo "<div style = \"width:5%; float:left; margin:10px 0 10px 0;  line-height: 34px; text-align:center;\">";
            echo "$maxAntwort";
            echo "</div>";
            echo "<div style = \"width:100%; float:left; line-height: 34px; text-align:center;\">";
            echo "$einheitAntwort: <span id=\"range\">$value</span>";
            echo "</div>";

// javascript
            echo "<script type=\"text/javascript\">";
            echo "function showValue(newValue)";
            echo "{document.getElementById(\"range\").innerHTML=newValue; }";
            echo "</script>";
        }
    }

    if ($fragenTyp == 5) { // Fragetyp: Checkbox untereinander
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) { // while Antworten ausgeben
            $checked = get_checked($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID);
            echo "<p><input type=\"checkbox\" name=\"answers{$fragenID}[]\" value=\"" . $rowAntwort->AntwortenSel_ID . "\" $checked/>&nbsp;&nbsp; $rowAntwort->AntwortenText_txt </p>";
        } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) {
    }

    if ($fragenTyp == 6) { // Fragetyp: Checkbox nebeneinander
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        if ($numAntwort > 0) {
            $numProzent = 100 / $numAntwort;

            echo "<div style = \"text-align:center;\">";
            $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
            $qryAntwort->execute(array(':name' => $fragenID));
            while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) { // while Antworten ausgeben
                echo "<div style = \"width:$numProzent%; float:left; margin:10px 0 10px 0; \">";
                echo "$rowAntwort->AntwortenText_txt<br>";
                $checked = get_checked($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID);
                echo "<input type=\"checkbox\" name=\"answers{$fragenID}[]\" value=\"" . $rowAntwort->AntwortenSel_ID . "\" $checked/>";
                echo "</div>";
            } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) { 
            echo "</div>";
        }
    }

    if ($fragenTyp == 7) { // Fragetyp: Relevanz
        $answer = array();
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {
            $answer[] = $rowAntwort;
        }
// Ausgabe
        foreach ($answer as $j => $data) {
            echo "<div style = \"width:5%; float:left; margin:10px 0 10px 0;\">";
            $numAntwort = $j + 1;
            echo "$numAntwort.";
            echo "</div>";
            echo "<div style = \"width:95%; float:left; margin:10px 0 10px 0;\">";
            echo "<select name=\"answers{$fragenID}[]\" border = \"0\" class = \"table\">";
            echo "<option disabled selected value></option>";
            foreach ($answer as $i => $data) {
                $rowAntwort = $answer[$i];
                $selected = get_selected($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID, $numAntwort);
                echo "<option $selected value=\"$rowAntwort->AntwortenSel_ID\">" . $rowAntwort->AntwortenText_txt . "</option>";
            } // foreach ($answer as $i => $data) {
            echo "</select>";
            echo "</div>";
        } // foreach ($answer as $j => $data) {
    }

    if ($fragenTyp == 8) { // Fragetyp: Priorisierung 100
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt, AntwortenSelAktiv_bln FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        while ($rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ)) {
            echo "<div>";
            echo "<div style = \"width:70%; display:inline-table; margin: 10px 0; text-align: left;\">";
            echo $rowAntwort->AntwortenText_txt;
            echo "</div>";
            echo "<div style = \"width:30%; display:inline-table; margin:10px 0; text-align: right;\">";
            $value = get_value($ansRead, $fragenID, $rowAntwort->AntwortenSel_ID, NULL);
            echo "<input type = \"number\" min = 0 max = 100 class = \"form-control form-relevanz\" name=\"answers{$fragenID}[$rowAntwort->AntwortenSel_ID]\" value = $value placeholder = \"100\">";
            echo "</div>";
            echo "</div>";
        } // while ($rowAntwort = mysql_fetch_object($qryAntwort)) {
    }

    if ($fragenTyp == 9) { // Fragetyp: Textbox
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        if ($numAntwort >= 3) {
            // Parameter definieren
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $rows = $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $maxlength = $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $placeholder = $rowAntwort->AntwortenText_txt;
        } else {
            $maxlength = 255;
            $rows = 2;
            $placeholder = "";
        }
        $value = get_value($ansRead, $fragenID, 0, NULL);

        echo "<div style = \"text-align:center;\">";
//        echo "<textarea class = \"form-control form-textarea\" name=\"answers{$fragenID}[0]\" rows = \"3\"  maxlength = \"255\">$value</textarea>";
        echo "<textarea class = \"form-control form-textarea\" name=\"answers{$fragenID}[0]\" rows = $rows  placeholder=$placeholder maxlength = $maxlength>$value</textarea>";
        echo "</div>";
    }

    if ($fragenTyp == 10) { // Fragetyp: numeric
        $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenText_txt FROM tblAntwortenSel INNER JOIN tblAntworten ON tblAntwortenSel.AntwortenSelAntworten_fkey = tblAntworten.Antworten_ID WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntwort->execute(array(':name' => $fragenID));
        $numAntwort = $qryAntwort->rowCount();
        if ($numAntwort >= 3) {
            // Parameter definieren
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $min = $rowAntwort->AntwortenText_txt;
            $idAntwort = $rowAntwort->AntwortenSel_ID;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $max = $rowAntwort->AntwortenText_txt;
            $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
            $placeholder = $rowAntwort->AntwortenText_txt;
        } else {
            $min = 0;
            $max = 100;
            $placeholder = 0;
        }
        $value = get_value($ansRead, $fragenID, $idAntwort, NULL);

        echo "<div style = \"text-align:center;\">";
        echo "<input type = \"number\" min = $min max = $max class = \"form-control form-numeric\" name=\"answers{$fragenID}[$idAntwort]\" value = $value placeholder = $placeholder>";
        echo "</div>";
    }
}

// ----------------------------------------------------------------------------
// Funktion: Daten aus Datenbank laden und in Array speichern
// ----------------------------------------------------------------------------
function load_data($connection, $umfrage, $block, $user, $chart) {
// Array anlegen
    $answersRead = array();

// Daten aus Datenbank laden und in Array speichern
    $qryFragen = $connection->prepare("SELECT FragenSel_ID, FragenSelTyp_int FROM tblFragenSel WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = 1");
    $qryFragen->execute(array(':name1' => $umfrage, ':name2' => $block));
    while ($rowFragen = $qryFragen->fetch(PDO::FETCH_OBJ)) {
        $answersRead[$rowFragen->FragenSel_ID] = read_data($connection, $rowFragen->FragenSel_ID, $user, $rowFragen->FragenSelTyp_int, $chart);
    }
//    echo "laden:";
//    print_r($answersRead);
//    echo "\n";
// Array zurückgeben
    return $answersRead;
}

// ----------------------------------------------------------------------------
// Funktion: Daten aus Datenbank laden
// ----------------------------------------------------------------------------
function read_data($connection, $frage, $user, $typ, $chart) {
// Array anlegen
    $tmpData = array();

// Daten laden
    if ($typ != 4 && $typ != 9) { // Slider
        $qryAntworten = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelOther_bln FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
        $qryAntworten->execute(array(':name' => $frage));
        while ($rowAntworten = $qryAntworten->fetch(PDO::FETCH_OBJ)) {
            $tmpData[$rowAntworten->AntwortenSel_ID] = 0;
        }
    } else if ($typ == 9) { // Textbox
        $tmpData[0] = '';
    }

    if (!$chart) { // Daten aus Runde 1
        $qryData = $connection->prepare("SELECT DataAntwortenSel_fkey, DataWert_dbl, DataWert_txt FROM tblData WHERE DataUser_fkey = :name1 AND DataFragenSel_fkey = :name2");
        $qryData->execute(array(':name1' => $user, ':name2' => $frage));
        while ($rowData = $qryData->fetch(PDO::FETCH_OBJ)) {
            if ($typ == 9) { // Textbox
                $tmpData[0] = $rowData->DataWert_txt;
            } else if ($typ == 1) {
                $qryAntworten = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelOther_bln FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name1 AND AntwortenSel_ID = :name2");
                $qryAntworten->execute(array(':name1' => $frage, ':name2' => $rowData->DataAntwortenSel_fkey));
                $rowAntworten = $qryAntworten->fetch(PDO::FETCH_OBJ);
                if ($rowAntworten->AntwortenSelOther_bln == 1) {
                    $tmpData[$rowData->DataAntwortenSel_fkey] = $rowData->DataWert_txt;
                } else {
                    $tmpData[$rowData->DataAntwortenSel_fkey] = intval($rowData->DataWert_dbl);
                }
            } else if ($typ == 4) { // slider
                $tmpData[$rowData->DataAntwortenSel_fkey] = doubleval($rowData->DataWert_dbl);
            } else { // others
                $tmpData[$rowData->DataAntwortenSel_fkey] = intval($rowData->DataWert_dbl);
            }
        }
    } else { // Daten aus Runde 2
        $qryData = $connection->prepare("SELECT DataAntwortenSel0_fkey, DataWert0_dbl, DataWert0_txt FROM tblData WHERE DataUser_fkey = :name1 AND DataFragenSel_fkey = :name2");
        $qryData->execute(array(':name1' => $user, ':name2' => $frage));
        while ($rowData = $qryData->fetch(PDO::FETCH_OBJ) AND ($rowData->DataAntwortenSel0_fkey != NULL OR $rowData->DataWert0_txt != NULL)) {
            if ($typ == 9) { // Textbox
                $tmpData[0] = $rowData->DataWert0_txt;
            } else if ($typ == 1) {
                $qryAntworten = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelOther_bln FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name1 AND AntwortenSel_ID = :name2");
                $qryAntworten->execute(array(':name1' => $frage, ':name2' => $rowData->DataAntwortenSel0_fkey));
                $rowAntworten = $qryAntworten->fetch(PDO::FETCH_OBJ);
                if ($rowAntworten->AntwortenSelOther_bln == 1) {
                    $tmpData[$rowData->DataAntwortenSel0_fkey] = $rowData->DataWert0_txt;
                } else {
                    $tmpData[$rowData->DataAntwortenSel0_fkey] = intval($rowData->DataWert0_dbl);
                }
            } else if ($typ == 4) { // slider
                $tmpData[$rowData->DataAntwortenSel0_fkey] = doubleval($rowData->DataWert0_dbl);                
            } else { // others
                $tmpData[$rowData->DataAntwortenSel0_fkey] = intval($rowData->DataWert0_dbl);
            }
        }
    }

//    print_r($tmpData);
// Daten zurückgeben
    return $tmpData;
}

// ----------------------------------------------------------------------------
// Funktion: Daten zum speichern in Datenbank in Array speichern
// ----------------------------------------------------------------------------
function store_data($connection, $umfrage, $block, $user, $chart) {
// Variablen anlegen
    $writeData = array();
    $writeDataType = array();

// Gehe durch Fragen in Block (Formular) ...
    $qryFragen = $connection->prepare("SELECT FragenSel_ID, FragenSelTyp_int FROM tblFragenSel INNER JOIN tblFragen ON tblFragenSel.FragenSelFragen_fkey = tblFragen.Fragen_ID WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = 1");
    $qryFragen->execute(array(':name1' => $umfrage, ':name2' => $block));
//    echo "speichern:";
    while ($rowFragen = $qryFragen->fetch(PDO::FETCH_OBJ)) {
        $typ = $rowFragen->FragenSelTyp_int;
        $tmpData = array();

// Daten aus Formular lesen
        if ($typ == 1) { // Radio untereinander
            if (isset($_POST["answers$rowFragen->FragenSel_ID"][0])) {
                $data = $_POST["answers$rowFragen->FragenSel_ID"][0];
                $qryAntwort = $connection->prepare("SELECT AntwortenSel_ID, AntwortenSelOther_bln FROM tblAntwortenSel WHERE AntwortenSel_ID = :name");
                $qryAntwort->execute(array(':name' => $data));
                $rowAntwort = $qryAntwort->fetch(PDO::FETCH_OBJ);
                if (isset($rowAntwort->AntwortenSel_ID) AND $rowAntwort->AntwortenSelOther_bln == 1) {
                    $tmpData[$data] = $_POST["answers$rowFragen->FragenSel_ID"][1];
                } else {
                    $tmpData[$data] = 1;
                }
            }
        } elseif ($typ == 2) { // radio nebeneinander
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $data) {
                    $tmpData[$data] = 1;
                }
            }
        } elseif ($typ == 3) { // radio nebeneinander
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $data) {
                    $tmpData[$data] = 1;
                }
            }
        } elseif ($typ == 4) { // Slider
//            echo "a";
//            print_r($_POST["answers$rowFragen->FragenSel_ID"]);
//            echo "b";
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $i => $data) {
                    $tmpData[$i] = $data;
                }
            }
        } elseif ($typ == 5 || $typ == 6) { // Checkboxen nebeneinander oder untereinander
            $qryAntworten = $connection->prepare("SELECT AntwortenSel_ID FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name AND AntwortenSelAktiv_bln = 1");
            $qryAntworten->execute(array(':name' => $rowFragen->FragenSel_ID));
            while ($rowAntworten = $qryAntworten->fetch(PDO::FETCH_OBJ)) {
                $tmpData[$rowAntworten->AntwortenSel_ID] = 0;
            }
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $data) {
                    $tmpData[$data] = 1;
                }
            }
        } elseif ($typ == 7) { // Relevanz
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $i => $data) {
                    $tmpData[$data] = $i + 1;
                }
            }
        } elseif ($typ == 8) { // Priorisierung 100
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $i => $data) {
                    $data = intval($data);
                    if ($data >= 0 && $data <= 100) {
                        $tmpData[$i] = intval($data);
                    } else {
                        $tmpData[$i] = 0;
                    }
                }
            }
        } elseif ($typ == 9) { // Textfeld
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $i => $data) {
                    $tmpData[$i] = $data;
                }
            }
        } elseif ($typ == 10) { // Numerisch
            if (isset($_POST["answers$rowFragen->FragenSel_ID"])) {
                foreach ($_POST["answers$rowFragen->FragenSel_ID"] as $i => $data) {
                    $tmpData[$i] = intval($data);
                }
            }
        }

        $writeData[$rowFragen->FragenSel_ID] = $tmpData;
        $writeDataType[$rowFragen->FragenSel_ID] = $rowFragen->FragenSelTyp_int;
    } // while ($rowFragen = mysql_fetch_object($qryFragen)) {
// Daten speichern
    foreach ($writeData as $i => $data) {
        insert_data($connection, $data, $user, $writeDataType[$i], $i, $chart);
    }

// geschriebene Daten zurückgeben
//    print_r($writeData);
    return $writeData;
}

// ----------------------------------------------------------------------------
// Funktion: Daten in Datenbank speichern
// ----------------------------------------------------------------------------
function insert_data($connection, $dataArray, $user, $typ, $frage, $chart) {

//    echo "insert";
// gehe durch Datenarray
    foreach ($dataArray as $antwort => $data) {
        if (isset($antwort)) { // Antwort vorhanden
// Daten in Datenbank speichern
            $value = $data;
            if ($typ == 1 OR $typ == 2 OR $typ == 3 OR $typ == 4 OR $typ == 9 OR $typ == 10) { // Single Choice: Radio, Dropdown, Slider, Numerisch
                $qryAntworten = $connection->prepare("SELECT Data_ID FROM tblData WHERE DataUser_fkey = :name1 AND DataFragenSel_fkey = :name2");
                $qryAntworten->execute(array(':name1' => $user, ':name2' => $frage));
            } else { // Multiple Choice: Checkbox, Relevanz, Priorität 100
                $qryAntworten = $connection->prepare("SELECT Data_ID FROM tblData WHERE DataUser_fkey = :name1 AND DataFragenSel_fkey = :name2 AND DataAntwortenSel_fkey = :name3");
                $qryAntworten->execute(array(':name1' => $user, ':name2' => $frage, ':name3' => $antwort));
            }

// Daten vorhanden Flag setzen
            $existent = $qryAntworten->rowCount();

            if (!$chart) { // Runde 1
                if ($existent) { // bereits Daten vorhanden: Update
                    if ($typ == 1) {
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel_fkey= :name1, DataWert_dbl= :name2, DataWert_txt= :name3 WHERE DataFragenSel_fkey= :name4 AND DataUser_fkey= :name5");
                        if ($value == 1) {
                            $value = NULL;
                        }
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => 1, ':name3' => $value, ':name4' => $frage, ':name5' => $user));
                    } else if ($typ == 9) { // Textbox
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataWert_txt= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4");
                        $tmpStmt->execute(array(':name2' => $value, ':name3' => $frage, ':name4' => $user));
                    } else if ($typ == 2 OR $typ == 3 OR $typ == 4 OR $typ == 10) { // Single Choice: Radio, Dropdown, Slider
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel_fkey= :name1, DataWert_dbl= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4");
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => $value, ':name3' => $frage, ':name4' => $user));
                    } else { // Multiple Choice: Checkbox, Relevanz, Priorität 100
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel_fkey= :name1, DataWert_dbl= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4 AND DataAntwortenSel_fkey = :name5");
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => $value, ':name3' => $frage, ':name4' => $user, ':name5' => $antwort));
                    }
                } else { // noch keine Daten vorhanden: Insert
                    if ($typ == 1) { // radio
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataAntwortenSel_fkey, DataUser_fkey, DataWert_dbl, DataWert_txt) VALUES (:name1, :name2, :name3, :name4, :name5)");
                        if ($value == 1) {
                            $value = NULL;
                        }
                        $tmpStmt->execute(array(':name1' => $frage, ':name2' => $antwort, ':name3' => $user, ':name4' => 1, ':name5' => $value));
                    } else if ($typ == 9) { // Textbox
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataUser_fkey, DataWert_txt) VALUES (:name1, :name3, :name4)");
                        $tmpStmt->execute(array(':name1' => $frage, ':name3' => $user, ':name4' => $value));
                    } else { // others
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataAntwortenSel_fkey, DataUser_fkey, DataWert_dbl) VALUES (:name1, :name2, :name3, :name4)");
                        $tmpStmt->execute(array(':name1' => $frage, ':name2' => $antwort, ':name3' => $user, ':name4' => $value));
                    }
                }
            } else { // Runde 2
                if ($existent) { // bereits Daten vorhanden: Update
                    if ($typ == 1) { // radio buttons
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel0_fkey= :name1, DataWert0_dbl= :name2, DataWert0_txt= :name3 WHERE DataFragenSel_fkey= :name4 AND DataUser_fkey= :name5");
                        if ($value == 1) {
                            $value = NULL;
                        }
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => 1, ':name3' => $value, ':name4' => $frage, ':name5' => $user));
                    } else if ($typ == 9) { // Textbox
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataWert0_txt= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4");
                        $tmpStmt->execute(array(':name2' => $value, ':name3' => $frage, ':name4' => $user));
                    } else if ($typ == 2 OR $typ == 3 OR $typ == 4 OR $typ == 10) { // Single Choice: Radio, Dropdown, Slider
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel0_fkey= :name1, DataWert0_dbl= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4");
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => $value, ':name3' => $frage, ':name4' => $user));
                    } else { // Multiple Choice: Checkbox, Relevanz, Priorität 100
                        $tmpStmt = $connection->prepare("UPDATE tblData SET DataAntwortenSel0_fkey= :name1, DataWert0_dbl= :name2 WHERE DataFragenSel_fkey= :name3 AND DataUser_fkey= :name4 AND DataAntwortenSel_fkey = :name5");
                        $tmpStmt->execute(array(':name1' => $antwort, ':name2' => $value, ':name3' => $frage, ':name4' => $user, ':name5' => $antwort));
                    }
                } else { // noch keine Daten vorhanden: Insert
                    if ($typ == 1) {
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataUser_fkey, DataWert0_dbl, DataWert0_txt) VALUES (:name1, :name2, :name3, :name4)");
                        if ($value == 1) {
                            $value = NULL;
                        }
                        $tmpStmt->execute(array(':name1' => $frage, ':name2' => $user, ':name3' => 1, ':name4' => $value, ':name5' => $user));
                    } else if ($typ == 9) { // Textbox
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataUser_fkey, DataWert0_txt) VALUES (:name1, :name3, :name4)");
                        $tmpStmt->execute(array(':name1' => $frage, ':name3' => $user, ':name4' => $value));
                    } else { // others
                        $tmpStmt = $connection->prepare("INSERT INTO tblData (DataFragenSel_fkey, DataAntwortenSel0_fkey, DataUser_fkey, DataWert0_dbl) VALUES (:name1, :name2, :name3, :name4)");
                        $tmpStmt->execute(array(':name1' => $frage, ':name2' => $antwort, ':name3' => $user, ':name4' => $value));
                    }
                } // if ($existent) {
            } // if (!$chart) {
        } // if (isset($antwort)) {
    } // foreach ($dataArray as $antwort => $data) {
}

// ----------------------------------------------------------------------------
// Funktion: Daten aus Datenbank löschen
// ----------------------------------------------------------------------------
function delete_data($connection, $umfrage, $block, $user) {

// Antworten löschen
    $qryAntworten = $connection->prepare("SELECT FragenSel_ID, FragenSelTyp_int FROM tblFragenSel INNER JOIN tblFragen ON tblFragenSel.FragenSelFragen_fkey = tblFragen.Fragen_ID WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = 1");
    $qryAntworten->execute(array(':name1' => $umfrage, ':name2' => $block));
    $qryAnswersRead = $connection->prepare("SELECT tblAntworten.IDAntworten FROM tblAntworten INNER JOIN tblFragen ON tblAntworten.ingFrage = tblFragen.IDFragen WHERE tblFragen.ingBlock = :name1 AND tblFragen.ingPoll = :name2");
    $qryAnswersRead->execute(array(':name1' => $block, ':name2' => $umfrage));
    while ($rowAnswersRead = $qryAnswersRead->fetch(PDO::FETCH_OBJ)) {
        $tmpStmt = $connection->prepare("DELETE FROM tblData WHERE DataUser_fkey = :name1 AND DataAntwortenSel_fkey = :name2");
        $tmpStmt->execute(array(':name1' => $user, ':name2' => $rowAntworten->IDAntworten));
    }
//    echo "löschen";
}

// ----------------------------------------------------------------------------
// Funktion: Antwort in Formular aktiv (checked)?
// ----------------------------------------------------------------------------
function get_checked($antworten, $frage, $antwort) {

    $checked = "";
//    echo "Antworten: ";
//    print_r($antworten);
    if (!empty($antworten)) {
        $antwortenSel = $antworten[$frage];
        if ($antwortenSel[$antwort] != 0 OR is_string($antwortenSel[$antwort])) {
            $checked = "checked";
        }
    }

// checked Flag zurückgeben
    return $checked;
}

// ----------------------------------------------------------------------------
// Funktion: Antwort in Formular aktiv (selected)?
// ----------------------------------------------------------------------------
function get_selected($antworten, $frage, $antwort, $numAntwort) {

    $selected = "";
//    echo "ansewrs:";
//    print_r($answers);
    if (empty($numAntwort)) {
        $numAntwort = 1;
    }

    if (!empty($antworten)) {
        $antwortenSel = $antworten[$frage];
        if ($antwortenSel[$antwort] == $numAntwort) {
            $selected = "selected";
        }
    }

// selected Flag zurückgeben    
    return $selected;
}

// ----------------------------------------------------------------------------
// Funktion: Antwort in Formular aktiv (Wert)?
// ----------------------------------------------------------------------------
function get_value($antworten, $frage, $antwort, $default) {

    $value = $default;
//    echo "answers:";
//    print_r($antworten);
//    echo $antwort;
//    echo $frage;

    if (!empty($antworten)) {
        $antwortenSel = $antworten[$frage];
        foreach ($antwortenSel as $i => $val) {
            if ($i == $antwort) {
                if ($val != 0 OR is_string($val)) {
                    $value = $val;
                }
            }
        }
    }

// Wert zurückgeben
    return $value;
}

// ----------------------------------------------------------------------------
// Funktion: Array mit fehlerhaften oder fehlenden Eingabe in Formular. Kein Fehler = 0.
// ----------------------------------------------------------------------------
function get_fehler($connection, $answersWrite, $umfrage, $block) {

// Array anlegen
    $fehlerEintraege = array();

// Anzahl Einträge lesen
//    print_r($answersWrite);
    $numEintraege = get_numeintraege($connection, $answersWrite, $umfrage, $block);
    $qryFragen = $connection->prepare("SELECT FragenSel_ID, FragenSelTyp_int, FragenSelObligatorisch_bln FROM tblFragenSel INNER JOIN tblFragen ON tblFragenSel.FragenSelFragen_fkey = tblFragen.Fragen_ID WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = 1");
    $qryFragen->execute(array(':name1' => $umfrage, ':name2' => $block));
    while ($rowFragen = $qryFragen->fetch(PDO::FETCH_OBJ)) {
        $fehlerEintraege[$rowFragen->FragenSel_ID] = 0;

        if ($rowFragen->FragenSelObligatorisch_bln) {// Eintrag obligatorisch? TODO: Slider, Relevanz, Priorisierung 100
            if ($rowFragen->FragenSelTyp_int == 7) { // Fragetyp: Relevanz
                $qryAntworten = $connection->prepare("SELECT AntwortenSel_ID FROM tblAntwortenSel WHERE AntwortenSelFragenSel_fkey = :name1 AND AntwortenSelAktiv_bln = 1");
                $qryAntworten->execute(array(':name1' => $rowFragen->FragenSel_ID));
                $n = $qryAntworten->rowCount();
                if ($numEintraege[$rowFragen->FragenSel_ID] != ($n * $n + $n) / 2) { // kleiner Gauss
                    $fehlerEintraege[$rowFragen->FragenSel_ID] = 1;
                }
            } elseif ($rowFragen->FragenSelTyp_int == 8) { // Fragetyp: Priorisierung 100
                if ($numEintraege[$rowFragen->FragenSel_ID] != 100) {
                    $fehlerEintraege[$rowFragen->FragenSel_ID] = 100 - $numEintraege[$rowFragen->FragenSel_ID];
                }
            } elseif ($rowFragen->FragenSelTyp_int == 4) {// Fragetyp: Slider
                if ($numEintraege[$rowFragen->FragenSel_ID] < 0) {
                    $fehlerEintraege[$rowFragen->FragenSel_ID] = 1;
                }
            } else { // Fragetyp: sonstige
                if ($numEintraege[$rowFragen->FragenSel_ID] < 1) {
                    $fehlerEintraege[$rowFragen->FragenSel_ID] = 1;
                }
            }
        } // if ($obligatorisch) {
    } // while ($rowFragen = mysql_fetch_object($qryFragen)) {
//    echo "fehler";
//    print_r($fehlerEintraege);
// Array mit fehlerhaften Einträgen zurückgeben
    return $fehlerEintraege;
}

// ----------------------------------------------------------------------------
// Funktion: Anzahl Fehler zurückgeben
// ----------------------------------------------------------------------------
function get_numfehler($fehler) {

    $numFehler = 0;
    foreach ($fehler as $data) {
        if ($data != 0) {
            $numFehler ++;
        }
    }
//    echo $numFehler;
// Anzahl Fehler zurückgeben
    return $numFehler;
}

// ----------------------------------------------------------------------------
// Funktion: Fehler anzeigen
// ----------------------------------------------------------------------------
function show_fehler($fehler, $frage, $typ) {
//    print_r($fehler);
    if (!empty($fehler) && $fehler[$frage] != 0) {
        echo "<div class=\"alert alert-warning\" role=\"alert\">";
        echo "<strong>Fehlerhafte Eingabe.</strong> ";
        if ($typ == 7) { // Fragetyp: Relevanz
            echo "Bitte ordnen sie die Antwortmöglichkeiten nach Relevanz. Jede Antwortmöglichkeit darf nur einmal gewählt werden.";
        } elseif ($typ == 8) { // Fragetyp: Priorisierung 100
            if ($fehler[$frage] > 0) {
                $data = $fehler[$frage];
                echo "Bitte verteilen sie 100 Punkte auf die gegebenen Antwortmöglichkeiten. Es sind noch $data Punkte zu vergeben.";
            } else {
                $data = (-1) * $fehler[$frage];
                echo "Bitte verteilen sie 100 Punkte auf die gegebenen Antwortmöglichkeiten. Es wurden $data Punkte zuviel vergeben.";
            }
        } else { // Fragetyp: Sonstige
            echo "Bitte Frage beantworten.";
        }
        echo "</div>";
    }
}

// ----------------------------------------------------------------------------
// Funktion: Anzahl Einträge
// ----------------------------------------------------------------------------
function get_numeintraege($connection, $answersWrite, $umfrage, $block) {

    $numEintraege = array();

    $qryFragen = $connection->prepare("SELECT FragenSel_ID, FragenSelTyp_int FROM tblFragenSel INNER JOIN tblFragen ON tblFragenSel.FragenSelFragen_fkey = tblFragen.Fragen_ID WHERE FragenSelUmfragen_fkey = :name1 AND FragenSelBlock_int = :name2 AND FragenSelAktiv_bln = 1");
    $qryFragen->execute(array(':name1' => $umfrage, ':name2' => $block));
    while ($rowFragen = $qryFragen->fetch(PDO::FETCH_OBJ)) {
        $numEintraege[$rowFragen->FragenSel_ID] = 0;
        foreach ($answersWrite[$rowFragen->FragenSel_ID] as $i => $data) {
            if (is_string($data)) {
                $numEintraege[$rowFragen->FragenSel_ID] = strlen($data);
            } else {
                $numEintraege[$rowFragen->FragenSel_ID] += $data;
            }
        }
    }
//    echo "missing";
//    print_r($numEintraege);
//    print_r($answersWrite);
// Anzahl Einträge zurückgeben
    return $numEintraege;
}

// ----------------------------------------------------------------------------
// Funktion: Umfragen in Array laden
// ----------------------------------------------------------------------------
function load_umfragen($connection, $umfrageTyp) {

// Session Variablen initialisieren
    unset($_SESSION['block']);
    $_SESSION['start'] = 1;

// Array anlegen
    $umfragen = array();

// Umfragen in Array laden
    $qryUmfragen = $connection->query("SELECT Umfragen_ID, UmfragenText_txt, UmfragenAktiv_bln, UmfragenChart_bln FROM tblUmfragen ORDER BY Umfragen_ID ASC");
    while ($rowUmfragen = $qryUmfragen->fetch(PDO::FETCH_OBJ)) {
        if ($umfrageTyp == 1) { // Delphi Umfrage
            $umfragen[$rowUmfragen->Umfragen_ID][0] = $rowUmfragen->UmfragenText_txt;
            $umfragen[$rowUmfragen->Umfragen_ID][1] = $rowUmfragen->UmfragenChart_bln;
            $umfragen[$rowUmfragen->Umfragen_ID][2] = $rowUmfragen->UmfragenAktiv_bln;
        } elseif ($umfrageTyp == 2) { // Live Umfrage
            $umfragen[$rowUmfragen->Umfragen_ID][0] = $rowUmfragen->UmfragenText_txt;
            $umfragen[$rowUmfragen->Umfragen_ID][1] = $rowUmfragen->UmfragenChart_bln;
            $umfragen[$rowUmfragen->Umfragen_ID][2] = $rowUmfragen->UmfragenAktiv_bln;
        } //  if ($umfrageTyp == 1) {
    } // while ($rowUmfragen = mysql_fetch_object($qryUmfragen)) {
// Array mit Umfragen zurückgeben
    return $umfragen;
}

// ----------------------------------------------------------------------------
// Funktion: Neuen Benutzer zu Datenbank hinzufügen
// ----------------------------------------------------------------------------
function set_user($connection, $loginUserID, $userIP) {

// Neuen Benutzer hinzufügen
    $qryNewUser = $connection->prepare("INSERT INTO tblUser (UserLogin_int, UserDate_dat, UserIP_txt) VALUES (:name1, now(), :name2)");
    $qryNewUser->execute(array(':name1' => $loginUserID, ':name2' => $userIP));
    $userID = (int) $connection->lastInsertId();

// Benutzer ID zurückgeben
    return $userID;
}

// ----------------------------------------------------------------------------
// Funktion: Benutzer ID aus Datenbank laden
// ----------------------------------------------------------------------------
function get_user($connection, $loginUserID) {

    $userID = 0;

// Benutzer ID aus Datenbank laden
    $qryUser = $connection->prepare("SELECT User_ID, UserLogin_int, UserIP_txt FROM tblUser WHERE UserLogin_int = :name1");
    $qryUser->execute(array(':name1' => $loginUserID));
    while ($rowUser = $qryUser->fetch(PDO::FETCH_OBJ)) {
        $userID = $rowUser->User_ID;
    }

// Benutzer ID zurückgeben
    return $userID;
}

// ----------------------------------------------------------------------------
// Funktion: Benutzer laden oder neu anlegen?
// ----------------------------------------------------------------------------
function add_user($connection, $loginUserID) {

    if ($_SESSION['start'] == 1) { // Start Flag?
        $_SESSION['start'] = 0; // Start Flag zurücksetzen
        if ($loginUserID == 0) { // offene Umfrage
            $_SESSION['user'] = set_user($connection, $loginUserID, $_SERVER['REMOTE_ADDR']);
        } else { // geschlossene Umfrage
            $userID = get_user($connection, $loginUserID);
            if ($userID > 0) { // Benutzer bei keiner Umfrage teilgenommen
                $_SESSION['user'] = $userID;
            } else { // Benutzer bereits bei einer Umfrage teilgenommen
                $_SESSION['user'] = set_user($connection, $loginUserID, $_SERVER['REMOTE_ADDR']);
            } // if ($userID > 0) {
        } // if ($loginUserID == 0) {
    } // if ($_SESSION['start'] == 1) {
}

// ----------------------------------------------------------------------------
// Funktion: Umfrage Runde in Datenbank speichern
// ----------------------------------------------------------------------------
function set_status($connection, $umfrage, $status) {

// update runde
    $tmpStmt = $connection->prepare("UPDATE tblUmfragen SET UmfragenChart_bln = :name1 WHERE Umfragen_ID = :name2");
    $tmpStmt->execute(array(':name1' => $status, ':name2' => $umfrage));
}

// ----------------------------------------------------------------------------
// Funktion: Umfrage Status Flag (aktiv?) aus Datenbank laden
// ----------------------------------------------------------------------------
function get_active($connection, $umfrage) {

// lese Status Flag
    $qryStatus = $connection->prepare("SELECT UmfragenAktiv_bln FROM tblUmfragen WHERE Umfragen_ID = :name1");
    $qryStatus->execute(array(':name1' => $umfrage));
    $rowStatus = $qryStatus->fetch(PDO::FETCH_OBJ);

// Status Flag zurückgeben
    return $rowStatus->UmfragenAktiv_bln;
}

// ----------------------------------------------------------------------------
// Funktion: Umfrage Status Flag (aktiv?) in Datenbank speichern 
// ----------------------------------------------------------------------------
function set_active($connection, $umfrage, $status) {

// speichere Status Flag
    $tmpStmt = $connection->prepare("UPDATE tblUmfragen SET UmfragenAktiv_bln = :name1 WHERE Umfragen_ID = :name2");
    $tmpStmt->execute(array(':name1' => $status, ':name2' => $umfrage));
}

// ----------------------------------------------------------------------------
// Funktion: Umfrage Runde aus Datenbank laden 
// ----------------------------------------------------------------------------
function get_status($connection, $umfrage) {

// lese Runde
    $qryStatus = $connection->prepare("SELECT UmfragenChart_bln FROM tblUmfragen WHERE Umfragen_ID = $umfrage");
    $qryStatus->execute(array(':name1' => $umfrage));
    $rowStatus = $qryStatus->fetch(PDO::FETCH_OBJ);

// Status Flag zurückgeben
    return $rowStatus->UmfragenChart_bln;
}

// ----------------------------------------------------------------------------
// Funktion: Admin Funktion: Wähle Status Flag und Runde
// ----------------------------------------------------------------------------
function select_status($connection, $umfrage, $umfrageTyp, $status) {

    $newStatus = $status;

// Status speichern gedrückt
    if (isset($_POST["submit_save$umfrage"])) {

// Runde wählen
        if (isset($_POST["status$umfrage"])) {
            set_status($connection, $umfrage, $_POST["status$umfrage"]);
            $newStatus = $_POST["status$umfrage"];
        }

// Umfrage aktivieren / deaktivieren
        if (isset($_POST["active$umfrage"])) {
            set_active($connection, $umfrage, $_POST["active$umfrage"]);
        } else {
            set_active($connection, $umfrage, 0);
        }
    }

// Text auswählen
    if ($umfrageTyp == 1) { // Delphi Umfrage
        $text0 = "Zeige Runde 1";
        $text1 = "Zeige Runde 2";
    } elseif ($umfrageTyp == 2) { // Live Umfrage
        $text0 = "Zeige Fragen";
        $text1 = "Zeige Ergebnis";
    }

// aktive Flag lesen: Runde
    if (get_status($connection, $umfrage) == 0) {
        $status0 = "checked";
        $status1 = "";
    } else {
        $status0 = "";
        $status1 = "checked";
    }

// aktive Flag lesen: status
    if (get_active($connection, $umfrage) == 0) {
        $active = "";
    } else {
        $active = "checked";
    }

// Ausgabe
    echo "</br>";
    echo "<p><input type=\"checkbox\" name=\"active$umfrage\" value=1 $active> Umfrage aktiv</p>";
    echo "<p><input type=\"radio\" name=\"status$umfrage\" value=0 $status0> $text0</p>";
    echo "<p><input type=\"radio\" name=\"status$umfrage\" value=1 $status1> $text1</p>";
    echo "<input type=\"submit\" name=\"submit_save$umfrage\" value=\"Speichern\" />";
    echo "</br>";

// aktuellen Status zurückgeben
    return $newStatus;
}

// ----------------------------------------------------------------------------
// Funktion: Mail versenden
// ----------------------------------------------------------------------------
function send_mail($connection, $umfrage, $umfrageTyp, $user, $chart) {

// Benutzer ID aus Datenbank laden
    $qryUser = $connection->prepare("SELECT User_ID, UserLogin_int, UserIP_txt, UserDate_dat FROM tblUser WHERE User_ID = :name1");
    $qryUser->execute(array(':name1' => $user));
    $rowUser = $qryUser->fetch(PDO::FETCH_OBJ);

// Umfrage Text aus Datenbank laden
    $qryUmfragen = $connection->prepare("SELECT UmfragenText_txt FROM tblUmfragen WHERE Umfragen_ID = :name1");
    $qryUmfragen->execute(array(':name1' => $umfrage));
    $rowUmfragen = $qryUmfragen->fetch(PDO::FETCH_OBJ);

// Umfragetyp aus Datenbank laden
    if ($umfrageTyp == 1) { // Delphi Umfrage
        $umfrageTypTxt = "Delphi Umfrage";
        $runde = $chart + 1;
        $rundeText = " Runde $runde";
    } elseif ($umfrageTyp == 2) { // Live Umfrage
        $umfrageTypTxt = "Live Umfrage";
    }

// Variablen speichern
    $umfrageText = $rowUmfragen->UmfragenText_txt;
    $userIP = $rowUser->UserIP_txt;
    $userName = $_SESSION['loginUserName'];
    $userID = $_SESSION['loginUserID'];

// Zeitstempel
    $timestamp = date('Y-m-d H:i:s');

// Text Formatieren
    $text = "<h1>$umfrageTypTxt: $umfrage</h1><h2>$umfrageText</h2><p>Benutzer: $userName</br>Benutzer ID: $userID</br>IP: $userIP</br>Teilnehmer ID: $user</br>Datum: $timestamp</br></p>";

// Mail Settings
    $mail = new PHPMailer;
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->SMTPAuth = true; // Enable SMTP authentication
//    $mail->SMTPDebug = 2; //Please enable debug if you want to check if the mail sent successfully.

    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->Username = 'xxx@gmail.com';    // SMTP username
    $mail->Password = '*****';  // SMTP password
    $mail->SMTPSecure = 'ssl';   // Enable encryption, 'ssl' also accepted
    $mail->Port = 465;

    $mail->addAddress("felix.graesser@tu-dresden.de", 'YourName');
//    $mail->From = $from;
    $mail->FromName = "OnlineDelphi";
    $mail->Subject = "$umfrageTypTxt: $umfrage";
    $mail->Body = $text;
    $mail->AltBody = $text;

    if (!$mail->Send()) {
//        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
//        echo "Message has been sent";
    }
}

?>