<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Validation\ValidationException;

class ProdutoService
{
    public function create(array $dados): Produto
    {
        $this->verificarMargem($dados['preco'], $dados['preco_custo']);

        return Produto::create($dados);
    }

    public function update(Produto $produto, array $dados): Produto
    {
        $preco = $dados['preco'] ?? $produto->preco;
        $custo = $dados['preco_custo'] ?? $produto->preco_custo;

        $this->verificarMargem($preco, $custo);

        $produto->update($dados);

        return $produto;
    }

    private function verificarMargem(float $preco, float $custo): void
    {
        $precoMinimo = round($custo + (0.1 * $custo), 2);

        if ($preco < $precoMinimo) {
            throw ValidationException::withMessages([
                'preco' => sprintf('O preço deve ser pelo menos 10 por cento acima do valor de custo: %.2f', $precoMinimo),
            ]);
        }
    }
}
