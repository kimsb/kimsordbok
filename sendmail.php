<?php
require 'vendor/autoload.php';
$sendgrid = new SendGrid(getenv("SENDGRID_USERNAME"), getenv("SENDGRID_PASSWORD"));

$url = parse_url(getenv("HEROKU_POSTGRESQL_AQUA_URL"));
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
    while ($before = mysql_fetch_array($beforeChanges)) {
        $dictionarySql = "SELECT * FROM dictionary WHERE word = '$before[word]'";
        $dictionaryResult = mysql_query($dictionarySql, $mysql_connection);
        if (mysql_num_rows($dictionaryResult) !== 0) {
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
    $message = "God morgen, det har blitt gjort endringer i ordlisten!\r\n";
    if (!empty($newArray)) {
        sort($newArray);
        $message .= "\r\nNye ord:\r\n";
        foreach ($newArray as $newWord) {
            $message .= "$newWord\r\n";
        }
    }
    if (!empty($yesArray)) {
        sort($yesArray);
        $message .= "\r\nOrd som har blitt godkjent:\r\n";
        foreach ($yesArray as $yesWord) {
            $message .= "$yesWord\r\n";
        }
    }
    if (!empty($newUncertainArray)) {
        sort($newUncertainArray);
        $message .= "\r\nNye ord med status 'usikker':\r\n";
        foreach ($newUncertainArray as $newUncertainWord) {
            $message .= "$newUncertainWord\r\n";
        }
    }
    if (!empty($maybeArray)) {
        sort($maybeArray);
        $message .= "\r\nOrd som har fått status 'usikker':\r\n";
        foreach ($maybeArray as $maybeWord) {
            $message .= "$maybeWord\r\n";
        }
    }
    if (!empty($deletedArray)) {
        sort($deletedArray);
        $message .= "\r\nOrd som er slettet fra listen:\r\n";
        foreach ($deletedArray as $deletedWord) {
            $message .= "$deletedWord\r\n";
        }
    }
    $message .= "\r\n\r\nKim";

    //send mail
    //$to = 'kimbovim@gmail.com, tom.bovim@haugen-gruppen.no';
    $email = new SendGrid\Email();
    $email->addTo('kbovim@hotmail.com')
        ->setFrom('kimbovim@gmail.com')
        ->setSubject('Oppadteringer')
        ->setText($message);

    $status = $sendgrid->send($email);

    //lagrer mailen i databasen
    date_default_timezone_set("Europe/Oslo");
    $timestamp = date("Y m j H:i:s");
    $sql = "INSERT INTO mailarchive (timestamp, mail, status) VALUES ('$timestamp', '$message', '$status->message')";
    pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());

    if ($status->message === 'success') {
        $sql = "TRUNCATE beforechanges";
        pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
    }
}
?>
