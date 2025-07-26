<?php

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Enums\TandaStatus;
use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Models\TandaFunding;
use EdLugz\Tanda\Models\TandaTransaction;
use EdLugz\Tanda\TandaClient;
use Illuminate\Support\Facades\Config;

class Status extends TandaClient
{
    /**
     * Check transaction status endpoint on Tanda API.
     *
     * @var string
     */
    protected string $endPoint;

    /**
     * The organization ID assigned for the application on Tanda API.
     *
     * @var string
     */
    protected string $orgId;

    /**
     * Transaction constructor.
     *
     * @throws TandaException
     */
    public function __construct()
    {
        parent::__construct();

        $this->orgId = Config::get('tanda.organisation_id');
        $this->endPoint = "io/v3/organizations/$this->orgId/request/";
    }

    /**
     * Query transaction status.
     *
     * @param string $reference
     * @param string $shortCode
     *
     * @return array
     */
    private function status(string $reference, string $shortCode): array
    {
        try {
            $response = (object) $this->call($this->endPoint . $reference . '?shortCode=' . $shortCode, [], 'GET');
        } catch (TandaException $e) {
            return [
                'request_status'  => $e->getCode() ?? '500',
                'request_message' => $e->getMessage() ?? 'An unexpected error occurred.',
            ];
        }

        $data = [
            'request_status'  => $response->status ?? 'UNKNOWN',
            'request_message' => $response->message ?? 'No response message provided.',
        ];

        $statusCode = $response->status ?? $response['status'];

        if (TandaStatus::from($statusCode) === TandaStatus::SUCCESSFUL) {
            // Extract transaction reference from result parameters if available
            if (!empty($response->resultParameters)) {
                foreach ($response->result as $param) {
                    if ($param->id === 'ref') {
                        $transactionReceipt = $param->value;
                        break;
                    }
                }
            }

            $data = array_merge($data, [
                'receipt_number'        => $transactionReceipt,
                'transaction_reference' => $transactionReceipt,
                'timestamp'             => isset($response->datetimeCompleted)
                    ? date('Y-m-d H:i:s', strtotime($response->datetimeCompleted))
                    : null,
            ]);
        }

        return $data;
    }

    /**
     * Check funding transaction status.
     *
     * @param string $reference
     * @return TandaFunding
     * @throws TandaException
     */
    public function fundingCheck(string $reference): TandaFunding
    {
        $funding = TandaFunding::where('fund_reference', $reference)->first();

        if (!$funding) {
            throw new TandaException("Funding transaction with reference $reference not found.", 404);
        }

        $data = $this->status($reference, $funding->merchant_wallet);
        
        $funding->update($data);

        return $funding;
    }

    /**
     * Check payment transaction status.
     *
     * @param string $reference
     * @throws TandaException
     * @return TandaTransaction
     */
    public function paymentCheck(string $reference): TandaTransaction
    {
        $transaction = TandaTransaction::where('payment_reference', $reference)->first();

        if (!$transaction) {
            throw new TandaException("Transaction with reference $reference not found.", 404);
        }

        $data = $this->status($reference, $transaction->merchant_wallet);

        $transaction->update($data);

        return $transaction;
    }
}
