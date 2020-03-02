<?php

namespace dominus77\maintenance\widgets\maintenance;

use yii\base\Widget;
use dominus77\maintenance\widgets\maintenance\assets\MaintenanceFormAsset;
use dominus77\maintenance\models\FileStateForm;
use yii\helpers\Json;

/**
 * Class MaintenanceForm
 * @package dominus77\maintenance\widgets\maintenance
 *
 * @property array $options
 */
class MaintenanceForm extends Widget
{
    /**
     * @var bool
     */
    public $status;

    /**
     * @var FileStateForm
     */
    public $model;

    public function init()
    {
        parent::init();
        $this->model = $this->findModel();
        if ($this->model instanceof FileStateForm) {
            $this->status = $this->status ?: true;
        }
    }

    /**
     * @return string|void
     */
    public function run()
    {
        if ($this->status === true) {
            $this->registerResource();
            echo $this->render('maintenance-form', ['model' => $this->model]);
        }
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            'modeOn' => FileStateForm::MODE_MAINTENANCE_ON,
            'modeOff' => FileStateForm::MODE_MAINTENANCE_OFF
        ];
    }

    /**
     * Register resource
     */
    protected function registerResource()
    {
        $view = $this->getView();
        MaintenanceFormAsset::register($view);
        $options = Json::encode($this->getOptions());
        $script = "            
            initMaintenanceForm({$options});
        ";
        $view->registerJs($script);
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
