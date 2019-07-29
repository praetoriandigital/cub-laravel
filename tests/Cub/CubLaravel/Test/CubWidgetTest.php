<?php namespace Cub\CubLaravel\Test;

use CubWidget;

class CubWidgetTest extends CubLaravelTestCase
{
    /** @test */
    function header_script_returns_script()
    {
        $expected = '<script>'
                  . '(function(){'
                  . 'if (document.getElementById("cub-widget-script")) {return;}'
                  . 'var firstScript = document.getElementsByTagName("script")[0];'
                  . 'var cubJs = document.createElement("script");'
                  . 'cubJs.id = "cub-widget-script";'
                  . 'cubJs.src = "//cub-praetorian.netdna-ssl.com/cub-widget.0.18.x.js";'
                  . 'firstScript.parentNode.insertBefore(cubJs, firstScript);'
                  . '}());'
                  . '</script>';

        $this->assertEquals(CubWidget::headerScript(), $expected);
    }

    /** @test */
    function footer_script_returns_script()
    {
        $expected = '<script>'
                  . 'var cubAsyncInit = function(cub) {'
                  . 'cub.start({'
                  . 'apiKey: "'.config('cub.public_key').'"'
                  . '});'
                  . '};'
                  . '</script>';

        $this->assertEquals(CubWidget::footerScript(), $expected);
    }

    /** @test */
    function menu_returns_menu_div()
    {
        $this->assertEquals(CubWidget::menu(), '<div id="cub-widget-menu"></div>');
    }

    /** @test */
    function app_returns_app_div()
    {
        $this->assertEquals(CubWidget::app(), '<div id="cub-widget-app"></div>');
    }
}
