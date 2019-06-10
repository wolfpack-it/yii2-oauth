<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\AccessToken;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\RefreshToken;
use yii\base\InvalidConfigException;

/**
 * Class RefreshTokenRepository
 * @package WolfpackIT\oauth\components\repository
 */
class RefreshTokenRepository
    extends Repository
    implements RefreshTokenRepositoryInterface
{
    /**
     * @var string
     */
    public $accessTokenClass = AccessToken::class;

    /**
     * @var string
     */
    public $modelClass = RefreshToken::class;

    /**
     * @param $identifier
     * @return array|null|RefreshToken
     */
    protected function findRefreshToken($identifier): ?RefreshToken
    {
        return $this->modelClass::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return RefreshToken[]
     */
    public function findActiveRefreshTokensForUserEntity(UserEntityInterface $userEntity): array
    {
        $accessTokenIdsQuery = $this->accessTokenClass::find()->andWhere(['user_id' => $userEntity->getId()])->select('id');
        return $this->modelClass::find()->andWhere(['access_token_id' => $accessTokenIdsQuery])->active()->all();
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param Client $clientEntity
     * @return RefreshToken[]
     */
    public function findActiveRefreshTokensForUserEntityAndClientEntity(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        $accessTokenIdsQuery = $this->accessTokenClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->select('id');
        return $this->modelClass::find()->andWhere(['access_token_id' => $accessTokenIdsQuery])->active()->all();
    }

    /**
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new $this->modelClass([
            'status' => RefreshToken::STATUS_CREATION
        ]);
    }

    public function init()
    {
        if (!is_subclass_of($this->modelClass, RefreshTokenEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . RefreshTokenEntityInterface::class);
        }

        parent::init();
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
     * @param Client $client
     * @return int
     */
    public function revokeAllRefreshTokensForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $client): int
    {
        $accessTokenIdsQuery = $this->accessTokenClass::find()
            ->andWhere([
                'user_id' => $userEntity->getId(),
                'client_id' => $client->getId()
            ])
            ->select('id');

        return $this->modelClass::updateAll(
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