<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\StateFormInterface;
use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\Maintenance;
use Exception;
use DateTime;

/**
 * Class FileStateForm
 * @package dominus77\maintenance\models
 *
 * @property string $dateTime
 * @property string $path
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
     * @var bool
     */
    public $subscribe = false;
    /**
     * CountDownWidget
     * @var bool
     */
    public $countDown = false;

    /**
     * Path to file
     * @var string
     */
    private $_path;

    /**
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function init()
    {
        parent::init();
        $this->_path = $this->state->getFileStatePath();
        $this->loadModel();
        if ($this->mode === null) {
            $this->mode = $this->state->isEnabled() ? Maintenance::STATUS_CODE_MAINTENANCE : Maintenance::STATUS_CODE_OK;
        }
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function rules()
    {
        return [
            [['mode'], 'required'],
            [['mode'], 'number'],
            [['mode'], 'filter', 'filter' => [$this, 'integerTypeCast']],

            [['date'], 'string', 'min' => 19, 'max' => 19],
            ['date', 'default', 'value' => function () {
                return $this->getDateTime();
            }],
            [['date'], 'validateDateAttribute'],

            [['title', 'text'], 'string'],

            [['subscribe', 'countDown'], 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => false]
        ];
    }

    /**
     * Type Cast integer
     *
     * @param $value string|integer
     * @return int
     */
    public function integerTypeCast($value)
    {
        return (int)$value;
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
        $d = DateTime::createFromFormat($this->getDateFormat(), $date);
        return $d && $d->format($this->getDateFormat()) === $date;
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
     * Path to file
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Set data model
     */
    public function loadModel()
    {
        $stateArray = $this->prepareLoadModel($this->path);
        $this->load([$stateArray], false);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return BackendMaintenance::t('app', $this->state->defaultTitle);
    }

    /**
     * @return string
     */
    public function getText()
    {
        return BackendMaintenance::t('app', $this->state->defaultContent);
    }

    /**
     * Current Datetime
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getDateTime()
    {
        return Yii::$app->formatter->asDatetime($this->getTimestamp(), 'php:' . $this->getDateFormat());
    }

    /**
     * Mode name
     *
     * @return mixed
     */
    public function getModeName()
    {
        return ArrayHelper::getValue(self::getModesArray(), $this->mode);
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
     *
     * @return bool|int
     */
    public function save()
    {
        $result = false;
        if ($this->mode === Maintenance::STATUS_CODE_MAINTENANCE) {
            file_put_contents($this->path,
                $this->prepareData());
            chmod($this->path, 0765);
            $result = true;
        }
        if ($this->mode === Maintenance::STATUS_CODE_OK) {
            $model = new SubscribeForm();
            $result = $model->send();
            $this->disable();
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
            $value = trim($value);
            if ($value) {
                $result .= $attribute . ' = ' . $value . PHP_EOL;
            }
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
        $date = new DateTime(date($this->getDateFormat()));
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
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->state->isEnabled() && ($this->mode !== Maintenance::STATUS_CODE_OK);
    }

    /**
     * StatusCode
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->mode;
    }
}
