<?php

namespace WolfpackIT\oauth\components;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use WolfpackIT\oauth\components\repository\AccessTokenRepository;
use WolfpackIT\oauth\components\repository\AuthCodeRepository;
use WolfpackIT\oauth\components\repository\RefreshTokenRepository;
use WolfpackIT\oauth\models\activeRecord\AccessToken;
use WolfpackIT\oauth\models\activeRecord\Client;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Helper service to find authorized clients and revoke them
 *
 * Class UserClientService
 * @package WolfpackIT\oauth\components
 */
class UserClientService extends Component
{
    /** @var AccessTokenRepository */
    protected $accessTokenRepository;
    /** @var AuthCodeRepository */
    protected $authCodeRepository;
    /** @var RefreshTokenRepository */
    protected $refreshTokenRepository;

    /**
     * UserClientService constructor.
     * @param AccessTokenRepository $accessTokenRepository
     * @param AuthCodeRepository $authCodeRepository
     * @param RefreshTokenRepository $refreshTokenRepository
     * @param array $config
     */
    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        AuthCodeRepository $authCodeRepository,
        RefreshTokenRepository $refreshTokenRepository,
        array $config = []
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->authCodeRepository = $authCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;

        parent::__construct($config);
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return Client[]
     */
    public function getAuthorizedClientsForUser(UserEntityInterface $userEntity): array
    {
        $clientIds = array_unique(array_merge(
            ArrayHelper::getColumn($this->accessTokenRepository->findActiveAccessTokensForUserEntity($userEntity), 'client_id'),
            ArrayHelper::getColumn($this->authCodeRepository->findActiveAuthCodesForUserEntity($userEntity), 'client_id'),
            ArrayHelper::getColumn(
                AccessToken::findAll(['id' => ArrayHelper::getColumn($this->refreshTokenRepository->findActiveRefreshTokensForUserEntity($userEntity), 'access_token_id')]),
                'client_id'
            )
        ));

        return Client::findAll(['id' => $clientIds]);
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param ClientEntityInterface $client
     * @return int
     */
    public function revokeClientForUser(UserEntityInterface $userEntity, ClientEntityInterface $client): int
    {
        return
            $this->accessTokenRepository->revokeAllAccessTokensForUserAndClient($userEntity, $client)
            + $this->authCodeRepository->revokeAllAuthCodesForUserAndClient($userEntity, $client)
            + $this->refreshTokenRepository->revokeAllRefreshTokensForUserAndClient($userEntity, $client);
    }
}