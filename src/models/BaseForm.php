<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\base\Model;
use dominus77\maintenance\interfaces\StateInterface;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use Generator;

/**
 * Class BaseForm
 * @package dominus77\maintenance\models
 *
 * @property string $dateFormat
 */
class BaseForm extends Model
{
    /**
     * Format datetime
     * @var string
     */
    protected $dateFormat;

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function init()
    {
        parent::init();
        $this->state = Yii::$container->get(StateInterface::class);
        $this->dateFormat = $this->state->dateFormat;
    }

    /**
     * Format datetime
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Prepare data to save
     *
     * @return string
     */
    public function prepareSaveData()
    {
        $result = '';
        foreach ($this->attributes as $attribute => $value) {
            $value = $value ?: 'null';
            $value = trim($value);
            if ($value) {
                $result .= $attribute . ' = ' . $value . PHP_EOL;
            }
        }
        return $result;
    }

    /**
     * Prepare data to load in model
     *
     * @param $path string
     * @return array
     */
    public function prepareLoadModel($path)
    {
        $items = [];
        if ($contentArray = $this->getContentArray($path)) {
            foreach ($contentArray as $item) {
                $arr = explode(' = ', $item);
                if (isset($arr[0], $arr[1])) {
                    $items[$arr[0]] = $arr[1] === 'null' ? null : $arr[1];
                }
            }
        }
        return $items;
    }

    /**
     * Return content to array this file
     *
     * @param $file string
     * @return array
     */
    public function getContentArray($file)
    {
        $contents = $this->readTheFile($file);
        $items = [];
        foreach ($contents as $key => $item) {
            $items[] = $item;
        }
        return array_filter($items);
    }

    /**
     * Read generator
     *
     * @param $file string
     * @return Generator
     */
    protected function readTheFile($file)
    {
        if (file_exists($file) && $handle = fopen($file, 'rb')) {
            if ($handle !== false) {
                while (!feof($handle)) {
                    yield trim(fgets($handle));
                }
            }
            fclose($handle);
        }
    }
}
