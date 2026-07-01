<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PagoFacilService
{
    public function generarQr(
        float $monto,
        string $concepto,
        string $clientName,
        string $documentId,
        string $phoneNumber,
        string $email,
        string $clientCode,
        ?string $paymentNumber = null,
    ): array {
        $accessToken = $this->login();
        $paymentNumber = $paymentNumber ?: self::generarPaymentNumber();
        $monto = round($monto, 2);

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Response-Language' => 'es',
            ])
            ->post($this->baseUrl().'generate-qr', [
                'paymentMethod' => 34,
                'clientName' => $clientName,
                'documentType' => 1,
                'documentId' => $documentId,
                'phoneNumber' => $phoneNumber,
                'email' => $email,
                'paymentNumber' => $paymentNumber,
                'amount' => $monto,
                'currency' => 2,
                'clientCode' => $clientCode,
                'callbackUrl' => $this->callbackUrl(),
                'orderDetail' => [[
                    'serial' => 1,
                    'product' => $concepto,
                    'quantity' => 1,
                    'price' => $monto,
                    'discount' => 0,
                    'total' => $monto,
                ]],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PagoFacil no pudo generar el QR. HTTP '.$response->status());
        }

        $json = $response->json();

        if (($json['error'] ?? -1) !== 0) {
            throw new RuntimeException('PagoFacil rechazo la generacion del QR: '.($json['message'] ?? 'sin mensaje'));
        }

        $values = $json['values'] ?? null;

        if (! is_array($values) || empty($values['qrBase64'])) {
            throw new RuntimeException('PagoFacil no devolvio el QR generado.');
        }

        return [
            'transactionId' => $values['transactionId'] ?? $paymentNumber,
            'paymentNumber' => $paymentNumber,
            'qrBase64' => $values['qrBase64'],
            'estado' => ((int) ($values['status'] ?? 1)) === 1 ? 'PENDIENTE' : 'ERROR',
        ];
    }

    public static function generarPaymentNumber(): string
    {
        return strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 16));
    }

    private function login(): string
    {
        $tokenSecret = trim((string) env('TC_TOKEN_SECRET'));
        $tokenService = trim((string) env('TC_TOKEN_SERVICE'));

        if ($tokenSecret === '' || $tokenService === '') {
            throw new RuntimeException('Faltan TC_TOKEN_SECRET o TC_TOKEN_SERVICE para PagoFacil.');
        }

        $response = Http::withHeaders([
            'tcTokenSecret' => $tokenSecret,
            'tcTokenService' => $tokenService,
            'Accept' => '*/*',
        ])->withBody('', 'text/plain')->post($this->baseUrl().'login');

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo autenticar con PagoFacil. HTTP '.$response->status());
        }

        $json = $response->json();

        if ((int) ($json['status'] ?? 0) !== 1 || empty($json['values']['accessToken'])) {
            throw new RuntimeException('PagoFacil no devolvio accessToken: '.($json['message'] ?? 'sin mensaje'));
        }

        return $json['values']['accessToken'];
    }

    private function baseUrl(): string
    {
        $baseUrl = trim((string) env('URL_PAGO_FASIL'));

        if ($baseUrl === '') {
            throw new RuntimeException('Falta URL_PAGO_FASIL para PagoFacil.');
        }

        return rtrim($baseUrl, '/').'/';
    }

    private function callbackUrl(): string
    {
        $callbackUrl = trim((string) env('URL_CALLBACK'));

        if ($callbackUrl === '') {
            throw new RuntimeException('Falta URL_CALLBACK para PagoFacil.');
        }

        return $callbackUrl;
    }
}
