<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Invoice $invoice) => [
                'invoice_number' => $invoice->invoice_number,
                'description'    => $invoice->item_description,
                'total'          => $invoice->total,
                'currency'       => $invoice->currency,
                'status'         => $invoice->status,
                'issued_at'      => $invoice->issued_at,
                'download_url'   => $invoice->pdf_path
                    ? Storage::disk('public')->url($invoice->pdf_path)
                    : null,
            ]);

        return response()->json($invoices);
    }
}