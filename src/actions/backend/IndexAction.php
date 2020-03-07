<?php

namespace dominus77\maintenance\actions\backend;

use Yii;
use yii\base\Action;
use yii\web\Response;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\BackendMaintenance;
use yii\web\Session;

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
    public $viewPath;

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
            $this->defaultName = BackendMaintenance::t('app', 'Mode site');
        }
    }

    /**
     * @return string|Response
     */
    public function run()
    {
        $this->setViewPath();
        $model = new FileStateForm();
        if (($post = Yii::$app->request->post()) && $model->load($post) && $model->validate()) {
            $message = $model->isEnabled() ? BackendMaintenance::t('app', 'Maintenance mode successfully updated!') : '';
            $result = $model->save();
            $this->setMessage($message, $result);
            return $this->controller->refresh();
        }
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams($model));
    }

    /**
     * @param $message string
     * @param $result bool|int
     */
    protected function setMessage($message, $result)
    {
        /** @var Session $session */
        $session = Yii::$app->session;
        if (is_bool($result) && $result === true) {
            $session->setFlash(FileStateForm::MAINTENANCE_UPDATE_KEY, $message);
        }
        if (is_numeric($result)) {
            $session->setFlash(FileStateForm::MAINTENANCE_NOTIFY_SENDER_KEY, BackendMaintenance::t('app',
                '{n, plural, =0{no followers} =1{one message sent} other{# messages sent}}',
                ['n' => $result])
            );
        }
    }

    /**
     * @param $model FileStateForm
     * @return array
     */
    protected function getViewRenderParams($model)
    {
        return [
            'name' => $this->defaultName,
            'model' => $model,
        ];
    }

    /**
     * Set View Path
     */
    protected function setViewPath()
    {
        if ($this->viewPath !== null) {
            $this->controller->setViewPath($this->viewPath);
        } else {
            $this->controller->setViewPath('@dominus77/maintenance/views/backend/maintenance');
        }
    }
}
