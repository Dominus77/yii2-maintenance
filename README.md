Maintenance mode for Yii2
=========================

[![Latest Version](https://poser.pugx.org/dominus77/yii2-maintenance/v/stable)](https://packagist.org/packages/dominus77/yii2-maintenance)
[![Software License](https://poser.pugx.org/dominus77/yii2-maintenance/license)](https://github.com/Dominus77/yii2-maintenance/blob/master/LICENSE.md)
[![Build Status](https://travis-ci.org/Dominus77/yii2-maintenance.svg?branch=master)](https://travis-ci.org/Dominus77/yii2-maintenance)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Dominus77/yii2-maintenance/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Dominus77/yii2-maintenance/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Dominus77/yii2-maintenance/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

Switching the site on Yii2 to maintenance mode with a timer, a subscription form and sending notifications to users.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require dominus77/yii2-maintenance "dev-master"
```

or add

```
"dominus77/yii2-maintenance": "dev-master"
```

to the require section of your `composer.json` file.

Documentation
-------------
* [English](docs/en/README.md)
* [Русский](docs/ru/README.md)

Connection and setup
--------------------

Add to your config file:
```php
// frontend/config/main.php
$config = [
    'bootstrap' => [
        //...
        'dominus77\maintenance\Maintenance'
    ],
    //...
    'container' => [
        'singletons' => [
            'dominus77\maintenance\Maintenance' => [
                'class' => 'dominus77\maintenance\Maintenance',
    
                // Route to action
                'route' => 'maintenance/index',
    
                // Filters. Read Filters for more info.
                'filters' => [
                    [
                        'class' => 'dominus77\maintenance\filters\URIFilter',
                        'uri' => [
                            'debug/default/view',
                            'debug/default/toolbar',                            
                            'users/default/login',
                            'users/default/logout',
                            'users/default/request-password-reset'
                        ]
                    ]
                ],
    
                // HTTP Status Code
                //'statusCode' => 503,
                // optional
                // Retry-After header
                // If not set, set automatically from the set time, + 10 minutes
                //'retryAfter' => 120 // or Wed, 21 Oct 2020 07:28:00 GMT for example
            ],
            'dominus77\maintenance\interfaces\StateInterface' => [
                'class' => 'dominus77\maintenance\states\FileState',
                // optional: format datetime
                // 'dateFormat' => 'd-m-Y H:i:s',
    
                // optional: use different filename for controlling maintenance state:
                // 'fileName' => 'my_file.ext',
                // optional: use a different file name to store subscribers of end-of-service notify
                // 'fileSubscribe' => 'my_file_subscribe.ext',
    
                // optional: use different directory for controlling maintenance state:
                'directory' => '@runtime',
            ]
        ]
    ],
    // Page Maintenance
    'controllerMap' => [
        //...
        'maintenance' => [
            'class' => 'dominus77\maintenance\controllers\frontend\MaintenanceController',
            'layout' => '@dominus77/maintenance/views/frontend/layouts/maintenance',
            'viewPath' => '@dominus77/maintenance/views/frontend/maintenance',
        ],
    ],
    //...
];
```

Filters
-------
You can use filters for allow excepts:

```php
// frontend/config/main.php
$config = [
    //...
    'container' => [
        'singletons' => [
            'dominus77\maintenance\Maintenance' => [
                'class' => 'dominus77\maintenance\Maintenance',
                // Route to action
                'route' => 'maintenance/index',
                // Filters. Read Filters for more info.
                'filters' => [
                    //Allowed URIs filter. Your can allow debug panel URI.
                    [
                        'class' => 'dominus77\maintenance\filters\URIFilter',
                        'uri' => [
                            'debug/default/view',
                            'debug/default/toolbar',                            
                            'users/default/login',
                            'users/default/logout',
                            'users/default/request-password-reset'
                        ]
                    ],
                    // Allowed roles filter
                    [
                        'class' => 'dominus77\maintenance\filters\RoleFilter',
                        'roles' => ['admin'] // Permissions
                    ],
                    // Allowed IP addresses filter
                    [
                        'class' => 'dominus77\maintenance\filters\IpFilter',
                        'ips' => [
                            '127.0.0.1',
                        ]
                    ],
                    //Allowed user names
                    [
                        'class' => 'dominus77\maintenance\filters\UserFilter',
                        'checkedAttribute' => 'username',
                        'users' => [
                            'admin', // username
                        ],
                    ]
                ],
            ]
        ]
    ]    
];
```
You can create custom filter:
```php
use dominus77\maintenance\Filter;

class MyCustomFilter extends Filter
{
    public $time;

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return (bool) $this->time > 3600;
    }
}
```
Set maintenance mode by backend
-------------------------------
Add to your common config file:
```php
// common/config/main.php
$params = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);
$config = [
    'container' => [
        'singletons' => [
            'dominus77\maintenance\interfaces\StateInterface' => [
                'class' => 'dominus77\maintenance\states\FileState',
                // optional: format datetime
                // 'dateFormat' => 'd-m-Y H:i:s',
                // optional: use different filename for controlling maintenance state:
                // 'fileName' => 'myfile.ext',
                // optional: use a different file name to store subscribers of end-of-service notify
                // 'fileSubscribe' => 'my_file_subscribe.ext',
                // optional: use different directory for controlling maintenance state:
                'directory' => '@frontend/runtime',

                // Configure sender for subscribers
                'subscribeOptions' => [
                    'template' => [
                        'html' => '@dominus77/maintenance/mail/emailNotice-html',
                        'text' => '@dominus77/maintenance/mail/emailNotice-text'
                    ],
                    'backLink' => $params['frontendUrl'], // configure urlManager in console/config/main.php
                    'from' => $params['senderEmail'], // noreply@mail.com
                    //'subject' => 'Notification of completion of technical work'
                ]
            ]
        ]
    ],
];
```

Add to your backend config file:
```php
// backend/config/main.php
$config = [
    'bootstrap' => [
        //...    
        'dominus77\maintenance\BackendMaintenance',
    ],
    //...
    'controllerMap' => [
        'maintenance' => [
            'class' => 'dominus77\maintenance\controllers\backend\MaintenanceController',
            'viewPath' => '@dominus77/maintenance/views/backend/maintenance',
            'roles' => ['admin'] // Permissions, managing maintenance mode
        ],
    ],
];
```
Url dashboard: `http://mysite.com/admin/maintenance`

Set maintenance mode by console
-------------------------------

Add to your console config file:
```php
// console/config/main.php
$params = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$config = [
    'bootstrap' => [
        //...    
        'dominus77\maintenance\BackendMaintenance',
    ],
    //...
    'container' => [
        'singletons' => [
            'dominus77\maintenance\interfaces\StateInterface' => [
                'class' => 'dominus77\maintenance\states\FileState',
                // optional: format datetime
                // 'dateFormat' => 'd-m-Y H:i:s',
                // optional: use different filename for controlling maintenance state:
                // 'fileName' => 'myfile.ext',
                // optional: use a different file name to store subscribers of end-of-service notify
                // 'fileSubscribe' => 'my_file_subscribe.ext',    
                // optional: use different directory for controlling maintenance state:
                'directory' => '@frontend/runtime',
            ]
        ]
    ],
    'controllerMap' => [
          //...
          'maintenance' => [
              'class' => 'dominus77\maintenance\commands\MaintenanceController',
          ],
    ],
    //..
    'components' => [
        //...        
        'urlManager' => [
            'hostInfo' => $params['frontendUrl'], // http://mysite.com
            //...
        ]
    ],      
    //...
];
```

Now you can set mode by command:
```
php yii maintenance
php yii maintenance/enable --date="25-02-2025 16:05:00" --title="Maintenance" --content="The site is undergoing technical work. We apologize for any inconvenience caused." --subscribe=true --timer=true
php yii maintenance/update --date="24-05-2023 19:05:00" --title="Maintenance" --content="The site is undergoing technical work. We apologize for any inconvenience caused." --subscribe=true --timer=true
php yii maintenance/followers
php yii maintenance/disable
```
You can use aliases:

```
--date -d
--title -t
--content -c
--subscribe -s
--timer -tm
```
License
-------
The MIT License (MIT). Please see [License File](https://github.com/Dominus77/yii2-maintenance/blob/master/LICENSE.md) for more information.
