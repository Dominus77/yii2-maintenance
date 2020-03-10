<?php

namespace dominus77\maintenance\widgets\followers;

use Exception;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ListView;
use dominus77\maintenance\models\SubscribeForm;
use dominus77\maintenance\widgets\followers\assets\FollowersAsset;

/**
 * Class FollowersWidget
 * @package dominus77\maintenance\widgets\followers
 *
 * @property ArrayDataProvider $dataProvider
 */
class FollowersWidget extends Widget
{
    /**
     * @var bool
     */
    public $status = true;

    /**
     * @var SubscribeForm
     */
    public $model;

    /**
     * @var int
     */
    public $pageSize = 18;

    /**
     * ListView options
     * @var array
     */
    public $options;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->model = $this->findModel();
        $options = [
            'tag' => 'div',
            'class' => 'list-wrapper',
            'id' => 'list-followers',
        ];
        $this->options = ArrayHelper::merge($this->options, $options);
    }

    /**
     * @return string|void
     * @throws Exception
     */
    public function run()
    {
        if ($this->status === true) {
            $this->registerResource();
            echo ListView::widget([
                'dataProvider' => $this->getDataProvider(),
                'layout' => "{summary}\n{items}\n{pager}",
                'options' => $this->options,
                'itemView' => static function ($model) {
                    return Html::a($model['email'], 'mailto:' . $model['email']);
                }
            ]);
        }
    }

    /**
     * Register resource
     */
    protected function registerResource()
    {
        $view = $this->getView();
        FollowersAsset::register($view);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->model->getFollowers(),
            'pagination' => [
                'pageSize' => $this->pageSize
            ],
        ]);
    }

    /**
     * @return SubscribeForm
     */
    protected function findModel()
    {
        if ($this->model === null) {
            return new SubscribeForm();
        }
        return $this->model;
    }
}
