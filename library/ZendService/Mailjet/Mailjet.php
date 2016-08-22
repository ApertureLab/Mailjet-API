<?php
/*
 * Copyright (c) Arnaud Ligny <arnaud@ligny.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZendService\Mailjet;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;

/**
 * Class Mailjet.
 */
class Mailjet
{
    /**
     * Mailjet API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Mailjet API secret key.
     *
     * @var string
     */
    protected $apiSecretKey;

    /**
     * Output format.
     */
    const OUTPUT_FORMAT = 'json';

    /**
     * Options.
     *
     * @var array
     */
    protected $options = [
        'uri'      => '{{protocol}}://api.mailjet.com/{{version}}/',
        'protocol' => 'http',
        'version'  => 0.1,
    ];

    /**
     * @var HttpClient
     */
    protected $httpClient = null;

    /**
     * Current method category (for method proxying).
     *
     * @var string
     */
    protected $methodCategory;

    /**
     * Categories of API methods.
     *
     * @var array
     */
    protected $methodCategories = [
        'api',
        'user',
        'message',
        'contact',
        'lists',
        'report',
        'help',
    ];

    /**
     * Performs object initializations.
     *
     * @param string          $apiKey
     * @param string          $apiSecretKey
     * @param null|HttpClient $httpClient
     */
    public function __construct($apiKey, $apiSecretKey, HttpClient $httpClient = null)
    {
        $this->apiKey = (string) $apiKey;
        $this->apiSecretKey = (string) $apiSecretKey;
        $this->setHttpClient($httpClient ?: new HttpClient())
            ->setAuth($apiKey, $apiSecretKey);
    }

    /**
     * Proxy service methods category.
     *
     * @param string $category
     *
     * @throws Exception\RuntimeException If method not in method categories list
     *
     * @return self
     */
    public function __get($category)
    {
        $category = strtolower($category);
        if (!in_array($category, $this->methodCategories)) {
            throw new Exception\RuntimeException(
                'Invalid method category "'.$category.'"'
            );
        }
        $this->methodCategory = $category;

        return $this;
    }

    /**
     * Method overloading.
     *
     * @param string $method
     * @param array  $args
     *
     * @throws Exception\RuntimeException
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $params = [];

        $method = ucfirst($method);
        if (!empty($args)) {
            $params = $args[0];
            if (!is_array($params)) {
                throw new Exception\RuntimeException(
                    '$params should be an array'
                );
            }
        }

        /*
         * If method category is not set
         */
        if (empty($this->methodCategory)) {
            throw new Exception\RuntimeException(
                'Invalid method "'.$method.'"'
            );
        }

        /*
         * Build Mailjet method name: category + method
         */
        $method = $this->methodCategory.$method;

        /*
         * If local method exist, call it
         */
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $params);
        }
        /*
         * Else request API directly
         */
        else {
            if ($method != 'helpMethod') {
                $helpMethod = $this->helpMethod($method);
                if ($helpMethod !== null && $helpMethod->status == 'OK') {
                    $methodRequestType = strtoupper($helpMethod->method->request_type);
                    if ($methodRequestType == 'GET') {
                        return $this->requestGet($method, $params);
                    } elseif ($methodRequestType == 'POST') {
                        return $this->requestPost($method, $params);
                    } else {
                        throw new Exception\RuntimeException(
                            'Invalid HTTP method "'.$methodRequestType.'"'
                        );
                    }
                } else {
                    throw new Exception\RuntimeException(
                        'Invalid method "'.$method.'"'
                    );
                }
            }
        }

        return false;
    }

    /**
     * Set HTTP client.
     *
     * @param HttpClient $httpClient
     *
     * @return HttpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        return $this->httpClient = $httpClient;
    }

    /**
     * Get HTTP client.
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Perform an HTTP GET or POST request
     * (GET by default).
     *
     * @param string $apiMethod
     * @param array  $params
     * @param string $method    ('GET' or 'POST')
     *
     * @return object Response
     */
    protected function request($apiMethod, array $params = [], $method = 'GET')
    {
        // ie: api.mailjet.com/0.1/methodFunction?option=value

        // Build URI
        $uri = strtr($this->options['uri'], [
            '{{protocol}}' => $this->options['protocol'],
            '{{version}}'  => $this->options['version'],
        ]);

        $params = array_merge($params, ['output' => self::OUTPUT_FORMAT]);

        $request = new HttpRequest();
        $request->setUri($uri.$apiMethod);

        /* @var HttpResponse $response  */
        if (strtoupper($method) == 'GET') {
            $request->setMethod(HttpRequest::METHOD_GET);
            $request->getQuery()->fromArray($params);
            $response = $this->getHttpClient()
                ->setMethod(HttpRequest::METHOD_GET)->send($request);
        } elseif (strtoupper($method) == 'POST') {
            $request->setMethod(HttpRequest::METHOD_POST);
            $request->getPost()->fromArray($params);
            $response = $this->getHttpClient()
                ->setMethod(HttpRequest::METHOD_POST)->send($request);
        } else {
            throw new Exception\RuntimeException(
                sprintf('Invalid HTTP method "%s" ("GET" or "POST" only)', $method)
            );
        }

        if ($response->isServerError() || $response->isClientError()) {
            throw new Exception\RuntimeException('An error occurred sending request. Status code: '.$response->getStatusCode());
        }

        return json_decode($response->getBody());
    }

    /**
     * Perform an HTTP GET request.
     *
     * @param string $apiMethod
     * @param array  $params
     *
     * @return object Response
     */
    public function requestGet($apiMethod, array $params = [])
    {
        return $this->request($apiMethod, $params, 'GET');
    }

    /**
     * Perform an HTTP POST request.
     *
     * @param string $apiMethod
     * @param array  $params
     *
     * @return object Response
     */
    public function requestPost($apiMethod, array $params = [])
    {
        return $this->request($apiMethod, $params, 'POST');
    }

    /**
     * Get description of a method.
     *
     * @param string $name
     *
     * @return HttpResponse
     */
    protected function helpMethod($name)
    {
        static $apiMethod = 'HelpMethod';
        $response = $this->requestGet(
            $apiMethod,
            [
                'name' => $name,
            ]
        );

        return $response;
    }
}
