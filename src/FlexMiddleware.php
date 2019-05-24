<?php

namespace FlexMiddleware;

use Middlewares\Utils\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class FlexMiddleware
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class FlexMiddleware implements MiddlewareInterface
{
    /** @var MiddlewareInterface[] */
    protected $middlewares = [];

    protected $index = 0;

    public function __construct(iterable $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->index = 0;

        return $this->processRecursive($request, $handler);
    }

    protected function processRecursive(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = $this->middlewares[$this->index] ?? null;
        if ($middleware) {
            $this->index++;
            return $this->processRecursive($request, new RequestHandler(function () use ($request, $middleware, $handler) {
                return $middleware->process($request, $handler);
            }));
        } else {
            return $handler->handle($request);
        }
    }
}