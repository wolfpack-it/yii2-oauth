<?php

namespace WolfpackIT\oauth\controllers;

use SamIT\Yii2\Traits\ActionInjectionTrait;
use yii\filters\Cors;
use yii\rest\Controller;

/**
 * Class RestController
 * @package WolfpackIT\oauth\controllers
 */
abstract class RestController extends Controller
{
    use ActionInjectionTrait;

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $result = parent::behaviors();

        $result['cors'] = [
            'class' => Cors::class,
        ];

        return $result;
    }
}
