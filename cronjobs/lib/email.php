<?php
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ .'/../../vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ .'/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ .'/../../vendor/phpmailer/phpmailer/src/SMTP.php';

function envia($subject, $body, $destinatario) {
    
    $conf=parse_ini_file(str_replace("\\","/",(dirname(__DIR__)))."/../app/config/config.ini", true) or die("No se ha leido el archivo de configuracion");

    $mail = new PHPMailer(true);
    $mail->isSMTP();

    $mail->isSMTP();

        $mail->SMTPAuth = true;

        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->SMTPSecure = "tls";

    //$mail->Timeout = 5; // set the timeout (seconds)
    $mail->CharSet = 'UTF-8';
    
    
    $dir = $conf['correo']['direccion'];
    $reply = $conf['correo']['reply'];
    

    $mail->Host = $conf['correo']['smtp'];
    $mail->Port = $conf['correo']['puerto'];

    $mail->Username = $dir;
    $mail->addReplyTo($dir, $reply);
    $mail->Password = $conf['correo']['password'];
    $mail->SetFrom($dir, $reply);

    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $body;


    $address = $destinatario;
    $mail->addAddress($address);




    if($address!="")
    try {
        if (!$mail->send())
            return $mail->ErrorInfo;
        else
            return "Ok";
    } catch (phpmailerException $e) {
        return "Excepcion PHPMAILER " . $e->errorMessage(); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        return "Otra Excepcion " . $e->getMessage(); //Boring error messages from anything else!
    }
}
