<?php

namespace tests;

use dominus77\maintenance\HighChartsAsset;
use yii\web\AssetBundle;

/**
 * Class HighchartsAssetTest
 * @package tests
 */
class HighchartsAssetTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function testRegisterAsset()
    {
        $view = $this->getView();
        $this->assertEmpty($view->assetBundles);
        HighChartsAsset::register($view);
        $this->assertCount(2, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles['dominus77\\highcharts\\HighChartsAsset']);
        $content = $view->renderFile('@tests/views/layouts/rawlayout.php');
        $this->assertContains('highcharts.src.js', $content);
    }
}
