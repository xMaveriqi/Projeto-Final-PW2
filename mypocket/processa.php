<?php

declare(strict_types=1);

require_once __DIR__ . '/classes/Carteira.php';

session_start();

function obterCarteira(): Carteira
{
    if (isset($_SESSION['carteira']) && $_SESSION['carteira'] instanceof Carteira) {
        return $_SESSION['carteira'];
    }

    $carteira = new Carteira();
    $_SESSION['carteira'] = $carteira;

    return $carteira;
}

$carteira = obterCarteira();
$message = '';
$messageType = 'suçeso';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING) ?? '';
    $valorBruto = filter_input(INPUT_POST, 'valor', FILTER_UNSAFE_RAW) ?? '';
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW) ?? '';
    $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING) ?? date('Y-m-d');

    $valor = (float) str_replace(',', '.', trim($valorBruto));

    try {
        if ($tipo === 'receita') {
            $receita = new Receita($valor, $descricao, $data);
            $carteira->adicionarReceita($receita);
        } elseif ($tipo === 'despesa') {
            $despesa = new Despesa($valor, $descricao, $data);
            $carteira->adicionarDespesa($despesa);
        } else {
            throw new Exception('Tipo de transação inválido.');
        }

        $_SESSION['mensagem'] = 'Lançamento registrado com sucesso.';
        $_SESSION['mensagem_tipo'] = 'success';
    } catch (Exception $exception) {
        $_SESSION['mensagem'] = $exception->getMessage();
        $_SESSION['mensagem_tipo'] = 'danger';
    }

    $_SESSION['carteira'] = $carteira;
    header('Location: index.php');
    exit;
}
