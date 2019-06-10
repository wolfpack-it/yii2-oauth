<?php

namespace WolfpackIT\oauth\controllers;

use WolfpackIT\oauth\models\form\authorize\Authorize;
use yii\rest\OptionsAction;

/**
 * Class AuthorizeController
 * @package WolfpackIT\oauth\controllers
 */
class AuthorizeController extends RestController
{
    /**
     * @return array
     */
    public function actions(): array
    {
        return [
            'index' => [
                'class' => Authorize::class
            ],
            'options' => [
                'class' => OptionsAction::class
            ],
        ];
    }

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