<?php

namespace WolfpackIT\oauth\components;

use yii\base\Component;

/**
 * Class Repository
 * @package WolfpackIT\oauth\components
 */
abstract class Repository extends Component
{
    /**
     * @var string
     */
    public $modelClass;
}