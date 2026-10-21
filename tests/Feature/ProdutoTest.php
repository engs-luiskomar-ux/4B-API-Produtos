<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_nao_cria_produto_com_preco_abaixo_da_margem(): void
    {
        $this->postJson('/api/produtos', [
            'nome' => 'Lajota',
            'preco_custo' => 100,
            'preco' => 109.99,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('preco');

        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_atualiza_produto_com_sucesso(): void
    {
        $id = $this->postJson('/api/produtos', [
            'nome' => 'Lajota',
            'preco_custo' => 100,
            'preco' => 110,
        ])->assertCreated()->json('data.id');

        $this->putJson('/api/produtos/'.$id, [
            'nome' => 'Lajota atualizada',
            'preco_custo' => 200,
            'preco' => 220,
            'quantidade' => 5,
        ])->assertOk()
            ->assertJsonPath('data.nome', 'Lajota atualizada')
            ->assertJsonPath('data.preco', 220)
            ->assertJsonPath('data.quantidade', 5);

        $this->assertDatabaseHas('produtos', [
            'id' => $id,
            'nome' => 'Lajota atualizada',
            'preco_custo' => 200,
            'preco' => 220,
            'quantidade' => 5,
        ]);

        $this->patchJson('/api/produtos/'.$id, [
            'descricao' => 'Produto em estoque',
        ])->assertOk();

        $this->assertDatabaseHas('produtos', [
            'id' => $id,
            'descricao' => 'Produto em estoque',
            'preco_custo' => 200,
            'preco' => 220,
        ]);
    }

    public function test_nao_atualiza_produto_com_preco_abaixo_da_margem(): void
    {
        $id = $this->postJson('/api/produtos', [
            'nome' => 'Lajota',
            'preco_custo' => 100,
            'preco' => 110,
        ])->assertCreated()->json('data.id');

        $atualizacoesInvalidas = [
            ['nome' => 'Nome inválido', 'preco' => 109.99],
            ['nome' => 'Nome inválido', 'preco_custo' => 101],
            ['nome' => 'Nome inválido', 'preco_custo' => 200, 'preco' => 219.99],
        ];

        foreach ($atualizacoesInvalidas as $dados) {
            $this->patchJson('/api/produtos/'.$id, $dados)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('preco');

            $this->assertDatabaseHas('produtos', [
                'id' => $id,
                'nome' => 'Lajota',
                'preco_custo' => 100,
                'preco' => 110,
            ]);
            $this->assertDatabaseCount('produtos', 1);
        }
    }
}
