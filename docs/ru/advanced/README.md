Подключение и настройка
=======================

Подключение и настройка для только что установленного расширенного шаблона [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced).

common/config/main.php
```php
<?php

use dominus77\maintenance\states\FileState;
use dominus77\maintenance\interfaces\StateInterface;

$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [   
    //...
    'container' => [
        'singletons' => [
            StateInterface::class => [
                'class' => FileState::class,
                'directory' => '@frontend/runtime',
                // Настроики шаблонов для подписчиков
                'subscribeOptions' => [
                    'template' => [
                        'html' => '@dominus77/maintenance/mail/emailNotice-html',
                        'text' => '@dominus77/maintenance/mail/emailNotice-text'
                    ]                                       
                ]
            ]
        ]
    ],
    //...
];
```
common/config/params.php
```php
<?php

return [
    //...
    'frontendUrl' => 'http://yii2-advanced.loc'
];
```
frontend/config/main.php
```php
<?php

use dominus77\maintenance\filters\UserFilter;
use dominus77\maintenance\controllers\frontend\MaintenanceController;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\filters\URIFilter;
use dominus77\maintenance\Maintenance;

//...

return [
    //...
    'language' => 'ru',
    //...
    'bootstrap' => [
        //...
        Maintenance::class
    ],    
    'container' => [
        'singletons' => [
            Maintenance::class => [
                'class' => Maintenance::class,                
                'route' => 'maintenance/index',                
                'filters' => [
                    // роуты для которых игнорировать режим
                    [
                        'class' => URIFilter::class,
                        'uri' => [
                            'debug/default/view',
                            'debug/default/toolbar',
                            'site/login',
                            'site/logout',
                            'site/request-password-reset',
                            'site/resend-verification-email'
                        ]
                    ],
                    // Пользователи для которых игнорировать режим
                    [
                        'class' => UserFilter::class,
                        'checkedAttribute' => 'username',
                        'users' => [
                            'admin',
                        ],
                    ]
                ],
            ],
            StateInterface::class => [
                'class' => FileState::class,
                'directory' => '@runtime',
            ]
        ]
    ],    
    'controllerMap' => [
        //...
        'maintenance' => [
            'class' => MaintenanceController::class,
        ],
    ],
    //...
];
```
backend/config/main.php
```php
<?php

use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\controllers\backend\MaintenanceController;

//...

return [
    //...
    'language' => 'ru',
    //...
    'bootstrap' => [        
        BackendMaintenance::class
    ],
    //...
    'controllerMap' => [
        'maintenance' => [
            'class' => MaintenanceController::class,
            'roles' => ['@'] // Авторизованный пользователь
        ],
    ],
    //...
];
```
console/config/main.php
```php
<?php

use dominus77\maintenance\commands\MaintenanceController;
use dominus77\maintenance\states\FileState;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\BackendMaintenance;

//...

return [
    //...
    'language' => 'ru',
    //...    
    'bootstrap' => [
        //...
        BackendMaintenance::class
    ],
    //...
    'container' => [
        'singletons' => [
            StateInterface::class => [
                'class' => FileState::class,
                'directory' => '@frontend/runtime',
            ]
        ]
    ],
    'controllerMap' => [
        //...
        'maintenance' => [
            'class' => MaintenanceController::class,
        ],
    ],
    'components' => [
        //...
        'urlManager' => [
            'hostInfo' => $params['frontendUrl'], // http://yii2-advanced.loc
            //...
        ]
    ],
    //...
];
```

Новые консольные команды:

| Команда                         | Описание                                |
|:------------------------------- |:--------------------------------------- |
| `php yii maintenance`           | Статус режима                           |
| `php yii maintenance/enable`    | Включение режима                        |
| `php yii maintenance/update`    | Редактирование режима                   |
| `php yii maintenance/followers` | Подписчики на оповещение                |
| `php yii maintenance/disable`   | Отключение режима и отправка оповещений |

Для `enable` и `update` доступны следующие опции:

| Опция       | Алиас | Описание                                            |
|:----------- |:----- |:--------------------------------------------------- |
| --date      |  -d   | Установка/Изменение даты окончания тех.обслуживания |
| --title     |  -t   | Установка/Изменение заголовка на странице           |
| --content   |  -c   | Установка/Изменение основного контента на странице  |
| --subscribe |  -s   | Отображать/Не отображать форму подписки на странице |
| --timer     |  -tm  | Отображать/Не отображать таймер на странице         | 

Пример:
```
php yii maintenance/enable -d="07-03-2020 18:00:00" -s=true -tm=true
php yii maintenance/update -t="Техническое обслуживание"
php yii maintenance/update -c="На сайте проводятся технические работы. Приносим извинения за доставленные неудобства."
```
Ссылка на вэб интерфейс админки `http://yii2-advanced.loc/backend/web/maintenance/index`

![maintenance.png](../images/maintenance-backend-advanced.png)