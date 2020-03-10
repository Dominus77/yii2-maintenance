<?php

namespace dominus77\maintenance\base;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use dominus77\maintenance\Maintenance;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\models\SubscribeForm;

/**
 * Class ConsoleController
 * @package dominus77\maintenance\base
 */
class ConsoleController extends Controller
{
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
    /**
     * @var string
     */
    protected $notUpdatedMode;
    /**
     * @var string
     */
    protected $disabledMode;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->stateForm = new FileStateForm();
        $this->subscribeForm = new SubscribeForm();
        $this->exampleData = $this->exampleDateFormat();
        $this->setMessageVales();
    }

    /**
     * Set Message Values
     */
    protected function setMessageVales()
    {
        $this->enabledMode = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_RED);
        $this->updatedMode = $this->ansiFormat(Maintenance::t('app', 'UPDATED'), Console::FG_GREEN);
        $this->notUpdatedMode = $this->ansiFormat(Maintenance::t('app', 'NOT UPDATED'), Console::FG_YELLOW);
        $this->disabledMode = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_GREEN);
        $this->enabled = $this->ansiFormat(Maintenance::t('app', 'ENABLED'), Console::FG_GREEN);
        $this->disabled = $this->ansiFormat(Maintenance::t('app', 'DISABLED'), Console::FG_RED);
    }

    /**
     * Render is Disabled
     */
    protected function renderGroupDisabled()
    {
        $status = $this->disabledMode;
        $this->renderMaintenanceModeHasBeenStatus($status);
        $this->stdout(PHP_EOL);

        $this->renderEnableMaintenanceMode();
        $this->stdout(PHP_EOL);
        $this->stdout(PHP_EOL);

        $this->renderOptionsTable();
    }

    /**
     * Render is Enabled
     */
    protected function renderGroupEnabled()
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
        $this->stdout(PHP_EOL);

        $this->renderOptionsTable();
    }

    /**
     * Maintenance Mode has been ENABLED/DISABLED
     *
     * @param $status string
     */
    protected function renderMaintenanceModeHasBeenStatus($status)
    {
        $message = Maintenance::t('app', 'Maintenance Mode has been {:status}', [
            ':status' => $status,
        ]);
        $this->stdout($message);
    }

    /**
     * Notified 2 subscribers.
     *
     * @param $count int
     */
    protected function renderNotifiedSubscribers($count = 0)
    {
        $this->stdout(Maintenance::t('app', '{n, plural, =0{No subscribers} =1{Notified one subscriber} other{Notified # subscribers}}.', [
            'n' => $count
        ]));
    }

    /**
     * on until 09-03-2020 11:15:04
     */
    protected function renderOnUntilDateTime()
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
    protected function renderSubscriptionInfo()
    {
        $count = count($this->subscribeForm->getEmails());
        $message = Maintenance::t('app', '{n, plural, =0{No subscribers} =1{Total one subscriber} other{Total # subscribers}}.', [
            'n' => $count
        ]);
        $this->stdout($message);
    }

    /**
     * Count Down: ENABLED
     */
    protected function renderCountDownStatus()
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
    protected function renderSubscriptionFormStatus()
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
    protected function renderDisableAndSubscribe()
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
    protected function renderEnableMaintenanceMode()
    {
        $message = Maintenance::t('app', "To enable the maintenance mode,\nuse:");
        $this->stdout(PHP_EOL . $message . PHP_EOL);

        $option = $this->mbLcFirst(trim(Maintenance::t('app', 'Option')));
        $value = $this->mbLcFirst(trim(Maintenance::t('app', 'Value')));
        $message = Maintenance::t('app', "php yii maintenance/enable --{:option}1='{:value}1' --{:option}2='{:value}2' ...", [
            ':option' => $option,
            ':value' => $value
        ]);
        $this->stdout($message);
    }

    /**
     * To update the maintenance mode,
     * use:
     * php yii maintenance/update --option='value'
     */
    protected function renderUpdateMaintenanceMode()
    {
        $message = Maintenance::t('app', "To update the maintenance mode,\nuse:");
        $this->stdout(PHP_EOL . $message . PHP_EOL);

        $option = $this->mbLcFirst(trim(Maintenance::t('app', 'Option')));
        $value = $this->mbLcFirst(trim(Maintenance::t('app', 'Value')));
        $message = Maintenance::t('app', "php yii maintenance/update --{:option}1='{:value}1' --{:option}2='{:value}2' ...", [
            ':option' => $option,
            ':value' => $value
        ]);
        $this->stdout($message);
    }

    /**
     * Options and aliases
     */
    protected function renderOptionsTable()
    {
        $option = Maintenance::t('app', 'Option');
        $alias = Maintenance::t('app', 'Alias');
        $value = Maintenance::t('app', 'Value');
        $exampleDat = $this->exampleData;

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
     * @param $str string
     * @return string
     */
    protected function mbLcFirst($str = '')
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
     * Example format date time
     * @return mixed
     */
    protected function exampleDateFormat()
    {
        return date($this->stateForm->getDateFormat());
    }
}
