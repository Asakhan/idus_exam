<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $order_num
 * @property integer $user_num
 * @property string $product_name
 * @property string $payment_created
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class Order extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'order_num';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['user_num', 'product_name', 'payment_created', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_num', 'user_num');
    }
}
