<?php

namespace oauth\controllers;

use common\models\form\session\Create;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use oauth\components\AuthorizationServer;
use oauth\components\User as UserComponent;
use oauth\components\WebRequest;
use oauth\components\WebResponse;
use oauth\models\activeRecord\User;
use oauth\models\form\authorize\Authorize;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Session;

/**
 * Class AuthorizationController
 * @package oauth\controllers
 */
class AuthorizationController extends Controller
{
    /**
     * Check if the user enters the correct credentials, log the user in and redirect to the authorize page
     */
    public function actionLogin (
        WebRequest $request,
        UserComponent $user
    ) {
        $user->setReturnUrl(Url::to(['/' . $this->id . '/' . $this->action->id]));

        if (!$user->isGuest) {
            return $this->redirect(['authorization/authorize']);
        }

        $model = \Yii::createObject(Create::class, [$user]);

        if ($request->isPost && $model->load($request->bodyParams) && $model->run()) {
            return $this->redirect(['authorization/authorize']);
        }

        return $this->render(
            'login',
            [
                'model' => $model
            ]
        );
    }

    /**
     * Let the user accept the requested scopes
     */
    public function actionAuthorize(
        AuthorizationServer $authorizationServer,
        WebRequest $request,
        WebResponse $response,
        UserComponent $user,
        Session $session
    ) {
        /** @var AuthorizationRequest $authRequest */
        $authRequest = $session->get(AuthorizeController::SESSION_AUTH_REQUEST);
        /** @var User $identity */
        $identity = $user->identity;

        if (is_null($authRequest)) {
            return $this->goHome();
        }

        $model = new Authorize($authRequest, $identity);

        if ($model->isAlreadyAuthorized()) {
            $model->authorizeScopes = true;
        }

        if (
            ($model->isAlreadyAuthorized() && $model->run())
            || ($request->isPost && $model->load($request->bodyParams) && $model->run())
        ) {
            try {
                $session->offsetUnset(AuthorizeController::SESSION_AUTH_REQUEST);
                return $authorizationServer->completeAuthorizationRequest($authRequest, $response);
            } catch (OAuthServerException $exception) {
                $session->offsetUnset(AuthorizeController::SESSION_AUTH_REQUEST);
                return $exception->generateHttpResponse($response);
            } catch (\Exception $exception) {
                return $exception;
            }
        }

        return $this->render(
            'authorize',
            [
                'model' => $model,
                'client' => $authRequest->getClient(),
                'scopes' => $authRequest->getScopes(),
                'identity' => $identity
            ]
        );
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['login'],
                            'allow' => true,
                            'roles' => ['?', '@']
                        ],
                        [
                            'actions' => ['authorize'],
                            'allow' => true,
                            'roles' => ['@']
                        ]
                    ]
                ]
            ],
            parent::behaviors()
        );
    }
}