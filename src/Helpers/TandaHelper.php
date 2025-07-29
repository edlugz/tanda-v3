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
            'MPESA' => '/(?:0)?((?:(?:7[01249][0-9])|(?:75[789])|(?:76[89])|(?:11[0-5]))[0-9]{6})$/',
            'AIRTELMONEY' => '/(?:0)?((?:(?:73[0-9])|(?:75[0-6])|(?:78[0-9])|(?:10[0-2]|104))[0-9]{6})$/',
            'TKASH' => '/(?:0)?(77[0-9]{7})$/',
            'EQUITEL' => '/(?:0)?(76[3-9][0-9]{6})$/',
        ];

        foreach ($patterns as $provider => $regex) {
            if (preg_match($regex, $mobileNumber)) {
                if ($airtime) {
                    return match ($provider) {
                        'MPESA' => 'SAFARICOM',
                        'AIRTELMONEY' => 'AIRTEL',
                        'TKASH' => 'TELKOM',
                        default => $provider, 
                    };
                }

                return $provider;
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
        return $this->processTransaction($request);
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
        return $this->processTransaction($request);
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
     * @param Request $request
     * @return TandaTransaction|null
     * @throws JsonException
     */
    private function processTransaction(Request $request): ?TandaTransaction
    {
        $transaction = TandaTransaction::where('transaction_id', $request->input('trackingId'))->first();

        if (!$transaction) {
            return null;
        }

        $result = $request->input('result', []);

        $updateData = [
            'json_result'        => json_encode($request->all(), JSON_THROW_ON_ERROR),
            'request_status'     => $request->input('status'),
            'request_message'    => $request->input('message'),
            'timestamp'          => $request->input('timestamp'),
        ];

        if ($request->input('status') === 'S000000') {
            $updateData['receipt_number']       = $request->input('transactionId') ?? 'N/A';
            $updateData['transaction_receipt']  = $result['ref'] ?? 'N/A';
        }

        $transaction->update($updateData);

        return $transaction;
    }

    /**
     * Generic funding processor.
     * @throws JsonException
     */
    private function processFunding(Request $request): ?TandaFunding
    {
        $funding = TandaFunding::where('fund_reference', $request->input('reference'))->first();

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

        $result = $request->input('result', []);

        if ($request->input('status') === 'S000000') {
            $data['receipt_number'] = $request->input('transactionId') ?? 'N/A';
            $data['transaction_reference'] = $result['ref'] ?? 'N/A';
        }

        $funding->update($data);

        return $funding;
    }

}
