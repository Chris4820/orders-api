<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(
            Order::with('items.product')->get()
        );
    }

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
                    $item->quantity
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