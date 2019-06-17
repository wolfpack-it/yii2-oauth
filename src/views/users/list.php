<?php

use kartik\icons\Icon;
use WolfpackIT\oauth\models\search\User as UserSearch;
use WolfpackIT\oauth\Module;
use yii\data\DataProviderInterface;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\User as UserComponent;
use yii\web\View;

/**
 * @var View $this
 * @var UserSearch $userSearch
 * @var DataProviderInterface $userDataProvider
 * @var UserComponent $user
 * @var Module $module
 */

$this->title = \Yii::t('oauth', 'Users');

echo GridView::widget([
    'filterModel' => $userSearch,
    'dataProvider' => $userDataProvider,
    'columns' => [
        [
            'attribute' => 'search',
            'value' => (new $userSearch->modelClass())->displayAttribute(),
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{authorizedClients}',
            'contentOptions' => [
                'class' => ['text-center']
            ],
            'buttons' => [
                'authorizedClients' => function ($url, $model, $key) {
                    $title = \Yii::t('oauth', 'Authorized clients');
                    $options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
                    return Html::a(Icon::show('eye'), ['users/authorized-clients', 'id' => $model->id], $options);
                },
            ],
            'visibleButtons' => [
                'authorizedClients' => function($model, $key, $index) use ($user, $module) {
                    return $user->can($module->userViewPermission, $model);
                },
            ]
        ]
    ]
]);