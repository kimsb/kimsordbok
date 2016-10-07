<?php
require 'vendor/autoload.php';

function send_notification_email($receiver, $message)
{
    $sendgrid = new SendGrid(getenv("SENDGRID_USERNAME"), getenv("SENDGRID_PASSWORD"));
    $email = new SendGrid\Email();
    $email->addTo($receiver)
        ->setFrom(getenv("MAIL_SENDER_ADDRESS"))
        ->setSubject('Dagens ord')
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

$htmlpage = file_get_contents(getenv("SCRABBLEFORBUNDET_URL"));
$dagensord = get_string_between($htmlpage, "<b>Dagens ord<b></h3>", "</li>");
$message = "<p>Dagens ord fra NSF:</p><br>";
$message .= trim($dagensord) . ".<br><br>Kim";
send_notification_email(getenv("MAIL_SENDER_ADDRESS"), $message);

?>
