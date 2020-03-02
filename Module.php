<?php

namespace sasha_x\admin;

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
        $moduleId = $module->id;
        $app->getUrlManager()->addRules([
            "$moduleId/<model:\S+>/<action>" => "$moduleId/admin/<action>",
            "$moduleId/<model:\S+>" => "$moduleId/admin/index",
            "$moduleId" => "$moduleId/admin/hello",
        ], false);
    }
}
