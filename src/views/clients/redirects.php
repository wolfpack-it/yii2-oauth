<?php

use kartik\form\ActiveForm;
use kartik\builder\Form;
use unclead\multipleinput\MultipleInput;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\form\clients\Redirects;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var Client $client
 * @var Redirects $model
 */

$this->title = \Yii::t('oauth','Update redirects of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('oauth', 'Update the set of redirects the client is allowed to use.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'redirects' => [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => MultipleInput::class,
            'options' => [
                'enableError' => true,
            ]
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

