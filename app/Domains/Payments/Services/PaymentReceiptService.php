<?php

declare(strict_types=1);

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentReceiptService
{
    public function generateReceipt(Payment $payment): string
    {
        $receiptPath = sprintf('receipts/payment-%d-%s.pdf', $payment->id, \now()->format('YmdHis'));
        Storage::disk('local')->put($receiptPath, $this->getReceiptContent($payment));

        return $receiptPath;
    }

    public function sendReceiptEmail(Payment $payment): void
    {
        $clientEmail = (string) ($payment->client?->email ?? '');

        if ($clientEmail === '') {
            return;
        }

        $html = $this->getReceiptContent($payment);

        Mail::html($html, function ($message) use ($payment, $clientEmail): void {
            $message
                ->to($clientEmail)
                ->subject('Payment Receipt - '.$payment->invoice?->invoice_number);
        });
    }

    public function getReceiptContent(Payment $payment): string
    {
        $formattedAmount = number_format(((int) $payment->amount) / 100, 2, '.', '');
        $currency = (string) $payment->currency_code;
        $invoiceNumber = (string) ($payment->invoice?->invoice_number ?? 'N/A');
        $paidAt = $payment->paid_at?->toDateTimeString() ?? \now()->toDateTimeString();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
</head>
<body>
    <h1>Payment Receipt</h1>
    <p>Payment ID: {$payment->id}</p>
    <p>Invoice: {$invoiceNumber}</p>
    <p>Amount: {$currency} {$formattedAmount}</p>
    <p>Method: {$payment->method->label()}</p>
    <p>Status: {$payment->status->value}</p>
    <p>Paid at: {$paidAt}</p>
</body>
</html>
HTML;
    }
}
