<?php

namespace TimTegeler\Routerunner;

use TimTegeler\Routerunner\Exception\RouterException;

/**
 * Class Router
 * @package TimTegeler\Routerunner
 */
class Router
{

    const SEPERATOR_OF_CLASS_AND_METHOD = "->";
    const FALLBACK_HTTP_METHOD = "GET";
    const FALLBACK_URI = "/";
    /**
     * @var string
     */
    private static $callableNameSpace = "\\";

    /**
     * @param $filename
     * @throws Exception\ParseException
     */
    public static function parse($filename)
    {
        $routes = Parser::parse($filename);
        Finder::setRoutes($routes);
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @param $callback
     * @throws Exception\ParseException
     */
    public static function route($httpMethod, $uri, $callback)
    {
        $routeFormat = "%s %s %s";
        $route = sprintf($routeFormat, $httpMethod, $uri, $callback);
        Finder::addRoute(Parser::createRoute($route));
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws RouterException
     */
    public static function execute($httpMethod, $uri)
    {
        try {
            $route = Finder::findRoute($httpMethod, $uri);
        } catch (RouterException $e) {
            $route = Finder::findRoute(self::FALLBACK_HTTP_METHOD, self::FALLBACK_URI);
        }
        $callable = self::generateCallable($route);
        if (is_callable($callable)) {
            return call_user_func_array($callable, $route->getParameter());
        } else {
            throw new RouterException("Route is not callable");
        }
    }

    /**
     * @param Route $route
     * @return array
     */
    private static function generateCallable(Route $route)
    {
        return explode(self::SEPERATOR_OF_CLASS_AND_METHOD, self::$callableNameSpace . "\\" . $route->getCallable());
    }

    /**
     * @param $callableNameSpace
     */
    public static function setCallableNameSpace($callableNameSpace)
    {
        self::$callableNameSpace = $callableNameSpace;
    }

    public static function activateCaching()
    {
        Parser::setCaching(True);
    }

    public static function deactivateCaching()
    {
        Parser::setCaching(False);
    }

}