<?php

declare(strict_types=1);

namespace EdLugz\Tanda\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class TandaTransaction
 *
 * @property int $id
 * @property int|null $payment_id
 * @property string $payment_reference
 * @property string $service_provider
 * @property string|null $short_code
 * @property decimal $amount
 * @property string|null $account_number
 * @property string|null $contact
 * @property string|null $service_provider_id
 * @property string|null $response_status
 * @property string|null $response_message
 * @property string|null $transaction_id
 * @property string|null $request_status
 * @property string|null $request_message
 * @property string|null $receipt_number
 * @property string|null $transaction_receipt
 * @property string|null $timestamp
 * @property string $transactable_type
 * @property int $transactable_id
 * @property string|null $json_response
 * @property string|null $json_result
 * @property string|null $json_request
 * @property string|null $registered_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|TandaTransaction where(string $column, mixed $value)
 * @method static TandaTransaction create(array $attributes)
 * @method update(array $attributes)
 * @method first()
 */
class TandaTransaction extends Model
{
    use SoftDeletes, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'json_response' => 'string',
        'json_result' => 'string',
        'json_request' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
