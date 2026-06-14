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

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mypocket-extrato.csv"');
    echo $carteira->exportarCsv();
    exit;
}

$mensagem = $_SESSION['mensagem'] ?? null;
$mensagemTipo = $_SESSION['mensagem_tipo'] ?? 'info';
unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPocket - Organizador Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-..." crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">MyPocket</a>
        <span class="navbar-text text-white">Controle financeiro com POO</span>
    </div>
</nav>
<div class="container my-4">
    <div class="row gy-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title">Saldo Atual</h1>
                    <p class="display-6 mb-0 text-success">R$ <?= number_format($carteira->getSaldo(), 2, ',', '.') ?></p>
                    <p class="text-muted">O saldo é calculado apenas por transações registradas no histórico.</p>
                </div>
            </div>
        </div>

        <?php if ($mensagem !== null): ?>
            <div class="col-12">
                <div class="alert alert-<?= escape($mensagemTipo) ?> alert-dismissible fade show" role="alert">
                    <?= escape($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Registrar transação</h2>
                    <form action="processa.php" method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="valor">Valor (R$)</label>
                            <input id="valor" name="valor" type="number" step="0.01" min="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="descricao">Descrição</label>
                            <input id="descricao" name="descricao" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="data">Data</label>
                            <input id="data" name="data" type="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <a href="index.php?export=csv" class="btn btn-outline-secondary">Exportar CSV</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Extrato</h2>
                    <?php $historico = $carteira->getHistorico(); ?>
                    <?php if (empty($historico)): ?>
                        <p class="text-muted">Nenhuma transação registrada ainda.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Data</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($historico as $transacao): ?>
                                    <?php $isEntrada = $transacao->getTipo() === 'Entrada'; ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?= $isEntrada ? 'bg-success' : 'bg-danger' ?>">
                                                <?= escape($transacao->getTipo()) ?>
                                            </span>
                                        </td>
                                        <td><?= escape($transacao->getDescricao()) ?></td>
                                        <td><?= escape($transacao->getDataFormatada()) ?></td>
                                        <td class="text-end <?= $isEntrada ? 'text-success' : 'text-danger' ?>">
                                            <?= ($isEntrada ? '+ ' : '- ') . number_format($transacao->getValor(), 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
</body>
</html>
