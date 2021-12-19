<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $service_num
 * @property string $service_name
 * @property string $api_key
 * @property string $created_at
 * @property string $updated_at
 */
class Service extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'service_num';

    /**
     * @var array
     */
    protected $fillable = ['service_name', 'api_key', 'created_at', 'updated_at'];

}
