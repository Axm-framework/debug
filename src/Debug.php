<?php

namespace Axm\Debug;

use Axm\Debug\DebugBar;
use Axm\Debug\StandardDebugBar;

class Debug
{
    private static $instance;
    private $debugbar;
    private $assets;


    /**
     * Constructor de la clase.
     *
     * @param array $config Configuración para DebugBar
     */
    private function __construct(array $config = [])
    {
        $this->debugbar = new StandardDebugBar();
        $this->assets   = $this->debugbar->getJavascriptRenderer();

        $this->configure($config);
    }

    /**
     * 
     */
    public static function this()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Configura DebugBar.
     *
     * @param array $config Configuración para DebugBar
     * @return void
     */
    public function configure(array $config = [])
    {
        if (isset($config['collectors'])) {

            $collectors = $this->debugbar->getCollectors();
            foreach ($collectors as $name => $collector) {
                if (isset($config['collectors'][$name])) {
                    $collector->setEnabled($config['collectors'][$name]['enabled']);
                }
            }
        }

        if (isset($config['options'])) {
            $this->debugbar->setOptions($config['options']);
        }

        if (isset($config['storage'])) {
            $storage = $this->debugbar->getStorage();
            $storage->setStoragePath($config['storage']['path']);
            $storage->setStorageId($config['storage']['id']);
        }
    }

    /**
     * Agrega recolectores de datos a DebugBar.
     *
     * @param array $collectors Array de recolectores de datos
     * @return void
     */
    public function addColletors(array $collectors)
    {
        foreach ($collectors as $collector) {
            $this->debugbar->addCollector($collector);
        }
    }

    /**
     * Agrega vistas a ViewsColletor DebugBar.
     *
     * @param array $collectors Array de recolectores de datos
     * @return void
     */
    public function addViews(string|array $views)
    {
        if (is_string($views)) {
            $views = [$views];
        }

        foreach ($views as $view) {
            $this->debugbar['views']->addView($view);
        }
    }

    /**
     * Inicia la recolección de datos de DebugBar.
     *
     * @return void
     */
    public function start()
    {
        $this->debugbar['time']->startMeasure();
    }

    /**
     * Detiene la recolección de datos de DebugBar.
     *
     * @return void
     */
    public function stop()
    {
        $this->debugbar->stopCollectors();
    }


    /**
     * Adds a message to the MessagesCollector
     *
     * A message can be anything from an object to a string
     *
     * @param mixed $message
     * @param string $label
     */
    public function addMessage(string $message, string $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var \DebugBar\DataCollector\MessagesCollector $collector */
            $collector =  $this->debugbar->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }

    /**
     * Adds a message info to the MessagesCollector
     *
     */
    public function info(string $message)
    {
        return $this->addMessage($message, 'info');
    }

    /**
     * Adds a message error to the MessagesCollector
     *
     */
    public function error(string $message)
    {
        return $this->addMessage($message, 'error');
    }

    /**
     * Adds a message warning to the MessagesCollector
     *
     */
    public function warning(string $message)
    {
        return $this->addMessage($message, 'warning');
    }

    /**
     * Adds a message debug to the MessagesCollector
     *
     */
    public function debug(string $message)
    {
        return $this->addMessage($message, 'debug');
    }
    /**
     * Adds a message log to the MessagesCollector
     *
     */
    public function log(string $message)
    {
        return $this->addMessage($message, 'log');
    }

    /**
     * Retorna el HTML para mostrar la barra de depuración.
     *
     * @return string HTML de la barra de depuración
     */
    public function styles()
    {
        return $this->assets->renderHead();
    }

    /**
     * Retorna el js y html para mostrar la barra de depuración.
     *
     * @return string HTML de la barra de depuración
     */
    public function render()
    {
        return $this->assets->render();
    }

    /**
     * Checks if a data collector has been added
     */
    public function hasCollector(string $name): bool
    {
        return  $this->debugbar->hasCollector($name);
    }

    /**
     * Returns a data collector
     *
     * @param string $name
     * @return DataCollectorInterface
     * @throws DebugBarException
     */
    public function getCollector(string $name)
    {
        return $this->debugbar->getCollector($name);
    }

    /**
     * Returns an array of all data collectors
     *
     * @return array[DataCollectorInterface]
     */
    public function getCollectors()
    {
        return $this->debugbar->getCollectors();
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param \Closure $closure
     * @return mixed
     */
    public function measure($label, \Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $result = $collector->measure($label, $closure);
        } else {
            $result = $closure();
        }

        return $result;
    }
}
