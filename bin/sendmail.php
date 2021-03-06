<?php
require 'vendor/autoload.php';
$sendgrid = new SendGrid(getenv("SENDGRID_USERNAME"), getenv("SENDGRID_PASSWORD"));

$url = parse_url(getenv("DATABASE_URL"));
$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);

$db_conn = pg_connect("user=$username password=$password host=$host sslmode=require dbname=$database") or die('Could not connect: ' . pg_last_error());
pg_query("SET NAMES 'utf8'");

$newArray = array();
$newUncertainArray = array();
$yesArray = array();
$maybeArray = array();
$deletedArray = array();

//for alle ord det har blitt gjort endringer på siden sist
//populerer arrayene
$sql = "SELECT * FROM beforechanges";
$beforeChanges = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
if (pg_numrows($beforeChanges) !== 0) {
    while ($before = pg_fetch_array($beforeChanges)) {
        $dictionarySql = "SELECT * FROM dictionary WHERE word = '$before[word]'";
        $dictionaryResult = pg_exec($db_conn, $dictionarySql) or die('Query failed: ' . pg_last_error());
        if (pg_numrows($dictionaryResult) !== 0) {
            $now = pg_fetch_array($dictionaryResult);
            if ($now['isvalid'] === 't') { //now = valid
                if ($before['status'] === "uncertain") {
                    $yesArray[] = $before['word'];
                } elseif ($before['status'] === "notPresent") {
                    $newArray[] = $before['word'];
                }
            } else { //now = uncertain
                if ($before['status'] === "valid") {
                    $maybeArray[] = $before['word'];
                } elseif ($before['status'] === "notPresent") {
                    $newUncertainArray[] = $before['word'];
                }
            }
        } else { //now = notPresent
            if ($before['status'] !== "notPresent") {
                $deletedArray[] = $before['word'];
            }
        }
    }

//fyller mailtekst
    $message = "God morgen, det har blitt gjort endringer i ordlisten!<br>";
    if (!empty($newArray)) {
        sort($newArray);
        $message .= "<br>Nye ord:<br>";
        foreach ($newArray as $newWord) {
            $message .= "$newWord<br>";
        }
    }
    if (!empty($yesArray)) {
        sort($yesArray);
        $message .= "<br>Ord som har blitt godkjent:<br>";
        foreach ($yesArray as $yesWord) {
            $message .= "$yesWord<br>";
        }
    }
    if (!empty($newUncertainArray)) {
        sort($newUncertainArray);
        $message .= "<br>Nye ord med status = usikker:<br>";
        foreach ($newUncertainArray as $newUncertainWord) {
            $message .= "$newUncertainWord<br>";
        }
    }
    if (!empty($maybeArray)) {
        sort($maybeArray);
        $message .= "<br>Ord som har fått status = usikker:<br>";
        foreach ($maybeArray as $maybeWord) {
            $message .= "$maybeWord<br>";
        }
    }
    if (!empty($deletedArray)) {
        sort($deletedArray);
        $message .= "<br>Ord som er slettet fra listen:<br>";
        foreach ($deletedArray as $deletedWord) {
            $message .= "$deletedWord<br>";
        }
    }
    $message .= "<br><br>Kim";

    $mailSql = "SELECT * FROM scrabbeller";
    $mailResult = pg_exec($db_conn, $mailSql) or die('Query failed: ' . pg_last_error());
    if (pg_numrows($mailResult) !== 0) {
        while ($row = pg_fetch_array($mailResult)) {
            //send mail
            $email = new SendGrid\Email();
            $email->addTo($row[email])
                ->setFrom(getenv("MAIL_SENDER_ADDRESS"))
                ->setSubject('Oppdateringer i ordboka')
                ->setHtml($message);

            $sendgrid->send($email);
        }
    }

    //lagrer mailen i databasen
    date_default_timezone_set("Europe/Oslo");
    $timestamp = date("Y m j H:i:s");
    $sql = "INSERT INTO mailarchive (timestamp, mail) VALUES ('$timestamp', '$message')";
    pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());

    $sql = "TRUNCATE beforechanges";
    pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
}
?>