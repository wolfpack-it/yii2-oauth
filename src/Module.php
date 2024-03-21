<?php

namespace WolfpackIT\oauth;

use DateInterval;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use WolfpackIT\oauth\components\AuthorizationServer;
use WolfpackIT\oauth\components\repository\AccessTokenRepository;
use WolfpackIT\oauth\components\repository\AuthCodeRepository;
use WolfpackIT\oauth\components\repository\ClientRepository;
use WolfpackIT\oauth\components\repository\RefreshTokenRepository;
use WolfpackIT\oauth\components\repository\ScopeRepository;
use WolfpackIT\oauth\components\repository\UserRepository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as YiiModule;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

/**
 * Class Module
 * @package WolfpackIT\oauth
 */
class Module extends YiiModule
{
    /**
     * @var string
     */
    public $clientCreatePermission = 'create';

    /**
     * @var string
     */
    public $clientDeletePermission = 'delete';

    /**
     * @var string
     */
    public $clientListPermission = 'list';

    /**
     * @var string
     */
    public $clientReadPermission = 'read';

    /**
     * @var string
     */
    public $clientUpdatePermission = 'write';

    /**
     * @var string|Connection
     */
    public $db = 'db';

    /**
     * The leeway in seconds for token expiration and creation when validating
     * @var ?int
     */
    public $tokenValidationLeeway;

    /**
     * @var DateInterval
     */
    public $defaultAccessTokenTtl;

    /**
     * @var string
     */
    public $defaultPermission = 'write';

    /**
     * @var DateInterval
     */
    public $defaultRefreshTokenTtl;

    /**
     * Random string used to encrypt
     *
     * @var string|CryptKey
     */
    public $encryptionKey;

    /**
     * @var array
     */
    public $i18n = [
        'class' => PhpMessageSource::class
    ];

    /**
     * @var string
     */
    public $implicitGrantQueryDelimiter = '#';

    /**
     * Use null if the application layout should be used
     *
     * @var string
     */
    public $layout = 'main';

    /**
     * Params that will be added to the application parameters
     *
     * @var array
     */
    public $params;

    /**
     * Path to private key file, can use alias
     *
     * @var string
     */
    public $privateKey;

    /**
     * Path to public key file, can use alias
     *
     * @var string
     */
    public $publicKey;

    /**
     * @var string;
     */
    public $userClass;

    /**
     * @var string
     */
    public $userListPermission = 'list';

    /**
     * @var string
     */
    public $userViewPermission = 'view';

    /**
     * @var string
     */
    public $userWritePermission = 'write';

    /**
     * @var string
     */
    public $authorizationServerComponent = 'authorizationServer';

    /**
     * @var Configuration
     */
    public $configuration;

    /**
     * @var string|array|AuthorizationServer
     */
    public $authorizationServer;

    protected function createAuthorizationServer(): AuthorizationServer
    {
        /** @var ClientRepository $clientRepository */
        $clientRepository = \Yii::createObject(ClientRepository::class);
        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = \Yii::createObject(ScopeRepository::class);
        /** @var AccessTokenRepository $accessTokenRepository */
        $accessTokenRepository = \Yii::createObject(AccessTokenRepository::class);
        /** @var AuthCodeRepository $authCodeRepository */
        $authCodeRepository = \Yii::createObject(AuthCodeRepository::class);
        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = \Yii::createObject(RefreshTokenRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = \Yii::createObject(UserRepository::class, [['modelClass' => $this->userClass]]);

        $authorizationServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $this->privateKey,
            $this->encryptionKey
        );

        //Get more information about which grant types to use for what cases here: https://oauth2.thephpleague.com/authorization-server/which-grant/

        //ClientCredentialsGrant config
        $authorizationServer->enableGrantType(
            new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
            $this->defaultAccessTokenTtl
        );

        //PasswordGrant config
        $passwordGrant = new \League\OAuth2\Server\Grant\PasswordGrant(
            $userRepository,
            $refreshTokenRepository
        );
        $passwordGrant->setRefreshTokenTTL($this->defaultRefreshTokenTtl);
        $authorizationServer->enableGrantType(
            $passwordGrant,
            $this->defaultAccessTokenTtl
        );

        //AuthCodeGrant config
        $authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            $this->defaultAccessTokenTtl
        );
        $authCodeGrant->setRefreshTokenTTL($this->defaultRefreshTokenTtl);
        $authorizationServer->enableGrantType(
            $authCodeGrant,
            $this->defaultAccessTokenTtl
        );

        //ImplicitGrant
        $implicitGrant = new \League\OAuth2\Server\Grant\ImplicitGrant(
            $this->defaultAccessTokenTtl,
            $this->implicitGrantQueryDelimiter
        );
        $authorizationServer->enableGrantType(
            $implicitGrant,
            $this->defaultAccessTokenTtl
        );

        //RefreshTokenGrant config
        $refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant(
            $refreshTokenRepository
        );
        $refreshTokenGrant->setRefreshTokenTTL($this->defaultRefreshTokenTtl);
        $authorizationServer->enableGrantType(
            $refreshTokenGrant,
            $this->defaultAccessTokenTtl
        );

        $this->setComponents([
            $this->authorizationServerComponent => $authorizationServer
        ]);

        return $authorizationServer;
    }

    public function init()
    {
        if (!$this->userClass) {
            throw new InvalidConfigException('User class must be set.');
        } elseif (!is_subclass_of($this->userClass, UserEntityInterface::class)) {
            throw new InvalidConfigException('User class must implement ' . UserEntityInterface::class);
        }

        $this->module->i18n->translations['oauth'] = $this->i18n;

        \Yii::configure($this, require __DIR__ . '/config/module.php');

        $this->module->params = ArrayHelper::merge($this->module->params, $this->params);

        $this->defaultAccessTokenTtl = $this->defaultAccessTokenTtl ?? new DateInterval('PT1H');
        $this->defaultRefreshTokenTtl = $this->defaultRefreshTokenTtl ?? new DateInterval('P10Y');

        if (!$this->privateKey instanceof CryptKey) {
            if (is_string($this->privateKey) && is_file(\Yii::getAlias($this->privateKey))) {
                $this->privateKey = \Yii::getAlias($this->privateKey);
            } else {
                $this->privateKey =
                    is_string($this->privateKey) && $this->module->has($this->privateKey)
                        ? $this->module->get($this->privateKey)
                        : \Yii::createObject($this->privateKey);
            }
        }

        if (!$this->publicKey instanceof CryptKey) {
            if (is_string($this->publicKey) && is_file(\Yii::getAlias($this->publicKey))) {
                $this->publicKey = \Yii::getAlias($this->publicKey);
            } else {
                $this->publicKey =
                    is_string($this->publicKey) && $this->module->has($this->publicKey)
                        ? $this->module->get($this->publicKey)
                        : \Yii::createObject($this->publicKey);
            }
        }


        $this->configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($this->privateKey->getKeyPath(), $this->privateKey->getPassPhrase() ?? ''),
            InMemory::file($this->publicKey->getKeyPath())
        );
        $this->initAuthorizationServer();

        parent::init();
    }

    /**
     * @return Connection
     * @throws InvalidConfigException
     */
    public function getDb(): Connection
    {
        return $this->get($this->db);
    }

    public function initAuthorizationServer()
    {
        if(is_null($this->authorizationServer)) {
            $this->authorizationServer = $this->createAuthorizationServer();
        }

        if (!$this->authorizationServer instanceof AuthorizationServer) {
            $this->authorizationServer = is_string($this->authorizationServer) && $this->module->has($this->authorizationServer) ? $this->module->get($this->authorizationServer) : \Yii::createObject($this->authorizationServer);
        }
    }
}
