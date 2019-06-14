<?php

use kartik\form\ActiveForm;
use kartik\builder\Form;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\form\clients\GrantTypes;
use yii\helpers\Html;
use yii\web\View;


/**
 * @var View $this
 * @var Client $client
 * @var GrantTypes $model
 */

$this->title = \Yii::t('oauth','Update grant types of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('oauth', 'Update the grant types the client is allowed to use.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'grantTypes' => [
            'type' => Form::INPUT_CHECKBOX_LIST,
            'items' => $model->grantTypeOptions()
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

