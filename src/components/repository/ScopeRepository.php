<?php

namespace oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use oauth\components\Repository;
use oauth\models\activeRecord\AccessToken;
use oauth\models\activeRecord\AccessTokenScope;
use oauth\models\activeRecord\AuthCodeScope;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\Scope;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ScopeRepository
    extends Repository
    implements ScopeRepositoryInterface
{
    /** @var AccessTokenRepository */
    protected $accessTokenRepository;
    /** @var AuthCodeRepository */
    protected $authCodeRepository;
    /** @var RefreshTokenRepository */
    protected $refreshTokenRepository;

    /**
     * ScopeRepository constructor.
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
     * @param ClientEntityInterface $clientEntity
     * @return ScopeEntityInterface[]
     */
    public function getAuthorizedScopesForUserAndClient(UserEntityInterface $userEntity, ClientEntityInterface $clientEntity): array
    {
        $refreshTokens = $this->refreshTokenRepository->findActiveRefreshTokensForUserEntityAndClientEntity($userEntity, $clientEntity);
        $accessTokens = ArrayHelper::merge(
            AccessToken::find()->andWhere(['id' => ArrayHelper::getColumn($refreshTokens, 'access_token_id')])->indexBy('id')->all(),
            ArrayHelper::index($this->accessTokenRepository->findActiveAccessTokensForUserEntityAndClientEntity($userEntity, $clientEntity), 'id')
        );
        $authCodes = $this->authCodeRepository->findActiveAuthCodesForUserEntityAndClientEntity($userEntity, $clientEntity);

        $scopeIds = array_unique(array_merge(
            AccessTokenScope::find()->andWhere(['access_token_id' => ArrayHelper::getColumn($accessTokens, 'id')])->select('scope_id')->column(),
            AuthCodeScope::find()->andWhere(['auth_code_id' => ArrayHelper::getColumn($authCodes, 'id')])->select('scope_id')->column()
        ));

        return Scope::findAll(['id' => $scopeIds]);
    }

    /**
     * @param string $identifier
     * @return ScopeEntityInterface|null
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        return Scope::find()->andWhere(['identifier' => $identifier])->one();
    }

    /**
     * @param array $scopes
     * @param string $grantType
     * @param Client $clientEntity
     * @param null $userIdentifier
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $permittedScopes = $clientEntity->getScopes(
            function (ActiveQuery $query) use ($scopes, $grantType, $userIdentifier) {
                if (empty($scopes) === true) {
                    $query->andWhere(['is_default' => true]);
                }
                $query->andWhere([
                    'or',
                    ['user_id' => null],
                    ['user_id' => $userIdentifier]
                ]);
                $query->andWhere([
                    'or',
                    ['grant_type' => null],
                    ['grant_type' => $grantType]
                ]);
            }
        );
        if (empty($scopes) === false) {
            $permittedScopes->andWhere(['in', 'identifier', $scopes]);
        }
        return $permittedScopes->all();
    }
}