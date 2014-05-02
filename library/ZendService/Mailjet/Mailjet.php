<?php
/**
 * ZendService\Mailjet
 *
 * @link      https://github.com/Narno/ZendService_Mailjet
 * @copyright Copyright (c) 2012-2013 Arnaud Ligny
 * @license   http://opensource.org/licenses/MIT MIT license
 * @package   Zend_Service
 */

namespace ZendService\Mailjet;

use Zend\Http;

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Mailjet
 */
class Mailjet
{
    /**
     * Mailjet API key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Mailjet API secret key
     *
     * @var string
     */
    protected $apiSecretKey;
    
    /**
     * Output format
     */
    const OUTPUT_FORMAT = 'json';

    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'uri'      => '{{protocol}}://api.mailjet.com/{{version}}/',
        'protocol' => 'http',
        'version'  => 0.1,
    );

    /**
     * @var HttpClient
     */
    protected $httpClient = null;

    /**
     * Current method category (for method proxying)
     *
     * @var string
     */
    protected $methodCategory;

    /**
     * Categories of API methods
     *
     * @var array
     */
    protected $methodCategories = array(
        'api',
        'user',
        'message',
        'contact',
        'lists',
        'report',
        'help',
    );

    /**
     * Performs object initializations
     *
     * @param string $apiKey
     * @param string $apiSecretKey
     * @param null|Http\Client $httpClient
     */
    public function __construct($apiKey, $apiSecretKey, Http\Client $httpClient = null)
    {
        $this->apiKey       = (string) $apiKey;
        $this->apiSecretKey = (string) $apiSecretKey;
        $this->setHttpClient($httpClient ?: new Http\Client)
            ->setAuth($apiKey, $apiSecretKey);
    }

    /**
     * Proxy service methods category
     *
     * @param  string $category
     * @return self
     * @throws Exception If method not in method categories list
     */
    public function __get($category)
    {
        $category = strtolower($category);
        if (!in_array($category, $this->methodCategories)) {
            throw new Exception\RuntimeException(
                'Invalid method category "' . $category . '"'
            );
        }
        $this->methodCategory = $category;
        return $this;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     * @throws Exception\RuntimeException if unable to find method
     */
    public function __call($method, $args)
     {
        $params = array();

        $method = ucfirst($method);
        if (!empty($args)) {
            $params = $args[0];
            if (!is_array($params)) {
                throw new \Exception(
                    '$params should be an array'
                );
            }
        }

        /**
         * If method category is not setted
         */
        if (empty($this->methodCategory)) {
            throw new Exception\RuntimeException(
                'Invalid method "' . $method . '"'
            );
        }

        /**
         * Build Mailjet method name: category + method
         */
        $method = $this->methodCategory . $method;

        /**
         * If local method exist, call it
         */
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }
        /**
         * Else request API directly
         */
        else {
            if ($method != 'helpMethod') {
                $helpMethod = $this->helpMethod($method);
                if ($helpMethod !== NULL && $helpMethod->status == 'OK') {
                    $methodRequestType = strtoupper($helpMethod->method->request_type);
                    if ($methodRequestType == 'GET') {
                        return $this->requestGet($method, $params);
                    }
                    elseif ($methodRequestType == 'POST') {
                        return $this->requestPost($method, $params);
                    }
                    else {
                        throw new Exception\RuntimeException(
                            'Invalid HTTP method "' . $methodRequestType . '"'
                        );
                    }
                }
                else {
                    throw new Exception\RuntimeException(
                        'Invalid method "' . $method . '"'
                    );
                }
            }
        }
    }

    /**
     * Set HTTP client
     *
     * @param Http\Client $httpClient
     * @return Http\Client
     */
    public function setHttpClient(Http\Client $httpClient)
    {
        return $this->httpClient = $httpClient;
    }

    /**
     * Get HTTP client
     *
     * @return Http\Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Perform an HTTP GET or POST request
     * (GET by default)
     *
     * @param  string API method
     * @param  array parameters
     * @param  string HTTP method ('GET' or 'POST')
     * @return object Response
     */
    public function request($apiMethod, array $params = array(), $method = 'GET')
    {
        // ie: api.mailjet.com/0.1/methodFunction?option=value

        // Build URI
        $uri = strtr($this->options['uri'], array(
            '{{protocol}}' => $this->options['protocol'],
            '{{version}}'  => $this->options['version'],
        ));

        $params = array_merge($params, array('output' => self::OUTPUT_FORMAT));

        $request = new Http\Request;
        $request->setUri($uri . $apiMethod);

        if (strtoupper($method) == 'GET') {
            $request->setMethod(Http\Request::METHOD_GET);
            $request->getQuery()->fromArray($params);
            $response = $this->getHttpClient()
                ->setMethod(Http\Request::METHOD_GET)->send($request);
        }
        elseif (strtoupper($method) == 'POST') {
            $request->setMethod(Http\Request::METHOD_POST);
            $request->getPost()->fromArray($params);
            $response = $this->getHttpClient()
                ->setMethod(Http\Request::METHOD_POST)->send($request);
        }

        if ($response->isServerError() || $response->isClientError()) {
            throw new Exception\RuntimeException('An error occurred sending request. Status code: ' . $response->getStatusCode());
        }

        return json_decode($response->getBody());
    }

    /**
     * Perform an HTTP GET request
     *
     * @param string API method
     * @param array parameters
     * @return object Response
     */
    public function requestGet($apiMethod, array $params = array())
    {
        return $this->request($apiMethod, $params, 'GET');
    }

    /**
     * Perform an HTTP POST request
     *
     * @param string API method
     * @param array parameters
     * @return object Response
     */
    public function requestPost($apiMethod, array $params = array())
    {
        return $this->request($apiMethod, $params, 'POST');
    }

    /**
     * Get description of a method
     *
     * @param string API method
     */
    public function helpMethod($name)
    {
        static $apiMethod = 'HelpMethod';
        $response = $this->requestGet(
            $apiMethod,
            array(
                'name' => $name,
            )
        );
        return $response;
    }
}
