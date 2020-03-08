<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use dominus77\maintenance\Filter;

/**
 * Class URIFilter
 * @package dominus77\maintenance\filters
 */
class URIFilter extends Filter
{
    /**
     * @var array|string
     */
    public $uri;
    /**
     * @var yii\web\Request|yii\console\Request
     */
    protected $request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->request instanceof yii\web\Request) {
            $this->request = Yii::$app->request;
        }
        if (is_string($this->uri)) {
            $this->uri = [$this->uri];
        }
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function isAllowed()
    {
        if ($this->request && is_array($this->uri) && !empty($this->uri) && $resolve = $this->request->resolve()) {
            $this->uri = ArrayHelper::merge($this->uri, ['maintenance/subscribe']);
            return (bool)in_array($resolve[0], $this->uri, true);
        }
        return false;
    }
}
