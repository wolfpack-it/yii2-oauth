<?php

namespace WolfpackIT\oauth\actions;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WolfpackIT\oauth\components\AuthorizationServer;
use WolfpackIT\oauth\components\Request;
use WolfpackIT\oauth\components\Response;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Request as YiiRequest;
use yii\web\Response as YiiResponse;

/**
 * Class AccessTokenAction
 * @package WolfpackIT\oauth\actions
 */
class AccessTokenAction extends Action
{
    /**
     * Required params to send: see https://oauth2.thephpleague.com/authorization-server/
     *
     * @param AuthorizationServer $authorizationServer
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Exception|OAuthServerException|mixed|ResponseInterface
     * @throws InvalidConfigException
     */
    public function run(
        AuthorizationServer $authorizationServer,
        YiiRequest $request,
        YiiResponse $response
    ) {
        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidConfigException('The request class for the module must implement ' . ServerRequestInterface::class . ', use can use ' . Request::class . ' via DI.');
        }

        if (!$response instanceof ResponseInterface) {
            throw new InvalidConfigException('The response class for the module must implement ' . ResponseInterface::class . ', use can use ' . Response::class . ' via DI.');
        }

        try {
            $authorizationServer->respondToAccessTokenRequest($request, $response);
            //Since the default response format is json, json decode it so content negotiation works
            return json_decode($response->content);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}