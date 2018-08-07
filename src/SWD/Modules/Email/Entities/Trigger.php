<?php
namespace SWD\Modules\Email\Entities;
use SWD\Entities\EntityBase;
use SWD\Modules\Email\Traits\EmailEntity;
use SWD\Modules\Email\Traits\SmtpConfiguration;
use SWD\Modules\Email\Traits\TriggerEntity;
use SWD\Modules\Email\Trigger_interface;

abstract class Trigger extends EntityBase implements Trigger_interface
{
    function getName()
    {
        return "({$this->getId()}) {$this->getUrl()} ";
    }

    use TriggerEntity;
    use EmailEntity;

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        self::__loadEmailMetadata($m);
        self::__loadTriggerMetadata($m);
    }



}