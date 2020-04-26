<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model app\models\Device */
/* @var $form yii\widgets\ActiveForm */
?>

<div class=<?= $modelSlug ?>"-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'offset' => 'col-sm-offset-3',
                'label' => 'col-sm-2',
                'wrapper' => 'col-sm-9',
                'error' => '',
                'hint' => 'col-sm-3',
            ],
        ],
    ]);

    foreach ($columns as $column) {
        //TODO: textarea, checkbox, datetime, password & other input types
        [$column, $format] = explode(':', $column);
        /** @var \yii\widgets\ActiveField $field */
        $field = $form->field($model, $column);
        $value = Html::getAttributeValue($model, $column);

        switch ($format) {
            case 'boolean':
                $input = $field->checkbox();
                break;
            case 'password':
                $input = $field->passwordInput();
                break;
            default:
                if (is_array($value)) {
                    $value = Json::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE |  JSON_PRETTY_PRINT);
                    $input = $field->textarea([
                        'value' => $value,
                    ]);
                } else {
                    $input = $field->textInput(['maxlength' => true]);
                }
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
