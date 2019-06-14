<?php

namespace WolfpackIT\oauth\controllers;

use WolfpackIT\oauth\components\repository\ClientRepository;
use WolfpackIT\oauth\components\repository\ScopeRepository;
use WolfpackIT\oauth\components\UserClientService;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\search\Client;
use WolfpackIT\oauth\models\search\User as UserSearch;
use WolfpackIT\oauth\Module;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\User as UserComponent;

/**
 * Class UsersController
 * @package WolfpackIT\oauth\controllers
 */
class UsersController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'list';

    public function actionAuthorizedClients(
        UserClientService $userClientService,
        ScopeRepository $scopeRepository,
        Request $request,
        UserComponent $user,
        $id = null
    ) {
        /** @var Module $module */
        $module = $this->module;

        /** @var UserEntityInterface $userModel */
        $userModel = $id ? $this->findUser($id, $module->userViewPermission) : $user->identity;
        $clientIds = ArrayHelper::getColumn($userClientService->getAuthorizedClientsForUser($userModel), 'id');


        $filterModel = \Yii::createObject(
            Client::class,
            [['ids' => $clientIds]]
        );
        $filterModel->load($request->queryParams);

        return $this->render(
            'authorizedClients',
            [
                'userModel' => $userModel,
                'user' => $user,
                'filterModel' => $filterModel,
                'dataProvider' => $filterModel->search(),
                'scopeRepository' => $scopeRepository
            ]
        );
    }

    public function actionList(
        Request $request,
        UserComponent $user
    ) {
        /** @var Module $module */
        $module = $this->module;

        $userClass = $module->userClass;

        if (!$user->can($module->userListPermission, new $userClass())) {
            throw new ForbiddenHttpException(\Yii::t('oauth', 'You do not have permission to {permission}', ['permission' => $module->userListPermission]));
        }

        $userSearch = \Yii::createObject(UserSearch::class, [['modelClass' => $userClass]]);
        $userSearch->load($request->queryParams);
        $userDataProvider = $userSearch->search();

        return $this->render(
            'list',
            [
                'userSearch' => $userSearch,
                'userDataProvider' => $userDataProvider,
                'user' => $user,
                'module' => $module
            ]
        );
    }

    public function actionRevokeClient(
        ClientRepository $clientRepository,
        UserClientService $userClientService,
        Request $request,
        $id,
        $clientId
    ) {
        /** @var Module $module */
        $module = $this->module;

        $this->findUser($id, $module->userWritePermission);

        if ($request->isDelete) {
            $user = $module->userClass::findOne(['id' => $id]);
            $client = $clientRepository->modelClass::findOne(['id' => $clientId]);
            $userClientService->revokeClientForUser($user, $client);
        }

        return $request->referrer ? $this->redirect($request->referrer) : $this->goBack();
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => [
                                'authorized-clients',
                                'list',
                                'revoke-client',
                            ],
                            'roles' => ['@']
                        ]
                    ]
                ]
            ],
            parent::behaviors()
        );
    }

    /**
     * @param $id
     * @param null $permission
     * @return UserEntityInterface|null
     * @throws ForbiddenHttpException
     */
    protected function findUser($id, $permission = null): ?UserEntityInterface
    {
        /** @var Module $module */
        $module = $this->module;
        $permission = $permission ?? $module->defaultPermission;

        return parent::findModel($module->userClass, $id, $permission);
    }
}