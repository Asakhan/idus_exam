<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $user_num
 * @property string $name
 * @property string $nickname
 * @property string $password
 * @property string $phone
 * @property string $email
 * @property string $sex
 * @property string $created_at
 * @property string $updated_at
 */
class User extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_num';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'nickname', 'password', 'phone', 'email', 'gender', 'created_at', 'updated_at'];

}
