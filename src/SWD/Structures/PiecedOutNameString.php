<?php
namespace SWD\Structures;
class PiecedOutNameString
{
    protected $name;
    protected $original;
    protected $ext;
    protected $increment;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @return string|null
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return int|null
     */
    public function getIncrement()
    {
        return $this->increment;
    }
    
    function __construct(string $stringName)
    {
        /*
     * https://www.phpliveregex.com/p/pgN
     */
    preg_match("/^(.*)\(([0-9]+)\)(\.[^\s]*)?$|^(.*)(\.[^\s]*)?$/U", $name, $output_array);
    switch (count($output_array)){
        case 6:
            /*
                array(6
                0	=>	hasdf.sd.jpg
                1	=>
                2	=>
                3	=>
                4	=>	hasdf
                5	=>	.sd.jpg
                )
             */
            $this->name = $output_array[4];
            $this->ext = $output_array[5];
            break;
        case 5:
            /*
            array(5
                0	=>	hasdlfkjasdf
                1	=>
                2	=>
                3	=>
                4	=>	hasdlfkjasdf
            )
            */
            $this->name = $output_array[4];
            break;
        case 4:
            /*
             array(4
                0	=>	hasdf sd (32).jpg
                1	=>	hasdf sd
                2	=>	32
                3	=>	.jpg
            )
            */
            $this->name = $output_array[1];
            $this->increment = $output_array[2];
            $this->ext = $output_array[3];
            break;
        case 3:
            /*
             array(3
                0	=>	hasdlfkjasdf(2)
                1	=>	hasdlfkjasdf
                2	=>	2
            )
             */
            $this->name = $output_array[1];
            $this->increment = $output_array[2];
            break;
        default:
            throw new \Exception('Unanticipated response',500);
            break;

    }

    } 
    
    function getMySqlLikeParameter(){return "{$this->getName()}%{$this->getExt()}" ;}
    

}