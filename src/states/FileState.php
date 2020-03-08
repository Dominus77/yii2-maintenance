<?php

namespace dominus77\maintenance\states;

use Yii;
use Exception;
use yii\base\BaseObject;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\models\FileStateForm;
use yii\base\InvalidArgumentException;

/**
 * Class FileState
 * @package dominus77\maintenance\states
 *
 * @property array|mixed $subscribeOptionsTemplate
 * @property string $fileStatePath
 */
class FileState extends BaseObject implements StateInterface
{
    /**
     * @var string the filename that will determine if the maintenance mode is enabled
     */
    public $fileName = 'YII_MAINTENANCE_MODE_ENABLED';

    /**
     * Default title
     * @var string
     */
    public $defaultTitle = 'Maintenance';

    /**
     * Default content
     * @var string
     */
    public $defaultContent = 'The site is undergoing technical work. We apologize for any inconvenience caused.';

    /**
     * @var string name of the file where subscribers will be stored
     */
    public $fileSubscribe = 'YII_MAINTENANCE_MODE_SUBSCRIBE';

    /**
     * Options SubscribeFormWidget
     * @var array
     */
    public $subscribeOptions = [];

    /**
     * @var string the directory in that the file stated in $fileName above is residing
     */
    public $directory = '@runtime';

    /**
     * @var string the complete path of the file - populated in init
     */
    public $path;

    /**
     * @var string the complete path of the file subscribe - populated in init
     */
    public $subscribePath;

    /**
     * Enter Datetime format
     * @var string
     */
    public $dateFormat = 'd-m-Y H:i:s';

    /**
     * Initialization
     */
    public function init()
    {
        $this->path = $this->getFilePath($this->fileName);
        $this->subscribePath = $this->getFilePath($this->fileSubscribe);
    }

    /**
     * Turn on mode.
     *
     * @return bool|mixed
     */
    public function enable()
    {
        $this->disable();
        file_put_contents($this->getFileStatePath(),
            'The maintenance Mode of your Application is enabled if this file exists.');
        chmod($this->path, 0765);
        return true;
    }

    /**
     * Turn off mode.
     *
     * @return bool|mixed
     */
    public function disable()
    {
        if (file_exists($this->getFileStatePath())) {
            unlink($this->getFileStatePath());
        }
        return true;
    }

    /**
     * @return bool will return true if the file exists
     */
    public function isEnabled()
    {
        return file_exists($this->getFileStatePath());
    }

    /**
     * Timestamp
     *
     * @return int
     * @throws Exception
     */
    public function timestamp()
    {
        $model = new FileStateForm();
        return $model->getTimestamp();
    }

    /**
     * Status code
     * @return int|string
     */
    public function statusCode()
    {
        $model = new FileStateForm();
        return $model->getStatusCode();
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @return string
     */
    public function getDefaultTitle()
    {
        return $this->defaultTitle;
    }

    /**
     * @return string
     */
    public function getDefaultContent()
    {
        return $this->defaultContent;
    }

    /**
     * @return string
     */
    public function getFileStatePath()
    {
        if (is_string($this->path)) {
            return $this->path;
        }
        throw new InvalidArgumentException("Invalid path alias: $this->directory . '/' . $this->fileName");
    }

    /**
     * @return string
     */
    public function getSubscribePath()
    {
        if (is_string($this->subscribePath)) {
            return $this->subscribePath;
        }
        throw new InvalidArgumentException("Invalid path alias: $this->directory . '/' . $this->fileName");
    }

    /**
     * @return array
     */
    public function getSubscribeOptions()
    {
        return $this->subscribeOptions;
    }

    /**
     * @return array|mixed
     */
    public function getSubscribeOptionsTemplate()
    {
        return (isset($this->subscribeOptions['template']) && !empty($this->subscribeOptions['template'])) ?
            $this->subscribeOptions['template'] : [
                'html' => '@dominus77/maintenance/mail/emailNotice-html',
                'text' => '@dominus77/maintenance/mail/emailNotice-text'
            ];
    }

    /**
     * Return file path.
     *
     * @param $fileName string
     * @return bool|string
     */
    protected function getFilePath($fileName)
    {
        return Yii::getAlias($this->directory . '/' . $fileName);
    }
}
