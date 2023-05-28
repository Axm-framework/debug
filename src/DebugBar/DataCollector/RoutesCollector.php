<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axm\Debug\DataCollector;

use Axm;

/**
 * Collects array data
 */
class RoutesCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $name;

    protected $routes;

    // The HTML var dumper requires debug bar users to support the new inline assets, which not all
    // may support yet - so return false by default for now.
    protected $useHtmlVarDumper = false;

    /**
     * Sets a flag indicating whether the Symfony HtmlDumper will be used to dump variables for
     * rich variable rendering.
     *
     * @param bool $value
     * @return $this
     */
    public function useHtmlVarDumper($value = true)
    {
        $this->useHtmlVarDumper = $value;
        return $this;
    }

    /**
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return mixed
     */
    public function isHtmlVarDumperUsed()
    {
        return $this->useHtmlVarDumper;
    }

    /**
     * @param array  $data
     * @param string $name
     */
    public function __construct(array $data = [])
    {
        $this->routes = !empty($data) ? $data : Axm::app()->router->getAllRoutes();
    }

    /**
     * Sets the data
     *
     * @param array $data
     */
    public function setRoutes(array $data)
    {
        $this->routes = $data;
    }

    /**
     * @return array
     */
    // public function collect()
    // {
    //     $router     = Axm::app()->router;
    //     $controller = Axm::app()->controller;
    //     $request    = Axm::app()->request;

    //     // Matched Route
    //     $matchedRoute = [
    //         'controller' => $controller->getName(),
    //         'method'     => $controller->getAction(),
    //         'paramCount' => count($request->getRouteParams()),
    //         'truePCount' => 8, //count(Axm::app()->request->getReflectionParams()),
    //         'params'     => 2 //$router->getCallBack(),
    //     ];

    public function collect()
    {
        $router = Axm::app()->router;
        $data   = [];

        $methods = $router::$verbs;
        foreach ($methods as $verb) {
            $raw = $router->getRoutes($verb);
            $routes = [];

            foreach ($raw as $route => $handler) {

                // filter for strings, as callbacks aren't displayable
                if (is_string($handler)) {
                    $routes[] = [
                        'verb'    => $verb,
                        'route'   => $route,
                        'handler' => ltrim($handler, '/')
                    ];
                }

                if (is_array($handler)) {

                    $handlerString = '';
                    foreach ($handler as $part) {
                        if (is_string($part)) {
                            $handlerString .= '/' . $part;
                            [$method, $handler] = $this->implodePath($handlerString);
                        }
                    }

                    $routes[] = [
                        'verb'    => $verb,
                        'route'   => $route,
                        'handler' => ltrim($handler, '/'),
                        'method'  => ltrim($method, '/')
                    ];
                }
            }

            if ($this->isHtmlVarDumperUsed()) {
                $routes = $this->getVarDumper()->renderVar($routes);
            } else if (!is_string($routes)) {
                $routes = $this->getDataFormatter()->formatVar($routes);
            }

            $data[$verb] = $routes;
        }

        return $data;
    }

    public function implodePath(string $path, $separator = '/')
    {
        $lastSlashPos = strrpos($path, $separator);
        $handler = substr($path, $lastSlashPos);  // +1 para omitir la barra diagonal
        $method  = substr($path, 0, $lastSlashPos);

        return [$handler, $method];
    }


    public function splitPath(string $path, $separator = '/')
    {
        $lastSlashPos = strrpos($path, $separator);
        $handlerPath  = substr($path, $lastSlashPos);  // +1 para omitir la barra diagonal
        $methodPath   = substr($path, 0, $lastSlashPos);

        return [$methodPath, $handlerPath];
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'routes';
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->isHtmlVarDumperUsed() ? $this->getVarDumper()->getAssets() : [];
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        $widget = $this->isHtmlVarDumperUsed()
            ? 'PhpDebugBar.Widgets.HtmlVariableListWidget'
            : 'PhpDebugBar.Widgets.VariableListWidget';

        return [
            'routes' => [
                'icon'    => 'fast',
                'widget'  => $widget,
                'map'     => 'routes',
                'default' => '{}'
            ]
        ];
    }
}
