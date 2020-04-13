<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', $modelTitle);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= $modelSlug ?>-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create ' . $modelTitle), ["$modelSlug/create"], ['class' => 'btn btn-success']) ?>
        <?php
        if ($this->params['allowTruncate']) {
            echo Html::a(Yii::t('app', 'Truncate'), ["$modelSlug/truncate"], [
                'class' => 'btn btn-danger float-right pull-right',
                'data' => [
                    'confirm' => Yii::t('app',
                        'Are you sure you want to truncate this table? You really cant revert it.'),
                    'method' => 'post',
                ],
            ]);
        }
        ?>
    </p>

    <?php
    $columnsList = [];


    //$columnsList[] = ['class' => 'yii\grid\SerialColumn'];

    //model columns list to show
    foreach ($columns as $column) {
        [$column, $format] = explode(':', $column);
        $columnAttrs = [
            'attribute' => $column,
            'format' => $format,
        ];
        if ($format != 'boolean') {
            $columnAttrs['filterInputOptions'] = [
                'class' => 'form-control',
                'id' => null,
                'size' => '10',
            ];
        }
        $columnsList[] = $columnAttrs;
    }
    $columnsList[] = [
        'class' => 'yii\grid\ActionColumn',
        'controller' => $modelSlug,
    ];

    Pjax::begin();

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columnsList,
    ]);

    Pjax::end();
    ?>


</div>
