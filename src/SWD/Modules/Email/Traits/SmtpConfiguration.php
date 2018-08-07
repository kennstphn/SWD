<?php
namespace SWD\Modules\Email\Traits;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

trait SmtpConfiguration 
{
    protected $host, $port = 587, $smtpAuth = 1, $displayName, $username, $password;

    static function __loadSmtpMetadata($m,$addUniqueConstraint = true)
    {
        $b= new ClassMetadataBuilder($m);

        if($addUniqueConstraint){
            $b->addUniqueConstraint(['host', 'port', 'username', 'password', 'smtpAuth'], 'unique_configuration');
        }

        $b->addField('host', 'string');
        $b->addField('port', 'integer');
        $b->addField('smtpAuth', 'boolean');
        $b->addField('displayName', 'string');
        $b->addField('username', 'string');
        $b->addField('password', 'string');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getSmtpAuth()
    {
        return (bool)$this->smtpAuth;
    }

    /**
     * @param bool $smtpAuth
     */
    public function setSmtpAuth($smtpAuth)
    {
        $this->smtpAuth = (int)$smtpAuth;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }



}