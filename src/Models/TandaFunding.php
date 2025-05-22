<?php

declare(strict_types=1);

namespace EdLugz\Tanda\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class TandaFunding
 *
 * @property int $id
 * @property int|null $funding_id
 * @property string $fund_reference
 * @property string $service_provider
 * @property string $account_number
 * @property string $amount
 * @property string|null $response_status
 * @property string|null $response_message
 * @property string|null $transaction_id
 * @property string|null $request_status
 * @property string|null $request_message
 * @property string|null $receipt_number
 * @property string|null $timestamp
 * @property string|null $transaction_reference
 * @property string|null $json_result
 * @property string|null $json_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|TandaFunding where(string $column, mixed $value)
 * @method static TandaFunding create(array $attributes)
 * @method first()
 */
class TandaFunding extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'funding_id',
        'fund_reference',
        'service_provider',
        'account_number',
        'merchant_wallet',
        'short_code',
        'amount',
        'response_status',
        'response_message',
        'tracking_id',
        'transaction_id',
        'request_status',
        'request_message',
        'receipt_number',
        'timestamp',
        'transaction_reference',
        'json_request',
        'json_result',
        'json_response',
    ];

    protected $casts = [
        'json_request' => 'string',
        'json_result' => 'string',
        'json_response' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
