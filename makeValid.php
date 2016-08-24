<?php
$word = $_POST['word'];

$mysql_host = "localhost";
$mysql_database = "ordbok";
$mysql_user = "kim";
$mysql_password = "kimmysqlordbok";

$mysql_connection = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die(mysql_error());
mysql_query("SET NAMES 'utf8'");
mysql_select_db($mysql_database, $mysql_connection);

$word = mb_strtoupper($word, 'UTF-8');

//lagrer originalstatus av ordet
$sql = "SELECT * FROM dictionary WHERE word = '$word'";
$status = "notPresent";
$result = mysql_query($sql, $mysql_connection);
if (mysql_num_rows($result) !== 0) {
    $row = mysql_fetch_array($result);
    if ($row[isValid]) {
        $status = "valid";
    } else {
        $status = "uncertain";
    }
}
$sql = "INSERT IGNORE INTO beforeChanges (word, status) VALUES ('$word', '$status')";
mysql_query($sql, $mysql_connection);

//lagrer endringen
date_default_timezone_set("Europe/Oslo");
$timestamp = date("Y m j H:i:s");
$username = ucfirst($_SERVER['REMOTE_USER']);
$action = "approved";
$sql = "INSERT INTO changes (timestamp, username, action, word) VALUES ('$timestamp', '$username', '$action', '$word')";
mysql_query($sql, $mysql_connection);

//oppdaterer databasen
$sql = "UPDATE dictionary SET isValid=1 WHERE word = '$word'";
mysql_query($sql, $mysql_connection);
?>
