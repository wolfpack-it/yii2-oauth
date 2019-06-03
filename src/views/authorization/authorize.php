<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Form;
use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\Scope;
use oauth\models\activeRecord\User;
use oauth\models\form\authorize\Authorize;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * @var View $this
 * @var Authorize $model
 * @var Client $client
 * @var Scope[] $scopes
 * @var User $identity
 */

$this->title = \Yii::t('app', 'Authorize {clientName}', ['clientName' => $client->name]);

echo Html::tag(
    'p',
    \Yii::t(
        'app',
        'You are logged in as {userName}.<br>{a}Click here to continue as someone else{/a}.',
        [
            'userName' => Html::tag('strong', $identity->name),
            'a' => Html::beginTag(
                'a',
                [
                    'href' => \yii\helpers\Url::to(['/session/delete']),
                    'data-method' => 'delete'
                ]
            ),
            '/a' => Html::endTag('a')
        ]
    )
);

$form = ActiveForm::begin([

]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'scopes' => [
            'type' => Form::INPUT_RAW,
            'value' => \Yii::t('app', '{clientName} is requesting the following scopes:', ['clientName' => $client->name]) .
                Html::ul(ArrayHelper::getColumn($scopes, 'user_name'))
        ],
    ],
    'buttons' => [
        [
            'content' => \Yii::t('app', 'Reject'),
            'type' => 'submit',
            'class' => ['btn btn-secondary hidden-xs'],
            'name' => Html::getInputName($model, 'authorizeScopes'),
            'value' => '0'
        ],
        [
            'content' => \Yii::t('app', 'Accept'),
            'type' => 'submit',
            'class' => ['btn', 'btn-success'],
            'name' => Html::getInputName($model, 'authorizeScopes'),
            'value' => '1'
        ],
        [
            'content' => \Yii::t('app', 'Reject'),
            'type' => 'submit',
            'class' => ['btn btn-secondary visible-xs'],
            'name' => Html::getInputName($model, 'authorizeScopes'),
            'value' => '0'
        ],
    ]
]);

ActiveForm::end();