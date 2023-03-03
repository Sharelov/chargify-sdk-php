<?php

namespace Crucial\Tests\Helpers;

use Crucial\Service\Chargify;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;

class ClientHelper
{
    /**
     * Get a Chargify client instance
     *
     * @param string|null $mockResponseFile Filename containing mocked response
     * @param string $env (test|dev)
     *
     * @return Chargify
     */
    public static function getInstance(string $mockResponseFile = null, string $env = 'test'): Chargify
    {
        $config = array();
        switch ($env) {
            case 'test':
                $config = require dirname(__DIR__).'/config/client.php';
                break;
            case 'dev':
                $config = require dirname(__DIR__).'/config/ClientConfig.Dev.php';
                break;
        }

        $chargify = new Chargify($config);

        if (! empty($mockResponseFile)) {
            $mock = new MockHandler([
                Psr7\Message::parseResponse(MockResponse::read($mockResponseFile)),
            ]);
            $handler = HandlerStack::create($mock);

//            $logger = new Logger('Logger');
//            $logger->pushHandler(new StreamHandler(dirname(__DIR__) . '/artifacts/logs/guzzle.log', Logger::DEBUG));
//
//            $middleware = new LoggerMiddleware($logger);
//            $template   = MessageFormatter::DEBUG;
//            $middleware->setFormatter(new MessageFormatter($template));
//
//            $handler->push($middleware);

            $chargify->getHttpClient()->getConfig('handler')->setHandler($handler);
        }

        return $chargify;
    }
}
