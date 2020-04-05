Simple admin panel for yii2
===========================

Looks like "universal CRUD" for models list you configure.

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

4. Assumed you have user model with is_admin property. If no, extend and edit AdminController::checkAccess() code.

TODO
----

- readOnly flag in table map
- model relations process