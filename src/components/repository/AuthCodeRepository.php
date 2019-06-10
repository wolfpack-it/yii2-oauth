<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\AuthCode;
use WolfpackIT\oauth\models\activeRecord\Client;
use yii\base\InvalidConfigException;

/**
 * Class AuthCodeRepository
 * @package WolfpackIT\oauth\components\repository
 */
class AuthCodeRepository
    extends Repository
    implements AuthCodeRepositoryInterface
{
    /**
     * @var string
     */
    public $modelClass = AuthCode::class;

    /**
     * @param $identifier
     * @return null|AuthCode
     */
    protected function findAuthCode($identifier): ?AuthCode
    {
        return $this->modelClass::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return AuthCode[]
     */
    public function findActiveAuthCodesForUserEntity(UserEntityInterface $userEntity): array
    {
        return $this->modelClass::find()->andWhere(['user_id' => $userEntity->getId()])->active()->all();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param Client $clientEntity
     * @return AuthCode[]
     */
    public function findActiveAuthCodesForUserEntityAndClientEntity(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        return $this->modelClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->active()->all();
    }

    /**
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new $this->modelClass([
            'status' => AuthCode::STATUS_CREATION
        ]);
    }

    public function init()
    {
        if (!is_subclass_of($this->modelClass, AuthCodeEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . AuthCodeEntityInterface::class);
        }

        parent::init();
    }

    /**
     * @param string $codeId
     * @return bool
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = $this->findAuthCode($codeId);
        return !is_null($authCode) && $authCode->status === AuthCode::STATUS_REVOKED;
    }

    /**
     * @param AuthCode $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $authCodeEntity->status = AuthCode::STATUS_ENABLED;
        if (!$authCodeEntity->validate()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
        $authCodeEntity->save();
    }

    /**
     * Revokes all auth codes for a user and client
     *
     * @param UserEntityInterface $userEntity
     * @param Client $client
     * @return int
     */
    public function revokeAllAuthCodesForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $client): int
    {
        return AuthCode::updateAll(
            ['status' => AuthCode::STATUS_REVOKED],
            [
                'user_id' => $userEntity->getId(),
                'client_id' => $client->getId()
            ]
        );
    }

    /**
     * @param string $codeId
     */
    public function revokeAuthCode($codeId): void
    {
        $authCode = $this->findAuthCode($codeId);
        if (!is_null($authCode)) {
            $authCode->status = AuthCode::STATUS_REVOKED;
            $authCode->save();
        }
    }
}