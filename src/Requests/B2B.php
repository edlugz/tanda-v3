<?php

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Helpers\TandaHelper;
use EdLugz\Tanda\Models\TandaTransaction;
use EdLugz\Tanda\TandaClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class B2B extends TandaClient
{
    protected string $endPoint;
    protected string $orgId;
    protected string $resultUrl;

    public function __construct(string $resultUrl = null)
    {
        parent::__construct();

        $this->orgId = Config::get('tanda.organisation_id');

        if (!$this->orgId) {
            throw new TandaException('Missing organization ID in configuration.');
        }

        $this->endPoint = 'io/v3/organizations/' . $this->orgId . '/request';
        $this->resultUrl = $resultUrl ?? TandaHelper::getPaymentResultUrl();
    }

    /**
     * Process a B2B transaction.
     *
     * @param string $commandId
     * @param array $requestParameters
     * @param array $customFieldsKeyValue
     * @return TandaTransaction
     */
    private function processTransaction(
        string $commandId,
        array $requestParameters,
        array $customFieldsKeyValue = []
    ): TandaTransaction {
        $reference = (string) Str::ulid();

        $parameters = [
            'commandId'         => $commandId,
            'serviceProviderId' => 'MPESA',
            'reference'         => $reference,
            'request' => $requestParameters,
        ];

        $paymentData = array_merge([
            'payment_reference'   => $reference,
            'service_provider'    => 'MPESA',
            'json_request'        => json_encode($parameters),
        ], $customFieldsKeyValue);

        $payment = TandaTransaction::create($paymentData);

        try {
            $response = $this->call($this->endPoint, $parameters);
            $payment->update(['json_response' => json_encode($response)]);
        } catch (TandaException $e) {
            Log::error("Tanda API Error: {$e->getMessage()}");
            $response = (object) [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        $payment->update([
            'response_status'  => $response->status ?? $response['status'] ?? 'ERROR',
            'response_message' => $response->message ?? $response['message'] ?? 'Unknown error',
            'transaction_id'   => $response->trackingId ?? $response['trackingId'] ?? null,
        ]);

        return $payment;
    }

    /**
     * Send money from merchant wallet to till.
     */
    public function buygoods(string $merchantWallet, string $amount, string $till, array $customFieldsKeyValue = []): TandaTransaction
    {
        return $this->processTransaction(
            'MerchantTo3rdPartyMerchantPayment',
            [
                ['id' => 'amount', 'label' => 'amount', 'value' => $amount],
                ['id' => 'narration', 'label' => 'Narration', 'value' => 'payment to till'],
                ['id' => 'ipnUrl', 'label' => 'Notification', 'value' => $this->resultUrl],
                ['id' => 'partyA', 'label' => 'Short Code', 'value' => $merchantWallet],
                ['id' => 'partyB', 'label' => 'Till Number', 'value' => $till],
            ],
            array_merge($customFieldsKeyValue, [
                'merchant_wallet' => $merchantWallet,
                'amount' => $amount,
                'account_number' => $till,
                'service_provider_id' => 'MPESA',
            ])
        );
    }

    /**
     * Send money from merchant wallet to paybill business numbers.
     */
    public function paybill(string $merchantWallet, string $amount, string $paybill, string $accountNumber, array $customFieldsKeyValue = []): TandaTransaction
    {
        return $this->processTransaction(
            'MerchantTo3rdPartyBusinessPayment',
            [
                ['id' => 'amount', 'label' => 'amount', 'value' => $amount],
                ['id' => 'narration', 'label' => 'Narration', 'value' => 'payment to paybill'],
                ['id' => 'ipnUrl', 'label' => 'Notification', 'value' => $this->resultUrl],
                ['id' => 'shortCode', 'label' => 'Short Code', 'value' => $merchantWallet],
                ['id' => 'businessNumber', 'label' => 'Business Number', 'value' => $paybill],
                ['id' => 'accountReference', 'label' => 'Account reference', 'value' => $accountNumber],
            ],
            array_merge($customFieldsKeyValue, [
                'merchant_wallet' => $merchantWallet,
                'amount' => $amount,
                'service_provider_id' => 'MPESA',
                'account_number' => $paybill,
            ])
        );
    }
}
