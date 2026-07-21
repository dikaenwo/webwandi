<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analytics — Skena Coffee</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #2d1b0e; background: #fff; }
        .header { background: #3d2314; color: white; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 11px; opacity: 0.7; margin-top: 4px; }
        .header .period { text-align: right; font-size: 11px; opacity: 0.8; }
        .content { padding: 24px 32px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px; }
        .summary-card { background: #fdf6ee; border: 1px solid #e7d5bf; border-radius: 10px; padding: 14px 16px; }
        .summary-card .label { font-size: 10px; color: #7a5c42; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 6px; }
        .summary-card .value { font-size: 18px; font-weight: 800; color: #3d2314; }
        .summary-card .sub { font-size: 10px; color: #7a5c42; margin-top: 2px; }
        h2 { font-size: 14px; font-weight: 700; color: #3d2314; margin-bottom: 10px; border-left: 3px solid #b47a3a; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        thead th { background: #3d2314; color: white; padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 600; }
        tbody tr:nth-child(even) { background: #fdf6ee; }
        tbody tr:hover { background: #f5e8d5; }
        tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #e7d5bf; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }
        .badge-done { background: #d1fae5; color: #065f46; }
        .badge-paid { background: #dbeafe; color: #1e40af; }
        .badge-making { background: #ede9fe; color: #4c1d95; }
        .badge-ready { background: #dcfce7; color: #14532d; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e7d5bf; font-size: 10px; color: #7a5c42; text-align: center; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed;top:16px;right:16px;z-index:100;">
        <button onclick="window.print()" style="background:#3d2314;color:white;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
            🖨️ Print / Simpan PDF
        </button>
        <button onclick="window.close()" style="background:#e7d5bf;color:#3d2314;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;margin-left:8px;">
            ✕ Tutup
        </button>
    </div>

    <div class="header">
        <div>
            <h1>☕ Skena Coffee</h1>
            <div class="subtitle">Laporan Analytics &amp; Penjualan</div>
        </div>
        <div class="period">
            <div>Periode: <strong>{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</strong></div>
            <div style="margin-top:4px;">Dicetak: {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="content">
        {{-- Summary Cards --}}
        <div class="summary-grid" style="margin-top:20px;">
            <div class="summary-card">
                <div class="label">Total Pendapatan</div>
                <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="sub">{{ $totalOrders }} order selesai</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Order</div>
                <div class="value">{{ $totalOrders }}</div>
                <div class="sub">Periode terpilih</div>
            </div>
            <div class="summary-card">
                <div class="label">Rata-rata / Order</div>
                <div class="value">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</div>
                <div class="sub">Nilai rata-rata transaksi</div>
            </div>
            <div class="summary-card">
                <div class="label">Periode</div>
                <div class="value" style="font-size:14px;">{{ round($from->diffInDays($to)) + 1 }} Hari</div>
                <div class="sub">{{ $from->format('d M') }} — {{ $to->format('d M Y') }}</div>
            </div>
        </div>

        {{-- Orders Table --}}
        <h2>Detail Semua Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Tanggal & Waktu</th>
                    <th>Customer</th>
                    <th>Meja</th>
                    <th>Item</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Pajak</th>
                    <th class="text-right">Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $i => $order)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td style="font-family:monospace;font-size:10px;">{{ $order->order_id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->customer_name ?? '-' }}</td>
                    <td class="text-center">{{ $order->table_number }}</td>
                    <td>
                        @if(is_array($order->items))
                            {{ collect($order->items)->map(fn($i) => $i['qty'].'x '.$i['name'])->implode(', ') }}
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($order->tax, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight:700;">{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>{{ strtoupper($order->payment_method ?? 'QRIS') }}</td>
                    <td>
                        <span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding:20px;color:#7a5c42;">Tidak ada data transaksi untuk periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Total Row --}}
        @if($orders->count() > 0)
        <div style="text-align:right;margin-top:-20px;margin-bottom:28px;padding:12px 16px;background:#3d2314;color:white;border-radius:8px;">
            <span style="font-size:13px;font-weight:800;">
                Total Keseluruhan: Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                &nbsp;|&nbsp; {{ $totalOrders }} Transaksi
            </span>
        </div>
        @endif

        <div class="footer">
            Laporan ini dibuat otomatis oleh sistem Admin Skena Coffee &bull; {{ \Carbon\Carbon::now()->format('d M Y, H:i:s') }}
        </div>
    </div>

    <script>
        // Auto trigger print dialog after short delay
        setTimeout(() => window.print(), 800);
    </script>
</body>
</html>
