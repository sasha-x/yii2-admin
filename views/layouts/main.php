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
            $classes = ['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center'];
            foreach ($this->context->modelMap as $id => $class) {
                $classNs = explode('\\', $class);
                $label = Html::tag('span', Html::encode(end($classNs))) . '<span class="icon"></span>';
                echo Html::a($label, ["$id/index"], [
                    'class' => $id === $this->params['modelSlug'] ? array_merge($classes, ['active']) : $classes,
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
