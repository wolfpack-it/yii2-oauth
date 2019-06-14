<?php

namespace WolfpackIT\oauth\components;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use WolfpackIT\oauth\components\repository\AccessTokenRepository;
use WolfpackIT\oauth\components\repository\AuthCodeRepository;
use WolfpackIT\oauth\components\repository\ClientRepository;
use WolfpackIT\oauth\components\repository\RefreshTokenRepository;
use WolfpackIT\oauth\components\repository\ScopeRepository;
use WolfpackIT\oauth\interfaces\ClientEntityInterface;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
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
    /**
     * @var AccessTokenRepository
     */
    protected $accessTokenRepository;

    /**
     * @var AuthCodeRepository
     */
    protected $authCodeRepository;

    /**
     * @var ClientRepository
     */
    protected $clientRepository;

    /**
     * @var RefreshTokenRepository
     */
    protected $refreshTokenRepository;

    /**
     * @var ScopeRepository
     */
    protected $scopeRepository;

    /**
     * UserClientService constructor.
     * @param AccessTokenRepository $accessTokenRepository
     * @param AuthCodeRepository $authCodeRepository
     * @param ClientRepository $clientRepository
     * @param RefreshTokenRepository $refreshTokenRepository
     * @param ScopeRepository $scopeRepository
     * @param array $config
     */
    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        AuthCodeRepository $authCodeRepository,
        ClientRepository $clientRepository,
        RefreshTokenRepository $refreshTokenRepository,
        ScopeRepository $scopeRepository,
        array $config = []
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->authCodeRepository = $authCodeRepository;
        $this->clientRepository = $clientRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->scopeRepository = $scopeRepository;

        parent::__construct($config);
    }

    /**
     * @param UserEntityInterface $userEntity
     * @return ClientEntityInterface[]
     */
    public function getAuthorizedClientsForUser(UserEntityInterface $userEntity): array
    {
        $clientIds = array_unique(array_merge(
            ArrayHelper::getColumn($this->accessTokenRepository->findActiveAccessTokensForUserEntity($userEntity), 'client_id'),
            ArrayHelper::getColumn($this->authCodeRepository->findActiveAuthCodesForUserEntity($userEntity), 'client_id'),
            ArrayHelper::getColumn(
                $this->accessTokenRepository->modelClass::findAll(['id' => ArrayHelper::getColumn($this->refreshTokenRepository->findActiveRefreshTokensForUserEntity($userEntity), 'access_token_id')]),
                'client_id'
            )
        ));

        return $this->clientRepository->modelClass::findAll(['id' => $clientIds]);
    }

    /**
     * @param UserEntityInterface $userEntity
     * @param ClientEntityInterface $clientEntity
     * @return ScopeEntityInterface[]
     */
    public function getAuthorizedScopesForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        $allAccessTokenIds = $this->accessTokenRepository->modelClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->select('id')->asArray()->column();
        $validAccessTokenIdsByRefreshToken = $this->refreshTokenRepository->modelClass::find()->andWhere(['access_token_id' => $allAccessTokenIds])->active()->select('access_token_id')->asArray()->column();
        $validAccessTokenIds = $this->accessTokenRepository->modelClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->active()->select('id')->asArray()->column();
        $accessTokenScopeIds = $this->scopeRepository->accessTokenScopeClass::find()->andWhere(['access_token_id' => array_merge($validAccessTokenIdsByRefreshToken, $validAccessTokenIds)])->select('scope_id')->column();

        $validAuthCodeIds = $this->authCodeRepository->modelClass::find()->andWhere(['user_id' => $userEntity->getId(), 'client_id' => $clientEntity->getId()])->active()->select('id')->asArray()->column();
        $authCodeScopeIds = $this->scopeRepository->authCodeScopeClass::find()->andWhere(['auth_code_id' => $validAuthCodeIds])->select('scope_id')->column();

        return $this->scopeRepository->modelClass::findAll(['id' => array_unique(array_merge($accessTokenScopeIds, $authCodeScopeIds))]);
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