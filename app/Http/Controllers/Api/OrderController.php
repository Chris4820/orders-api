<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: '/api/orders',
        summary: 'Listar todas as encomendas',
        tags: ['Orders'],
        responses: [
            new OA\Response(response: 200, description: 'Sucesso')
        ]
    )]
    public function index()
    {
        return response()->json(
            Order::with('items.product')->get()
        );
    }

    #[OA\Post(
        path: '/api/orders',
        summary: 'Criar uma nova encomenda',
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customer_name', 'customer_email', 'products'],
                properties: [
                    new OA\Property(property: 'customer_name', type: 'string', example: 'João Silva'),
                    new OA\Property(property: 'customer_email', type: 'string', example: 'joao@email.com'),
                    new OA\Property(
                        property: 'products',
                        type: 'array',
                        description: 'Lista de produtos da encomenda',
                        items: new OA\Items(
                            type: 'object',
                            required: ['product_id', 'quantity'],
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                new OA\Property(property: 'quantity', type: 'integer', example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Encomenda criada com sucesso'),
            new OA\Response(response: 422, description: 'Erro de validação ou stock insuficiente')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                'distinct',
            ],
            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $productIds = collect($validated['products'])
                ->pluck('product_id');

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get();

            foreach ($validated['products'] as $item) {
                $product = $products->firstWhere(
                    'id',
                    $item['product_id']
                );

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'products' => "Stock insuficiente para o produto: {$product->name}.",
                    ]);
                }
            }

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
            ]);

            foreach ($validated['products'] as $item) {
                $product = $products->firstWhere(
                    'id',
                    $item['product_id']
                );

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);

                $product->decrement(
                    'stock',
                    $item['quantity']
                );
            }

            return $order;
        });

        return response()->json(
            $order->load('items.product'),
            201
        );
    }

    #[OA\Post(
        path: '/api/orders/{order}/cancel',
        summary: 'Cancelar uma encomenda',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'order',
                in: 'path',
                required: true,
                description: 'ID da encomenda',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Encomenda cancelada com sucesso'),
            new OA\Response(response: 422, description: 'Encomenda já se encontra cancelada')
        ]
    )]
    public function cancel(Order $order)
    {
        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order is already cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->load('items.product');

            foreach ($order->items as $item) {
                $item->product->increment(
                    'stock',
                    $item['quantity']
                );
            }

            $order->update([
                'status' => 'cancelled',
            ]);
        });

        return response()->json(
            $order->fresh()->load('items.product')
        );
    }
}