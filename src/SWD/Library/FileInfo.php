<?php

namespace SWD\Library;


class FileInfo
{
    protected static $data = null;

    static function mimeTypes(){
        return array_keys(self::getData());
    }

    static function fileExtension(string $mimeType){
        if ( ! array_key_exists($mimeType, self::getData())){throw new \Exception("Unknown mimetype ({$mimeType})");}
        return explode(' ', self::getData()[$mimeType] )[0];
    }

     static function getData(){return self::$data ?? self::_buildRepo();}
    protected static function _buildRepo(){
        $file = (new \DateTime())->format('Y-m').'-finfo-repo.json';
        if ( file_exists($file)){
            return json_decode(file_get_contents($file),true);
        }
        
        $ch = new \Curl\Curl();
        $ch->get('https://svn.apache.org/repos/asf/httpd/httpd/branches/2.4.x/docs/conf/mime.types');
        $input_lines = $ch->response;
        $input_lines = implode(PHP_EOL,preg_grep("/^[^#].*$/", explode("\n", $input_lines))); //clear commented
        $input_lines=preg_replace("/^(\S+)(\s+)(.+)$/m", "$1,$3", $input_lines); //restructure to comma separated
        $data = [];
        foreach(explode(PHP_EOL,$input_lines) as $item){
            $item = preg_split("/,/", $item);
            $data[$item[0]] = $item[1];
        }
        
        self::$data = $data;
        file_put_contents(__DIR__.'/'.$file,json_encode($data));

        return $data;

    }

}