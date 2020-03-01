<?php

namespace dominus77\maintenance\widgets\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Class SubscribeFormAsset
 * @package dominus77\maintenance\widgets\assets
 */
class SubscribeFormAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->sourcePath = __DIR__ . '/src';

        $this->js = [
            'js/script.js'
        ];

        $this->publishOptions = [
            'forceCopy' => YII_ENV_DEV ? true : false
        ];
    }

    /**
     * @var array
     */
    public $depends = [
        JqueryAsset::class,
    ];
}
