<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $generators \yii\gii\Generator[] */
/* @var $activeGenerator \yii\gii\Generator */
/* @var $content string */

?>
<?php $this->beginContent('@app/views/layouts/main.php'); ?>
<div class="row">
    <div class="col-md-2 col-sm-3">
        <div class="list-group">
            <?php
            $baseClasses = ['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center'];
            foreach ($this->params['leftMenu'] as $slug => $className) {
                $label = Html::tag('span', Html::encode($className)) . '<span class="icon"></span>';

                $classes = ($slug === $this->params['modelSlug']) ? array_merge($baseClasses,
                    ['active']) : $baseClasses;

                echo Html::a($label, ["$slug/index"], [
                    'class' => $classes,
                ]);
            }
            ?>
        </div>
    </div>
    <div class="col-md-10 col-sm-9">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent(); ?>
