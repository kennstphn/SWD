<?php
namespace SWD\Modules\Email;

interface MailApp_interface
{
    function send(SmtpConfiguration_interface $smtp, Email_interface $email);

}