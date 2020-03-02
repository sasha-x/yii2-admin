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
        //TODO: textarea, checkbox, datetime, password & other input types
        [$column, $format] = explode(':', $column);
        $field = $form->field($model, $column);

        switch ($format) {
            case 'boolean':
                $input = $field->checkbox();
                break;
            case 'password':
                $input = $field->passwordInput();
                break;
            default:
                $input = $field->textInput(['maxlength' => true]);
                break;
        }

        echo $input;
    }

    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
