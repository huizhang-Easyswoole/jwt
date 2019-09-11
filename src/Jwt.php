<?php


namespace EasySwoole\Jwt;


class Jwt
{
    const ALG_METHOD_AES = 'AES';
    const ALG_METHOD_HMACSHA256 = 'HMACSHA256';

    private static $instance;
    private $secretKey = 'EasySwoole';
    private $alg = self::ALG_METHOD_HMACSHA256;

    public static function getInstance():Jwt
    {
        if(!isset(self::$instance)){
            self::$instance = new Jwt();
        }
        return self::$instance;
    }

    function setSecretKey(string $key):Jwt
    {
        $this->secretKey = $key;
        return $this;
    }

    function algMethod(string $method):Jwt
    {
        $this->alg = $method;
        return $this;
    }

    public function publish():Object
    {
        $obj = new Object();
        $obj->setAlg($this->alg);
        return $obj;
    }

    public function decode(?string $raw):?Object
    {
        $raw = json_decode(base64_decode(urldecode($raw)),true);
        if(empty($raw['signature'])){
            throw new Exception("signature is empty");
        }
        return new Object($raw,true);
    }


    public function __signature(Object $object):?string
    {
        $array = $object->toArray();
        unset($array['signature']);
        $json = json_encode($array,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        switch ($object->getAlg()){
            case self::ALG_METHOD_HMACSHA256:{
                return hash_hmac('sha256', $json, $this->secretKey);
            }
            case self::ALG_METHOD_AES:{
                return openssl_encrypt($json, 'AES-128-ECB', $this->secretKey);
            }
        }
        return null;
    }
}