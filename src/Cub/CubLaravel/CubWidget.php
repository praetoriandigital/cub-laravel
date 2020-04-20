<?php namespace Cub\CubLaravel;

use Config;

class CubWidget
{
    /**
     * Get Cub Widget header script
     *
     * @return string
     */
    public function headerScript()
    {
        $script = '<script>'
                . '(function(){'
                . 'if (document.getElementById("cub-widget-script")) {return;}'
                . 'var firstScript = document.getElementsByTagName("script")[0];'
                . 'var cubJs = document.createElement("script");'
                . 'cubJs.id = "cub-widget-script";'
                . 'cubJs.src = "//lid.cdn.lexipol.com/cub-widget.0.28.x.js";'
                . 'firstScript.parentNode.insertBefore(cubJs, firstScript);'
                . '}());'
                . '</script>';

        return $script;
    }

    /**
     * Get Cub Widget footer script
     *
     * @return string
     */
    public function footerScript()
    {
        $script = '<script>'
                . 'var cubAsyncInit = function(cub) {'
                . 'cub.start({'
                . 'apiKey: "'.Config::get('cub::config.public_key').'"'
                . '});'
                . '};'
                . '</script>';

        return $script;
    }

    /**
     * Get Cub Widget menu element
     *
     * @return string
     */
    public function menu()
    {
        return '<div id="cub-widget-menu"></div>';
    }

    /**
     * Get Cub Widget app element
     *
     * @return string
     */
    public function app()
    {
        return '<div id="cub-widget-app"></div>';
    }
}
