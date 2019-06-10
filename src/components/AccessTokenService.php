<?php

namespace WolfpackIT\oauth\components;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;

/**
 * Class AccessTokenService
 * @package WolfpackIT\oauth\components
 */
class AccessTokenService extends Component
{
    /**
     * @var string|array|AccessTokenRepositoryInterface
     */
    public $accessTokenRepository = AccessTokenRepositoryInterface::class;

    /**
     * @var string
     */
    public $tokenHeader = 'Authorization';

    /**
     * @var string
     */
    public $tokenPattern = '/^Bearer\s+(.*?)$/';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->accessTokenRepository = is_string($this->accessTokenRepository) && \Yii::$app->has($this->accessTokenRepository)
            ? \Yii::$app->get($this->accessTokenRepository)
            : \Yii::createObject($this->accessTokenRepository);

        if (!$this->accessTokenRepository instanceof AccessTokenRepositoryInterface) {
            throw new InvalidConfigException('Access token repository must be instance of ' . AccessTokenRepositoryInterface::class);
        }

        if (!isset($this->tokenHeader, $this->tokenPattern)) {
            throw new InvalidConfigException('TokenHeader and TokenPattern must be set.');
        }

        parent::init();
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getJwtFromRequest(Request $request): ?string
    {
        $authHeader = $request->getHeaders()->get($this->tokenHeader);

        if (is_null($authHeader)) {
            return null;
        }

        if (preg_match($this->tokenPattern, $authHeader, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * @param $jwt
     * @return Token|null
     * @throws InvalidConfigException
     */
    public function getToken($jwt): ?Token
    {
        $result = null;

        try {
            $result = $this->getAndValidateToken($jwt);
        } catch (UnauthorizedHttpException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param $jwt
     * @return Token
     * @throws InvalidConfigException
     * @throws UnauthorizedHttpException
     */
    public function getAndValidateToken($jwt): Token
    {
        try {
            $token = (new Parser())->parse($jwt);

            try {
                if ($token->verify(new Sha256(), \Yii::$app->get('oauthPublicKey')->getKeyPath()) === false) {
                    throw new UnauthorizedHttpException('Access token could not be verified');
                }
            } catch (\BadMethodCallException $exception) {
                throw new UnauthorizedHttpException('Access token is not signed');
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if ($token->validate($data) === false) {
                throw new UnauthorizedHttpException('Access token is invalid');
            }

            // Check if token has been revoked
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw new UnauthorizedHttpException('Access token has been revoked');
            }

            return $token;
        } catch (\InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw new UnauthorizedHttpException($exception->getMessage());
        }
    }
}