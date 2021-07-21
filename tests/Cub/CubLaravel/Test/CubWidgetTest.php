<?php namespace Cub\CubLaravel\Test;

use CubWidget;

class CubWidgetTest extends CubLaravelTestCase
{
    public function testHeaderScriptReturnsScript()
    {
        $expected = '<script>'
            . '(function(){'
            . 'if (document.getElementById("cub-widget-script")) {return;}'
            . 'var firstScript = document.getElementsByTagName("script")[0];'
            . 'var cubJs = document.createElement("script");'
            . 'cubJs.id = "cub-widget-script";'
            . 'cubJs.src = "//lid.cdn.lexipol.com/cub-widget.0.28.x.js";'
            . 'firstScript.parentNode.insertBefore(cubJs, firstScript);'
            . '}());'
            . '</script>';

        self::assertEquals($expected, CubWidget::headerScript());
    }

    public function testFooterScriptReturnsScript()
    {
        $expected = '<script>'
                  . 'var cubAsyncInit = function(cub) {'
                  . 'cub.start({'
                  . 'apiKey: "'.config('cub.public_key').'"'
                  . '});'
                  . '};'
                  . '</script>';

        self::assertEquals($expected, CubWidget::footerScript());
    }

    public function testMenuReturnsMenuDiv()
    {
        self::assertEquals('<div id="cub-widget-menu"></div>', CubWidget::menu());
    }

    public function testAppReturnsAppDiv()
    {
        self::assertEquals('<div id="cub-widget-app"></div>', CubWidget::app());
    }
}
