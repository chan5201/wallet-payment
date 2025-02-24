<?php

namespace App\Models;

use App\Events\TransferStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    public $table = 'transfers';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($transaction) {
            if ($transaction->isDirty('status')) {
                event(new TransferStatusUpdated($transaction, $transaction->getOriginal('status'), $transaction->status));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
