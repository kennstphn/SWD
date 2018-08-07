<?php

namespace SWD\Modules\Email\Entities;


use SWD\Entities\EntityBase;
use SWD\Modules\Email\SmtpConfiguration_interface;

abstract class SmtpConfiguration extends EntityBase implements SmtpConfiguration_interface
{
    use \SWD\Modules\Email\Traits\SmtpConfiguration;

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        self::__loadSmtpMetadata($m);
    }




}