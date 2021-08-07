<?php

namespace Mail;

require_once(DIR_SYSTEM . 'library/phpmailer/PHPMailer.php');
require_once(DIR_SYSTEM . 'library/phpmailer/SMTP.php');
require_once(DIR_SYSTEM . 'library/phpmailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer as LibraryPHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Phpmailer
{
    public function send() {
        $mail = new LibraryPHPMailer();
        
        $mail->Enconding = 'base64';
        $mail->CharSet = 'UTF-8';

		if (is_array($this->to)) {
			foreach ($this->to as $to) {
				$mail->AddAddress($to);
			}
		} else {
			$mail->AddAddress($this->to);
		}

		$mail->Subject = $this->subject;

		$mail->AddReplyTo($this->from, $this->sender);
		$mail->SetFrom($this->from, $this->sender);

		foreach ($this->bccs as $bcc)
			$mail->addBCC($bcc);

		if (!$this->html) {
			$mail->Body = $this->text;
		} else {
			$mail->MsgHTML($this->html);

			if ($this->text) {
				$mail->AltBody = $this->text;
			} else {
				$mail->AltBody = 'This is a HTML email and your email client software does not support HTML email!';
			}
		}

		foreach ($this->attachments as $attachment)
			if (file_exists($attachment))
				$mail->addAttachment($attachment);

        $mail->IsSMTP();
        $mail->Host = $this->smtp_hostname;
        $mail->Port = $this->smtp_port;
        $mail->SMTPAuth = true;

        if ($this->smtp_port == '587')
            $mail->SMTPSecure = 'tls';
        elseif ($this->smtp_port == '465')
            $mail->SMTPSecure = 'ssl';

        if (!empty($this->smtp_username)  && !empty($this->smtp_password)) {
            $mail->Host = $this->smtp_hostname;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
        }

		$mail->Send();
    }
}