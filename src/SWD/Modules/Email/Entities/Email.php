<?php

namespace SWD\Modules\Email\Entities;


use SWD\Entities\EntityBase;
use SWD\Modules\Email\EmailInstance_interface;
use SWD\Modules\Email\Traits\EmailInstance;

abstract class Email extends EntityBase implements EmailInstance_interface
{
    use EmailInstance;

    function getName()
    {
        $sent = $this->getSent() ? 'Sent '.$this->getSent()->format('Y-m-d H:i:s'): 'Not sent';
        return "\"{$this->getSubject()}\" {$sent}";
    }

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        self::__loadEmailInstanceMetadata($m);
        self::__loadEmailMetadata($m);

    }


}