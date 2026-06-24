<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .brand { font-size: 22px; font-weight: bold; color: #4f46e5; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { margin: 0; font-size: 24px; color: #1e293b; }
        .invoice-title p { margin: 4px 0; color: #64748b; }
        .details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .details div { width: 48%; }
        .details h4 { color: #94a3b8; font-size: 11px; text-transform: uppercase; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; color: #64748b; }
        td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; }
        .totals { width: 250px; margin-left: auto; margin-top: 20px; }
        .totals table td { border: none; padding: 6px 10px; }
        .totals .grand-total td { font-weight: bold; font-size: 16px; border-top: 2px solid #1e293b; padding-top: 10px; }
        .footer { margin-top: 50px; text-align: center; color: #94a3b8; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">{{ $brandName ?? 'NexFlow' }}</div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p>{{ $invoice->invoice_number }}</p>
        </div>
    </div>

    <div class="details">
        <div>
            <h4>Billed To</h4>
            <p>
                {{ $invoice->billed_to_name }}<br>
                {{ $invoice->billed_to_email }}<br>
                @if($invoice->billed_to_address)
                    {{ $invoice->billed_to_address }}
                @endif
            </p>
        </div>
        <div style="text-align: right;">
            <h4>Invoice Date</h4>
            <p>{{ $invoice->issued_at?->format('d M Y') }}</p>
            <h4>Status</h4>
            <p style="text-transform: uppercase; font-weight: bold;">{{ $invoice->status }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->item_description }}</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td>Discount</td>
                <td style="text-align: right;">- {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->tax > 0)
            <tr>
                <td>Tax</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Total</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Thank you for your business.<br>
        This is a system-generated invoice.
    </div>
</body>
</html>