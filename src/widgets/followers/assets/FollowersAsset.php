<?php

namespace dominus77\maintenance\widgets\followers\assets;

use yii\web\AssetBundle;

/**
 * Class FollowersAsset
 * @package dominus77\maintenance\widgets\followers\assets
 */
class FollowersAsset extends AssetBundle
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
            'css/style.css'
        ];

        $this->publishOptions = [
            'forceCopy' => YII_ENV_DEV ? true : false
        ];
    }
}
