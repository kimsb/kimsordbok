<?php
require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$url = parse_url(getenv("HEROKU_POSTGRESQL_AQUA_URL"));
$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);

$db_conn = pg_connect("user=$username password=$password host=$host sslmode=require dbname=$database") or die('Could not connect: ' . pg_last_error());
pg_query("SET NAMES 'utf8'");

$consumerKey = getenv("TWITTER_CONSUMER_KEY");
$consumerSecret = getenv("TWITTER_CONSUMER_SECRET");
$accessToken = getenv("TWITTER_ACCESS_TOKEN");
$accessSecret = getenv("TWITTER_ACCESS_SECRET");

$sqlW = "SELECT * FROM dictionary WHERE isvalid = true AND word like '%W%' AND LENGTH(word) > 4 AND LENGTH(word) < 9 ORDER BY RANDOM()";
$sqlC = "SELECT * FROM dictionary WHERE isvalid = true AND word like '%C%' AND LENGTH(word) > 4 AND LENGTH(word) < 9 ORDER BY RANDOM()";
$resultW = pg_exec($db_conn, $sqlW) or die('Query failed: ' . pg_last_error());
$resultC = pg_exec($db_conn, $sqlC) or die('Query failed: ' . pg_last_error());
if (pg_numrows($resultW) !== 0 && pg_numrows($resultC) !== 0) {
    $wordW = pg_fetch_array($resultW)['word'];
    $wordC = pg_fetch_array($resultC)['word'];
    $connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessSecret);
    $connection->setTimeouts(60, 90);
    $connection->post('statuses/update', array('status' => "$wordW\n$wordC"));
}
?>
