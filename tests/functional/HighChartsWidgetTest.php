<?php

namespace tests;

use dominus77\highcharts\HighChartsWidget;

/**
 * Class HighChartsWidgetTest
 * @package tests
 */
class HighChartsWidgetTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testRunChart()
    {
        $chart = HighChartsWidget::widget([
            'enable3d' => true,
            'enableMore' => true,
            'clientOptions' => [
                'theme' => 'avocado',
                'chart' => [
                    'type' => 'bar'
                ],
                'title' => [
                    'text' => 'Fruit Consumption'
                ],
                'xAxis' => [
                    'categories' => [
                        'Apples',
                        'Bananas',
                        'Oranges'
                    ]
                ],
                'yAxis' => [
                    'title' => [
                        'text' => 'Fruit eaten'
                    ]
                ],
                'series' => [
                    ['name' => 'Jane', 'data' => [1, 0, 4]],
                    ['name' => 'John', 'data' => [5, 7, 3]]
                ]
            ]
        ]);
        $this->assertContains('', $chart);
    }
}
