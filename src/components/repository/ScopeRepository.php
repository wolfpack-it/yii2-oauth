<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\AccessTokenScope;
use WolfpackIT\oauth\models\activeRecord\AuthCodeScope;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\Scope;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class ScopeRepository
 * @package WolfpackIT\oauth\components\repository
 */
class ScopeRepository
    extends Repository
    implements ScopeRepositoryInterface
{
    /**
     * @var AccessTokenRepository
     */
    protected $accessTokenRepository;

    /**
     * @var string
     */
    public $accessTokenScopeClass = AccessTokenScope::class;

    /**
     * @var AuthCodeRepository
     */
    protected $authCodeRepository;

    /**
     * @var string
     */
    public $authCodeScopeClass = AuthCodeScope::class;

    /**
     * @var string
     */
    public $modelClass = Scope::class;

    /**
     * @var RefreshTokenRepository
     */
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
            $this->accessTokenRepository->modelClass::find()->andWhere(['id' => ArrayHelper::getColumn($refreshTokens, 'access_token_id')])->indexBy('id')->all(),
            ArrayHelper::index($this->accessTokenRepository->findActiveAccessTokensForUserEntityAndClientEntity($userEntity, $clientEntity), 'id')
        );
        $authCodes = $this->authCodeRepository->findActiveAuthCodesForUserEntityAndClientEntity($userEntity, $clientEntity);

        $scopeIds = array_unique(array_merge(
            $this->accessTokenScopeClass::find()->andWhere(['access_token_id' => ArrayHelper::getColumn($accessTokens, 'id')])->select('scope_id')->column(),
            $this->authCodeScopeClass::find()->andWhere(['auth_code_id' => ArrayHelper::getColumn($authCodes, 'id')])->select('scope_id')->column()
        ));

        return $this->modelClass::findAll(['id' => $scopeIds]);
    }

    /**
     * @param string $identifier
     * @return ScopeEntityInterface|null
     */
    public function getScopeEntityByIdentifier($identifier): ?Scope
    {
        return $this->modelClass::find()->andWhere(['identifier' => $identifier])->one();
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

    public function init()
    {
        if (!is_subclass_of($this->modelClass, ScopeEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . ScopeEntityInterface::class);
        }

        parent::init();
    }
}