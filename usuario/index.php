<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
 
$app = AppFactory::create();
 
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
 
$usuarios = [
        ['id' => 1, 'nome' => 'Ana Silva',
         'email' => 'ana@gmail.com',
         'login' => 'ana',
         'senha' => 'senha123'],

        ['id' => 2, 'nome' => 'Bruno Souza',
         'email' => 'bruno@gmail.com',
         'login' => 'bruno',
         'senha' => 'senha123'],

        ['id' => 3, 'nome' => 'Carla Lima',
         'email' => 'carla@gmail.com',
         'login' => 'carla',
         'senha' => 'senha123'],

        ['id' => 4, 'nome' => 'Diego Costa',
         'email' => 'diego@gmail.com',
         'login' => 'diego',
         'senha' => 'senha123'],

        ['id' => 5, 'nome' => 'Eva Martins',
         'email' => 'eva@gmail.com',
         'login' => 'eva',
         'senha' => 'senha123'],
];
 
// status
$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
 
//GET
$app->get('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $response->getBody()->write(json_encode($usuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
//Filtro
$app->get('/usuarios', function ($request, $response) use (&$usuarios) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
 
    if($nome) {
        $usuariosFiltrados = array_filter($usuarios, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
    } else {
        $usuariosFiltrados = $usuarios;
    }
 
    $response->getBody()->write(json_encode(array_values($usuariosFiltrados)));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
//POST
$app->post('/usuarios', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();
    $novoUsuario = ['id' => count($usuarios) + 1, 'nome' => $dados['nome'], 'email' => $dados['email'], 'login' => $dados['login'], 'senha' => $dados['senha']];
    $usuarios[] = $novoUsuario;
    $response->getBody()->write(json_encode($novoUsuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});
 
//PUT
$app->put('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $dados = $request->getParsedBody();
    $usuario['nome'] = $dados['nome'];
    $usuario['email'] = $dados['email'];
    $usuario['login'] = $dados['login'];
    $response->getBody()->write(json_encode($usuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->put('/usuarios/{id}/senha', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $dados = $request->getParsedBody();
    $usuario['senha'] = $dados['senha'];
    $response->getBody()->write(json_encode($usuario));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
//DELETE
$app->delete('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $usuario = current(array_filter($usuarios, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$usuario) {
        $response->getBody()->write(json_encode(['erro' => 'Usuário não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $usuarios = array_filter($usuarios, fn ($item) => $item['id'] !== (int) $args['id']);
    $response->getBody()->write(json_encode(['mensagem' => 'Usuário removido com sucesso']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
$app->run();