<?php

use yii\helpers\Html;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = Yii::t('app', "Create $modelTitle");
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $modelTitle), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= $modelSlug ?>-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelSlug' => $modelSlug,
        'columns' => $columns,
    ]) ?>

</div>
