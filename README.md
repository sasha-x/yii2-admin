Simple admin panel for yii2
===========================

Looks like "universal CRUD" for models list you configure.
Inspired by PhpMyAdmin & SonataAdminBundle

Простая админ-панель БД для yii2.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

### Required

1. Either run

```
php composer.phar require --prefer-dist sasha-x/yii2-admin "*"
```

or add

```
"sasha-x/yii2-admin": "*"
```

to the require section of your `composer.json` file.

2. In `config/web.php`

```php

'modules' => [
        'admin' => [
            'class' => 'sasha_x\admin\Module',
            'allowTruncate' => true,
            'models' => [
                'app\models\User',
                ... other models you need to edit ...
            ]
        ],
    ],

//Need to add custom routing
$config['bootstrap'][] = 'admin';

```

### Optional

3. Tweak scenarios for each destination model. Looks like:

```php

    //fields list for each action in admin module gets here
    public function scenarios()
    {
        return [
            'default' => ['username', 'email', 'plainPassword', 'status', 'is_admin'],
            'index' => ['username', 'email', 'status', 'is_admin', 'created_at', 'last_login'],
            'view' => ['id', 'username', 'email', 'status', 'is_admin', 'created_at', 'updated_at', 'last_login'],
            'create' => ['username', 'email', 'plainPassword', 'is_admin'],
            'update' => ['username', 'email', 'plainPassword', 'status', 'is_admin'],
        ];
    }

```

3.b. Make `UserAdmin` model class, if you don't want to touch basic model.

4. Assumed you have User model with is_admin property. If no, extend and edit AdminController::checkAccess() code.

Change defaults
---------------

Example:

```php
//new default values set
Yii::$container->setDefinitions([
    'yii\data\Pagination' => [
        'defaultPageSize' => 40,
    ],
    'yii\grid\ActionColumn' => [
        'template' => '{update} {delete}',
    ],
    'yii\i18n\Formatter' => [
        'dateFormat' => 'php:Y-m-d',
        'timeFormat' => 'php:H:i:s',
        'datetimeFormat' => 'php:Y-m-d H:i:s',
    ],
]);
```

TODO
----

- readOnly flag in table map
- model relations process

- релейшены = лейблы / отключаемые
- названия сценариев - константами/переменными
- расширеные тайпхинты для gridview и _form
- вынос конфига из центрального в модульный

- кастомные страницы
- modal, jexcel
