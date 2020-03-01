<?php

namespace dominus77\maintenance\controllers\backend;

use yii\filters\AccessControl;
use yii\web\Controller;
use dominus77\maintenance\actions\backend\IndexAction;

/**
 * Class MaintenanceController
 * @package dominus77\maintenance\controllers\backend
 */
class MaintenanceController extends Controller
{
    /**
     * Roles
     * @var array
     */
    public $roles = [];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => $this->roles
                    ]
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
            ]
        ];
    }
}
