<?php

use common\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var string $content
 */

?>

<?php $this->beginContent('@frontend/views/layouts/base.php') ?>

<?= $this->render('../menu') ?>

    <div id="wrap">
        <div class="container">
            <?= !empty($this->title) ? Html::tag('h1', $this->title) : '' ?>
            <?= $content ?>
        </div>
    </div>

<?= $this->render('../footer') ?>

<?php $this->endContent() ?>