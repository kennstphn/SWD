<?php
namespace SWD\Modules\Email\Traits;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

trait EmailEntity
{
    protected $toList=[],$replyTo,$ccList = [], $bccList=[],$subject,$plainText, $html;
    
    static function __loadEmailMetadata($m){
        $b = new ClassMetadataBuilder($m);
        $b->addField('toList', 'array');
        $b->createField('replyTo', 'string')->nullable(true)->build();
        $b->addField('ccList', 'array');
        $b->addField('bccList', 'array');
        $b->addField('subject', 'string');
        $b->createField('plainText', 'text')->nullable(true)->build();
        $b->createField('html', 'text')->nullable(true)->build();
    }

    /**
     * @return array
     */
    public function getToList()
    {
        return $this->toList;
    }

    /**
     * @param array $toList
     */
    public function setToList($toList)
    {
        $this->toList = $toList;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }

    /**
     * @return array
     */
    public function getCcList()
    {
        return $this->ccList;
    }

    /**
     * @param array $ccList
     */
    public function setCcList($ccList)
    {
        $this->ccList = $ccList;
    }

    /**
     * @return array
     */
    public function getBccList()
    {
        return $this->bccList;
    }

    /**
     * @param array $bccList
     */
    public function setBccList($bccList)
    {
        $this->bccList = $bccList;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getPlainText()
    {
        return $this->plainText;
    }

    /**
     * @param string $plainText
     */
    public function setPlainText($plainText)
    {
        $this->plainText = $plainText;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }
    
    


}