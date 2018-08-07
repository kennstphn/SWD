<?php
namespace SWD\Modules\Email;

interface SmtpConfiguration_interface
{
    /**
     * @return string
     */
    public function getHost();

    /**
     * @return integer
     */
    public function getPort();


    /**
     * @return mixed
     */
    public function getSmtpAuth();


    /**
     * @return string
     */
    public function getUsername();


    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getDisplayName();

   
}