<?php
namespace SWD\Website;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

class CsrfProtection
{
    const MISMATCH_ERROR = 'Invalid Request. Potential CSRF detected due to client/server token mismatch';
    const MISSING_SERVER_TOKEN = 'Missing server anti-csrf token';
    const MISSING_CLIENT_TOKEN = 'Invalid Request. Missing anti-csrf token.';
    const TOKEN_KEY = 'antiCsrfToken';
    
    function __construct(Request_interface $request_interface, Response_interface $response_interface)
    {
        $this->request = $request_interface;
        $this->response = $response_interface;
    }
    
    function blockInvalidRequests(){
        if ( ! in_array(strtolower($this->request->method()),['put','post']) ){
            return;
        }
        
        if ($this->getClientToken() !== $this->getServerToken()){
            throw new CsrfException(CsrfException::CLIENT_SERVER_MISMATCH);
        }
    }
    
    
    function persistToken(callable $tokenGenerator = null){
        
        $token = $this->request->session()->containsKey(self::TOKEN_KEY)
            ? $this->request->session()->get(self::TOKEN_KEY)
            : $this->generateToken($tokenGenerator);
        
        $this->response->addMeta(self::TOKEN_KEY, $token);
        $this->response->setSessionVar(self::TOKEN_KEY, $token);
        
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getClientToken(){
        $token = $this->request->post()->get(self::TOKEN_KEY);
        if ( 
            is_null($token)||$token == ''||$token == false||!is_string($token)
        ){
            throw new CsrfException(CsrfException::MISSING_CLIENT_CODE);
        }
        return $token;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getServerToken(){
        $token = $this->request->session()->get(self::TOKEN_KEY);
        if (
            is_null($token)||$token == ''||$token == false||!is_string($token)
        ){
            throw new CsrfException(CsrfException::MISSING_SERVER_CODE);
        }
        return $token;    
    }

    protected function generateToken(callable $tokenGenerator = null){
        return $tokenGenerator ? call_user_func($tokenGenerator) : bin2hex(random_bytes(32));
    }
}