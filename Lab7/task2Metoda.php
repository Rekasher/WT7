<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>General</title>
    <style>
        body {
            margin: 0 auto;
            font-size: 15px;
        }

        .form-container {
            width: 600px;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
        }

        form h2 {
            margin: 0;
            font-family: GENISO;
        }

        input {
            display: block;
            margin: 0px auto 5px auto;
            border-color: black;
            border-width: 1px;
            border-radius: 5px;
        }

        .text-head {
            width: 530px;
            height: 25px;
            padding: 0 10px;
        }

        .text-message {
            width: 550px;
            height: 300px;
            align-content: start;
        }

        .send-button {
            margin-right: 21px;
            width: 100px;
        }

        .send-button:hover
        {
            background: #CD214F;
            border-color: #CD214F; border-style: solid;
            cursor: pointer;
        }
    </style>
</head>
<body>
<form class="form-container" method="get">
    <h2>RECEIVERS</h2>
    <input class="text-head" type="text" name="Receivers">

    <h2>SUBJECT</h2>
    <input class="text-head" type="text" name="Subject">

    <h2>TEXT</h2>
    <textarea class="text-message" name="MessageText"></textarea>

    <input class="send-button" type="submit" value="Send!">
</form>
</body>
</html>

<?php

require '/usr/share/php/libphp-phpmailer/src/PHPMailer.php';
require '/usr/share/php/libphp-phpmailer/src/SMTP.php';
require '/usr/share/php/libphp-phpmailer/src/Exception.php';

$email = new PHPMailer\PHPMailer\PHPMailer();
$email->IsSMTP();
$email->SMTPAuth = true;
$email->Host = "ssl://smtp.gmail.com";
$email->Port = 465;
$email->Username = "testpop3lab@gmail.com";
$email->Password = "hardpass123";
$email->SetFrom("ivan.makiev1@gmail.com");

$fileReceivers = fopen("receivers.txt", "w");

if (!(isset($_GET['Receivers']) && isset($_GET['Subject']) && isset($_GET['MessageText']))) {
    die();
}

if ($_GET['Receivers'] == "")
    die("Receivers empty.");

$emailsDiff = 0;
$fakeEmails = array();
foreach (explode(" ", $_GET['Receivers']) as $receiverEmail)
{
    $emailsDiff++;
    if($email->AddAddress($receiverEmail)) {
        $emailsDiff--;
        fwrite($fileReceivers, $receiverEmail . "\n");
    }
    else
    {
        $fakeEmails[] = $receiverEmail;
    }
}

$email->Subject = $_GET['Subject'];
$email->Body = $_GET['MessageText'];

if(!$email->Send()) {
    echo "Error: ". $email->ErrorInfo;
}
else {
    echo "Email has been sent.";
}

if($emailsDiff != 0) {
    echo("Not all addresses entered correctly.<br/>");
    echo("Fake emails: <br/>");
    foreach ($fakeEmails as $currEmail)
    {
        echo $currEmail. "<br/>";
    }
}

fclose($fileReceivers);
