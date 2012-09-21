<?php
/**
 * @package Zend_Service
 */

namespace ZendService\Mailjet;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Mailjet
 */
class Mailjet
{   
    /**
     * Your Mailjet API key
     *
     * @var string
     */
    protected $apiKey;
    
    /**
     * Your Mailjet API secret key
     *
     * @var string
     */
    protected $apiSecretKey;
    
    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'uri'      => '{{protocol}}://api.mailjet.com/{{version}}/',
        'protocol' => 'http',
        'version'  => 0.1,
        'output'   => 'json',
    );
    
    /**
     * @var HttpClient
     */
    protected $httpClient = null;
    
    /**
     * Performs object initializations
     *
     * @param   string $apiKey
     * @param   string $apiSecretKey
     */
    public function __construct($apiKey, $apiSecretKey, HttpClient $httpClient = null)
    {
        $this->apiKey       = (string) $apiKey;
        $this->apiSecretKey = (string) $apiSecretKey;
        $this->setHttpClient($httpClient ?: new HttpClient)
            ->setAuth($apiKey, $apiSecretKey);
    }
    
    /**
     * @param HttpClient $httpClient
     * @return Mailjet
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }
    
    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
    
    public function execute($apiMethod, array $params = array(), $method = 'GET')
    {
        // ie: api.mailjet.com/0.1/methodFunction?option=value

        // Build URI
        $uri = strtr($this->options['uri'], array(
            '{{protocol}}' => $this->options['protocol'],
            '{{version}}'  => $this->options['version'],
        ));

        $request = new HttpRequest;
        $request->setUri($uri . $apiMethod);

        if (strtoupper($method) == 'GET') {
            $request->setMethod(Request::METHOD_GET);
            $request->getQuery()->fromArray($params);
        }
        elseif (strtoupper($method) == 'POST') {
            $request->setMethod(Request::METHOD_POST);
            $request->getPost()->fromArray($params);
        }

        $response = $this->httpClient->send($request);

        // DEBUG
        Zend_Debug::dump($response);
        die();
        
        if ($response->isServerError() || $response->isClientError()) {
            throw new Exception\RuntimeException('An error occurred sending request. Status code: '
                                                 . $response->getStatusCode());
        }

        return $response;
    }
    
    /**
     * Adds a contact to a list
     */
    public function listAddcontact(string $email, int $listId, $force = false)
    {
        static $apiMethod = 'listsAddcontact';
        
        $response = $this->execute(
            $apiMethod,
            array(
                'contact' => $email,
                'id'      => $listId,
                'force'   => $force,
            ),
            'POST'
        );
        
        return $response->contact_id;
    }
}