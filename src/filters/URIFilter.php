<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use dominus77\maintenance\Filter;

/**
 * Class URIFilter
 * @package dominus77\maintenance\filters
 */
class URIFilter extends Filter
{
    /**
     * @var array
     */
    public $uri;
    /**
     * @var Request
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
        if (is_array($this->uri) && !empty($this->uri) && $resolve = $this->request->resolve()) {
            return (bool)in_array($resolve[0], $this->uri, true);
        }
        return false;
    }
}
