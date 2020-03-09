<?php

namespace dominus77\maintenance\commands;

use yii\base\Module;
use dominus77\maintenance\base\ConsoleController;
use dominus77\maintenance\Maintenance;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\interfaces\StateInterface;

/**
 * Maintenance mode
 * @package dominus77\maintenance\commands
 *
 * @property FileStateForm $defaultValue
 * @property FileStateForm $fileStateForm
 */
class MaintenanceController extends ConsoleController
{
    /**
     * Date
     * @var string
     */
    public $date;
    /**
     * Title
     * @var string
     */
    public $title;
    /**
     * Content
     * @var string
     */
    public $content;
    /**
     * @var string
     */
    public $subscribe;

    /**
     * @var string
     */
    public $timer;

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * MaintenanceController constructor.
     *
     * @param string $id
     * @param Module $module
     * @param StateInterface $state
     * @param array $config
     */
    public function __construct($id, Module $module, StateInterface $state, array $config = [])
    {
        $this->state = $state;
        parent::__construct($id, $module, $config);
    }


    /**
     * Options
     *
     * @param string $actionId
     * @return array|string[]
     */
    public function options($actionId)
    {
        return [
            'date',
            'title',
            'content',
            'subscribe',
            'timer'
        ];
    }

    /**
     * Aliases
     *
     * @return array
     */
    public function optionAliases()
    {
        return [
            'd' => 'date',
            't' => 'title',
            'c' => 'content',
            's' => 'subscribe',
            'tm' => 'timer'
        ];
    }

    /**
     * Maintenance status
     */
    public function actionIndex()
    {
        if ($this->stateForm->isEnabled()) {
            $this->renderGroupEnabled();
        } else {
            $this->renderGroupDisabled();
        }
    }

    /**
     * Enable maintenance mode
     */
    public function actionEnable()
    {
        $stateForm = new FileStateForm();
        if (!$this->stateForm->isEnabled()) {
            $stateForm->mode = Maintenance::STATUS_CODE_MAINTENANCE;
            $stateForm = $this->setFileStateForm($stateForm);
            $this->setDefaultValue($stateForm);
            if ($stateForm->validate()) {
                $stateForm->save();
            }
        }
        $this->renderGroupEnabled();
    }

    /**
     * Disable maintenance mode and send notify
     */
    public function actionDisable()
    {
        $status = $this->disabledMode;
        $this->renderMaintenanceModeHasBeenStatus($status);
        $this->stdout(PHP_EOL);
        if ($this->stateForm->isEnabled()) {
            $this->stateForm->disable();
            $result = $this->subscribeForm->send();
            if ($result || $result === 0) {
                $this->renderNotifiedSubscribers($result);
                $this->stdout(PHP_EOL);
            }
        }
        $this->renderEnableMaintenanceMode();
        $this->stdout(PHP_EOL);
        $this->renderOptionsTable();
    }

    /**
     * Update date and time maintenance mode
     */
    public function actionUpdate()
    {
        if ($this->stateForm->isEnabled()) {
            $this->stateForm->mode = Maintenance::STATUS_CODE_MAINTENANCE;
            $stateForm = $this->setFileStateForm($this->stateForm);

            if ($stateForm->validate()) {
                $stateForm->save();

                $status = $this->updatedMode;
                $this->renderMaintenanceModeHasBeenStatus($status);
                $this->stdout(PHP_EOL);

                $this->renderOnUntilDateTime();
                $this->stdout(PHP_EOL);
                $this->stdout(PHP_EOL);

                $this->renderCountDownStatus();
                $this->stdout(PHP_EOL);
                $this->renderSubscriptionFormStatus();
                $this->stdout(PHP_EOL);

                $this->renderUpdateMaintenanceMode();
                $this->stdout(PHP_EOL);
                $this->stdout(PHP_EOL);

                $this->renderOptionsTable();
            } else {
                $status = $this->notUpdatedMode;
                $this->renderMaintenanceModeHasBeenStatus($status);
                $this->stdout(PHP_EOL);

                $this->renderUpdateMaintenanceMode();
                $this->stdout(PHP_EOL);
                $this->stdout(PHP_EOL);

                $this->renderOptionsTable();
            }
        } else {
            $this->renderGroupDisabled();
        }
    }

    /**
     * Show subscribers to whom messages
     */
    public function actionSubscribers()
    {
        if (!$this->stateForm->isEnabled()) {
            $this->renderGroupDisabled();
        } else if ($emails = $this->subscribeForm->getEmails()) {
            $status = $this->enabledMode;
            $this->renderMaintenanceModeHasBeenStatus($status);
            $this->stdout(PHP_EOL);

            $this->renderOnUntilDateTime();
            $this->stdout(PHP_EOL);

            $this->renderSubscriptionInfo();
            $this->stdout(PHP_EOL);
            $this->stdout(PHP_EOL);

            foreach ($emails as $email) {
                $this->stdout($email . PHP_EOL);
            }
        } else {
            $status = $this->enabledMode;
            $this->renderMaintenanceModeHasBeenStatus($status);
            $this->stdout(PHP_EOL);
            $this->renderOnUntilDateTime();
            $this->stdout(PHP_EOL);

            $this->stdout(Maintenance::t('app', 'No subscribers'));
            $this->stdout(PHP_EOL);
        }
    }

    /**
     * Options
     */
    public function actionOptions()
    {
        $this->renderOptionsTable();
    }

    /**
     * @param FileStateForm $stateForm
     * @return FileStateForm
     */
    protected function setFileStateForm(FileStateForm $stateForm)
    {
        if ($this->date) {
            $stateForm->date = $this->date;
        }
        if ($this->title) {
            $stateForm->title = $this->title;
        }
        if ($this->content) {
            $stateForm->text = $this->content;
        }
        if ($this->subscribe) {
            $stateForm->subscribe = $this->subscribe === 'true';
        }
        if ($this->timer) {
            $stateForm->countDown = $this->timer === 'true';
        }
        return $stateForm;
    }

    /**
     * @param FileStateForm $stateForm
     */
    protected function setDefaultValue(FileStateForm $stateForm)
    {
        if ($stateForm->title === null && $this->title === null) {
            $stateForm->title = Maintenance::t('app', $stateForm->getDefaultTitle());
        }
        if ($stateForm->text === null && $this->content === null) {
            $stateForm->text = Maintenance::t('app', $stateForm->getDefaultText());
        }
    }
}

