<?php

declare(strict_types=1);

require_once __DIR__ . '/Receita.php';
require_once __DIR__ . '/Despesa.php';

class Carteira
{
    private float $saldo = 0.0;
    /** @var Transacao[] */
    private array $historico = [];

    public function getSaldo(): float
    {
        return $this->saldo;
    }

    /**
     * @return Transacao[]
     */
    public function getHistorico(): array
    {
        return $this->historico;
    }

    public function adicionarReceita(Receita $receita): void
    {
        $this->saldo += $receita->getValor();
        $this->historico[] = $receita;
    }

    public function adicionarDespesa(Despesa $despesa): void
    {
        if ($despesa->getValor() > $this->saldo) {
            throw new Exception('Saldo insuficiente para essa despesa.');
        }

        $this->saldo -= $despesa->getValor();
        $this->historico[] = $despesa;
    }

    public function exportarCsv(): string
    {
        $csv = "Tipo,Descrição,Data,Valor\n";

        foreach ($this->historico as $transacao) {
            $csv .= sprintf(
                '%s,%s,%s,%.2f\n',
                $transacao->getTipo(),
                str_replace(["\r", "\n", ','], [' ', ' ', ';'], $transacao->getDescricao()),
                $transacao->getDataFormatada('Y-m-d'),
                $transacao->getValor()
            );
        }

        return $csv;
    }
}
