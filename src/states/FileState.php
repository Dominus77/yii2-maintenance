<?php

namespace dominus77\maintenance\states;

use Yii;
use Exception;
use RuntimeException;
use yii\base\BaseObject;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\models\FileStateForm;

/**
 * Class FileState
 * @package dominus77\maintenance\states
 *
 * @property bool|string $filePath
 * @property array $contentArray
 * @property array $maintenanceFileLinesParamsArray
 * @property bool $validDate
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
     * @return mixed|void
     */
    public function enable()
    {
        try {
            file_put_contents($this->path,
                'The maintenance Mode of your Application is enabled if this file exists.');
            chmod($this->path, 0765);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Attention: the maintenance mode could not be enabled because {$this->path} could not be created."
            );
        }
    }

    /**
     * Turn off mode.
     *
     * @return int|mixed
     */
    public function disable()
    {
        $result = false;
        try {
            if (file_exists($this->path)) {
                $result = unlink($this->path);
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Attention: the maintenance mode could not be disabled because {$this->path} could not be removed."
            );
        }
        return $result;
    }

    /**
     * @return bool will return true if the file exists
     */
    public function isEnabled()
    {
        return file_exists($this->path);
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
     * @return int
     */
    public function statusCode()
    {
        $model = new FileStateForm();
        return $model->getStatusCode();
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
