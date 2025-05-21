<?php

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Models\TandaFunding;
use EdLugz\Tanda\Models\TandaTransaction;
use EdLugz\Tanda\TandaClient;

class Status extends TandaClient
{
    /**
     * Check transaction status endpoint on Tanda API.
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
     * Transaction constructor.
     *
     * @throws TandaRequestException
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
            $response = $this->call($this->endPoint . $reference . '?shortCode=' . $shortCode, [], 'GET');
        } catch (TandaException $e) {
            return [
                'request_status'  => $e->getCode() ?? '500',
                'request_message' => $e->getMessage() ?? 'An unexpected error occurred.',
            ];
        }

        // Ensure response is an object
        $response = (object) $response;

        $data = [
            'request_status'  => $response->status ?? 'UNKNOWN',
            'request_message' => $response->message ?? 'No response message provided.',
        ];

        if ($response->status === '000000') {
            $transactionReceipt = $response->receiptNumber ?? null;

            // Extract transaction reference from result parameters if available
            if (!empty($response->resultParameters)) {
                foreach ($response->resultParameters as $param) {
                    if ($param->id === 'transactionRef') {
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
     * @param string $shortCode
     * @return TandaFunding
     * @throws TandaException
     */
    public function fundingCheck(string $reference, string $shortCode): TandaFunding
    {
        $funding = TandaFunding::where('funding_reference', $reference)->first();

        if (!$funding) {
            throw new TandaException("Funding transaction with reference $reference not found.", 404);
        }

        $data = $this->status($reference, $shortCode);
        $funding->update($data);

        return $funding;
    }

    /**
     * Check payment transaction status.
     *
     * @param string $reference
     * @param string $shortCode
     * @throws TandaException
     * @return TandaTransaction
     */
    public function paymentCheck(string $reference, string $shortCode): TandaTransaction
    {
        $transaction = TandaTransaction::where('payment_reference', $reference)->first();

        if (!$transaction) {
            throw new TandaException("Transaction with reference $reference not found.", 404);
        }

        $data = $this->status($reference, $shortCode);

        $transaction->update($data);

        return $transaction;
    }
}
