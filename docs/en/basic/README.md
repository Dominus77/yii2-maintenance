Connection and setup
====================

Connecting and configure for the newly installed [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic) basic template.

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
    'language' => 'en',
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
                'filters' => [
                    // routes for which to ignore mode
                    [
                        'class' => URIFilter::class,
                        'uri' => [                            
                            'site/login',
                            'site/logout'
                        ]
                    ],
                    // Users for whom to ignore mode
                    [
                        'class' => UserFilter::class,
                        'checkedAttribute' => 'username',
                        'users' => ['admin']
                    ]
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
            'roles' => ['@'] // Authorized User
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
    'language' => 'en',
    //...
    'bootstrap' => [
        //...
        BackendMaintenance::class
    ],    
    'container' => [
        'singletons' => [
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

New console commands:

| Command                         | Description                             |
|:------------------------------- |:--------------------------------------- |
| `php yii maintenance`           | Mode status                             |
| `php yii maintenance/enable`    | Enable mode                             |
| `php yii maintenance/update`    | Editing mode                            |
| `php yii maintenance/followers` | Alert Subscribers                       |
| `php yii maintenance/disable`   | Turn off the mode and send alerts       |

The following options are available for `enable` and `update`:

| Option      | Alias | Description                                         |
|:----------- |:----- |:--------------------------------------------------- |
| --date      |  -d   | Set/Change Maintenance End Date                     |
| --title     |  -t   | Set/Change the title on the page                    |
| --content   |  -c   | Set/Change the main content on the page             |
| --subscribe |  -s   | Show/Hide the subscription form on the page         |
| --timer     |  -tm  | Show/Hide the timer on page                         | 

Example:
```
php yii maintenance/enable -d="07-03-2020 18:00:00" -s=true -tm=true
php yii maintenance/update -t="Maintenance"
php yii maintenance/update -c="The site is undergoing technical work. We apologize for any inconvenience caused."
```
Link to the admin interface web interface `http://yii2-basic.loc/maintenance-admin/index`

![maintenance.png](../images/maintenance-backend-basic.png)