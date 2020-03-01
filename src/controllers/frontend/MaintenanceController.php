<?php

namespace dominus77\maintenance\controllers\frontend;

use yii\web\Controller;
use yii\filters\VerbFilter;
use dominus77\maintenance\actions\frontend\IndexAction;
use dominus77\maintenance\actions\frontend\SubscribeAction;

/**
 * Class MaintenanceController
 * @package dominus77\maintenance\controllers\frontend
 */
class MaintenanceController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'subscribe' => ['POST'],
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::class
            ],
            'subscribe' => [
                'class' => SubscribeAction::class
            ]
        ];
    }
}
