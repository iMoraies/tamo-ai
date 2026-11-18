<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static fn () => redirect()->to('/tamo-ai'));
$routes->get('/tamo-ai/entrar', 'TamoAi\AuthController::login');
$routes->post('/tamo-ai/entrar', 'TamoAi\AuthController::attempt');
$routes->get('/tamo-ai/sair', 'TamoAi\AuthController::logout');
$routes->group('tamo-ai', ['filter' => 'tamoaiauth'], static function ($routes) {
    $routes->get('/', 'TamoAi\AgendaController::index');
    $routes->get('eventos/novo', 'TamoAi\AgendaController::create');
    $routes->post('eventos/salvar', 'TamoAi\AgendaController::store');
    $routes->get('eventos/editar/(:num)', 'TamoAi\AgendaController::edit/$1');
    $routes->post('eventos/atualizar/(:num)', 'TamoAi\AgendaController::update/$1');
    $routes->post('eventos/status/(:num)', 'TamoAi\AgendaController::status/$1');
    $routes->post('eventos/deletar/(:num)', 'TamoAi\AgendaController::delete/$1');
    $routes->get('eventos/(:num)', 'TamoAi\FinanceController::event/$1');
    $routes->post('eventos/(:num)/financeiro/atualizar', 'TamoAi\FinanceController::updateFinancial/$1');
    $routes->post('eventos/(:num)/pagamentos/salvar', 'TamoAi\FinanceController::storePayment/$1');
    $routes->post('eventos/(:num)/pagamentos/(:num)/atualizar', 'TamoAi\FinanceController::updatePayment/$1/$2');
    $routes->post('eventos/(:num)/pagamentos/(:num)/pago', 'TamoAi\FinanceController::markPaymentPaid/$1/$2');
    $routes->post('eventos/(:num)/pagamentos/(:num)/deletar', 'TamoAi\FinanceController::deletePayment/$1/$2');
    $routes->get('eventos/(:num)/fechamento/imprimir', 'TamoAi\FinanceController::printClosing/$1');
    $routes->get('eventos/(:num)/comprovantes/novo', 'TamoAi\ReceiptController::create/$1');
    $routes->match(['get', 'post'], 'emails/enviar-fechamento/(:num)', 'TamoAi\EmailController::eventForm/$1');
    $routes->post('emails/enviar-fechamento/(:num)/enviar', 'TamoAi\EmailController::sendEvent/$1');

    $routes->get('caixa', 'TamoAi\CashBoxController::index');
    $routes->post('caixa/movimentos/salvar', 'TamoAi\CashBoxController::store');
    $routes->get('caixa/movimentos/editar/(:num)', 'TamoAi\CashBoxController::edit/$1');
    $routes->post('caixa/movimentos/atualizar/(:num)', 'TamoAi\CashBoxController::update/$1');
    $routes->post('caixa/movimentos/deletar/(:num)', 'TamoAi\CashBoxController::delete/$1');
    $routes->get('caixa/exportar-csv', 'TamoAi\CashBoxController::exportCsv');

    $routes->get('comprovantes', 'TamoAi\ReceiptController::index');
    $routes->get('comprovantes/novo', 'TamoAi\ReceiptController::create');
    $routes->post('comprovantes/salvar', 'TamoAi\ReceiptController::store');
    $routes->get('comprovantes/editar/(:num)', 'TamoAi\ReceiptController::edit/$1');
    $routes->post('comprovantes/atualizar/(:num)', 'TamoAi\ReceiptController::update/$1');
    $routes->get('comprovantes/(:num)/download', 'TamoAi\ReceiptController::download/$1');
    $routes->post('comprovantes/deletar/(:num)', 'TamoAi\ReceiptController::delete/$1');

    $routes->get('relatorios', 'TamoAi\ReportController::index');
    $routes->get('relatorios/mensal/exportar-csv', 'TamoAi\ReportController::exportMonthly');
    $routes->get('relatorios/mensal/imprimir', 'TamoAi\ReportController::printMonthly');

    $routes->get('freelancers', 'TamoAi\FreelancerController::index');
    $routes->post('freelancers/salvar', 'TamoAi\FreelancerController::store');
    $routes->post('freelancers/atualizar/(:num)', 'TamoAi\FreelancerController::update/$1');
    $routes->post('freelancers/deletar/(:num)', 'TamoAi\FreelancerController::delete/$1');

    $routes->get('materiais', 'TamoAi\MaterialController::index');
    $routes->get('materiais/novo', 'TamoAi\MaterialController::create');
    $routes->post('materiais/salvar', 'TamoAi\MaterialController::store');
    $routes->get('materiais/editar/(:num)', 'TamoAi\MaterialController::edit/$1');
    $routes->post('materiais/atualizar/(:num)', 'TamoAi\MaterialController::update/$1');
    $routes->post('materiais/deletar/(:num)', 'TamoAi\MaterialController::delete/$1');

    $routes->get('indisponibilidades', 'TamoAi\UnavailabilityController::index');
    $routes->post('indisponibilidades/salvar', 'TamoAi\UnavailabilityController::store');
    $routes->get('indisponibilidades/editar/(:num)', 'TamoAi\UnavailabilityController::edit/$1');
    $routes->post('indisponibilidades/atualizar/(:num)', 'TamoAi\UnavailabilityController::update/$1');
    $routes->post('indisponibilidades/deletar/(:num)', 'TamoAi\UnavailabilityController::delete/$1');

    $routes->get('emails', 'TamoAi\EmailController::index');
    $routes->post('emails/salvar', 'TamoAi\EmailController::store');
    $routes->post('emails/deletar/(:num)', 'TamoAi\EmailController::delete/$1');
    $routes->get('logs', 'TamoAi\LogController::index');

    $routes->get('integrantes', 'TamoAi\UserController::members');
    $routes->get('perfil', 'TamoAi\UserController::profile');
    $routes->post('perfil/atualizar', 'TamoAi\UserController::updateProfile');

    $routes->get('usuarios', 'TamoAi\UserController::index');
    $routes->get('usuarios/novo', 'TamoAi\UserController::create');
    $routes->post('usuarios/salvar', 'TamoAi\UserController::store');
    $routes->get('usuarios/editar/(:num)', 'TamoAi\UserController::edit/$1');
    $routes->post('usuarios/atualizar/(:num)', 'TamoAi\UserController::update/$1');
    $routes->post('usuarios/deletar/(:num)', 'TamoAi\UserController::delete/$1');
});
