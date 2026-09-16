<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        summary: 'Listar todos os produtos',
        tags: ['Products'],
        responses: [
            new OA\Response(response: 200, description: 'Sucesso')
        ]
    )]
    public function index()
    {
        return Product::all();
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Criar um novo produto',
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'stock'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Teclado Mecânico'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 79.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 15)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Produto criado com sucesso'),
            new OA\Response(response: 422, description: 'Erro de validação')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    #[OA\Put(
        path: '/api/products/{product}',
        summary: 'Atualizar um produto existente',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                in: 'path',
                required: true,
                description: 'ID do produto',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'stock'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Teclado Mecânico RGB'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 89.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 20)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Produto atualizado com sucesso'),
            new OA\Response(response: 422, description: 'Erro de validação'),
            new OA\Response(response: 404, description: 'Produto não encontrado')
        ]
    )]
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    #[OA\Delete(
        path: '/api/products/{product}',
        summary: 'Eliminar um produto',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                in: 'path',
                required: true,
                description: 'ID do produto',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Produto eliminado com sucesso'),
            new OA\Response(response: 404, description: 'Produto não encontrado')
        ]
    )]
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }
}