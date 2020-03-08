Filters
==================

You can use filters to resolve exceptions:
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
                // Filters
                'filters' => [
                    // Routes for which to ignored mode
                    [
                        'class' => URIFilter::class,
                        'uri' => [
                            'debug/default/view',
                            'debug/default/toolbar',                            
                            'site/login',
                            'site/logout'
                        ]
                    ],
                    // Users for whom to ignored mode
                    [
                        'class' => UserFilter::class,
                        'checkedAttribute' => 'username',
                        'users' => [
                            'admin'
                        ]
                    ],
                    // RBAC permissions for which to ignored mode      
                    [
                        'class' => RoleFilter::class,
                        'roles' => [
                            'admin'
                        ]
                    ],
                    // IP addresses for which to ignored mode
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

You can also create your own filter:
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