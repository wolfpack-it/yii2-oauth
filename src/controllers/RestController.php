<?php

namespace WolfpackIT\oauth\controllers;

use SamIT\Yii2\Traits\ActionInjectionTrait;
use yii\rest\Controller;

/**
 * Class RestController
 * @package WolfpackIT\oauth\controllers
 */
abstract class RestController extends Controller
{
    use ActionInjectionTrait;
}
