<?php

namespace dominus77\maintenance\widgets\subscribe;

use yii\base\Widget;
use dominus77\maintenance\models\SubscribeForm as SubscribeFormModel;
use dominus77\maintenance\widgets\subscribe\assets\SubscribeFormAsset;

/**
 * Class SubscribeForm
 * @package dominus77\maintenance\widgets\subscribe
 */
class SubscribeForm extends Widget
{
    /**
     * @var bool
     */
    public $status = true;

    /**
     * @var SubscribeFormModel
     */
    public $model;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->model = $this->model ?: new SubscribeFormModel();
    }

    /**
     * @return string|void
     */
    public function run()
    {
        if ($this->status === true) {
            $this->registerResource();
            echo $this->render('subscribe-form', ['model' => $this->model]);
        }
    }

    /**
     * Register resource
     */
    protected function registerResource()
    {
        $view = $this->getView();
        SubscribeFormAsset::register($view);
    }
}
