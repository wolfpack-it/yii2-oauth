<?php

namespace WolfpackIT\oauth\components;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use WolfpackIT\oauth\components\repository\AccessTokenRepository;
use WolfpackIT\oauth\Module;
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
    public $accessTokenRepository = AccessTokenRepository::class;

    /**
     * The leeway in seconds for token expiration and creation when validating
     * @var ?int
     */
    public $tokenValidationLeeway;

    /**
     * @var string
     */
    public $tokenHeader = 'Authorization';

    /**
     * @var string
     */
    public $tokenPattern = '/^Bearer\s+(.*?)$/';

    public Configuration $configuration;

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

        $this->configuration = $this->configuration ?? Module::getInstance()->configuration;

        $this->tokenValidationLeeway = $this->tokenValidationLeeway ?? Module::getInstance()->tokenValidationLeeway;

        parent::init();
    }

    /**
     * @param  Request  $request
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
     * @throws UnauthorizedHttpException
     */
    public function getAndValidateToken($jwt): Token
    {
        try {
            $token = $this->configuration->parser()->parse($jwt);

            $validator = new Validator();

            if (!$validator->validate($token,
                new SignedWith($this->configuration->signer(), $this->configuration->verificationKey()))) {
                throw new UnauthorizedHttpException('Access token is not signed');
            }

            $leeway = $this->tokenValidationLeeway ? \DateInterval::createFromDateString($this->tokenValidationLeeway.' seconds') : null;
            if (!$validator->validate($token,
                new StrictValidAt(SystemClock::fromUTC(), $leeway))) {
                throw new UnauthorizedHttpException('Access token is invalid');
            }

            // Check if token has been revoked
            if ($this->accessTokenRepository->isAccessTokenRevoked($token->claims()->get('jti'))) {
                throw new UnauthorizedHttpException('Access token has been revoked');
            }

            return $token;
        } catch (\Throwable $t) {
            // JWT couldn't be parsed so return the request as is
            throw new UnauthorizedHttpException($t->getMessage());
        }
    }
}
