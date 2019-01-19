<?php
namespace SWD\Modules\Email\Workflow;
use SWD\Modules\Email\EmailInstance_interface;
use SWD\Modules\Email\SmtpConfiguration_interface;
use SWD\Structures\EmailAddress;

class Send
{
    static $smtpDebug = 0;
    
    static function send(SmtpConfiguration_interface$configuration, EmailInstance_interface $email, $debug=null){

        $mailer = new \PHPMailer\PHPMailer\PHPMailer();

        if( ! is_null($debug)){$debug = self::$smtpDebug;}

        //Tell PHPMailer to use SMTP
        $mailer->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mailer->SMTPDebug = $debug;
        //Set the hostname of the mail server
        $mailer->Host = $configuration->getHost();
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mailer->Port = $configuration->getPort();
        //Whether to use SMTP authentication
        $mailer->SMTPAuth = $configuration->getSmtpAuth();
        if($mailer->SMTPAuth){
            //Username to use for SMTP authentication
            $mailer->Username = $configuration->getUsername();
            //Password to use for SMTP authentication
            $mailer->Password = $configuration->getPassword();
        }


        //Set who the message is to be sent from
        $mailer->setFrom($configuration->getUsername(), $configuration->getDisplayName());

        if($email->getReplyTo()){
            $replyPieces = explode(',', $email->getReplyTo());
            //Set an alternative reply-to address
            $mailer->addReplyTo($address = array_shift($replyPieces),implode(',', $replyPieces));
        }

        //set "to" fields
        for($i = 0, $c=count($email->getToList());$i<$c;$i++){
            $address = self::emailFromString($email->getToList()[$i]);
            //Set who the message is to be sent to
            $mailer->addAddress($address->address,$address->displayName);
        }

        //set "cc" fields
        for($i = 0, $c=count($email->getCcList());$i<$c;$i++){

            $address = self::emailFromString($email->getCcList()[$i]);
            $mailer->addCC($address->address,$address->displayName);
        }

        //set 'bcc' fields
        for($i = 0, $c=count($email->getBccList());$i<$c;$i++){
            $address= self::emailFromString($email->getBccList()[$i]);
            //Set who the message is to be sent to
            $mailer->addBCC($address->address,$address->displayName);
        }


        //Set the subject line
        $mailer->Subject = $email->getSubject();

        /*
         * PlainText may be an alt for html, or may be a plaintext email.
         */
        if($email->getHtml()){ // use case HTML Email
            $mailer->msgHTML($email->getHtml());
            if($email->getPlainText() && strlen($email->getPlainText())>0){
                //use case html email w/ manual altBody
                $mailer->AltBody = $email->getPlainText();
            }
        }else{
            //use case plaintext email
            $mailer->Body =$email->getPlainText();
        }


        //Attach an image file
        //$mailer->addAttachment('images/phpmailer_mini.png');

        $success = $mailer->send();

        //send the message, check for errors
        if ( ! $success) {
            $email->setSent(null);
            return false;
        } else {
            $email->setSent((new \DateTime())->setTimezone(new \DateTimeZone('UTC')));
            return true;
        }
    }

    protected static function emailFromString($email){
        return EmailAddress::createFromString($email);
    }
}