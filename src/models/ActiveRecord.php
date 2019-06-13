<?php

namespace WolfpackIT\oauth\models;

use WolfpackIT\oauth\Module;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord as YiiActiveRecord;
use yii\db\Connection;

/**
 * Class ActiveRecord
 * @package WolfpackIT\oauth\models
 */
class ActiveRecord extends YiiActiveRecord
{
    /**
     * @return mixed|Connection
     * @throws InvalidConfigException
     */
    public static function getDb()
    {
        return Module::getInstance()->getDb();
    }
}