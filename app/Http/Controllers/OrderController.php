<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class OrderController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Create an order and get Midtrans Snap token.
     */
    public function createToken(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'table_number'   => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.name'   => 'required|string',
            'items.*.price'  => 'required|integer|min:0',
            'items.*.qty'    => 'required|integer|min:1',
        ]);

        $items    = $request->items;
        $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);
        $tax      = (int) round($subtotal * 0.1);
        $total    = $subtotal + $tax;

        $orderId = 'SKENA-' . strtoupper(date('Ymd')) . '-' . strtoupper(Str::random(6));

        // Build Midtrans item details (including tax as a line item)
        $itemDetails = collect($items)->map(fn($i) => [
            'id'       => $i['id'] ?? Str::slug($i['name']),
            'price'    => (int) $i['price'],
            'quantity' => (int) $i['qty'],
            'name'     => mb_substr($i['name'] . ($i['variant'] ? ' (' . $i['variant'] . ')' : ''), 0, 50),
        ])->toArray();

        // Add tax as a separate line item
        $itemDetails[] = [
            'id'       => 'TAX',
            'price'    => $tax,
            'quantity' => 1,
            'name'     => 'Pajak (10%)',
        ];

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $request->customer_name,
                'phone'      => $request->customer_phone,
                'email'      => $request->customer_email ?: 'customer@skenacoffee.id',
            ],
            'enabled_payments' => ['qris', 'gopay', 'shopeepay'],
            'callbacks' => [
                'finish' => url('/order/status'),
            ],
        ];

        try {
            // Single API call — returns both token and redirect_url
            $snapResponse = \Midtrans\Snap::createTransaction($params);
            $snapToken    = $snapResponse->token;
            $redirectUrl  = $snapResponse->redirect_url ?? null;

            $order = Order::create([
                'order_id'              => $orderId,
                'customer_name'         => $request->customer_name,
                'customer_phone'        => $request->customer_phone,
                'customer_email'        => $request->customer_email,
                'table_number'          => $request->table_number,
                'items'                 => $items,
                'subtotal'              => $subtotal,
                'tax'                   => $tax,
                'total'                 => $total,
                'payment_method'        => 'qris',
                'midtrans_order_id'     => $orderId,
                'midtrans_token'        => $snapToken,
                'midtrans_redirect_url' => $redirectUrl,
                'status'                => 'pending',
            ]);

            return response()->json([
                'success'     => true,
                'token'       => $snapToken,
                'order_id'    => $orderId,
                'order_db_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Midtrans payment notification webhook (server-side callback).
     */
    public function notification(Request $request)
    {
        try {
            $notification = new Notification();

            $orderId           = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus       = $notification->fraud_status;
            $paymentType       = $notification->payment_type;
            $transactionId     = $notification->transaction_id;

            $order = Order::where('midtrans_order_id', $orderId)->first();
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->midtrans_transaction_id = $transactionId;
            $order->midtrans_payment_type   = $paymentType;

            if ($transactionStatus === 'capture') {
                $order->status  = ($fraudStatus === 'accept') ? 'paid' : 'failed';
                if ($order->status === 'paid') $order->paid_at = now();
            } elseif ($transactionStatus === 'settlement') {
                $order->status  = 'paid';
                $order->paid_at = now();
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $order->status = 'cancelled';
            } elseif ($transactionStatus === 'pending') {
                $order->status = 'pending';
            }

            $order->save();

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update payment status from client side (after Snap popup closes).
     */
    public function updateStatus(Request $request)
    {
        $request->validate(['order_id' => 'required|string', 'status' => 'required|string']);

        $order = Order::where('midtrans_order_id', $request->order_id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($request->status === 'paid' && $order->status === 'pending') {
            $order->status  = 'paid';
            $order->paid_at = now();
            $order->save();
        }

        return response()->json(['success' => true, 'order' => $order]);
    }

    /**
     * Show order status page.
     */
    public function status(Request $request)
    {
        $orderId = $request->get('order_id');
        $order   = $orderId ? Order::where('midtrans_order_id', $orderId)->first() : null;

        // Enrich items with image_url from Menu table if missing
        if ($order && is_array($order->items)) {
            $menuNames = collect($order->items)->pluck('name')->unique()->toArray();
            $menus = \App\Models\Menu::whereIn('name', $menuNames)
                ->get()
                ->mapWithKeys(fn($m) => [$m->name => $m->image_url]);
            
            $enrichedItems = array_map(function ($item) use ($menus) {
                if (empty($item['image_url']) && isset($menus[$item['name']])) {
                    $item['image_url'] = $menus[$item['name']];
                }
                return $item;
            }, $order->items);

            $order->items = $enrichedItems;
        }

        return view('order-status', compact('order'));
    }

    /**
     * Return order history for given order IDs (used by localStorage-based tracking).
     */
    public function history(Request $request)
    {
        $orderIds = $request->get('order_ids', []);
        if (empty($orderIds) || !is_array($orderIds)) {
            return response()->json([]);
        }

        // Limit to 20 most recent
        $orders = Order::whereIn('midtrans_order_id', $orderIds)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->order_id,
                    'customer_name'  => $order->customer_name,
                    'table'          => $order->table_number,
                    'items'          => is_array($order->items) ? $order->items : [],
                    'items_count'    => is_array($order->items) ? count($order->items) : 0,
                    'subtotal'       => $order->subtotal,
                    'tax'            => $order->tax,
                    'total'          => $order->total,
                    'status'         => $order->status,
                    'payment_method' => $order->payment_method,
                    'created_at'     => $order->created_at->toIso8601String(),
                    'time_ago'       => $order->created_at->diffForHumans(),
                    'date_formatted' => $order->created_at->format('d M Y, H:i'),
                ];
            });

        return response()->json($orders);
    }

    /**
     * Return live status for a single order (used for polling).
     */
    public function liveStatus($id)
    {
        $order = Order::where('midtrans_order_id', $id)->first();
        if (!$order) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status'    => $order->status,
            'order_id'  => $order->order_id,
            'updated_at'=> $order->updated_at->toIso8601String(),
        ]);
    }
}
