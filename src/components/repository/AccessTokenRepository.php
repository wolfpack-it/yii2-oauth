<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use oauth\components\Repository;
use oauth\models\activeRecord\AccessToken;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\Scope;

/**
 * Class AccessTokenRepository
 * @package WolfpackIT\oauth\components\repository
 */
class AccessTokenRepository
    extends Repository
    implements AccessTokenRepositoryInterface
{
    /**
     * @param $identifier
     * @return null|AccessToken
     */
    protected function findAccessToken($identifier): ?AccessToken
    {
        return AccessToken::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return AccessToken[]
     */
    public function findActiveAccessTokensForUserEntity(UserEntityInterface $userEntity): array
    {
        return AccessToken::find()->andWhere(['user_id' => $userEntity->getIdentifier()])->active()->all();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param Client $clientEntity
     * @return AccessToken[]
     */
    public function findActiveAccessTokensForUserEntityAndClientEntity(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        return AccessToken::find()->andWhere(['user_id' => $userEntity->getIdentifier(), 'client_id' => $clientEntity->getId()])->active()->all();
    }

    /**
     * @param Client $clientEntity
     * @param Scope[] $scopes
     * @param int $userIdentifier
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessToken([
            'client_id' => $clientEntity->id,
            'user_id' => $userIdentifier,
            'status' => AccessToken::STATUS_CREATION
        ]);
        $accessToken->save();
        $accessToken->populateRelation('grantedScopes', $scopes);
        return $accessToken;
    }


    /**
     * @param string $codeId
     * @return bool
     */
    public function isAccessTokenRevoked($codeId): bool
    {
        $authCode = $this->findAccessToken($codeId);
        return !is_null($authCode) && $authCode->status === AccessToken::STATUS_REVOKED;
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
     * @param ClientEntityInterface $client
     * @return int
     */
    public function revokeAllAccessTokensForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $client): int
    {
        return AccessToken::updateAll(
            ['status' => AccessToken::STATUS_REVOKED],
            [
                'user_id' => $userEntity->getIdentifier(),
                'client_id' => Client::find()->select('id')->andWhere(['identifier' => $client->getIdentifier()])
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