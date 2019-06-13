<?php

namespace WolfpackIT\oauth;

use WolfpackIT\oauth\interfaces\UserEntityInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as YiiModule;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

/**
 * Class Module
 * @package WolfpackIT\oauth
 */
class Module extends YiiModule
{
    /**
     * @var string
     */
    public $clientCreatePermission = 'create';

    /**
     * @var string
     */
    public $clientDeletePermission = 'delete';

    /**
     * @var string
     */
    public $clientListPermission = 'list';

    /**
     * @var string
     */
    public $clientReadPermission = 'read';

    /**
     * @var string
     */
    public $clientUpdatePermission = 'write';

    /**
     * @var string|Connection
     */
    public $db = 'db';

    /**
     * @var string
     */
    public $defaultPermission = 'write';

    /**
     * @var array
     */
    public $i18n = [
        'class' => PhpMessageSource::class
    ];

    /**
     * Use null if the application layout should be used
     *
     * @var string
     */
    public $layout = 'main';

    /**
     * Params that will be added to the application parameters
     *
     * @var array
     */
    public $params;

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

        $this->module->i18n->translations['oauth'] = $this->i18n;

        \Yii::configure($this, require __DIR__ . '/config/module.php');

        $this->module->params = ArrayHelper::merge($this->module->params, $this->params);

        parent::init();
    }

    /**
     * @return Connection
     * @throws InvalidConfigException
     */
    public function getDb(): Connection
    {
        return $this->get($this->db);
    }
}