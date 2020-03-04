<?php

use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\controllers\frontend\MaintenanceController;
use dominus77\maintenance\BackendMaintenance;

$params = [
    'frontendUrl' => 'http://test.loc',
    'senderEmail' => 'noreply@test.loc'
];

return [
    'id' => 'maintenance-tests',
    'language' => 'en',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        BackendMaintenance::class
    ],
    'container' => [
        'singletons' => [
            StateInterface::class => [
                'class' => FileState::class,
                // optional: format datetime
                'dateFormat' => 'd-m-Y H:i:s',
                // optional: use different filename for controlling maintenance state:
                'fileName' => 'YII_TEST_MAINTENANCE_MODE_ENABLED',
                // optional: use a different file name to store subscribers of end-of-service notify
                'fileSubscribe' => 'YII_TEST_MAINTENANCE_MODE_SUBSCRIBE',
                // optional: use different directory for controlling maintenance state:
                'directory' => '@runtime',
                // Configure sender for subscribers
                'subscribeOptions' => [
                    'template' => [
                        'html' => '@dominus77/mail/emailNotice-html',
                        'text' => '@dominus77/mail/emailNotice-text'
                    ],
                    'backLink' => $params['frontendUrl'], // configure urlManager in console/config/main.php
                    'from' => $params['senderEmail'], // noreply@mail.com
                    'subject' => 'Notification of completion of technical work'
                ]
            ]
        ]
    ],
    'controllerMap' => [
        'maintenance' => [
            'class' => MaintenanceController::class,
            'layout' => '@dominus77/maintenance/views/frontend/layouts/maintenance',
            'viewPath' => '@dominus77/maintenance/views/frontend/maintenance',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-maintenance-test',
            'enableCsrfValidation' => false
        ]
    ]
];