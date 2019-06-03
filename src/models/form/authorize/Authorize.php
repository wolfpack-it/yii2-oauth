<?php

namespace oauth\models\form\authorize;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use oauth\models\activeRecord\AccessToken;
use oauth\models\activeRecord\AccessTokenScope;
use oauth\models\activeRecord\AuthCode;
use oauth\models\activeRecord\AuthCodeScope;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\RefreshToken;
use oauth\models\activeRecord\Scope;
use oauth\models\activeRecord\User;
use oauth\models\Form;
use yii\validators\BooleanValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\RequiredValidator;

/**
 * Class Authorize
 * @package oauth\models\form\authorize
 */
class Authorize extends Form
{
    public $authorizeScopes;

    protected $authorizationRequest;
    protected $user;

    /**
     * Authorize constructor.
     * @param AuthorizationRequest $authorizationRequest
     * @param User $user
     * @param array $config
     */
    public function __construct(
        AuthorizationRequest $authorizationRequest,
        User $user,
        array $config = []
    ) {
        $this->authorizationRequest = $authorizationRequest;
        $this->user = $user;

        parent::__construct($config);
    }

    /**
     * @return int[]
     */
    protected function getAuthorizedScopeIds(): array
    {
        $client = $this->getClient();
        $allAccessTokenIds = AccessToken::find()->andWhere(['user_id' => $this->user->id, 'client_id' => $client->id])->select('id')->asArray()->column();
        $validAccessTokenIdsByRefreshToken = RefreshToken::find()->andWhere(['access_token_id' => $allAccessTokenIds])->active()->select('access_token_id')->asArray()->column();
        $validAccessTokenIds = AccessToken::find()->andWhere(['user_id' => $this->user->id, 'client_id' => $client->id])->active()->select('id')->asArray()->column();
        $accessTokenScopeIds = AccessTokenScope::find()->andWhere(['access_token_id' => array_merge($validAccessTokenIdsByRefreshToken, $validAccessTokenIds)])->select('scope_id')->column();

        $validAuthCodeIds = AuthCode::find()->andWhere(['user_id' => $this->user->id, 'client_id' => $client->id])->active()->select('id')->asArray()->column();
        $authCodeScopeIds = AuthCodeScope::find()->andWhere(['auth_code_id' => $validAuthCodeIds])->select('scope_id')->column();

        return array_unique(array_merge($accessTokenScopeIds, $authCodeScopeIds));
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->authorizationRequest->getClient();
    }

    /**
     * @return Scope[]
     */
    public function getNewScopes(): array
    {
        $authorizedScopeIds = $this->getAuthorizedScopeIds();
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