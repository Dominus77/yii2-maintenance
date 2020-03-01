<?php

namespace dominus77\maintenance\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class MaintenanceAsset
 * @package dominus77\maintenance\assets
 */
class MaintenanceAsset extends AssetBundle
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

        $this->css = [
            'css/maintenance.css',
        ];

        $this->publishOptions = [
            'forceCopy' => YII_ENV_DEV ? true : false
        ];
    }

    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
    ];
}
