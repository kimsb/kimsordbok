<?php
require 'vendor/autoload.php';

function send_notification_email($sender, $receiver, $message)
{
    $sendgrid = new SendGrid(getenv("SENDGRID_USERNAME"), getenv("SENDGRID_PASSWORD"));
    $email = new SendGrid\Email();
    $email->addTo($receiver)
        ->setFrom($sender)
        ->setSubject('Scrabbeller er oppdatert!')
        ->setHtml($message);

    $sendgrid->send($email);
}

function get_string_between($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function perform_diff()
{
    $url = parse_url(getenv("HEROKU_POSTGRESQL_AQUA_URL"));
    $host = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $database = substr($url["path"], 1);

    $db_conn = pg_connect("user=$username password=$password host=$host sslmode=require dbname=$database") or die('Could not connect: ' . pg_last_error());
    pg_query("SET NAMES 'utf8'");

    $sql = "SELECT * FROM scrabbeller ORDER BY id";
    $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
    if (pg_numrows($result) !== 0) {
        $sender = pg_fetch_result($result, 0, "id");
        while ($row = pg_fetch_array($result)) {
            $contents = file_get_contents($row[url]);
            if ($contents !== FALSE) {
                $newrating = str_replace(',', '.', get_string_between($contents, "Rating:</span> ", "<br />"));
                if (strcmp($row[rating], $newrating) != 0) {
                    $update = "UPDATE scrabbeller SET rating='$newrating' WHERE email = '" . $row[email] . "'";
                    pg_exec($db_conn, $update) or die('Query failed: ' . pg_last_error());

                    $message = "Hei, det har akkurat skjedd en endring i ratingen din på Scrabbeller!<br><br>";
                    $ratingdiff = $newrating - $row[rating];
                    if ($ratingdiff > 0) {
                        $message .= "Godt jobba! Du har gått opp $ratingdiff poeng!<br><br>";
                    } else {
                        $ratingdiff = abs($ratingdiff);
                        $message .= "Auda, du har gått ned $ratingdiff poeng...<br><br>";
                    }
                    $message .= "Gå til <a href='" . $row[url] . "'>Scrabbeller</a> for å se alle oppdateringer.";
                    send_notification_email($sender, $row[email], $message);
                }
            }
        }
    }
}

perform_diff();

?>
