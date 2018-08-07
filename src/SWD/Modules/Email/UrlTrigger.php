<?php
namespace SWD\Modules\Email;
use App\Factories\EntityManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use SWD\Helper\TwigFilterCollection;
use SWD\Structures\HelperTraits\DotClass;
use SWD\Website\Module;
use SWD\Website\Website;

class UrlTrigger extends Module
{
    use DotClass;
    static public $triggerClass;
    static public $emailInstanceClass;

    protected $moduleOutput =[
        'preflight'=>[],
        'rendering'=>[],
        'sending'=>[],
        'sent'=>0
    ];
    
    function em():EntityManager{
        return call_user_func(['App\\Factories\\EntityManagerFactory','create']);
    }
    
    /** @var ArrayCollection $errors */
    protected $errors, $successes;
    function __construct()
    {
        $this->errors=new ArrayCollection();
        $this->successes = new ArrayCollection();
    }

    /** @var  Website $website */
    protected $website;
    
    /** @var  \Twig_Environment $twig */
    protected $twig;
    
    function __invoke(string $hookName, Website $website)
    {
        $this->website = $website;
        if( $this->preflightChecksOut()){
            
            $triggers = $this->getTriggers($this::$triggerClass, $website);
            
            foreach($triggers as $trigger){
                $template = $this->getEmailTemplate($trigger);
                
                $emailInstance = $this->renderEmail($template);
                
                try{
                    $emailInstance = $this->sendEmail($trigger, $emailInstance);
                }catch (\Throwable $e){
                    $this->errors->add($e);
                }
             
                $this->storeEmailInstance($emailInstance);
                $this->successes->add($emailInstance->getId());
            }
        }

        $output =[];
        $output['successfully-sent']=$this->successes->toArray();
        if($this->errors->count()){
            $output['errors']=array_map(function(\Throwable $e){
                return [
                    'message'=>$e->getMessage(),
                    'type'=>get_class($e),
                    'line'=>$e->getLine(),
                    'file'=>$e->getFile(),
                ];
            },$this->errors->toArray());
        }
        

        $website->response()->addMeta(
            $this::dotClass(), $output
        );

    }
    
    function sendEmail(Trigger_interface $trigger, EmailInstance_interface $email){
        
        $email->setSent(null);
        
        $trigger->getMailApp()
            ->send(
                $trigger->getSmtpConfiguration(), 
                $email
            );
        
        $email->setSent(new \DateTime());
    
        return $email;
    }
    
    protected function storeEmailInstance(EmailInstance_interface $email){
        $em = $this->em();
        $em->persist($email);
        $em->flush($email);
    }
    
    protected function getTriggers(string $triggerClass, Website $website){
        /** @var Trigger_interface $triggerClass */
        return $triggerClass::findTriggersBy($website);
    }
    
    protected function createTwig():\Twig_Environment{
        $loader = new \Twig_Loader_Array([]);
        $twig = new \Twig_Environment($loader);
        $twig->addFilter(TwigFilterCollection::dotClass());
        return $twig;
    }
    
    protected function getEmailTemplate(Trigger_interface $trigger):Email_interface{
        return $trigger;
    }
    
    protected function getNewEmailInstance():EmailInstance_interface{
        $class = $this::$emailInstanceClass;
        return new $class;
    }
    
    protected function renderEmail(Email_interface $email):Email_interface{
        
        $instance = $this->getNewEmailInstance();

        $instance->setToList($this->renderArray($email->getToList()));
        
        if($email->getReplyTo()){
            $instance->setReplyTo($this->renderString($email->getReplyTo()));
        }

        $instance->setCcList($this->renderArray($email->getCcList()));
        $instance->setBccList($this->renderArray($email->getBccList()));
        $instance->setSubject($this->renderString($email->getSubject()));
        
        if($email->getPlainText()){
            $instance->setPlainText($this->renderString($email->getPlainText()));
        }
        
        if($email->getHtml()){
            $instance->setHtml($this->renderString($email->getHtml()));
        }
        
        return $instance;
    }

    /**
     * @param array $array
     * @return array
     * 
     * used for to, cc, bcc fields
     */
    protected function renderArray(array $array){
        for($i=0,$c=count($array);$i<$c;$i++){
            $array[$i] = $this->renderString($array[$i]);
        }
        return $array;
    }
    
    protected function renderString($string){
        return $this->twig
            ->createTemplate($string)
            ->render([
                'request'=>$this->website->request(), 
                'response'=>$this->website->response()
            ]);
    }
    

    protected function preflightChecksOut(){
        try{
            $this->twig = $this->createTwig();
        }catch (\Throwable $e){$this->errors->add($e);}
        
        try{
            if( ! in_array(EmailInstance_interface::class, class_implements($this::$emailInstanceClass))){
                throw new \Exception(EmailInstance_interface::class.' not implemented in '.$this::$emailInstanceClass);
            }
        }catch (\Throwable $e){$this->errors->add($e);}

        try{
            if( ! in_array(Trigger_interface::class, class_implements($this::$triggerClass))){
                throw new \Exception(Trigger_interface::class.' not implemented in '.$this::$emailInstanceClass);
            }
        }catch (\Throwable $e){$this->errors->add($e);}
        
        return $this->errors->count() === 0;

    }
    


}