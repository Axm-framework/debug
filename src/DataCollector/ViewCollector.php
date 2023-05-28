<?php

namespace Axm\Debug\DataCollector;

use Axm\Debug\DataFormatter\SimpleFormatter;
use Axm\Debug\DataCollector\DataCollector;
use Axm\Debug\DataCollector\Renderable;
use Axm\Debug\Bridge\Twig\TwigCollector;

use Axm;
use Axm\Views\View;
use InvalidArgumentException;

class ViewCollector extends DataCollector implements Renderable
{
    protected $name;
    protected $templates = [];
    protected $collect_data;
    protected $exclude_paths;

    /**
     * A list of known editor strings.
     *
     * @var array
     */
    protected $editors = [
        'sublime'                => 'subl://open?url=file://%file&line=%line',
        'textmate'               => 'txmt://open?url=file://%file&line=%line',
        'emacs'                  => 'emacs://open?url=file://%file&line=%line',
        'macvim'                 => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm'               => 'phpstorm://open?file=%file&line=%line',
        'idea'                   => 'idea://open?file=%file&line=%line',
        'vscode'                 => 'vscode://file/%file:%line',
        'vscode-insiders'        => 'vscode-insiders://file/%file:%line',
        'vscode-remote'          => 'vscode://vscode-remote/%file:%line',
        'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%file:%line',
        'vscodium'               => 'vscodium://file/%file:%line',
        'nova'                   => 'nova://core/open/file?filename=%file&line=%line',
        'xdebug'                 => 'xdebug://%file@%line',
        'atom'                   => 'atom://core/open/file?filename=%file&line=%line',
        'espresso'               => 'x-espresso://open?filepath=%file&lines=%line',
        'netbeans'               => 'netbeans://open/?f=%file:%line',
    ];

    /**
     * Create a ViewCollector
     *
     * @param bool $collectData Collects view data when tru
     * @param string[] $excludePaths Paths to exclude from collection
     */
    public function __construct($collectData = true, $excludePaths = [])
    {
        $this->setDataFormatter(new SimpleFormatter());
        $this->templates     = [];
        $this->collect_data  = $collectData;
        $this->exclude_paths = $excludePaths;
    }

    public function getName()
    {
        return 'views';
    }

    /**
     * Get the editor href for a given file and line, if available.
     *
     * @param string $filePath
     * @param int    $line
     *
     * @throws InvalidArgumentException If editor resolver does not return a string
     *
     * @return null|string
     */
    protected function getEditorHref($filePath, $line)
    {
        $config = Axm::app()->config();
        $config->load(APP_PATH . '/Config/DebugBar.php');

        if (empty($config->editor)) {
            return null;
        }

        if (empty($this->editors[$config->editor])) {
            throw new InvalidArgumentException(
                'Unknown editor identifier: ' . $config->editor . '. Known editors:' .
                    implode(', ', array_keys($this->editors))
            );
        }

        $filePath = $this->replaceSitesPath($filePath);

        $url = str_replace(['%file', '%line'], [$filePath, $line], $this->editors[$config->editor]);

        return $url;
    }

    /**
     * Add a View instance to the Collector
     *
     * @param \Axm\View $view
     */
    public function addView(View $view)
    {
        $name = $view->getName();
        $path = $view->getPath();

        if (!is_object($path)) {

            $type = pathinfo($path, PATHINFO_EXTENSION);
        } else {
            $type = get_class($view);
            $path = '';
        }

        foreach ($this->exclude_paths as $excludePath) {
            if (strpos($path, $excludePath) !== false) {
                return;
            }
        }

        if (!$this->collect_data) {
            $params = array_keys($view->getData());
        } else {

            $data = [];
            foreach ($view->getData() as $key => $value) {
                $data[$key] = $this->getDataFormatter()->formatVar($value);
            }

            $params = $data;
        }

        $template = [
            'name'        => $path ? sprintf('%s (%s)', $name, $path) : $name,
            'param_count' => count($params),
            'params'      => $params,
            'type'        => $type,
            'editorLink'  => $this->getEditorHref($view->getPath(), 0),
        ];

        if ($this->getXdebugLink($path)) {
            $template['xdebug_link'] = $this->getXdebugLink(realpath($view->getPath()));
        }

        $this->templates[] = $template;
    }

    /**
     * 
     */
    // public function collect()
    // {
    //     $templates = $this->templates;

    //     return [
    //         'nb_templates' => count($templates),
    //         'templates'    => $templates,
    //     ];
    // }
    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect()
    {
        // $this->templateCount = $this->blockCount = $this->macroCount = 0;
        // $this->templates     = [];
        // $this->computeData($this->profile);

        // return [
        //     'nb_templates'                => $this->templateCount,
        //     'nb_blocks'                   => $this->blockCount,
        //     'nb_macros'                   => $this->macroCount,
        //     'templates'                   => $this->templates,
        //     'accumulated_render_time'     => $this->profile->getDuration(),
        //     'accumulated_render_time_str' => $this->getDataFormatter()->formatDuration($this->profile->getDuration()),
        //     'memory_usage_str'            => $this->getDataFormatter()->formatBytes($this->profile->getMemoryUsage()),
        //     'callgraph'                   => $this->getHtmlCallGraph(),
        //     'badge'                       => implode(
        //         '/',
        //         [
        //             $this->templateCount,
        //             $this->blockCount,
        //             $this->macroCount,
        //         ]
        //     ),
        // ];
    }

    public function getWidgets()
    {
        return [
            'views' => [
                'icon'    => 'leaf',
                'tooltip' => 'Views',
                'widget'  => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map'     => 'views',
                'default' => '[]'
            ],
            'views:badge' => [
                'map'     => 'views.nb_templates',
                'default' => 0
            ]
        ];
    }


    /**
     * Replace remote path
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function replaceSitesPath($filePath)
    {
        $config = Axm::config();
        $config->load(AXM_PATH . '/Config/Debugbar.php');

        return str_replace($config->remote_sites_path, $config->local_sites_path, $filePath);
    }
}
