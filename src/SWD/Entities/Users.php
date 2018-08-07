<?php
namespace SWD\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Modules\AccessControl\User_interface;

abstract class Users extends EntityBase implements User_interface
{
    protected $username, $nickname, $password, $email, $phone;
    
    static function __loadMetadata($m){
        parent::__loadMetadata($m);
        $b = new ClassMetadataBuilder($m);
        $b->addUniqueConstraint(array('username'), 'unique_username');
        
        $b->addField('username', 'string');
        $b->createField('password', 'string')->nullable(true)->build();
        
        $b->createField('nickname', 'string')->nullable(true)->build();
        $b->createField('email', 'string')->nullable(true)->build();
        $b->createField('phone', 'string')->nullable(true)->build();
    }

    function __construct()
    {
        $this->lastModified = new \DateTime();
    }

    function getName()
    {
        return $this->getNickname() ? $this->getNickname() : $this->getUsername();
    }

    function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    function getPassword(){
        return $this->password;
    }

    function verifyPassword($password)
    {
        return password_verify($password ,$this->password );
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
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
    
    


}