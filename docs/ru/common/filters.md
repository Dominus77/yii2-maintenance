Фильтры
==================

Вы можете использовать фильтры для разрешения исключений:
```php
<?php

use dominus77\maintenance\Maintenance;
use dominus77\maintenance\filters\RoleFilter;
use dominus77\maintenance\filters\URIFilter;
use dominus77\maintenance\filters\UserFilter;
use dominus77\maintenance\filters\IpFilter;

$config = [    
    //...
    'container' => [
        'singletons' => [
            Maintenance::class => [
                'class' => Maintenance::class,
                //...
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
                        'users' => [
                            'admin'
                        ]
                    ],
                    // Разрешения RBAC, для которых игнорировать режим      
                    [
                        'class' => RoleFilter::class,
                        'roles' => [
                            'admin'
                        ]
                    ],
                    // IP адреса, для которых игнорировать режим
                    [
                        'class' => IpFilter::class,
                        'ips' => [
                            '127.0.0.1',
                        ]
                    ],
                ],
            ],
            //...
        ]
    ],
    //...
];
```

Так же, вы можете создать собственный фильтр:
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
