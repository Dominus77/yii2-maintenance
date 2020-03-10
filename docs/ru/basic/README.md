Подключение и настройка
=======================

Подключение и настройка для только что установленного базового шаблона [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic).

app/config/web.php
```php
<?php

use dominus77\maintenance\Maintenance;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\filters\URIFilter;
use dominus77\maintenance\filters\UserFilter;
use dominus77\maintenance\controllers\frontend\MaintenanceController;
use dominus77\maintenance\controllers\backend\MaintenanceController as BackendMaintenanceController;

//...

$config = [
    //...
    'language' => 'ru',
    //...
    'bootstrap' => [
        //...      
        Maintenance::class
    ],
    //...
    'container' => [
        'singletons' => [
            Maintenance::class => [
                'class' => Maintenance::class,
                'route' => 'maintenance/index',
                // Фильтры
                'filters' => [
                    // Роуты, для которых игнорировать режим
                    [
                        'class' => URIFilter::class,
                        'uri' => [
                            'debug/default/view',
                            'debug/default/toolbar',                            
                            'site/login',
                            'site/logout'
                        ]
                    ],
                    // Пользователи, для которых игнорировать режим
                    [
                        'class' => UserFilter::class,
                        'checkedAttribute' => 'username',
                        'users' => ['admin']
                    ]
                    // Подробнее: https://github.com/Dominus77/yii2-maintenance/blob/master/docs/ru/common/filters.md
                ],
            ],
            StateInterface::class => [
                'class' => FileState::class,
                'subscribeOptions' => [                    
                    'template' => [
                        'html' => '@dominus77/maintenance/mail/emailNotice-html'
                    ]
                ],
                'directory' => '@runtime'
            ]
        ]
    ],    
    'controllerMap' => [
        //...
        'maintenance' => [
            'class' => MaintenanceController::class                     
        ],
        'maintenance-admin' => [
            'class' => BackendMaintenanceController::class,                                 
            'roles' => ['@'] // Авторизованный пользователь
        ]
    ],
    //...
];
```
app/config/params.php
```php
<?php

return [
    //...
    'frontendUrl' => 'http://yii2-basic.loc',
];
```
app/config/console.php
```php
<?php

use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\commands\MaintenanceController;

//...

$config = [
    //...
    'language' => 'ru',
    //...
    'bootstrap' => [
        //...
        BackendMaintenance::class
    ],    
    'container' => [
        'singletons' => [
            StateInterface::class => [
                'class' => FileState::class,
                // Настроики шаблонов для подписчиков
                'subscribeOptions' => [                    
                    'template' => [
                        'html' => '@dominus77/maintenance/mail/emailNotice-html'
                    ]
                ],
                'directory' => '@runtime'
            ]
        ]
    ],    
    'controllerMap' => [
        //...
        'maintenance' => [
            'class' => MaintenanceController::class
        ]
    ],    
    'components' => [
        //..        
        'urlManager' => [
            'hostInfo' => $params['frontendUrl'], // http://yii2-basic.loc
            //...
        ]
    ],
    //...
];
```

Использование
-------------
* [Фильтры](../common/filters.md)
* [Консольные команды](../common/console-commands.md)

Ссылка на вэб интерфейс админки `http://yii2-basic.loc/maintenance-admin/index`

![maintenance.png](../images/maintenance-backend-basic.png)
