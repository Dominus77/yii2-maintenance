<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use dominus77\maintenance\Filter;
use yii\web\Request as WebRequest;

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
     * @var WebRequest
     */
    protected $request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->request = Yii::$app->request;
        if (is_string($this->uri)) {
            $this->uri = [$this->uri];
        }
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     */
    public function isAllowed()
    {
        if (($this->request instanceof WebRequest) && is_array($this->uri) && !empty($this->uri) && $resolve = $this->request->resolve()) {
            $this->uri = ArrayHelper::merge($this->uri, ['maintenance/subscribe']);
            return (bool)in_array($resolve[0], $this->uri, true);
        }
        return false;
    }
}
