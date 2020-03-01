<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$title = $elementTitle;
$this->title = Yii::t('app', 'Update '.$modelTitle.': {name}', [
    'name' => $title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $modelTitle), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class=<?=$modelSlug?>"-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelSlug' => $modelSlug,
        'columns' => $columns,
    ]) ?>

</div>
