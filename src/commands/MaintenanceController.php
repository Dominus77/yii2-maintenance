<?php

namespace dominus77\maintenance\commands;

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
     * MaintenanceController constructor.
     * @param string $id
     * @param Module $module
     * @param StateInterface $state
     * @param array $config
     */
    public function __construct($id, Module $module, StateInterface $state, array $config = [])
    {
        $this->state = $state;
        $this->exampleData = $this->exampleDateFormat();
        parent::__construct($id, $module, $config);
    }


    /**
     * Options
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
     * Maintenance status or commands
     */
    public function actionIndex()
    {
        $stateForm = new FileStateForm();
        $subscribeForm = new SubscribeForm();

        $enabledMode = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_RED);
        $disabledMode = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_GREEN);

        $enabled = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_GREEN);
        $disabled = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_RED);

        if ($this->state->isEnabled()) {
            $datetime = $stateForm->getDateTime();

            $message = Maintenance::t('app', 'Maintenance Mode has been {:status}', [
                ':status' => $enabledMode
            ]);
            $this->stdout($message . PHP_EOL);

            $message = Maintenance::t('app', 'on until {:datetime}', [
                ':datetime' => $datetime
            ]);
            $this->stdout($message . PHP_EOL);

            $message = Maintenance::t('app', '{n, plural, =0{No followers} =1{Total one follower} other{Total # followers}}.', [
                'n' => count($subscribeForm->getEmails())
            ]);
            $this->stdout($message . PHP_EOL);
            $this->stdout(PHP_EOL);

            $status = $stateForm->isTimer() ? $enabled : $disabled;
            $message = Maintenance::t('app', 'Count Down: {:status}', [
                ':status' => $status
            ]);
            $this->stdout($message . PHP_EOL);

            $status = $stateForm->isSubscribe() ? $enabled : $disabled;
            $message = Maintenance::t('app', 'Subscription form: {:status}', [
                ':status' => $status
            ]);
            $this->stdout($message . PHP_EOL);

            $message = Maintenance::t('app', 'You can update the maintenance mode.');
            $this->stdout(PHP_EOL . $message . PHP_EOL);

            $message = Maintenance::t('app', "Use:\nphp yii maintenance/update --option='value'\n");
            $this->stdout($message . PHP_EOL);
            $this->getOptionsTable();
        } else {
            $message = Maintenance::t('app', 'Maintenance Mode has been {:status}', [
                ':status' => $disabledMode
            ]);
            $this->stdout($message . PHP_EOL);

            $this->stdout("\nMaintenance Mode enable.\n");
            $this->stdout("Use:\nphp yii maintenance/enable\nto enable maintenance mode.\n");

            $this->stdout("\nAlso maintenance Mode enable set to date and time.\n");
            $this->stdout("Use:\nphp yii maintenance/enable --date=\"$this->exampleData\"\nto enable maintenance mode to $this->exampleData.\n");
            $this->stdout("Note:\nThis date and time not disable maintenance mode\n");

            $this->stdout("\nMaintenance Mode update date and time.\n");
            $this->stdout("Use:\nphp yii maintenance/update --date=\"$this->exampleData\"\nto update maintenance mode to $this->exampleData.\n");
            $this->stdout("Note:\nThis date and time not disable maintenance mode\n");

            $this->stdout("\nSubscribers to whom messages will be sent after turning off the mode maintenance\n");
            $this->stdout("Use:\nphp yii maintenance/followers\nto show followers.\n");

            $this->stdout("\nMaintenance Mode disable.\n");
            $this->stdout("Use:\nphp yii maintenance/disable\nto disable maintenance mode.\n");
        }
    }

    /**
     * Enable maintenance mode
     */
    public function actionEnable()
    {
        $stateForm = new FileStateForm();
        if (!$this->state->isEnabled()) {
            $stateForm->mode = Maintenance::STATUS_CODE_MAINTENANCE;
            $stateForm = $this->setFileStateForm($stateForm);
            $this->setDefaultValue($stateForm);
            if ($stateForm->validate()) {
                $stateForm->save();
            }
        }
        $datetime = $stateForm->getDateTime();
        $enabled = $this->ansiFormat('ENABLED', Console::FG_RED);
        $this->stdout("Maintenance Mode has been $enabled\n");
        $this->stdout("on until $datetime\n");

        $this->stdout("\nMaintenance Mode update date and time.\n");
        $this->stdout("Use:\nphp yii maintenance/update --date=\"$this->exampleData\"\nto update maintenance mode to $this->exampleData.\n");
        $this->stdout("Note:\nThis date and time not disable maintenance mode\n");

        $this->stdout("\nSubscribers to whom messages will be sent after turning off the mode maintenance\n");
        $this->stdout("Use:\nphp yii maintenance/followers\nto show followers.\n");

        $this->stdout("\nMaintenance Mode disable.\n");
        $this->stdout("Use:\nphp yii maintenance/disable\nto disable maintenance mode.\n");
    }

    /**
     * Update date and time maintenance mode
     */
    public function actionUpdate()
    {
        $stateForm = new FileStateForm();
        if ($this->state->isEnabled()) {
            $stateForm->mode = Maintenance::STATUS_CODE_MAINTENANCE;
            $stateForm = $this->setFileStateForm($stateForm);
            if ($stateForm->validate()) {
                $stateForm->save();

                $updated = $this->ansiFormat(Maintenance::t('app', 'UPDATED'), Console::FG_GREEN);
                $message = Maintenance::t('app', 'Maintenance Mode has been {:status}', [
                    ':status' => $updated
                ]);
                $this->stdout($message . PHP_EOL);

            } else {
                $this->stdout("Not specified what to update\n");
                $this->stdout("\nUse:\n");
                $this->stdout("\nphp yii maintenance/update --date=\"$this->exampleData\"\nto update maintenance mode to $this->exampleData.\n");
                $this->stdout("\nphp yii maintenance/update --title=\"Maintenance\"\nto update maintenance mode title.\n");
                $this->stdout("\nphp yii maintenance/update --content=\"Maintenance\"\nto update maintenance mode text content.\n");
                $this->stdout("\nphp yii maintenance/update --subscribe=true\nto enable subscribe form for maintenance mode.\n");
                $this->stdout("\nphp yii maintenance/update --timer=true\nto enable count down timer form for maintenance mode.\n");
            }
        } else {
            $this->stdout("Maintenance Mode not enable!\n");

            $this->stdout("Use:\nphp yii maintenance/enable\nto enable maintenance mode.\n");

            $this->stdout("\nAlso maintenance Mode enable set to date and time.\n");
            $this->stdout("Use:\nphp yii maintenance/enable --date=\"$this->exampleData\"\nto enable maintenance mode to $this->exampleData.\n");
            $this->stdout("Note:\nThis date and time not disable maintenance mode\n");
        }
    }

    /**
     * Disable maintenance mode
     */
    public function actionDisable()
    {
        $stateForm = new FileStateForm();
        $this->stdout("Maintenance Mode has been disabled.\n");
        if ($stateForm->isEnabled()) {
            $stateForm->disable();
            $subscribeForm = new SubscribeForm();
            $result = $subscribeForm->send();
            if ($result || $result === 0) {
                $this->stdout("Notified ($result) subscribers.\n");
            }
        }

        $this->stdout("\nUse:\nphp yii maintenance/enable\nto enable maintenance mode.\n");

        $this->stdout("\nAlso maintenance Mode enable set to date and time.\n");
        $this->stdout("Use:\nphp yii maintenance/enable --date=\"$this->exampleData\"\nto enable maintenance mode to $this->exampleData.\n");
        $this->stdout("Note:\nThis date and time not disable maintenance mode\n");
    }

    /**
     * Show subscribers to whom messages
     */
    public function actionFollowers()
    {
        $stateForm = new FileStateForm();
        $subscribeForm = new SubscribeForm();
        if (!$stateForm->isEnabled()) {
            $this->stdout("Maintenance Mode not enable!\n");

            $this->stdout("\nUse:\nphp yii maintenance/enable\nto enable maintenance mode.\n");

            $this->stdout("\nAlso maintenance Mode enable set to date and time.\n");
            $this->stdout("Use:\nphp yii maintenance/enable --date=\"$this->exampleData\"\nto enable maintenance mode to $this->exampleData.\n");
            $this->stdout("Note:\nThis date and time not disable maintenance mode\n");
        } else if ($emails = $subscribeForm->getEmails()) {
            $this->stdout('Total (' . count($emails) . ') followers:' . PHP_EOL);
            foreach ($emails as $email) {
                $this->stdout($email . PHP_EOL);
            }
        } else {
            $this->stdout("No followers\n");
        }
    }

    public function actionOptions()
    {
        $this->getOptionsTable();
    }

    /**
     * Options and aliases
     */
    public function getOptionsTable()
    {
        $this->stdout('---------------------------------------------' . PHP_EOL);
        $this->stdout('|   Option    | Alias | Value               |' . PHP_EOL);
        $this->stdout('|=============|=======|=====================|' . PHP_EOL);
        $this->stdout('| --date      | -d    | ' . $this->exampleDateFormat() . ' |' . PHP_EOL);
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

