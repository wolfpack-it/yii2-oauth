<?php

namespace WolfpackIT\oauth;

use WolfpackIT\oauth\interfaces\UserEntityInterface;
use yii\base\InvalidConfigException;
use \yii\base\Module as YiiModule;

/**
 * Class Module
 * @package WolfpackIT\oauth
 */
class Module extends YiiModule
{
    /**
     * @var string;
     */
    public $userClass;

    public function init()
    {
        if (!$this->userClass) {
            throw new InvalidConfigException('User class must be set.');
        } elseif (!is_subclass_of($this->userClass, UserEntityInterface::class)) {
            throw new InvalidConfigException('User class must implement ' . UserEntityInterface::class);
        }

        \Yii::configure($this, require __DIR__ . '/config/module.php');

        parent::init();
    }
}