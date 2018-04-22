<?php
require 'vendor/autoload.php';

function send_notification_email($receiver, $message)
{
    $sendgrid = new SendGrid(getenv("SENDGRID_USERNAME"), getenv("SENDGRID_PASSWORD"));
    $email = new SendGrid\Email();
    $email->addTo($receiver)
        ->setFrom(getenv("MAIL_SENDER_ADDRESS"))
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

    $sql = "SELECT * FROM scrabbeller";
    $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
    if (pg_numrows($result) !== 0) {

        $htmlpage = file_get_contents(getenv("SCRABBELLER_URL"));

        while ($row = pg_fetch_array($result)) {

            $before_name = substr($htmlpage, 0, stripos($htmlpage, ('>'.$row[name])));
            $newplace = get_string_between(substr($before_name, strripos($before_name, "number")), "> ", " <");
            $id = get_string_between(substr($before_name, strripos($before_name, "spiller")), "id=", "\">");
            $after_name = substr($htmlpage, stripos($htmlpage, ('>'.$row[name])));
            $newrating = str_replace(',', '.', get_string_between($after_name, "number\">", "<"));

            echo "$row[name]: rating: $row[rating], new rating: $newrating";
            echo "$row[name]: place: $row[place], new place: $newplace";

            if (strcmp($row[rating], $newrating) != 0 || strcmp($row[place], $newplace) != 0) {
                $update = "UPDATE scrabbeller SET rating='$newrating', place='$newplace' WHERE email = '" . $row[email] . "'";
                pg_exec($db_conn, $update) or die('Query failed: ' . pg_last_error());

                $message = "Hei, det har akkurat skjedd en endring på Scrabbeller!<br><br>";
                $ratingdiff = round($newrating - $row[rating], 2);
                if ($ratingdiff > 0) {
                    $message .= "Godt jobba! Du har gått opp $ratingdiff poeng, til $newrating!<br><br>";
                } else if ($ratingdiff < 0) {
                    $ratingdiff = abs($ratingdiff);
                    $message .= "Auda, du har gått ned $ratingdiff poeng, til $newrating...<br><br>";
                }
                $placediff = $newplace - $row[place];
                if ($placediff < 0) {
                    $placediff = abs($placediff);
                    $message .= "Du har gått opp $placediff plass";
                    if ($placediff > 1) {
                        $message .= "er";
                    }
                    $message .= ", til $newplace. plass!<br><br>";
                } else if ($placediff > 0) {
                    $message .= "Du har gått ned $placediff plass";
                    if ($placediff > 1) {
                        $message .= "er";
                    }
                    $message .= ", til $newplace. plass.<br><br>";
                }

                $message .= "Gå til <a href='" . getenv("SCRABBELLER_SPILLER_URL") . "$id'>Scrabbeller</a> for å se alle oppdateringer.<br><br>Kim";
                send_notification_email($row[email], $message);
            }
        }
    }
}

perform_diff();

?>
