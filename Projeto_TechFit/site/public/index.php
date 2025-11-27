<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/BaseModel.php';

$router = new Router();

$router->get('/', 'HomeController@index');

$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/dashboard', 'AdminController@index');

$router->get('/agendamentos', 'AgendamentoController@index');
$router->post('/agendamentos/criar', 'AgendamentoController@store');
$router->post('/agendamentos/cancelar', 'AgendamentoController@cancelar');
$router->get('/agendamentos/relatorio', 'AgendamentoController@relatorioOcupacao');
$router->get('/agendamentos/notificacoes', 'AgendamentoController@notificacoes');

$router->get('/acessos', 'AcessoController@index');
$router->get('/admin/acessos', 'AcessoController@adminIndex');
$router->get('/acessos/qrcode', 'AcessoController@gerarQRCode');
$router->post('/acessos/registrar', 'AcessoController@registrarEntrada');
$router->get('/acessos/relatorio', 'AcessoController@relatorioUtilizacao');

$router->get('/mensagens', 'ComunicacaoController@index');
$router->get('/mensagens/nova', 'ComunicacaoController@nova');
$router->post('/mensagens/enviar', 'ComunicacaoController@enviar');
$router->get('/mensagens/segmentada', 'ComunicacaoController@segmentada');
$router->post('/mensagens/segmentada/enviar', 'ComunicacaoController@enviarSegmentada');
$router->get('/duvidas', 'ComunicacaoController@duvidas');
$router->post('/duvidas/criar', 'ComunicacaoController@criarDuvida');
$router->post('/duvidas/responder', 'ComunicacaoController@responderDuvida');

$router->get('/avaliacoes', 'AvaliacaoController@index');
$router->get('/avaliacoes/nova', 'AvaliacaoController@nova');
$router->post('/avaliacoes/salvar', 'AvaliacaoController@store');
$router->get('/avaliacoes/evolucao', 'AvaliacaoController@evolucao');
$router->get('/avaliacoes/treinos', 'AvaliacaoController@treinos');
$router->get('/avaliacoes/alertas', 'AvaliacaoController@alertas');
$router->post('/avaliacoes/alertas/enviar', 'AvaliacaoController@enviarAlerta');

$router->get('/admin/modalidades', 'AdminController@modalidades');
$router->post('/admin/modalidades/salvar', 'AdminController@salvarModalidade');
$router->post('/admin/modalidades/excluir', 'AdminController@excluirModalidade');
$router->get('/admin/turmas', 'AdminController@turmas');
$router->post('/admin/turmas/salvar', 'AdminController@salvarTurma');
$router->get('/admin/relatorios', 'AdminController@relatorios');
$router->get('/admin/usuarios', 'AdminController@usuarios');
$router->post('/admin/usuarios/salvar', 'AdminController@salvarUsuario');
$router->post('/admin/usuarios/excluir', 'AdminController@excluirUsuario');

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
