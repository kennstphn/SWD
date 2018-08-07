<?php
namespace SWD\Modules\Email;


interface EmailInstance_interface extends Email_interface
{
    /**
     * @param \DateTime | null $dateTime
     */
    function setSent($dateTime);

    /**
     * @return  \DateTime | null
     */
    function getSent();

}