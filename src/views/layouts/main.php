<?php

use kartik\icons\Icon;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var string $content
 */

Icon::map($this);

?>

<?php $this->beginContent(__DIR__ . '/base.php') ?>

<?= $this->render('../menu') ?>

    <div id="wrap">
        <div class="container">
            <?= !empty($this->title) ? Html::tag('h1', $this->title) : '' ?>
            <?= $content ?>
        </div>
    </div>

<?php $this->endContent() ?>