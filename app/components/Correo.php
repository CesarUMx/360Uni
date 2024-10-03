<?php
use \Phalcon\Di\Injectable;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Correo extends Injectable {

    public function envia($body, $subject, $destinatario = NULL, $archivos = []) {

        if (!isset($destinatario) || trim($destinatario) === "")
            return;


        $direccion=$this->config->correo->direccion;
        $mail = new PHPMailer(true);
        $mail->isSMTP();

        $mail->SMTPAuth = true;

        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        
        $mail->SMTPSecure = "tls";
        
        $mail->XMailer = ' ';

        $mail->Timeout       =   5; // set the timeout (seconds)
        $mail->CharSet = 'UTF-8';

        $mail->Host = $this->config->correo->smtp;

        $mail->Port = $this->config->correo->puerto;

        $mail->Username = $direccion;
        $mail->addReplyTo($direccion, $this->config->correo->reply);
        $mail->Password = $this->config->correo->password;
        $mail->SetFrom($direccion, $this->config->correo->reply);

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;


        $address = $destinatario;
        $mail->addAddress($address);



        if(is_array($archivos))
        foreach ($archivos as $a)
            $mail->AddAttachment($a["archivo"], $a["nombre"], 'base64', $a["tipo"]);





            try {
                if (!$mail->send())
                    return $mail->ErrorInfo;
                else
                    return "Ok";
            } catch (phpmailerException $e) {
                echo "Excepcion PHPMAILER " . $e->errorMessage(); //Pretty error messages from PHPMailer
            } catch (Exception $e) {
                echo "Otra Excepcion " . $e->getMessage(); //Boring error messages from anything else!
            }
        }
    }
    