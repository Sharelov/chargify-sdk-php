<?php

namespace Crucial\Tests\Helpers;

use Crucial\Service\ChargifyV2;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;

class ClientV2Helper
{
    /**
     * Get a Chargify client instance
     *
     * @param string|null $mockResponseFile Filename containing mocked response
     * @param string $env (test|dev)
     *
     * @return ChargifyV2
     */
    public static function getInstance(string $mockResponseFile = null, string $env = 'test'): ChargifyV2
    {
        $config = array();
        switch ($env) {
            case 'test':
                $config = require dirname(__DIR__) . '/config/client-v2.php';
                break;
            case 'dev':
                $config = require dirname(__DIR__) . '/config/ClientV2Config.Dev.php';
                break;
        }

        $chargify = new ChargifyV2($config);

        if (!empty($mockResponseFile)) {
            $mock = new MockHandler([
                Psr7\Message::parseResponse(MockResponse::read($mockResponseFile))
            ]);
            $handler = HandlerStack::create($mock);
            $chargify->getHttpClient()->getConfig('handler')->setHandler($handler);
        }

        return $chargify;
    }
}