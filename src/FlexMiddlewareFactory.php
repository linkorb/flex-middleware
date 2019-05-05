<?php

namespace FlexMiddleware;

use Middlewares\Uuid;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FlexMiddlewareFactory
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class FlexMiddlewareFactory
{
    // TODO factory
    public static function fromConfig($path): FlexMiddleware
    {
        $config = Yaml::parseFile($path);

        $middlewareConfigs = $config['middlewares'];

        $middlewares = [];


        foreach ($middlewareConfigs as $key => $middlewareConfig) {
            $class = $middlewareConfig['class'];
            unset($middlewareConfig['class']);

            $middleware = new $class;

            foreach ($middlewareConfig as $key => $value)
            {
                // TODO recursive
                $value = $value === "true" ? true : $value === "false" ? false : $value;

                if (method_exists($middleware, $key)) {
                    call_user_func([$middleware, $key], $value);
                    continue;
                }

                if (property_exists($middleware, $key)) {
                    $middleware->{$key} = $value;
                    continue;
                }

                throw new \InvalidArgumentException("Invalid config object $class have not $key method or property");
            }
        }

        return new FlexMiddleware($middlewares);
    }
}