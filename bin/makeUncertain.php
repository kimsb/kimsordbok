<?php
$word = $_POST['word'];

$url = parse_url(getenv("HEROKU_POSTGRESQL_AQUA_URL"));
$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);

$db_conn = pg_connect("user=$username password=$password host=$host sslmode=require dbname=$database") or die('Could not connect: ' . pg_last_error());
pg_query("SET NAMES 'utf8'");

$word = mb_strtoupper($word, 'UTF-8');

$intable = pg_exec($db_conn, "SELECT * FROM beforechanges WHERE word='$word'") or die('Query failed: ' . pg_last_error());
if (pg_numrows($intable) === 0) {
//lagrer originalstatus av ordet
    $sql = "SELECT * FROM dictionary WHERE word = '$word'";
    $status = "notPresent";
    $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
    if (pg_numrows($result) !== 0) {
        $row = pg_fetch_array($result);
        if ($row[isvalid] === 't') {
            $status = "valid";
        } else {
            $status = "uncertain";
        }
    }
    $sql = "INSERT INTO beforechanges (word, status) VALUES ('$word', '$status')";
    pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
}

//lagrer endringen
date_default_timezone_set("Europe/Oslo");
$timestamp = date("Y m j H:i:s");
$username = ucfirst($_SERVER['REMOTE_USER']);
$action = "disapproved";
$sql = "INSERT INTO changes (timestamp, username, action, word) VALUES ('$timestamp', '$username', '$action', '$word')";
pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());

//oppdaterer databasen
$sql = "UPDATE dictionary SET isvalid=false WHERE word = '$word'";
pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
?>
