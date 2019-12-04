<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\ClientEntityInterface as WolfpackITClientEntityInterface;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\AccessToken;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\Scope;
use yii\base\InvalidConfigException;

/**
 * Class AccessTokenRepository
 * @package WolfpackIT\oauth\components\repository
 */
class AccessTokenRepository
    extends Repository
    implements AccessTokenRepositoryInterface
{
    /**
     * @var string
     */
    public $modelClass = AccessToken::class;

    /**
     * @param $identifier
     * @return null|AccessToken
     */
    protected function findAccessToken($identifier): ?AccessToken
    {
        return $this->modelClass::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return AccessToken[]
     */
    public function findActiveAccessTokensForUserEntity(UserEntityInterface $userEntity): array
    {
        return $this->modelClass::find()->andWhere(['user_id' => $userEntity->getId()])->active()->all();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param Client $clientEntity
     * @return AccessToken[]
     */
    public function findActiveAccessTokensForUserEntityAndClientEntity(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        return $this->modelClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->active()->all();
    }

    /**
     * @param Client $clientEntity
     * @param Scope[] $scopes
     * @param int $userIdentifier
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessToken
    {
        /** @var AccessToken $accessToken */
        $accessToken = new $this->modelClass([
            'client_id' => $clientEntity->getId(),
            'user_id' => $userIdentifier,
            'status' => AccessToken::STATUS_CREATION
        ]);
        $accessToken->save();
        $accessToken->populateRelation('grantedScopes', $scopes);
        return $accessToken;
    }

    public function init()
    {
        if (!is_subclass_of($this->modelClass, AccessTokenEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . AccessTokenEntityInterface::class);
        }

        parent::init();
    }

    /**
     * @param string $codeId
     * @return bool
     */
    public function isAccessTokenRevoked($codeId): bool
    {
        $accessToken = $this->findAccessToken($codeId);
        return is_null($accessToken) || $accessToken->status === AccessToken::STATUS_REVOKED;
    }

    /**
     * @param AccessToken $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        if (!$accessTokenEntity->validate()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
        $accessTokenEntity->status = AccessToken::STATUS_ENABLED;
        $accessTokenEntity->save();
    }

    /**
     * Revokes all access tokens for a user and client
     *
     * @param UserEntityInterface $userEntity
     * @param Client $client
     * @return int
     */
    public function revokeAllAccessTokensForUserAndClient(UserEntityInterface $userEntity, WolfpackITClientEntityInterface $client): int
    {
        return $this->modelClass::updateAll(
            ['status' => AccessToken::STATUS_REVOKED],
            [
                'user_id' => $userEntity->getId(),
                'client_id' => $client->getId()
            ]
        );
    }

    /**
     * @param string $codeId
     */
    public function revokeAccessToken($codeId): void
    {
        $accessToken = $this->findAccessToken($codeId);
        if (!is_null($accessToken)) {
            $accessToken->status = AccessToken::STATUS_REVOKED;
            $accessToken->save();
        }
    }
}