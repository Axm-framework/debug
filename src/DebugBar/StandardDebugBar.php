<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axm\Debug;

use Axm;
use Axm\Debug\DataCollector\ExceptionsCollector;
use Axm\Debug\DataCollector\MemoryCollector;
use Axm\Debug\DataCollector\MessagesCollector;
use Axm\Debug\DataCollector\PhpInfoCollector;
use Axm\Debug\DataCollector\RequestDataCollector;
use Axm\Debug\DataCollector\TimeDataCollector;
use Axm\Debug\DataCollector\ViewCollector;
use Axm\Debug\DataCollector\ConfigCollector;
use Axm\Debug\DataCollector\LocalizationCollector;
use Axm\Debug\DataCollector\RoutesCollector;
use Axm\Debug\DataCollector\FilesCollector;


use Axm\Debug\DataCollector\PDO\PDOCollector;
use Axm\Debug\DataCollector\PDO\TraceablePDO;
use PDO;

/**
 * Debug bar subclass which adds all included collectors
 */
class StandardDebugBar extends DebugBar
{
    public function __construct()
    {
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MessagesCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
        $this->addCollector(new ExceptionsCollector());

        $this->addCollector(new ViewCollector());
        $this->addCollector(new ConfigCollector(Axm::app()->config()->all()));
        $this->addCollector(new LocalizationCollector());
        $this->addCollector(new RoutesCollector());
        $this->addCollector(new FilesCollector());

        // $pdo = new TraceablePDO(new PDO('sqlite::memory:'));
        // $this->addCollector(new PDOCollector($pdo));
    }
}
