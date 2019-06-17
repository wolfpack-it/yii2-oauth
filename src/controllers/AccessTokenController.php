<?php

namespace WolfpackIT\oauth\controllers;

use WolfpackIT\oauth\actions\oauth\AccessTokenAction;
use yii\filters\Cors;
use yii\rest\OptionsAction;

/**
 * Class AccessTokenController
 * @package WolfpackIT\oauth\controllers
 */
class AccessTokenController extends RestController
{
    /**
     * @return array
     */
    public function actions(): array
    {
        return [
            'index' => [
                'class' => AccessTokenAction::class,
            ],
            'options' => [
                'class' => OptionsAction::class
            ]
        ];
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $result = parent::behaviors();

        $result['authenticator']['optional'] = [
            'index',
            'options'
        ];

        return $result;
    }
}