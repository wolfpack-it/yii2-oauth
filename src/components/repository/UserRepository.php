<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\Module;
use yii\base\InvalidConfigException;

/**
 * Class UserRepository
 * @package oauth\components\repository
 */
class UserRepository
    extends Repository
    implements UserRepositoryInterface
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * TODO implement check for $grantType with client
     *
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @return UserEntityInterface|null
     * @throws InvalidConfigException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $user = $this->modelClass::findByUsernamePassword($username, $password);

        return $user;
    }

    public function init()
    {
        $this->modelClass = $this->modelClass ?? Module::getInstance()->userClass;

        if (!is_subclass_of($this->modelClass, UserEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . UserEntityInterface::class);
        }

        parent::init();
    }
}