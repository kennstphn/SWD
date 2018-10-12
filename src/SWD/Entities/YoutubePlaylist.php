<?php
namespace SWD\Entities;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class YoutubePlaylist extends EntityBase
{
    protected $playlist;

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new ClassMetadataBuilder($m);
        $b->addField('playlist', 'string');
        
    }
    
    /**
     * @return string
     */
    public function getPlaylist()
    {
        return $this->playlist;
    }

    /**
     * @param string $playlist
     */
    public function setPlaylist($playlist)
    {
        $this->playlist = $playlist;
    }

    function getName()
    {
        return $this->getPlaylist();
    }

    function __toString()
    {
        return $this->getPlaylist();
    }
}
