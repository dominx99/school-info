<?php

use Overtrue\Socialite\SocialiteManager;
use Respect\Validation\Validator as v;

$container = $app->getContainer();

$container->register(new \dominx99\school\Services\Database\EloquentDatabaseProvider());

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('resources/views', [
        'cache' => false,
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $view->addExtension(new dominx99\school\TwigExtensions\CsrfExtension($container['guard']));

    return $view;
};

$container['guard'] = function () {
    return new \Slim\Csrf\Guard;
};

$container['validator'] = function () {
    return new \dominx99\school\Validation\Validator;
};

$container['auth'] = function () {
    return new \dominx99\school\Auth\Auth;
};

$container['socialite'] = function () {
    $prefix = strtoupper(getenv('APP_ENV')) . '_';

    $config = [
        'google' => [
            'client_id'     => $_ENV[$prefix . 'GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV[$prefix . 'GOOGLE_SECRET'],
            'redirect'      => $_ENV[$prefix . 'GOOGLE_REDIRECT'],
        ],
        'github' => [
            'client_id'     => $_ENV[$prefix . 'GITHUB_CLIENT_ID'],
            'client_secret' => $_ENV[$prefix . 'GITHUB_SECRET'],
            'redirect'      => $_ENV[$prefix . 'GITHUB_REDIRECT'],
        ],
    ];

    return new SocialiteManager($config);
};

$container['avaibleProviders'] = function () {
    return ['google', 'github'];
};

$container['DocsController'] = function ($container) {
    return new \dominx99\school\Controllers\DocsController($container);
};

$container['AuthController'] = function ($container) {
    return new \dominx99\school\Controllers\AuthController($container);
};

$container['ApiAuthController'] = function ($container) {
    return new \dominx99\school\Controllers\Api\ApiAuthController($container);
};

$container['SocialiteController'] = function ($container) {
    return new \dominx99\school\Controllers\SocialiteController($container);
};

v::with('dominx99\school\Validation\\Rules\\');
