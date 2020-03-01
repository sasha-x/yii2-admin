yii2-admin
==========

Simple admin panel for yii2. 
Looks like "universal CRUD" for models list you configure.

Простая админ-панель БД для yii2.


Install
-------

1. `composer require sasha-x/yii2-admin`

2. In `config/web.php`

```php

'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
            'modelMap' => [
                'user' =>  'app\models\User',
                ... other models you like ...
            ]
        ],
    ],


$config['bootstrap'][] = 'admin';

```

3. Assumed you have user model with is_admin property. If no, extend and edit AdminController code.

4. If you have troubles with json type columns, add it to `config/web.php`

```php

'container' => [
        'definitions' => [
            \yii\db\mysql\ColumnSchema::class => [
                'class' => \yii\db\mysql\ColumnSchema::class,
                'disableJsonSupport' => true,
            ],
        ],
    ]

```