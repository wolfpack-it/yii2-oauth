<?php

namespace WolfpackIT\oauth\actions\oauth;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WolfpackIT\oauth\actions\OAuthAction;
use WolfpackIT\oauth\components\AuthorizationServer;
use WolfpackIT\oauth\components\Request;
use WolfpackIT\oauth\components\Response;
use WolfpackIT\oauth\components\UserClientService;
use WolfpackIT\oauth\models\form\authorize\Authorize;
use yii\base\InvalidConfigException;
use yii\web\Request as YiiRequest;
use yii\web\Response as YiiResponse;
use yii\web\Session;
use yii\web\User;

/**
 * Class AuthorizeAction
 * @package WolfpackIT\oauth\actions
 */
class AuthorizeAction extends OAuthAction
{
    const SESSION_AUTH_REQUEST = 'auth_request';

    /**
     * @var string
     */
    public $view = 'authorize';

    /**
     * Required params to send: see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     *
     * @param AuthorizationServer $authorizationServer
     * @param YiiRequest $request
     * @param YiiResponse $response
     * @param Session $session
     * @param User $user
     * @param UserClientService $userClientService
     * @return \Exception|OAuthServerException|mixed|ResponseInterface
     * @throws InvalidConfigException
     */
    public function run(
        YiiRequest $request,
        YiiResponse $response,
        Session $session,
        User $user,
        UserClientService $userClientService
    ) {
        /** @var Request $request */
        $request = \Yii::createObject(Request::class, [$request]);
        /** @var Response $response */
        $response = \Yii::createObject(Response::class, [$response]);

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidConfigException('The request class for the module must implement ' . ServerRequestInterface::class . ', use can use ' . Request::class . ' via DI.');
        }

        if (!$response instanceof ResponseInterface) {
            throw new InvalidConfigException('The response class for the module must implement ' . ResponseInterface::class . ', use can use ' . Response::class . ' via DI.');
        }

        try {
            $authRequest = $session->get(self::SESSION_AUTH_REQUEST);

            if (!$authRequest) {
                $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);
                $session->set(self::SESSION_AUTH_REQUEST, $authRequest);
            }

            if ($user->isGuest) {
                return $user->loginRequired();
            }

            $model = \Yii::createObject(Authorize::class, [$authRequest, $userClientService, $user->identity]);

            if ($model->isAlreadyAuthorized()) {
                $model->authorizeScopes = true;
            }

            if (
                ($model->isAlreadyAuthorized() && $model->run())
                || ($request->isPost && $model->load($request->bodyParams) && $model->run())
            ) {
                try {
                    $session->offsetUnset(self::SESSION_AUTH_REQUEST);
                    return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response)->getResponse();
                } catch (OAuthServerException $exception) {
                    $session->offsetUnset(self::SESSION_AUTH_REQUEST);
                    return $exception->generateHttpResponse($response)->getResponse();
                } catch (\Exception $exception) {
                    return $exception;
                }
            }

            return $this->controller->render(
                'authorize',
                [
                    'model' => $model,
                    'client' => $authRequest->getClient(),
                    'scopes' => $authRequest->getScopes(),
                    'identity' => $user->identity
                ]
            );

        } catch (OAuthServerException $exception) {
            $response = $exception->generateHttpResponse($response);
            return $response->getResponse();
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}