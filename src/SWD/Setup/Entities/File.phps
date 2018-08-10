<?php
namespace App\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Entities\EntityBase;

class File extends EntityBase
{

    protected $name, $folder, $data;

    static function loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new ClassMetadataBuilder($m);
        $b->addUniqueConstraint(array('name', 'folder'), 'unique_file');

        $b->addField('name', 'string');
        $b->addField('folder', 'string');
        $b->addField('data', 'blob');

    }

    function __construct()
    {
        parent::__construct();
        $this->folder = '/';

        $r = (string)rand(1000,9999);
        $this->name = time().'-'.$r;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = str_replace([' ','?','&'],['-','','and'],$name);
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return string
     */
    public function getData()
    {
        if ( ! is_null($this->data)){

            return stream_get_contents($this->data,-1,0);
        }
        return null;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function url(){
        return '/file'.$this->getFolder().'/'.$this->getName();
    }

    function __toString()
    {
        return $this->url();
    }

    function getMimeType(){
        preg_match("/([A-Z]+)/", stream_get_contents($this->data), $output_array);
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'jfif' => 'image/jpeg',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $key = strtolower($output_array[0]);
        return array_key_exists($key, $mime_types) ? $mime_types[$key] : $output_array[0];
    }

    function base64() {
        return 'data:'.$this->getMimeType().';base64,'.base64_encode($this->getData());
    }

    function buildCache(){
        $directory = PUBLIC_DIR.'/file/'.$this->getFolder();
        if ( ! file_exists($directory)){
            if ( ! mkdir($directory,0755,true)){
                throw new \Exception('Failed to create directory ('.$directory.')');
            }
        }
        if  ( !file_put_contents($directory.'/'.$this->getName(), $this->getData() )){
            throw new \Exception("Failed to created {$this->getName()} in directory ({$directory})");
        }
    }

}