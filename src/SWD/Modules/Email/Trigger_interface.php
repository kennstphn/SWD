<?php
namespace SWD\Modules\Email;
use SWD\Website\Website;

interface Trigger_interface extends Email_interface
{

    /**
     * @param Website $website
     * @return $this[]
     */
    static function findTriggersBy(Website $website);

    function getMailApp():\SWD\Modules\Email\MailApp_interface;

    function getSmtpConfiguration():\SWD\Modules\Email\SmtpConfiguration_interface;

}