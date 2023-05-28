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

/**
 * Collects info about the current request
 */
class FilesCollector extends DataCollector implements Renderable, AssetProvider

{
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


    public function collect()
    {
        $files = get_included_files();
        $coreFiles = [];
        $userFiles = [];

        foreach ($files as $file) {

            if (strpos($file, AXM_PATH) !== false || strpos($file, VENDOR_PATH) !== false  ) {
                $coreFiles[] = [
                    'name' => basename($file),
                    'path' => $file
                ];
            } else {
                $userFiles[] = [
                    'name' => basename($file),
                    'path' => $file
                ];
            }
        }

        $cFiles = $this->formatFiles($coreFiles);
        $uFiles = $this->formatFiles($userFiles);

        return [
            'Total Files'      => count($files),
            'Total Files Core' => count($coreFiles),
            'Total Files User' => count($userFiles),
            'Core Files'       => $cFiles,
            'User Files'       => $uFiles,
        ];
    }


    protected function formatFiles($files)
    {
        if ($this->isHtmlVarDumperUsed()) {
            return $this->getVarDumper()->renderVar($files);
        } else if (!is_string($files)) {
            return $this->getDataFormatter()->formatVar($files);
        }

        return $files;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'files';
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
            'files'  => [
                'icon'     => 'file',
                'widget'  => $widget,
                'map'     => 'files',
                'default' => '{}'
            ],
            'files:badge' => [
                'map'     => 'files.Total Files',
                'default' => 0
            ]
        ];
    }
}
