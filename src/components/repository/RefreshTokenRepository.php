<?php

namespace oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use oauth\components\Repository;
use oauth\models\activeRecord\AccessToken;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\RefreshToken;

class RefreshTokenRepository
    extends Repository
    implements RefreshTokenRepositoryInterface
{
    /**
     * @param $identifier
     * @return array|null|RefreshToken
     */
    protected function findRefreshToken($identifier): ?RefreshToken
    {
        return RefreshToken::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return RefreshToken[]
     */
    public function findActiveRefreshTokensForUserEntity(UserEntityInterface $userEntity): array
    {
        $accessTokenIdsQuery = AccessToken::find()->andWhere(['user_id' => $userEntity->getIdentifier()])->select('id');
        return RefreshToken::find()->andWhere(['access_token_id' => $accessTokenIdsQuery])->active()->all();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param Client $clientEntity
     * @return RefreshToken[]
     */
    public function findActiveRefreshTokensForUserEntityAndClientEntity(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        $accessTokenIdsQuery = AccessToken::find()->andWhere(['user_id' => $userEntity->getIdentifier(), 'client_id' => $clientEntity->getId()])->select('id');
        return RefreshToken::find()->andWhere(['access_token_id' => $accessTokenIdsQuery])->active()->all();
    }

    /**
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshToken([
            'status' => RefreshToken::STATUS_CREATION
        ]);
    }

    /**
     * @param string $tokenId
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->findRefreshToken($tokenId);
        return !is_null($refreshToken) && $refreshToken->status === RefreshToken::STATUS_REVOKED;
    }

    /**
     * @param RefreshToken $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshTokenEntity->status = RefreshToken::STATUS_ENABLED;
        if (!$refreshTokenEntity->validate()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
        $refreshTokenEntity->save();
    }

    /**
     * Revokes all refresh tokens for a user and client
     *
     * @param UserEntityInterface $userEntity
     * @param ClientEntityInterface $client
     * @return int
     */
    public function revokeAllRefreshTokensForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $client): int
    {
        $accessTokenIdsQuery = AccessToken::find()
            ->andWhere([
                'user_id' => $userEntity->getIdentifier(),
                'client_id' => Client::find()->select('id')->andWhere(['identifier' => $client->getIdentifier()])
            ])
            ->select('id');

        return RefreshToken::updateAll(
            ['status' => RefreshToken::STATUS_REVOKED],
            [
                'access_token_id' => $accessTokenIdsQuery
            ]
        );
    }

    /**
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $refreshToken = $this->findRefreshToken($tokenId);
        $refreshToken->status = RefreshToken::STATUS_REVOKED;
        $refreshToken->save();
    }
}