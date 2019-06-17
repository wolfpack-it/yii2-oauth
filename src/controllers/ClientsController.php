<?php

namespace WolfpackIT\oauth\controllers;

use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\form\clients\GrantTypes;
use WolfpackIT\oauth\models\form\clients\Redirects;
use WolfpackIT\oauth\models\form\clients\Scopes;
use WolfpackIT\oauth\models\search\Client as ClientSearch;
use WolfpackIT\oauth\Module;
use yii\base\Security;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\User as UserComponent;

/**
 * Class ClientsController
 * @package WolfpackIT\oauth\controllers
 */
class ClientsController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * @var string
     */
    public $modelClass = Client::class;

    public function actionCreate(
        Request $request,
        UserComponent $user,
        Security $security
    ) {
        /** @var Module $module */
        $module = $this->module;
        /** @var Client $model */
        $model = new $this->modelClass([
            'scenario' => Client::SCENARIO_CREATE
        ]);

        if (!$user->can($module->clientCreatePermission, $model)) {
            throw new ForbiddenHttpException(\Yii::t('oauth', 'You do not have permission to {permission}', ['permission' => $module->clientCreatePermission]));
        }

        $model->identifier = $security->generateRandomString(16);
        if ($request->isPost && $model->load($request->bodyParams) && $model->save()) {
            return $this->redirect(['clients/list']);
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
        /** @var Module $module */
        $module = $this->module;
        $model = $this->findClient($id, $module->clientDeletePermission);

        if ($request->isDelete) {
            $model->delete();
        }

        return $this->redirect(['clients/list']);
    }

    public function actionGrantTypes(
        Request $request,
        $id
    ) {
        /** @var Module $module */
        $module = $this->module;

        $client = $this->findClient($id, $module->clientUpdatePermission);
        $model = \Yii::createObject(GrantTypes::class, [$client]);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['clients/list']);
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
        /** @var Module $module */
        $module = $this->module;
        $clientSearch = \Yii::createObject(ClientSearch::class);
        $clientSearch->load($request->queryParams);
        $clientDataProvider = $clientSearch->search();

        return $this->render(
            'list',
            [
                'clientSearch' => $clientSearch,
                'clientDataProvider' => $clientDataProvider,
                'user' => $user,
                'module' => $module,
                'modelClass' => $this->modelClass
            ]
        );
    }

    public function actionRedirects(
        Request $request,
        $id
    ) {
        /** @var Module $module */
        $module = $this->module;

        $client = $this->findClient($id, $module->clientUpdatePermission);
        $model = new Redirects($client);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['clients/list']);
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
        /** @var Module $module */
        $module = $this->module;

        $client = $this->findClient($id, $module->clientUpdatePermission);
        $model = new Scopes($client);

        if ($request->isPut && $model->load($request->bodyParams) && $model->runInternal()) {
            return $this->redirect(['clients/list']);
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
        /** @var Module $module */
        $module = $this->module;

        $model = $this->findClient($id, $module->clientUpdatePermission);
        $model->scenario = Client::SCENARIO_UPDATE;

        if ($request->isPut && $model->load($request->bodyParams) && $model->save()) {
            return $this->redirect(['clients/list']);
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

    protected function findClient($id, $permission = null): ?Client
    {
        /** @var Module $module */
        $module = $this->module;
        $permission = $permission ?? $module->defaultPermission;
        return parent::findModel($this->modelClass, $id, $permission);
    }
}