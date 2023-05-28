<?php
/*
 * This file is part of the Debug package.
 *
 * (c) 2017 Tim Riemenschneider
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axm\Debug\Bridge\Twig;

use  Axm\Debug\DataCollector\TimeDataCollector;
use  Axm\Debug\Bridge\Twig\Twig_Profiler_Profile;
use  Axm\Debug\Bridge\Twig\Twig_Extension_Profiler;

/**
 * Class TimeableTwigExtensionProfiler
 *
 * Extends Twig_Extension_Profiler to add rendering times to the TimeDataCollector
 *
 * @package Debug\Bridge\Twig
 */
class TimeableTwigExtensionProfiler extends Twig_Extension_Profiler
{
    /**
     * @var \Debug\DataCollector\TimeDataCollector
     */
    private $timeDataCollector;

    /**
     * @param \Debug\DataCollector\TimeDataCollector $timeDataCollector
     */
    public function setTimeDataCollector(TimeDataCollector $timeDataCollector)
    {
        $this->timeDataCollector = $timeDataCollector;
    }

    public function __construct(\Twig_Profiler_Profile $profile, TimeDataCollector $timeDataCollector = null)
    {
        parent::__construct($profile);

        $this->timeDataCollector = $timeDataCollector;
    }

    public function enter(Twig_Profiler_Profile $profile)
    {
        if ($this->timeDataCollector && $profile->isTemplate()) {
            $this->timeDataCollector->startMeasure($profile->getName(), 'template ' . $profile->getName());
        }
        parent::enter($profile);
    }

    public function leave(Twig_Profiler_Profile $profile)
    {
        parent::leave($profile);
        if ($this->timeDataCollector && $profile->isTemplate()) {
            $this->timeDataCollector->stopMeasure($profile->getName());
        }
    }
}
