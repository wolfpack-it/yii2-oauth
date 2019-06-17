# OAuth2 Server module for Yii2

This extension provides [The PHP League: OAuth 2.0 Server](https://oauth2.thephpleague.com/) module for the Yii2 Framework.

[The PHP League: OAuth 2.0 Server](https://oauth2.thephpleague.com/) is a package that makes setting up a OAuth2 server easy.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require wolfpack-it/yii2-oauth
```

or add

```
"wolfpack-it/yii2-oauth": "^<latest version>"
```

to the `require` section of your `composer.json` file.

## Configuring

### Configure module

The basic configuration is a module in your application:

```php
'modules' => [
    'oauth' => [
        'class' => \WolfpackIT\oauth\Module::class,
        'userClass' => '<class of ActiveRecordUser implementing UserEntityInterface>',
        'db' => 'db', // component that should be used for the database connection
        'publicKey' => '<path to public key file te be used by CryptKey, or configuration>',
        'privateKey' => '<path to private key file te be used by CryptKey, or configuration>',
        'encryptionKey' => '<random string for encryption>',
    ]
]
```

See the public variables of the [Module](https://github.com/wolfpack-it/yii2-oauth/blob/master/src/Module.php) for the full configuration options.

An example on how to generate keys can be found [here](https://oauth2.thephpleague.com/installation/).

Whenever the `AccessTokenService` is being injected via DI, the module needs to be added to the bootstrap of the application.

### Running migrations

The migrations can be ran automatically by adding them to the [migration namespaces](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#namespaced-migrations):
```php
'migrationNamespaces' => [
    'WolfpackIT\oauth\migrations',
    'console\migrations',
]
```

To get this working, you will need to add an alias:
```php
'aliases' => [
    '@WolfpackIT/oauth' => '@vendor/wolfpack-it/yii2-oauth/src',
]
```

If you want to override the database connection in the migrations, you will need to bootstrap the oauth module also in the console.

### Add routes
To have the module accessible, make sure the correct routes are set in your urlManager.

For example when your module is called `oauth`:

```php
'urlManager' => [
    'rules' => [
        'oauth/<controller:[\w-]+>/<action:[\w-]+>' => 'oauth/<controller>/<action>'
    ]
]
```

## TODO
- Add tests 

## Credits
- [Joey Claessen](https://github.com/joester89)
- [Wolfpack IT](https://github.com/wolfpack-it)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/wolfpack-it/yii2-oauth/blob/master/LICENSE) for more information.