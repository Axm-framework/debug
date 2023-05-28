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
 * Collects info about the current localization state
 */
class LocalizationCollector extends DataCollector implements Renderable
{
    private $domain;


    public function __construct()
    {
        $this->domain = textdomain('');
    }

    /**
     * 
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the current locale
     *
     * @return string
     */
    public function getLocale()
    {
        return Axm::app()->config()->defaultLocale ?? Axm::app()->getLocale();
    }

    /**
     * @return array
     */
    public function collect()
    {
        return [
            'locale' => $this->getLocale(),
            'domain' => $this->getDomain()
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'localization';
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        $lo = $this->getLocale();
        $do = $this->getDomain();

        return [
            'domain' => [
                'icon'    => 'bookmark',
                'map'     => 'localization.domain',
                'tooltip' => 'domain: ' . $do
            ],
            'locale' => [
                'icon'    => 'flag',
                'map'     => 'localization.locale',
                'tooltip' => 'locale: ' . $lo
            ]
        ];
    }
}
