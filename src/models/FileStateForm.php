<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\StateFormInterface;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\Maintenance;
use Exception;
use DateTime;
use yii\web\Session;

/**
 * Class FileStateForm
 * @package dominus77\maintenance\models
 *
 * @property string $dateTime
 * @property mixed $modeName
 * @property int $statusCode
 * @property int $timestamp
 */
class FileStateForm extends BaseForm implements StateFormInterface
{
    const MAINTENANCE_NOTIFY_SENDER_KEY = 'notifySender';
    const MAINTENANCE_UPDATE_KEY = 'maintenanceUpdate';

    /**
     * Select mode
     * @var string
     */
    public $mode;
    /**
     * Datetime
     * @var string
     */
    public $date;
    /**
     * Title
     * @var string
     */
    public $title;
    /**
     * Text
     * @var string
     */
    public $text;
    /**
     * Subscribe
     * @var string
     */
    public $subscribe;
    /**
     * CountDownWidget
     * @var string
     */
    public $countDown;

    /**
     * @var FileState
     */
    protected $state;

    /**
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function init()
    {
        parent::init();
        $this->loadModel();
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function rules()
    {
        return [
            ['date', 'trim'],
            ['mode', 'required'],
            ['date', 'string', 'max' => 19],
            ['date', 'validateDateAttribute'],
            [['title', 'text'], 'string'],
            [['subscribe', 'countDown'], 'boolean']
        ];
    }

    /**
     * Validate date attribute
     *
     * @param $attribute
     * @throws InvalidConfigException
     */
    public function validateDateAttribute($attribute)
    {
        if ($attribute && !$this->validDate($this->$attribute)) {
            $example = $this->getDateTime();
            $this->addError($attribute, BackendMaintenance::t('app', 'Invalid date format. Use example: {:example}', [':example' => $example]));
        }
    }

    /**
     * Validate datetime
     *
     * @param $date
     * @return bool
     */
    public function validDate($date)
    {
        $d = DateTime::createFromFormat($this->dateFormat, $date);
        return $d && $d->format($this->dateFormat) === $date;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'mode' => BackendMaintenance::t('app', 'Mode'),
            'date' => BackendMaintenance::t('app', 'Date and Time'),
            'title' => BackendMaintenance::t('app', 'Title'),
            'text' => BackendMaintenance::t('app', 'Text'),
            'subscribe' => BackendMaintenance::t('app', 'Subscribe'),
            'countDown' => BackendMaintenance::t('app', 'Count Down'),
        ];
    }

    /**
     * Set data model
     */
    public function loadModel()
    {
        if ($stateArray = $this->prepareLoadModel($this->state->path)) {
            $this->setAttributes($stateArray);
        } else {
            $this->setAttributeDefaultData();
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function setAttributeDefaultData()
    {
        $this->mode = $this->mode ?: Maintenance::STATUS_CODE_OK;
        $this->date = $this->date ?: $this->getDateTime();
        $this->title = $this->title ?: BackendMaintenance::t('app', $this->state->defaultTitle);
        $this->text = $this->text ?: BackendMaintenance::t('app', $this->state->defaultContent);
        $this->subscribe = $this->subscribe ?: 'true';
        $this->countDown = $this->countDown ?: 'true';
    }

    /**
     * Current Datetime
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getDateTime()
    {
        return Yii::$app->formatter->asDatetime($this->getTimestamp(), 'php:' . $this->dateFormat);
    }

    /**
     * Mode name
     * @return mixed
     */
    public function getModeName()
    {
        return ArrayHelper::getValue(self::getModesArray(), (int)$this->mode);
    }

    /**
     * Modes
     * @return array
     */
    public static function getModesArray()
    {
        return [
            Maintenance::STATUS_CODE_OK => BackendMaintenance::t('app', 'Mode normal'),
            Maintenance::STATUS_CODE_MAINTENANCE => BackendMaintenance::t('app', 'Mode maintenance'),
        ];
    }

    /**
     * Save this in file
     */
    public function save()
    {
        $result = false;
        if ($this->mode === (string)Maintenance::STATUS_CODE_MAINTENANCE) {
            if (file_exists($this->state->path)) {
                unlink($this->state->path);
            }
            file_put_contents($this->state->path,
                $this->prepareData());
            $result = chmod($this->state->path, 0765);
        }
        if ($this->mode === (string)Maintenance::STATUS_CODE_OK) {
            $model = new SubscribeForm();
            $count = $model->send();
            $this->state->disable();
            /** @var Session $session */
            $session = Yii::$app->session;
            $session->setFlash(self::MAINTENANCE_NOTIFY_SENDER_KEY, BackendMaintenance::t('app',
                '{n, plural, =0{no followers} =1{one message sent} other{# messages sent}}',
                ['n' => $count])
            );
            $result = true;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function prepareData()
    {
        $result = '';
        foreach ($this->attributes as $attribute => $value) {
            $result .= $attribute . ' = ' . $value . PHP_EOL;
        }
        return $result;
    }

    /**
     * Enable
     *
     * @return bool|mixed
     */
    public function enable()
    {
        return $this->state->enable();
    }

    /**
     * Disable
     *
     * @return bool|mixed
     */
    public function disable()
    {
        return $this->state->disable();
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getTimestamp()
    {
        $date = new DateTime(date($this->dateFormat));
        if ($this->validDate($this->date)) {
            $date = new DateTime($this->date);
        }
        return $date->getTimestamp();
    }

    /**
     * @return bool
     */
    public function isTimer()
    {
        return (bool)$this->countDown;
    }

    /**
     * @return bool
     */
    public function isSubscribe()
    {
        return (bool)$this->subscribe;
    }

    /**
     * Return true is enable maintenance mode
     * @return bool
     */
    public function isEnabled()
    {
        return $this->state->isEnabled();
    }

    /**
     * StatusCode
     * @return int
     */
    public function getStatusCode()
    {
        if ($this->state->isEnabled()) {
            $mode = (int)$this->mode;
            return ($mode !== Maintenance::STATUS_CODE_OK) ? $mode : Maintenance::STATUS_CODE_MAINTENANCE;
        }
        return Maintenance::STATUS_CODE_OK;
    }
}
