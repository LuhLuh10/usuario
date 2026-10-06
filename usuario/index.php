<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$senhaInicial = password_hash('senha123', PASSWORD_DEFAULT);
$usuarios = [
    ['id' => 1, 'nome' => 'Ana Silva', 'email' => 'ana@example.com', 'login' => 'ana', 'senha' => $senhaInicial],
    ['id' => 2, 'nome' => 'Bruno Souza', 'email' => 'bruno@example.com', 'login' => 'bruno', 'senha' => $senhaInicial],
    ['id' => 3, 'nome' => 'Carla Lima', 'email' => 'carla@example.com', 'login' => 'carla', 'senha' => $senhaInicial],
    ['id' => 4, 'nome' => 'Diego Costa', 'email' => 'diego@example.com', 'login' => 'diego', 'senha' => $senhaInicial],
    ['id' => 5, 'nome' => 'Eva Martins', 'email' => 'eva@example.com', 'login' => 'eva', 'senha' => $senhaInicial],
];

function respostaJson($response, $dados, $status = 200)
{
    $response->getBody()->write(json_encode($dados, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
}

function usuarioPublico($usuario)
{
    unset($usuario['senha']);
    return $usuario;
}

$app->get('/status', function ($request, $response) {
    return respostaJson($response, ['status' => 'ok']);
});

$app->get('/usuarios', function ($request, $response) use (&$usuarios) {
    return respostaJson($response, array_map('usuarioPublico', $usuarios));
});

$app->get('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    foreach ($usuarios as $usuario) {
        if ($usuario['id'] === (int) $args['id']) {
            return respostaJson($response, usuarioPublico($usuario));
        }
    }
    return respostaJson($response, ['erro' => 'Usuário não encontrado'], 404);
});

$app->post('/usuarios', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();
    if (!is_array($dados) || empty($dados['nome']) || empty($dados['email']) || empty($dados['login']) || empty($dados['senha'])) {
        return respostaJson($response, ['erro' => 'Informe nome, email, login e senha'], 400);
    }

    $novoUsuario = [
        'id' => $usuarios ? max(array_column($usuarios, 'id')) + 1 : 1,
        'nome' => $dados['nome'],
        'email' => $dados['email'],
        'login' => $dados['login'],
        'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
    ];
    $usuarios[] = $novoUsuario;
    return respostaJson($response, usuarioPublico($novoUsuario), 201);
});

$app->put('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    $dados = $request->getParsedBody();
    foreach ($usuarios as $indice => $usuario) {
        if ($usuario['id'] === (int) $args['id']) {
            foreach (['nome', 'email', 'login'] as $campo) {
                if (isset($dados[$campo])) {
                    $usuarios[$indice][$campo] = $dados[$campo];
                }
            }
            return respostaJson($response, usuarioPublico($usuarios[$indice]));
        }
    }
    return respostaJson($response, ['erro' => 'Usuário não encontrado'], 404);
});

$app->delete('/usuarios/{id}', function ($request, $response, $args) use (&$usuarios) {
    foreach ($usuarios as $indice => $usuario) {
        if ($usuario['id'] === (int) $args['id']) {
            unset($usuarios[$indice]);
            $usuarios = array_values($usuarios);
            return respostaJson($response, ['mensagem' => 'Usuário removido com sucesso']);
        }
    }
    return respostaJson($response, ['erro' => 'Usuário não encontrado'], 404);
});

$app->post('/login', function ($request, $response) use (&$usuarios) {
    $dados = $request->getParsedBody();
    if (!is_array($dados) || empty($dados['login']) || empty($dados['senha'])) {
        return respostaJson($response, ['erro' => 'Informe login e senha'], 400);
    }
    foreach ($usuarios as $usuario) {
        if ($usuario['login'] === $dados['login'] && password_verify($dados['senha'], $usuario['senha'])) {
            return respostaJson($response, ['mensagem' => 'Login realizado com sucesso']);
        }
    }
    return respostaJson($response, ['erro' => 'Login ou senha inválidos'], 401);
});

$app->put('/usuarios/{id}/senha', function ($request, $response, $args) use (&$usuarios) {
    $dados = $request->getParsedBody();
    foreach ($usuarios as $posicao => $usuario) {
        if ($usuario['id'] === (int) $args['id']) {
            if (empty($dados['senha_atual']) || empty($dados['nova_senha'])) {
                return respostaJson($response, ['erro' => 'Informe senha_atual e nova_senha'], 400);
            }
            if (!password_verify($dados['senha_atual'], $usuario['senha'])) {
                return respostaJson($response, ['erro' => 'Senha atual incorreta'], 401);
            }
            $usuarios[$posicao]['senha'] = password_hash($dados['nova_senha'], PASSWORD_DEFAULT);
            return respostaJson($response, ['mensagem' => 'Senha alterada com sucesso']);
        }
    }
    return respostaJson($response, ['erro' => 'Usuário não encontrado'], 404);
});

$app->run();