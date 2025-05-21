# Tanda V3

## Installation

Via Composer

```bash
composer require edlugz/tanda-v3
```

## Publish Migration Files

```bash
php artisan vendor:publish --provider="EdLugz\Tanda\TandaServiceProvider" --tag="migrations"
```

Fill in all the details you will be requiring for your application. Here are the env variables for quick copy paste.

```bash
TANDA_RESULT_URL
TANDA_C2B_RESULT_URL
TANDA_CLIENT_ID
TANDA_CLIENT_SECRET
TANDA_ORG_ID
TANDA_AUTH_BASE_URL
TANDA_API_BASE_URL
```


## Usage

Using the facade

C2B - Fund Wallet (send stk push to mobile number)
```bash
Tanda::C2B()->request($serviceProviderId, $merchantWallet, $mobileNumber, $amount, $customFieldsKeyValue = []);
```

P2P -  send to internal wallets
```bash
Tanda::P2P()->send($senderWallet, $receiverWallet, $amount, $customFieldsKeyValue = []);
```

B2C -  send to bank accounts and mobile wallets
```bash
Tanda::B2C()->bank($merchantWallet, $bankCode, $amount, $accountNumber, $narration, $customFieldsKeyValue = []);
Tanda::B2C()->mobile($merchantWallet, $serviceProviderId, $amount, $mobileNumber, $customFieldsKeyValue = []);
Tanda::B2C()->internationalBank($merchantWallet, $serviceProviderId, $amount, $mobileNumber, $customFieldsKeyValue = []);
```

B2B - to paybills and till numbers
```bash
Tanda::B2B()->buygoods($merchantWallet, $amount, $till, $contact, $customFieldsKeyValue = []);
Tanda::B2B()->paybill($merchantWallet, $amount, $paybill, $accountNumber, $contact, $customFieldsKeyValue = []);
```

Status - check status
```bash
Tanda::status()->fundingCheck($reference, $shortCode);
Tanda::status()->paymentCheck($reference, $shortCode);
```

Helper functions - get mno network based on mobile number
```bash
Tanda::helper()->serviceProvider($mobileNumber);
```

Helper functions - receive payout results
```bash
Tanda::helper()->payout($data);
```

Helper functions - receive c2b results
```bash
Tanda::helper()->c2b($data);
```

## Security

If you discover any security related issues, please email eddy.lugaye@gmail.com instead of using the issue tracker.


## Credits

- [Eddy Lugaye][link-author]
- [All Contributors][link-contributors]


## License

MIT. Please see the [license file](license.md) for more information.
