<?php

namespace WolfpackIT\oauth\models\form\authorize;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use WolfpackIT\oauth\components\UserClientService;
use WolfpackIT\oauth\interfaces\ClientEntityInterface;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\Scope;
use WolfpackIT\oauth\models\Form;
use yii\validators\BooleanValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\RequiredValidator;

/**
 * Class Authorize
 * @package WolfpackIT\oauth\models\form\authorize
 */
class Authorize extends Form
{
    /**
     * @var bool
     */
    public $authorizeScopes;

    /**
     * @var AuthorizationRequest
     */
    protected $authorizationRequest;

    /**
     * @var UserEntityInterface
     */
    protected $user;

    /**
     * @var UserClientService
     */
    protected $userClientService;

    /**
     * Authorize constructor.
     * @param AuthorizationRequest $authorizationRequest
     * @param UserClientService $userClientService
     * @param UserEntityInterface $user
     * @param array $config
     */
    public function __construct(
        AuthorizationRequest $authorizationRequest,
        UserClientService $userClientService,
        UserEntityInterface $user,
        array $config = []
    ) {
        $this->authorizationRequest = $authorizationRequest;
        $this->userClientService = $userClientService;
        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * @return ClientEntityInterface
     */
    public function getClient(): ClientEntityInterface
    {
        return $this->authorizationRequest->getClient();
    }

    /**
     * @return Scope[]
     */
    public function getNewScopes(): array
    {
        $authorizedScopeIds = $this->userClientService->getAuthorizedScopesForUserAndClient($this->user, $this->getClient());
        return array_filter(
            $this->getScopes(),
            function(Scope $scope) use ($authorizedScopeIds) {
                return !in_array($scope->id, $authorizedScopeIds);
            }
        );
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->authorizationRequest->getScopes();
    }

    /**
     * Checks whether the requested scopes have already been
     *
     * @return bool
     */
    public function isAlreadyAuthorized(): bool
    {
        return empty($this->getNewScopes());
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['authorizeScopes'], RequiredValidator::class],
            [['authorizeScopes'], BooleanValidator::class],
            [['authorizeScopes'], DefaultValueValidator::class, 'value' => false]
        ];
    }

    /**
     * @return bool
     */
    public function runInternal(): bool
    {
        if ($result = $this->validate()) {
            $this->authorizationRequest->setUser($this->user);
            $this->authorizationRequest->setAuthorizationApproved((bool) $this->authorizeScopes);
        }

        return $result;
    }
}