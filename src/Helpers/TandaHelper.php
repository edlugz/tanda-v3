<?php

declare(strict_types=1);

namespace EdLugz\Tanda\Helpers;

use EdLugz\Tanda\Models\TandaFunding;
use EdLugz\Tanda\Models\TandaTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use JsonException;

class TandaHelper
{
    /**
     * Get mobile money or airtime provider from mobile number.
     *
     * @param string $mobileNumber - 0XXXXXXXXX
     * @param bool   $airtime
     *
     * @return string
     */
    public static function serviceProvider(string $mobileNumber, bool $airtime = false): string
    {
        $patterns = [
            'MPESA' => '/(?:0)?((?:(?:7[01249][0-9])|(?:75[789])|(?:76[89]))[0-9]{6})$/',
            'AIRTELMONEY' => '/(?:0)?((?:(?:73[0-9])|(?:75[0-6])|(78[5-9]))[0-9]{6})$/',
            'TKASH' => '/(?:0)?(77[0-9][0-9]{6})/',
            'EQUITEL' => '/0?(76[3-9][0-9]{6})/',
        ];

        foreach ($patterns as $provider => $regex) {
            if (preg_match($regex, $mobileNumber)) {
                return $airtime ? str_replace('MONEY', '', $provider) : $provider;
            }
        }

        return '0';
    }

    /**
     * Process payout results.
     * @throws JsonException
     */
    public function payout(Request $request): ?TandaTransaction
    {
        return $this->processTransaction($request, 'transaction_id');
    }

    /**
     * Process C2B (Customer-to-Business) results.
     * @throws JsonException
     */
    public function c2b(Request $request): ?TandaFunding
    {
        return $this->processFunding($request);
    }

    /**
     * Process IPN (Instant Payment Notification) results.
     * @throws JsonException
     *
     * {
     * "trackingId": "108c7c01-ef49-46a3-8413-4a4241e959ec",
     * "transactionId": "M95VBZRTM7A",
     * "reference": "XNDEERI1",
     * "status": "S000000",
     * "message": "Successfully processed",
     * "timestamp": "2024-06-12T13:30:29.258Z",
     * "result": {
     * "ref": "SFC99M93DD"
     * }
     * }
     */
    public function ipn(Request $request): TandaFunding
    {
        return TandaFunding::create([
            'fund_reference'        => $request->reference,
            'transaction_id'        => $request->trackingId,
            'receipt_number'        => $request->transactionId,
            'timestamp'             => now()->toDateTimeString(),
            'transaction_reference' => $request->input('result.ref'),
            'json_result'           => json_encode($request->all(), JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Process P2P (Peer-to-Peer) results.
     * @throws JsonException
     */
    public function p2p(Request $request): ?TandaTransaction
    {
        return $this->processTransaction($request, 'payment_reference');
    }

    /**
     * Base result URL.
     */
    public static function getBaseResultUrl(): string
    {
        return rtrim(Config::get('tanda.tanda_base_result_url'), '/');
    }

    /**
     * Payment result URL.
     */
    public static function getPaymentResultUrl(): string
    {
        return self::getBaseResultUrl() . '/' . ltrim(Config::get('tanda.result_url'), '/');
    }

    /**
     * Funding result URL.
     */
    public static function getFundingResultUrl(): string
    {
        return self::getBaseResultUrl() . '/' . ltrim(Config::get('tanda.c2b_result_url'), '/');
    }

    /**
     * Generic transaction processor.
     * @throws JsonException
     */
    private function processTransaction(Request $request, string $identifier): ?TandaTransaction
    {
        $transaction = TandaTransaction::where($identifier, $request->input($identifier))->first();

        if (!$transaction) {
            return null;
        }

        $transaction->update([
            'json_result' => json_encode($request->all(), JSON_THROW_ON_ERROR),
        ]);

        $data = [
            'request_status'  => $request->input('status'),
            'request_message' => $request->input('message'),
            'timestamp'       => $request->input('timestamp'),
        ];

        if ($request->input('status') === 'S000000') {
            $data['receipt_number'] = $request->input('transactionId') ?? 'N/A';
            $data['transaction_receipt'] = $request->input('result.ref', 'N/A');
        }

        $transaction->update($data);

        return $transaction;
    }

    /**
     * Generic funding processor.
     * @throws JsonException
     */
    private function processFunding(Request $request): ?TandaFunding
    {
        $funding = TandaFunding::where('transaction_id', $request->input('trackingId'))->first();

        if (!$funding) {
            return null;
        }

        $funding->update([
            'json_result' => json_encode($request->all(), JSON_THROW_ON_ERROR),
        ]);

        $data = [
            'request_status'  => $request->input('status'),
            'request_message' => $request->input('message'),
            'timestamp'       => $request->input('timestamp'),
        ];

        if ($request->input('status') === 'S000000') {
            $data['receipt_number'] = $request->input('transactionId') ?? 'N/A';
            $data['transaction_reference'] = $request->input('result.ref', 'N/A');
        }

        $funding->update($data);

        return $funding;
    }

}
