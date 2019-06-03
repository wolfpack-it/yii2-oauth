<?php

use common\objects\ActionColumn;
use common\widgets\GridView;
use oauth\components\User as UserComponent;
use oauth\helpers\Html;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\Permission;
use oauth\models\search\Client as ClientSearch;
use yii\data\DataProviderInterface;
use yii\web\View;

/**
 * @var View $this
 * @var ClientSearch $clientSearch
 * @var DataProviderInterface $clientDataProvider
 * @var UserComponent $user
 */

$this->title = \Yii::t('app', 'Clients');

echo GridView::widget([
    'filterModel' => $clientSearch,
    'dataProvider' => $clientDataProvider,
        'toolbar' =>
            $user->can(Permission::PERMISSION_CREATE, new Client())
                ? Html::a(\Yii::t('app', 'Create'), ['/clients/create'], ['class' => 'btn btn-primary', 'data-pjax' => 0])
                : ''
    ,
    'columns' => [
        'name',
        'identifier',
        [
            'attribute' => 'status',
            'value' => 'displayStatus',
            'filter' => $clientSearch->statusOptions()
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{update} {redirects} {grantTypes} {scopes} {delete}',
            'buttons' => [
                'grantTypes' => function ($url, $model, $key) {
                    $title = \Yii::t('app', 'Update grant types');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Html::icon(Html::ICON_CLIENT_GRANT_TYPE), ['/clients/grant-types', 'id' => $model->id], $options);
                },
                'redirects' => function ($url, $model, $key) {
                    $title = \Yii::t('app', 'Update redirects');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Html::icon(Html::ICON_CLIENT_REDIRECTS), ['/clients/redirects', 'id' => $model->id], $options);
                },
                'scopes' => function ($url, $model, $key) {
                    $title = \Yii::t('app', 'Update scopes');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Html::icon(Html::ICON_CLIENT_SCOPES), ['/clients/scopes', 'id' => $model->id], $options);
                }
            ],
            'visibleButtons' => [
                'delete' => function($model, $key, $index) use ($user) {
                    return $user->can(Permission::PERMISSION_DELETE, $model);
                },
                'grantTypes' => function($model, $key, $index) use ($user) {
                    return $user->can(Permission::PERMISSION_WRITE, $model);
                },
                'redirects' => function($model, $key, $index) use ($user) {
                    return $user->can(Permission::PERMISSION_WRITE, $model);
                },
                'scopes' => function($model, $key, $index) use ($user) {
                    return $user->can(Permission::PERMISSION_WRITE, $model);
                },
                'update' => function($model, $key, $index) use ($user) {
                    return $user->can(Permission::PERMISSION_WRITE, $model);
                },
            ]
        ]
    ]
]);