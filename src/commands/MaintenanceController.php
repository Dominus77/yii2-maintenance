<?php

namespace dominus77\maintenance\commands;

use Yii;
use dominus77\maintenance\BackendMaintenance;
use yii\helpers\Console;
use yii\console\Controller;
use yii\base\Module;
use dominus77\maintenance\Maintenance;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\models\SubscribeForm;
use dominus77\maintenance\interfaces\StateInterface;

/**
 * Maintenance mode
 * @package dominus77\maintenance\commands
 *
 * @property FileStateForm $defaultValue
 * @property FileStateForm $fileStateForm
 */
class MaintenanceController extends Controller
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
     * @var mixed string
     */
    protected $exampleData;

    /**
     * @var FileStateForm
     */
    protected $stateForm;
    /**
     * @var SubscribeForm
     */
    protected $subscribeForm;
    /**
     * @var string
     */
    protected $enabled;
    /**
     * @var string
     */
    protected $disabled;
    /**
     * @var string
     */
    protected $enabledMode;
    /**
     * @var string
     */
    protected $updatedMode;
    protected $notUpdatedMode;
    /**
     * @var string
     */
    protected $disabledMode;

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
        $this->exampleData = $this->exampleDateFormat();
        $this->stateForm = new FileStateForm();
        $this->subscribeForm = new SubscribeForm();
        $this->enabledMode = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_RED);
        $this->updatedMode = $this->ansiFormat(Maintenance::t('app', 'UPDATED'), Console::FG_GREEN);
        $this->notUpdatedMode = $this->ansiFormat(Maintenance::t('app', 'NOT UPDATED'), Console::FG_YELLOW);
        $this->disabledMode = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_GREEN);
        $this->enabled = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_GREEN);
        $this->disabled = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_RED);
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
        if ($this->state->isEnabled()) {
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
        if ($this->state->isEnabled()) {
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

                $this->renderOptionsTable();
            } else {
                $status = $this->notUpdatedMode;
                $this->renderMaintenanceModeHasBeenStatus($status);
                $this->stdout(PHP_EOL);

                $this->renderUpdateMaintenanceMode();
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
     * lcfirst()
     * @param $str string
     * @return string
     */
    protected function mb_lcfirst($str = '')
    {
        if (is_string($str) && !empty($str)) {
            $charset = Yii::$app->charset;
            $first = mb_substr($str, 0, 1, $charset);
            $last = mb_substr($str, 1);
            $first = mb_strtolower($first, $charset);
            $last = mb_strtolower($last, $charset);
            return $first . $last;
        }
        return $str;
    }

    /**
     * Render is Disabled
     */
    public function renderGroupDisabled()
    {
        $status = $this->disabledMode;
        $this->renderMaintenanceModeHasBeenStatus($status);
        $this->stdout(PHP_EOL);

        $this->renderEnableMaintenanceMode();
        $this->stdout(PHP_EOL);

        $this->renderOptionsTable();
    }

    /**
     * Render is Enabled
     */
    public function renderGroupEnabled()
    {
        $status = $this->enabledMode;
        $this->renderMaintenanceModeHasBeenStatus($status);
        $this->stdout(PHP_EOL);

        $this->renderOnUntilDateTime();
        $this->stdout(PHP_EOL);

        $this->renderSubscriptionInfo();
        $this->stdout(PHP_EOL);
        $this->stdout(PHP_EOL);

        $this->renderCountDownStatus();
        $this->stdout(PHP_EOL);

        $this->renderSubscriptionFormStatus();
        $this->stdout(PHP_EOL);

        $this->renderDisableAndSubscribe();
        $this->stdout(PHP_EOL);

        $this->renderUpdateMaintenanceMode();
        $this->stdout(PHP_EOL);

        $this->renderOptionsTable();
    }

    /**
     * Notified 2 subscribers.
     *
     * @param $count int
     */
    public function renderNotifiedSubscribers($count = 0)
    {
        $this->stdout(Maintenance::t('app', '{n, plural, =0{No subscribers} =1{Notified one subscriber} other{Notified # subscribers}}.', [
            'n' => $count
        ]));
    }

    /**
     * Maintenance Mode has been ENABLED/DISABLED
     *
     * @param $status string
     */
    public function renderMaintenanceModeHasBeenStatus($status)
    {
        $message = Maintenance::t('app', 'Maintenance Mode has been {:status}', [
            ':status' => $status,
        ]);
        $this->stdout($message);
    }

    /**
     * on until 09-03-2020 11:15:04
     */
    public function renderOnUntilDateTime()
    {
        $datetime = $this->stateForm->getDateTime();
        $message = Maintenance::t('app', 'on until {:datetime}', [
            ':datetime' => $datetime
        ]);
        $this->stdout($message);
    }

    /**
     * Total 2 followers.
     */
    public function renderSubscriptionInfo()
    {
        $message = Maintenance::t('app', '{n, plural, =0{No subscribers} =1{Total one subscriber} other{Total # subscribers}}.', [
            'n' => count($this->subscribeForm->getEmails())
        ]);
        $this->stdout($message);
    }

    /**
     * Count Down: ENABLED
     */
    public function renderCountDownStatus()
    {
        $status = $this->stateForm->isTimer() ? $this->enabled : $this->disabled;
        $message = Maintenance::t('app', 'Count Down: {:status}', [
            ':status' => $status
        ]);
        $this->stdout($message);
    }

    /**
     * Subscription form: ENABLED
     */
    public function renderSubscriptionFormStatus()
    {
        $status = $this->stateForm->isSubscribe() ? $this->enabled : $this->disabled;
        $message = Maintenance::t('app', 'Subscription form: {:status}', [
            ':status' => $status
        ]);
        $this->stdout($message);
    }

    /**
     * To turn off and notify subscribers,
     * use:
     * php yii maintenance/disable
     */
    public function renderDisableAndSubscribe()
    {
        $message = Maintenance::t('app', "To turn off and notify subscribers,\nuse:");
        $this->stdout(PHP_EOL . $message . PHP_EOL);
        $message = 'php yii maintenance/disable';
        $this->stdout($message);
    }

    /**
     * To enable the maintenance mode,
     * use:
     * php yii maintenance/enable --option='value'
     */
    public function renderEnableMaintenanceMode()
    {
        $message = Maintenance::t('app', "To enable the maintenance mode,\nuse:");
        $this->stdout(PHP_EOL . $message . PHP_EOL);

        $option = Maintenance::t('app', 'Option');
        $value = Maintenance::t('app', 'Value');
        $message = Maintenance::t('app', "php yii maintenance/enable --{:option}1='{:value}1' --{:option}2='{:value}2' ...", [
            ':option' => $this->mb_lcfirst(trim($option)),
            ':value' => $this->mb_lcfirst(trim($value))
        ]);
        $this->stdout($message);
    }

    /**
     * To update the maintenance mode,
     * use:
     * php yii maintenance/update --option='value'
     */
    public function renderUpdateMaintenanceMode()
    {
        $message = Maintenance::t('app', "To update the maintenance mode,\nuse:");
        $this->stdout(PHP_EOL . $message . PHP_EOL);

        $option = Maintenance::t('app', 'Option');
        $value = Maintenance::t('app', 'Value');
        $message = Maintenance::t('app', "php yii maintenance/update --{:option}1='{:value}1' --{:option}2='{:value}2' ...", [
            ':option' => $this->mb_lcfirst(trim($option)),
            ':value' => $this->mb_lcfirst(trim($value))
        ]);
        $this->stdout($message);
    }

    /**
     * Options and aliases
     */
    public function renderOptionsTable()
    {
        $option = Maintenance::t('app', 'Option');
        $alias = Maintenance::t('app', 'Alias');
        $value = Maintenance::t('app', 'Value');
        $exampleDat = $this->exampleDateFormat();
        $this->stdout(PHP_EOL);
        $this->stdout('---------------------------------------------' . PHP_EOL);
        $this->stdout('|   ' . $option . '    | ' . $alias . ' | ' . $value . '            |' . PHP_EOL);
        $this->stdout('|=============|=======|=====================|' . PHP_EOL);
        $this->stdout('| --date      | -d    | ' . $exampleDat . ' |' . PHP_EOL);
        $this->stdout('| --title     | -t    | string              |' . PHP_EOL);
        $this->stdout('| --content   | -c    | string              |' . PHP_EOL);
        $this->stdout('| --subscribe | -s    | true/false          |' . PHP_EOL);
        $this->stdout('| --timer     | -tm   | true/false          |' . PHP_EOL);
        $this->stdout('---------------------------------------------' . PHP_EOL);
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
            $stateForm->title = BackendMaintenance::t('app', $stateForm->getDefaultTitle());
        }
        if ($stateForm->text === null && $this->content === null) {
            $stateForm->text = BackendMaintenance::t('app', $stateForm->getDefaultText());
        }
    }

    /**
     * Example format date time
     * @return mixed
     */
    protected function exampleDateFormat()
    {
        return date($this->state->getDateFormat());
    }
}

