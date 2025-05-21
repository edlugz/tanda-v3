<?php

declare(strict_types=1);

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Helpers\TandaHelper;
use EdLugz\Tanda\Models\TandaFunding;
use EdLugz\Tanda\TandaClient;
use Illuminate\Support\Str;

class C2B extends TandaClient
{
    protected readonly string $endPoint;
    protected readonly string $orgId;
    protected readonly string $resultUrl;

    public function __construct(?string $resultUrl = null)
    {
        parent::__construct();

        $this->orgId = Config::get('tanda.organisation_id');
        $this->endPoint = "io/v3/organizations/$this->orgId/request";
        $this->resultUrl = $resultUrl ?? TandaHelper::getFundingResultUrl();
    }

    public function request(
        string $serviceProviderId,
        string $merchantWallet,
        string $mobileNumber,
        string $amount,
        array $customFieldsKeyValue = []
    ): TandaFunding {
        $reference = (string) Str::ulid();

        $funding = TandaFunding::create([
            'fund_reference'   => $reference,
            'service_provider' => $serviceProviderId,
            'account_number'   => $mobileNumber,
            'amount'           => $amount,
            'merchant_wallet'  => $merchantWallet,
            'shortcode'        => $merchantWallet,
            $customFieldsKeyValue,
        ]);

        $parameters = [
            'commandId'         => 'CustomerToMerchantMobileMoneyPayment',
            'serviceProviderId' => $serviceProviderId,
            'reference' => $reference,
            'request' => [
                ['id' => 'amount', 'label' => 'Amount', 'value' => $amount],
                ['id' => 'narration', 'label' => 'Narration', 'value' => $amount],
                ['id' => 'ipnUrl', 'label' => 'Notification URL', 'value' => $this->resultUrl],
                ['id' => 'shortCode', 'label' => 'Short Code', 'value' => $merchantWallet],
                ['id' => 'accountNumber', 'label' => 'Phone Number', 'value' => $mobileNumber],
            ],
        ];

        try {
            $response = (object) $this->call($this->endPoint, ['json' => $parameters]);
            $funding->update(['json_response' => json_encode($response)]);
        } catch (TandaException $e) {
            $response = (object) [
                'status' => (string) $e->getCode(),
                'responseCode' => (string) $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        $data = [
            'response_status'  => $response->status ?? 'error',
            'response_message' => $response->message ?? 'Unknown error',
        ];

        if (($response->status ?? '') === 'P202000') {
            $data['transaction_id'] = $response->trackingId ?? null;
            $data['tracking_id'] = $response->trackingId ?? null;
        }

        $funding->update($data);

        return $funding;
    }
}
