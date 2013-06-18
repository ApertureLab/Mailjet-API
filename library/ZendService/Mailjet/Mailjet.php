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
     * @return HttpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        return $this->httpClient = $httpClient;
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

        $params = array_merge($params, array('output' => $this->options['output']));

        $request = new HttpRequest;
        $request->setUri($uri . $apiMethod);

        if (strtoupper($method) == 'GET') {
            $request->setMethod(HttpRequest::METHOD_GET);
            $request->getQuery()->fromArray($params);
            $response = $this->httpClient
                ->setMethod(HttpRequest::METHOD_GET)->send($request);
        }
        elseif (strtoupper($method) == 'POST') {
            $request->setMethod(HttpRequest::METHOD_POST);
            $request->getPost()->fromArray($params);
            $response = $this->httpClient
                ->setMethod(HttpRequest::METHOD_POST)->send($request);
        }

        if ($response->isServerError() || $response->isClientError()) {
            throw new Exception\RuntimeException('An error occurred sending request. Status code: '
                                                 . $response->getStatusCode());
        }

        return $response->getContent();
    }

    /**
     * Adds a contact to a list
     */
    public function listAddcontact($email, $listId, $force = true)
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

        return json_decode($response)->contact_id;
    }
    
    /**
     * Get user infos
     */
    public function userGetInfos()
    {
        static $apiMethod = 'userInfos';

        $response = $this->execute(
            $apiMethod,
            array(),
            'GET'
        );

        return json_decode($response)->infos;
    }

    /**
     * Get all lists
     */
    public function listGetAll()
    {
        static $apiMethod = 'listsAll';

        $response = $this->execute(
            $apiMethod,
            array(),
            'GET'
        );

        return json_decode($response)->lists;
    }
    
    /**
     * Get domains list
     */
    public function userGetDomains()
    {
        static $apiMethod = 'userDomainlist';

        $response = $this->execute(
            $apiMethod,
            array(),
            'GET'
        );

        return json_decode($response)->domains;
    }
    
    /**
     * Get contact infos
     */
    public function contactGetInfos($email)
    {
        static $apiMethod = 'contactInfos';

        $response = $this->execute(
            $apiMethod,
            array(
                'contact' => $email,
            ),
            'GET'
        );

        return json_decode($response)->contact;
    }
}