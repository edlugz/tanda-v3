<?php

namespace EdLugz\Tanda\Requests;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Helpers\TandaHelper;
use EdLugz\Tanda\Models\TandaTransaction;
use EdLugz\Tanda\TandaClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class B2C extends TandaClient
{
    protected string $endPoint;
    protected string $orgId;
    protected string $resultUrl;

    public function __construct(string $resultUrl = null)
    {
        parent::__construct();

        $this->orgId = Config::get('tanda.organisation_id');

        $this->endPoint = "io/v3/organizations/$this->orgId/requests";
        $this->resultUrl = $resultUrl ?? TandaHelper::getPaymentResultUrl();
    }

    public function bank(
        string $merchantWallet,
        string $bankCode,
        string $amount,
        string $accountName,
        string $accountNumber,
        array $customFieldsKeyValue = []
    ): TandaTransaction {
        return $this->processTransaction(
            false,
            'MerchantToCustomerBankPayment',
            'PESALINK',
            $merchantWallet,
            $accountName,
            $accountNumber,
            $bankCode,
            $amount,
            $customFieldsKeyValue
        );
    }

    public function mobile(
        string $merchantWallet,
        string $serviceProviderId,
        string $amount,
        string $mobileNumber,
        array $customFieldsKeyValue = []
    ): TandaTransaction {
        return $this->processTransaction(
            true,
            'MerchantToCustomerMobileMoneyPayment',
            $serviceProviderId,
            $merchantWallet,
            null,
            $mobileNumber,
            null,
            $amount,
            $customFieldsKeyValue
        );
    }

    public function internationalBank(
        string $amount,
        string $mobileNumber,
        string $accountNumber,
        string $bankCode,
        string $senderType,
        string $beneficiaryType,
        string $beneficiaryAddress,
        string $beneficiaryActivity,
        string $beneficiaryCountry,
        string $currency,
        string $beneficiaryEmailAddress,
        string $documentType,
        string $documentNumber,
        string $accountName,
        string $narration,
        string $senderName,
        string $senderAddress,
        string $senderPhoneNumber,
        string $senderDocumentType,
        string $senderDocumentNumber,
        string $senderCountry,
        string $senderCurrency,
        string $senderSourceOfFunds,
        string $senderPrincipalActivity,
        string $senderBankCode,
        string $senderEmailAddress,
        string $senderPrimaryAccountNumber,
        string $senderDateOfBirth,
        string $shortCode,
        array $customFieldsKeyValue = []
    ): TandaTransaction
    {
        $reference = (string) Str::ulid();

        $payment = TandaTransaction::create(array_merge([
            'payment_reference'   => $reference,
            'service_provider'    => 'PESALINK',
            'merchant_wallet'     => $shortCode,
            'amount'              => $amount,
            'account_number'      => $accountNumber,
            'service_provider_id' => 'PESALINK',
        ], $customFieldsKeyValue));

        $parameters = [
            'commandId' => 'InternationalMoneyTransferBank',
            'serviceProviderId' => 'PESALINK',
            'reference' => $reference,
            'request' => [
                ['id' => 'amount', 'value' => $amount, 'label' => 'Amount'],
                ['id' => 'mobileNumber', 'value' => $mobileNumber, 'label' => 'Beneficiary Mobile number'],
                ['id' => 'accountNumber', 'value' => $accountNumber, 'label' => 'Beneficiry Account Number'],
                ['id' => 'bankCode', 'value' => $bankCode, 'label' => 'Beneficiary Bank code'],
                ['id' => 'senderType', 'value' => $senderType, 'label' => 'Sender Type'],
                ['id' => 'beneficiaryType', 'value' => $beneficiaryType, 'label' => 'Beneficiary Type'],
                ['id' => 'beneficiaryAddress', 'value' => $beneficiaryAddress, 'label' => 'Beneficiary address'],
                ['id' => 'beneficiaryActivity', 'value' => $beneficiaryActivity, 'label' => 'Beneficiary Activity'],
                ['id' => 'beneficiaryCountry', 'value' => $beneficiaryCountry, 'label' => 'Beneficiary country'],
                ['id' => 'currency', 'value' => $currency, 'label' => 'Sender Currency currency'],
                ['id' => 'beneficiaryEmailAddress', 'value' => $beneficiaryEmailAddress, 'label' => 'Beneficiary email address'],
                ['id' => 'documentType', 'value' => $documentType, 'label' => 'Beneficiary document type'],
                ['id' => 'documentNumber', 'value' => $documentNumber, 'label' => 'Beneficiary document number'],
                ['id' => 'accountName', 'value' => $accountName, 'label' => 'Account Name'],
                ['id' => 'narration', 'value' => $narration, 'label' => 'Narration'],
                ['id' => 'senderName', 'value' => $senderName, 'label' => 'Sender Name'],
                ['id' => 'senderAddress', 'value' => $senderAddress, 'label' => 'Sender Address'],
                ['id' => 'senderPhoneNumber', 'value' => $senderPhoneNumber, 'label' => 'Sender Phone Number'],
                ['id' => 'senderDocumentType', 'value' => $senderDocumentType, 'label' => 'Sender document type'],
                ['id' => 'senderDocumentNumber', 'value' => $senderDocumentNumber, 'label' => 'Sender document number'],
                ['id' => 'senderCountry', 'value' => $senderCountry, 'label' => 'Sender country'],
                ['id' => 'senderCurrency', 'value' => $senderCurrency, 'label' => 'Sender currency'],
                ['id' => 'senderSourceOfFunds', 'value' => $senderSourceOfFunds, 'label' => 'Sender source of funds'],
                ['id' => 'senderPrincipalActivity', 'value' => $senderPrincipalActivity, 'label' => 'Sender principal activity'],
                ['id' => 'senderBankCode', 'value' => $senderBankCode, 'label' => 'Sender bank code'],
                ['id' => 'senderEmailAddress', 'value' => $senderEmailAddress, 'label' => 'Sender email address'],
                ['id' => 'senderPrimaryAccountNumber', 'value' => $senderPrimaryAccountNumber, 'label' => 'Sender primary account number'],
                ['id' => 'senderDateOfBirth', 'value' => $senderDateOfBirth, 'label' => 'Sender date of birth'],
                ['id' => 'ipnUrl', 'value' => $this->resultUrl, 'label' => 'Notification URL'],
                ['id' => 'shortCode', 'value' => $shortCode, 'label' => 'Short code'],
            ],
        ];

        $payment->update(['json_request' => json_encode($parameters)]);

        try {
            $response = $this->call($this->endPoint, $parameters, 'POST');
            $response = is_object($response) ? $response : (object) $response;

            $payment->update(['json_response' => json_encode($response)]);
        } catch (TandaException $e) {
            Log::error('Tanda B2C Error', ['exception' => $e]);

            $response = (object) [
                'status'       => $e->getCode(),
                'message'      => $e->getMessage(),
            ];
        }

        $data = [
            'response_status'  => $response->status ?? 'UNKNOWN_ERROR',
            'response_message' => $response->message ?? 'No response message',
        ];

        if ($response->status === 'P202000') {
            $data['transaction_id'] = $response->trackingId ?? null;
            $data['tracking_id'] = $response->trackingId ?? null;
        }

        $payment->update($data);

        return $payment;

    }

    private function processTransaction(
        bool $mobile,
        string $commandId,
        string $serviceProviderId,
        string $shortCode,
        string $accountName,
        string $accountNumber,
        string $bankCode,
        string $amount,
        array $customFieldsKeyValue = []
    ): TandaTransaction {
        $reference = (string) Str::ulid();

        $payment = TandaTransaction::create(array_merge([
            'payment_reference'   => $reference,
            'service_provider'    => $serviceProviderId,
            'merchant_wallet'     => $shortCode,
            'amount'              => $amount,
            'account_number'      => $accountNumber,
            'service_provider_id' => $serviceProviderId,
        ], $customFieldsKeyValue));
        if($mobile) {
            $parameters = [
                'commandId' => $commandId,
                'serviceProviderId' => $serviceProviderId,
                'reference' => $reference,
                'request' => [
                    ['id' => 'amount', 'label' => 'Amount', 'value' => $amount],
                    ['id' => 'narration', 'label' => 'Narration', 'value' => 'Mobile payment'],
                    ['id' => 'ipnUrl', 'label' => 'Result URL', 'value' => $this->resultUrl],
                    ['id' => 'shortCode', 'label' => 'Merchant Wallet', 'value' => $shortCode],
                    ['id' => 'accountNumber', 'label' => 'Mobile Number', 'value' => $accountNumber],
                ],
            ];
        } else {
            $parameters = [
                'commandId' => $commandId,
                'serviceProviderId' => $serviceProviderId,
                'reference' => $reference,
                'request' => [
                    ['id' => 'amount', 'label' => 'Amount', 'value' => $amount],
                    ['id' => 'narration', 'label' => 'Narration', 'value' => 'Bank payment'],
                    ['id' => 'ipnUrl', 'label' => 'Result URL', 'value' => $this->resultUrl],
                    ['id' => 'shortCode', 'label' => 'Merchant Wallet', 'value' => $shortCode],
                    ['id' => 'accountNumber', 'label' => 'Bank Account Number', 'value' => $accountNumber],
                    ['id' => 'bankCode', 'label' => 'Bank Code', 'value' => $bankCode],
                    ['id' => 'accountName', 'label' => 'Account Name', 'value' => $accountName],
                ],
            ];
        }

        $payment->update(['json_request' => json_encode($parameters)]);

        try {
            $response = $this->call($this->endPoint, $parameters, 'POST');
            $response = is_object($response) ? $response : (object) $response;

            $payment->update(['json_response' => json_encode($response)]);
        } catch (TandaException $e) {
            Log::error('Tanda B2C Error', ['exception' => $e]);

            $response = (object) [
                'status'       => $e->getCode(),
                'message'      => $e->getMessage(),
            ];
        }

        $data = [
            'response_status'  => $response->status ?? 'UNKNOWN_ERROR',
            'response_message' => $response->message ?? 'No response message',
        ];

        if ($response->status === 'P202000') {
            $data['transaction_id'] = $response->trackingId ?? null;
            $data['tracking_id'] = $response->trackingId ?? null;
        }

        $payment->update($data);

        return $payment;
    }
}
