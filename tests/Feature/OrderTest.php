<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa a criação de uma encomenda com dados válidos.
     *
     * Verifica que:
     * - a API devolve HTTP 201;
     * - a encomenda é criada;
     * - o OrderItem é criado;
     * - o preço guardado no OrderItem é o preço actual do produto;
     * - o stock do produto é reduzido correctamente.
     */
    public function test_can_create_an_order(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
    }

    /**
     * Testa a criação de uma encomenda quando não existe stock suficiente.
     *
     * Verifica que:
     * - a API devolve HTTP 422;
     * - é devolvido um erro de validação;
     * - a encomenda não é criada;
     * - o stock não é alterado.
     */
    public function test_cannot_create_an_order_when_stock_is_insufficient(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('products');

        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 2,
        ]);
    }

    /**
     * Testa a criação de uma encomenda com um produto inexistente.
     *
     * Verifica que:
     * - a validação da API detecta que o product_id não existe;
     * - a API devolve HTTP 422;
     * - nenhuma encomenda é criada.
     */
    public function test_cannot_create_an_order_with_non_existing_product(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => 9999,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'products.0.product_id',
        ]);

        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * Testa a validação dos dados obrigatórios da encomenda.
     *
     * Verifica que:
     * - customer_name é obrigatório;
     * - customer_email é obrigatório e tem de ser válido;
     * - products é obrigatório;
     * - a API devolve HTTP 422;
     * - nenhuma encomenda é criada.
     */
    public function test_cannot_create_an_order_with_invalid_data(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => '',
            'customer_email' => 'email-invalido',
            'products' => [],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'customer_name',
            'customer_email',
            'products',
        ]);

        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * Testa a regra distinct dos produtos.
     *
     * Verifica que o mesmo produto não pode ser enviado
     * duas vezes na mesma encomenda.
     */
    public function test_cannot_create_an_order_with_duplicate_products(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'products.1.product_id',
        ]);

        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    /**
     * Testa o cancelamento de uma encomenda.
     *
     * Verifica que:
     * - a API devolve HTTP 200;
     * - o estado da encomenda passa para cancelled;
     * - o stock do produto é reposto;
     * - a encomenda continua existente na base de dados.
     */
    public function test_can_cancel_an_order_and_restore_stock(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $createResponse = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $createResponse->assertStatus(201);

        $order = Order::first();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);

        $response = $this->postJson(
            "/api/orders/{$order->id}/cancel"
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    /**
     * Testa que uma encomenda não pode ser cancelada duas vezes.
     *
     * Verifica que:
     * - a API devolve HTTP 422;
     * - é devolvida uma mensagem de erro;
     * - o stock não é alterado novamente.
     */
    public function test_cannot_cancel_an_already_cancelled_order(): void
    {
        $product = Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $createResponse = $this->postJson('/api/orders', [
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $createResponse->assertStatus(201);

        $order = Order::first();

        $this->postJson(
            "/api/orders/{$order->id}/cancel"
        )->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);

        $response = $this->postJson(
            "/api/orders/{$order->id}/cancel"
        );

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'Order is already cancelled.',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }
}