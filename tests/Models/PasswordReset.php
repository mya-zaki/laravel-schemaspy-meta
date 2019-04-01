<?php
namespace MyaZaki\LaravelSchemaspyMeta\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = ['email', 'token', 'created_at'];
    public $timestamps = false;
    protected $primaryKey = 'email';
    protected $keyType = 'string';
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}