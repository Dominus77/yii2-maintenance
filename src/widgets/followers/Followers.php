<?php

namespace dominus77\maintenance\widgets\followers;

use Exception;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ListView;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\widgets\followers\assets\FollowersAsset;

/**
 * Class Followers
 * @package dominus77\maintenance\widgets\followers
 *
 * @property ArrayDataProvider $listDataProvider
 */
class Followers extends Widget
{
    /**
     * @var bool
     */
    public $status;

    /**
     * @var FileStateForm
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
        if ($this->model instanceof FileStateForm) {
            $this->status = $this->status ?: true;
        }
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
                'dataProvider' => $this->getListDataProvider(),
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
    protected function getListDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->model->followers,
            'pagination' => [
                'pageSize' => $this->pageSize
            ],
        ]);
    }

    /**
     * @return FileStateForm
     */
    protected function findModel()
    {
        if ($this->model === null) {
            return new FileStateForm();
        }
        return $this->model;
    }
}
