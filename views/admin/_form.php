<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Device */
/* @var $form yii\widgets\ActiveForm */
?>

<div class=<?= $modelSlug ?>"-form">

    <?php $form = ActiveForm::begin();

    foreach ($columns as $column) {
        //TODO: checkbox, datetime, password & other input types
        echo $form->field($model, $column)->textInput(['maxlength' => true]);
    }

    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
