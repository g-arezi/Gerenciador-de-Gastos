<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

// Configurar container
$container = new Container();
AppFactory::setContainer($container);

// Configurar Twig
$container->set('view', function() {
    return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
});

// Criar aplicação
$app = AppFactory::create();

// Adicionar middleware de parsing do corpo da requisição
$app->addBodyParsingMiddleware();

// Adicionar middleware do Twig
$app->add(TwigMiddleware::createFromContainer($app, 'view'));

// Adicionar middleware de roteamento
$app->addRoutingMiddleware();

// Adicionar middleware de tratamento de erros
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Iniciar sessão
session_start();

// Registrar rotas
require __DIR__ . '/../src/routes.php';

$app->run();
