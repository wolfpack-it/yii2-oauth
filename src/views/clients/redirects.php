<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Form;
use oauth\models\activeRecord\Client;
use oauth\models\form\clients\Redirects;
use yii\web\View;

/**
 * @var View $this
 * @var Client $client
 * @var Redirects $model
 */

$this->title = \Yii::t('app','Update redirects of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('app', 'Update the set of redirects the client is allowed to use.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'redirects' => [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => \unclead\multipleinput\MultipleInput::class,
            'options' => [
                'enableError' => true,
            ]
        ]
    ],
    'buttons' => [
        [
            'content' => \Yii::t('app', 'Save'),
            'type' => 'submit',
            'class' => ['btn', 'btn-primary']
        ]
    ]
]);

ActiveForm::end();

