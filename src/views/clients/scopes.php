<?php

use kartik\form\ActiveForm;
use kartik\builder\Form;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\form\clients\Scopes;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var Client $client
 * @var Scopes $model
 */

$this->title = \Yii::t('app','Update scopes of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('app', 'Update the set of scopes the client is allowed to ask.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'scopes' => [
            'type' => Form::INPUT_CHECKBOX_LIST,
            'items' => $model->scopeOptions()
        ],
        'actions' => [
            'type' => Form::INPUT_RAW,
            'value' =>
                Html::beginTag('div', ['class' => ['text-right']]) .
                Html::submitButton(\Yii::t('oauth', 'Save'), ['class' => ['btn', 'btn-primary']]) .
                Html::endTag('div')
        ]
    ],
]);

$form::end();

