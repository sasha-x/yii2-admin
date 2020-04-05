<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = Yii::t('app', 'Update '.$modelTitle.': {name}', [
    'name' => $elementTitle,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $modelTitle), 'url' => ["$modelSlug/index"]];
$this->params['breadcrumbs'][] = ['label' => $elementTitle, 'url' => ['view', 'id' => $model->id]];
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
