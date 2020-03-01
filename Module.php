<?php

namespace app\modules\admin;

use yii\base\BootstrapInterface;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $defaultRoute = 'admin';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    public $modelMap = [];

    public function bootstrap($app)
    {
        $module = static::getInstance();
        $id = $module->id;
        $app->getUrlManager()->addRules([
            "$id/<model:\S+>/<action>" => "$id/admin/<action>",
        ], false);
    }
}
