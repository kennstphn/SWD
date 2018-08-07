<?php
namespace SWD\Modules\Email;


interface Email_interface
{
    /** @return integer */
    function getId();
    
    /**
     * @return array
     */
    public function getToList();
    /**
     * @param array $toList
     */
    public function setToList($toList);
    /**
     * @return string
     */
    public function getReplyTo();
    /**
     * @param string $replyTo
     */
    public function setReplyTo($replyTo);
    /**
     * @return array
     */
    public function getCcList();
    /**
     * @param array $ccList
     */
    public function setCcList($ccList);
    /**
     * @return array
     */
    public function getBccList();
    /**
     * @param array $bccList
     */
    public function setBccList($bccList);
    /**
     * @return string
     */
    public function getSubject();
    /**
     * @param string $subject
     */
    public function setSubject($subject);
    /**
     * @return string
     */
    public function getPlainText();
    /**
     * @param string $plainText
     */
    public function setPlainText($plainText);
    /**
     * @return string
     */
    public function getHtml();
    /**
     * @param string $html
     */
    public function setHtml($html);

}