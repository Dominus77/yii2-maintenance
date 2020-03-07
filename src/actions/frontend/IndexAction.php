<?php

namespace dominus77\maintenance\actions\frontend;

use Yii;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\models\SubscribeForm;
use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\models\FileStateForm;
use yii\base\Action;
use Exception;

/**
 * Class IndexAction
 * @package dominus77\maintenance\actions\frontend
 *
 * @property array $viewRenderParams
 */
class IndexAction extends Action
{
    /** @var string */
    public $defaultName;

    /** @var string */
    public $defaultMessage;

    /** @var string */
    public $viewPath;

    /** @var string */
    public $view;

    /** @var array */
    public $params = [];

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->state = Yii::$container->get(StateInterface::class);

        if ($this->defaultMessage === null) {
            $this->defaultMessage = BackendMaintenance::t('app', $this->state->defaultContent);
        }

        if ($this->defaultName === null) {
            $this->defaultName = BackendMaintenance::t('app', $this->state->defaultTitle);
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function run()
    {
        $this->setViewPath();
        $this->setLayout();
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams());
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getViewRenderParams()
    {
        $subscribeForm = new SubscribeForm();
        $fileStateForm = new FileStateForm();
        return [
            'title' => $fileStateForm->title ?: $this->defaultName,
            'name' => $fileStateForm->title,
            'message' => $fileStateForm->text,
            'subscribeForm' => $subscribeForm,
            'fileStateForm' => $fileStateForm,
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
            $this->controller->setViewPath('@dominus77/maintenance/views/frontend/maintenance');
        }
    }

    /**
     * Set Layout
     */
    protected function setLayout()
    {
        if ($this->controller->layout === null) {
            $this->controller->layout = '@dominus77/maintenance/views/frontend/layouts/maintenance';
        }
    }
}
