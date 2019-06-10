<?php

use WolfpackIT\oauth\components\Request;
use WolfpackIT\oauth\components\Response;
use yii\web\Request as YiiRequest;
use yii\web\Response as YiiResponse;

return [
    'container' => [
        'definitions' => [
            YiiRequest::class => Request::class,
            YiiResponse::class => Response::class
        ]
    ]
];