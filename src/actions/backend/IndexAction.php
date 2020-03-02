<?php

namespace dominus77\maintenance\actions\backend;

use Yii;
use yii\base\Action;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\BaseMaintenance;

/**
 * Class IndexAction
 * @package dominus77\maintenance\actions
 *
 * @property array $viewRenderParams
 */
class IndexAction extends Action
{
    /** @var string */
    public $defaultName;

    /** @var string */
    public $layout;

    /** @var string */
    public $view;

    /** @var array */
    public $params = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->defaultName === null) {
            $this->defaultName = BaseMaintenance::t('app', 'Mode site');
        }
    }

    /**
     * @return string|Response
     */
    public function run()
    {
        if ($this->layout !== null) {
            $this->controller->layout = $this->layout;
        }
        if ($this->view !== null) {
            $this->controller->view = $this->view;
        }
        $model = new FileStateForm();
        if (($post = Yii::$app->request->post()) && $model->load($post) && $model->validate() && $model->save()) {
            return $this->controller->refresh();
        }
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams($model));
    }

    /**
     * @param $model FileStateForm
     * @return array
     */
    protected function getViewRenderParams($model)
    {
        $listDataProvider = new ArrayDataProvider([
            'allModels' => $model->followers,
            'pagination' => [
                'pageSize' => 18
            ],
        ]);

        return [
            'name' => $this->defaultName,
            'model' => $model,
            'isEnable' => $model->isEnabled(),
            'listDataProvider' => $listDataProvider
        ];
    }
}
