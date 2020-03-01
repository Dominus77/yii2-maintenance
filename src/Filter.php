<?php

namespace dominus77\maintenance;

use yii\base\BaseObject;

/**
 * Class Filter
 * @package dominus77\maintenance
 */
abstract class Filter extends BaseObject
{
    /**
     * @return bool
     */
    abstract public function isAllowed();
}