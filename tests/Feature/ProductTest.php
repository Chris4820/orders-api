<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o GET /api/products.
     *
     * Verifica que a API consegue listar todos os produtos
     * existentes na base de dados e devolve HTTP 200.
     */
    public function test_can_list_products(): void
    {
        Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        Product::create([
            'name' => 'Rato',
            'price' => 25.00,
            'stock' => 5,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $response->assertJsonCount(2);

        $response->assertJsonFragment([
            'name' => 'Teclado',
        ]);

        $response->assertJsonFragment([
            'name' => 'Rato',
        ]);
    }

    /**
     * Testa o POST /api/products.
     *
     * Verifica que, ao enviar dados válidos, a API cria
     * correctamente um produto e devolve HTTP 201.
     */
    public function test_can_create_a_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Monitor',
            'price' => 199.99,
            'stock' => 10,
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'name' => 'Monitor',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Monitor',
            'price' => 199.99,
            'stock' => 10,
        ]);
    }

    /**
     * Testa a validação do POST /api/products.
     *
     * Verifica que a API rejeita dados inválidos,
     * devolve HTTP 422 e não cria o produto na base de dados.
     */
    public function test_cannot_create_product_with_invalid_data(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => '',
            'price' => -10,
            'stock' => -5,
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'price',
            'stock',
        ]);

        $this->assertDatabaseCount('products', 0);
    }

    /**
     * Testa o PUT /api/products/{product}.
     *
     * Verifica que um produto existente pode ser actualizado
     * com novos dados e que as alterações ficam guardadas na BD.
     */
    public function test_can_update_a_product(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Teclado Mecânico',
            'price' => 80.00,
            'stock' => 8,
        ]);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'Teclado Mecânico',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Teclado Mecânico',
            'price' => 80.00,
            'stock' => 8,
        ]);
    }

    /**
     * Testa o DELETE /api/products/{product}.
     *
     * Como o Product usa SoftDeletes, verifica que o produto
     * é marcado como eliminado através de deleted_at em vez
     * de ser removido fisicamente da base de dados.
     */
    public function test_can_delete_a_product(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Product deleted successfully.',
        ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}
