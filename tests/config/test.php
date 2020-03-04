<?php
return [
    'id' => 'maintenance-tests',
    'language'=>'en',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-maintenance-test',
            'enableCsrfValidation' => false
        ]
    ]
];