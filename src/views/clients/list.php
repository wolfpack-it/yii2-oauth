<?php

use kartik\icons\Icon;
use WolfpackIT\oauth\models\search\Client as ClientSearch;
use WolfpackIT\oauth\Module;
use yii\data\DataProviderInterface;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\User as UserComponent;
use yii\web\View;

/**
 * @var View $this
 * @var ClientSearch $clientSearch
 * @var DataProviderInterface $clientDataProvider
 * @var UserComponent $user
 * @var Module $module
 * @var string $modelClass
 */

$this->title = \Yii::t('oauth', 'Clients');

echo Html::beginTag('div', ['class' => 'text-right']);
echo $user->can($module->clientCreatePermission, new $modelClass())
    ? Html::a(\Yii::t('oauth', 'Create'), ['clients/create'], ['class' => 'btn btn-primary', 'data-pjax' => 0])
    : '';
echo Html::endTag('div');

echo GridView::widget([
    'filterModel' => $clientSearch,
    'dataProvider' => $clientDataProvider,
    'columns' => [
        'name',
        'identifier',
        [
            'attribute' => 'status',
            'value' => 'displayStatus',
            'filter' => $clientSearch->statusOptions()
        ],
        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => '{update} {redirects} {grantTypes} {scopes} {delete}',
            'contentOptions' => [
                'class' => ['text-center']
            ],
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Update');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Icon::show('edit'), ['clients/update', 'id' => $model->id], $options);
                },
                'grantTypes' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Update grant types');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Icon::show('arrow-circle-down'), ['clients/grant-types', 'id' => $model->id], $options);
                },
                'redirects' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Update redirects');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Icon::show('arrow-circle-left'), ['clients/redirects', 'id' => $model->id], $options);
                },
                'scopes' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Update scopes');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Icon::show('arrow-circle-up'), ['clients/scopes', 'id' => $model->id], $options);
                },
                'delete' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Delete');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0', 'data-method' => 'delete', 'data-confirm' => \Yii::t('oauth', 'Are you sure you want to delete the client?')];
                    return Html::a(Icon::show('trash-alt'), ['clients/delete', 'id' => $model->id], $options);
                }
            ],
            'visibleButtons' => [
                'delete' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->clientDeletePermission, $model);
                },
                'grantTypes' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->clientUpdatePermission, $model);
                },
                'redirects' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->clientUpdatePermission, $model);
                },
                'scopes' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->clientUpdatePermission, $model);
                },
                'update' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->clientUpdatePermission, $model);
                },
            ]
        ]
    ]
]);