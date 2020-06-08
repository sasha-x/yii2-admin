<?php

namespace sasha_x\admin;

use yii\base\BootstrapInterface;
use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $defaultRoute = 'admin';

    public $models = [];

    public $allowTruncate = false;

    public $customViewsPath = null;

    public function init()
    {
        parent::init();

        /*Yii::$container->setDefinitions([
            \yii\db\mysql\ColumnSchema::class => [
                'class' => \yii\db\mysql\ColumnSchema::class,
                'disableJsonSupport' => true,
            ],
        ]);*/



    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // your custom code here
        return true; // or false to not run the action
    }

    public function bootstrap($app)
    {
        $module = static::getInstance();
        $moduleId = $module->id;
        $app->getUrlManager()->addRules([
            "$moduleId/<model:\S+>/<action>" => "$moduleId/admin/<action>",
            "$moduleId/<model:\S+>" => "$moduleId/admin/index",
            "$moduleId" => "$moduleId/admin/index",
        ], false);
    }
}
