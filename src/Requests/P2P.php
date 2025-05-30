<?php

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Helpers\TandaHelper;
use EdLugz\Tanda\Models\TandaTransaction;
use EdLugz\Tanda\TandaClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class P2P extends TandaClient
{
    /**
     * Send P2P request endpoint on Tanda API.
     *
     * @var string
     */
    protected string $endPoint;

    /**
     * The organisation ID assigned for the application on Tanda API.
     *
     * @var string
     */
    protected string $orgId;

    /**
     * The result URL assigned for P2P transactions on Tanda API.
     *
     * @var string
     */
    protected string $resultUrl;

    /**
     * P2P constructor.
     *
     * @throws TandaException
     */
    public function __construct(string $resultUrl = null)
    {
        parent::__construct();

        $this->orgId = Config::get('tanda.organisation_id');
        $this->endPoint = 'io/v3/organizations/'.$this->orgId.'/request';
        $this->resultUrl = $resultUrl ?? TandaHelper::getPaymentResultUrl();
    }

    /**
     * Send money from one sub-wallet to another sub-wallet instantly.
     *
     * @param string $senderWallet
     * @param string $receiverWallet
     * @param string $amount
     * @param array $customFieldsKeyValue
     *
     * @return TandaTransaction
     * @throws TandaRequestException
     */
    public function send(
        string $senderWallet,
        string $receiverWallet,
        string $amount,
        array $customFieldsKeyValue = []
    ): TandaTransaction {

        $reference = (string) Str::ulid();

        $parameters = [
            'commandId'         => 'MerchantToMerchantTandaPayment',
            'serviceProviderId' => 'TANDA',
            'reference'         => $reference,
            'request' => [
                ['id' => 'amount', 'label' => 'amount', 'value' => $amount],
                ['id' => 'narration', 'label' => 'Narration', 'value' => 'payment to paybill'],
                ['id' => 'ipnUrl', 'label' => 'Notification', 'value' => $this->resultUrl],
                ['id' => 'partyA', 'label' => 'Short code', 'value' => $senderWallet],
                ['id' => 'partyB', 'label' => 'Short code', 'value' => $receiverWallet],
            ]
        ];

        $payment = TandaTransaction::create(array_merge([
            'payment_reference' => $reference,
            'service_provider'  => 'TANDA',
            'merchant_wallet'   => $senderWallet,
            'amount'            => $amount,
            'account_number'    => $receiverWallet,
            'json_request'      => json_encode($parameters),
        ], $customFieldsKeyValue));

        try {
            $response = (object) $this->call($this->endPoint, $parameters);

            $payment->update([
                'json_response' => json_encode($response),
            ]);
        } catch (TandaRequestException $e) {
            $response = (object) [
                'status'       => $e->getCode() ?? '500',
                'responseCode' => $e->getCode() ?? '500',
                'message'      => $e->getMessage() ?? 'An unexpected error occurred.',
            ];
        }

        $data = [
            'response_status'  => $response->status,
            'response_message' => $response->message,
        ];

        if (($response->status ?? '') === 'P202000') {
            $data['transaction_id'] = $response->trackingId ?? null;
            $data['tracking_id'] = $response->trackingId ?? null;
        }

        $payment->update($data);

        return $payment;
    }
}
