<?php

namespace oauth\controllers;

use common\components\User as UserComponent;
use common\exceptions\ForbiddenPermissionHttpException;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\Permission;
use oauth\models\form\clients\GrantTypes;
use oauth\models\form\clients\Redirects;
use oauth\models\form\clients\Scopes;
use oauth\models\search\Client as ClientSearch;
use yii\base\Security;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class ClientsController extends Controller
{
    public $defaultAction = 'list';

    public function actionCreate(
        Request $request,
        UserComponent $user,
        Security $security
    ) {
        $model = new Client([
            'scenario' => Client::SCENARIO_CREATE
        ]);

        if (!$user->can(Permission::PERMISSION_CREATE, $model)) {
            throw new ForbiddenPermissionHttpException(Permission::PERMISSION_CREATE);
        }

        $model->identifier = $security->generateRandomString(16);
        if ($request->isPost && $model->load($request->bodyParams) && $model->save()) {
            return $this->redirect(['/clients/list']);
        }

        return $this->render(
            'update',
            [
                'model' => $model
            ]
        );
    }

    public function actionDelete(
        Request $request,
        $id
    ) {
        $model = $this->findClient($id, Permission::PERMISSION_DELETE);

        if ($request->isDelete) {
            $model->delete();
        }

        return $this->redirect(['/clients/list']);
    }

    public function actionGrantTypes(
        Request $request,
        $id
    ) {
        $client = $this->findClient($id, Permission::PERMISSION_WRITE);
        $model = new GrantTypes($client);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['/clients/list']);
        }

        return $this->render(
            'grantTypes',
            [
                'client' => $client,
                'model' => $model
            ]
        );
    }

    public function actionList(
        Request $request,
        UserComponent $user
    ) {
        $clientSearch = new ClientSearch($user);
        $clientSearch->load($request->queryParams);
        $clientDataProvider = $clientSearch->search();

        return $this->render(
            'list',
            [
                'clientSearch' => $clientSearch,
                'clientDataProvider' => $clientDataProvider,
                'user' => $user
            ]
        );
    }

    public function actionRedirects(
        Request $request,
        $id
    ) {
        $client = $this->findClient($id, Permission::PERMISSION_WRITE);
        $model = new Redirects($client);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['/clients/list']);
        }

        return $this->render(
            'redirects',
            [
                'client' => $client,
                'model' => $model
            ]
        );
    }

    public function actionScopes(
        Request $request,
        $id
    ) {
        $client = $this->findClient($id, Permission::PERMISSION_WRITE);
        $model = new Scopes($client);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['/clients/list']);
        }

        return $this->render(
            'scopes',
            [
                'client' => $client,
                'model' => $model
            ]
        );
    }

    public function actionUpdate(
        Request $request,
        int $id
    ) {
        $model = $this->findClient($id, Permission::PERMISSION_WRITE);
        $model->scenario = Client::SCENARIO_UPDATE;

        if ($request->isPut && $model->load($request->bodyParams) && $model->save()) {
            return $this->redirect(['/clients/list']);
        }

        return $this->render(
            'update',
            [
                'model' => $model
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
                            'allow' => true,
                            'actions' => [
                                'create',
                                'delete',
                                'grant-types',
                                'list',
                                'redirects',
                                'scopes',
                                'update'
                            ],
                            'roles' => ['@']
                        ]
                    ]
                ]
            ],
            parent::behaviors()
        );
    }

    protected function findClient($id, $permission = Permission::PERMISSION_ADMINISTER): ?Client
    {
        return parent::findModel(Client::class, $id, $permission);
    }
}