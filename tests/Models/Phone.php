<?php
namespace MyaZaki\LaravelSchemaspyMeta\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'my_phones';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }
}