<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Form;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\Scope;
use WolfpackIT\oauth\models\form\authorize\Authorize;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var Authorize $model
 * @var Client $client
 * @var Scope[] $scopes
 * @var UserEntityInterface $identity
 */

$this->title = \Yii::t('app', 'Authorize {clientName}', ['clientName' => $client->name]);

echo Html::tag(
    'p',
    \Yii::t(
        'app',
        'You are logged in as {userName}.<br>{a}Click here to continue as someone else{/a}.',
        [
            'userName' => Html::tag('strong', $identity->{$identity->displayAttribute()}),
            'a' => Html::beginTag(
                'a',
                [
                    'href' => Url::to(['authorize/logout']),
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