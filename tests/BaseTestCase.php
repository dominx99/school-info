<?php

namespace dominx99\school;

use dominx99\school\App;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

class BaseTestCase extends TestCase
{
    /**
     * @var \Slim\App
     */
    protected $app;

    protected $container;

    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    public function setUp()
    {
        parent::setUp();

        if (!isset($_SESSION) && !headers_sent()) {
            session_start();
        }

        $this->createApplication();

        $traits = array_flip(class_uses_recursive(static::class));
        if (isset($traits[DatabaseTrait::class])) {
            $this->migrate();
            $this->setUserData();
        }
    }

    public function tearDown()
    {
        $traits = array_flip(class_uses_recursive(static::class));
        if (isset($traits[DatabaseTrait::class])) {
            $this->rollback();
        }
        $this->container->auth->logout();
        unset($this->app);
        parent::tearDown();
    }

    /**
     * @return \Slim\App instance
     * Function which has only to call and create Slim App instance
     */
    public function createApplication()
    {
        $this->app       = (new App())->boot();
        $this->container = $this->app->getContainer();
    }

    public function runApp($method, $uri)
    {
        $request = $this->newRequest([
            'method' => $method,
            'uri'    => $uri,
        ]);

        return $this->app->process($request, new Response());
    }

    /**
     * @return \Slim\Http\Request
     * Function that creates and returns request created from params
     */
    public function newRequest($options = [], $params = [])
    {
        $default = [
            'content_type' => 'application/json',
            'method'       => 'get',
            'uri'          => '/',
        ];

        $options = array_merge($default, $options);

        $env          = Environment::mock();
        $uri          = Uri::createFromString($options['uri']);
        $headers      = Headers::createFromEnvironment($env);
        $cookies      = [];
        $serverParams = $env->all();
        $body         = new RequestBody();
        $request      = new Request($options['method'], $uri, $headers, $cookies, $serverParams, $body);

        $request = $request->withParsedBody($params);
        $request = $request->withHeader('Content-Type', $options['content_type']);
        $request = $request->withMethod($options['method']);

        return $request;
    }
}
