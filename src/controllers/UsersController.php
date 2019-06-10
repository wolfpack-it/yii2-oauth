<?php

namespace WolfpackIT\oauth\controllers;

use common\models\activeRecord\Permission;
use oauth\components\repository\ScopeRepository;
use oauth\components\User as UserComponent;
use oauth\components\UserClientService;
use oauth\models\activeRecord\User;
use oauth\models\search\Client;
use yii\helpers\ArrayHelper;
use yii\web\Request;

/**
 * Class UsersController
 * @package WolfpackIT\oauth\controllers
 */
class UsersController extends Controller
{
    protected $userClass = User::class;

    public function actionAuthorizedClients(
        UserClientService $userClientService,
        ScopeRepository $scopeRepository,
        Request $request,
        UserComponent $user,
        $id = null
    ) {
        /** @var User $userModel */
        $userModel = $id ? $this->findUser($id, Permission::PERMISSION_VIEW) : $user->identity;

        $filterModel = \Yii::createObject(
            Client::class,
            [$user, ['ids' => ArrayHelper::getColumn($userClientService->getAuthorizedClientsForUser($userModel), 'id')]]
        );
        $filterModel->load($request->queryParams);

        return $this->render(
            ['authorizedClients', '@common/views/users/authorizedClients'],
            [
                'userModel' => $userModel,
                'user' => $user,
                'filterModel' => $filterModel,
                'dataProvider' => $filterModel->search(),
                'scopeRepository' => $scopeRepository
            ]
        );
    }
}