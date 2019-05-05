<?php

namespace FlexMiddlewareTest;

use FlexMiddleware\FlexMiddleware;
use FlexMiddleware\FlexMiddlewareFactory;
use Middlewares\ClientIp;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Middlewares\Uuid;
use PHPUnit\Framework\TestCase;

class FlexMiddlewareTest extends TestCase
{
    public function testMiddleware()
    {
        $flex = new FlexMiddleware([
            new ClientIp(),
            new Uuid(),
        ]);

        $ip = '123.123.123.123';
        $request = Factory::createServerRequest('GET', '/', ['REMOTE_ADDR' => $ip])
            ->withHeader('X-Forwarded', '11.11.11.11')
        ;

        $response = Dispatcher::run([
            $flex,
            function($request) { return Factory::createResponse(200)->withBody(Factory::createStream('hello ' . $request->getAttribute('client-ip'))); },
        ], $request);

        $response->getBody()->rewind();

        $this->assertNotEmpty($response->getHeader('X-Uuid'));
        $this->assertEquals('hello '. $ip, $response->getBody()->getContents());
    }

    public function testCreateFromYamlConfig()
    {
        $flex = FlexMiddlewareFactory::fromConfig(__DIR__ . DIRECTORY_SEPARATOR . 'middlewares.yaml');

        $this->assertNotEmpty($flex);
    }
}