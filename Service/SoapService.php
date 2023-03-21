<?php

namespace App\Service;

class SoapService extends \SoapClient
{
    private $client;
    
    public function __construct($wsdl, array $options = null)
    {
        $this->client = parent::__construct($wsdl, $options);
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    public function sendRequest($method, $params)
    {
        return json_decode(json_encode($this->client->__call($method, $params)), true);
    }
}
