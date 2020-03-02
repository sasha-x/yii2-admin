yii2-admin
==========

Simple admin panel for yii2. 
Looks like "universal CRUD" for models list you configure.

Простая админ-панель БД для yii2.


Install
-------

### Required

1. `composer require sasha-x/yii2-admin`

2. In `config/web.php`

```php

'modules' => [
        'admin' => [
            'class' => 'sasha_x\admin\Module',
            'modelMap' => [
                'user' =>  'app\models\User',
                ... other models you like ...
            ]
        ],
    ],


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

4. Assumed you have user model with is_admin property. If no, extend and edit AdminController code.

5. If you have troubles with json type columns, add it to `config/web.php`

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
