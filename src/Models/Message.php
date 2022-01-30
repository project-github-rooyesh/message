<?php
namespace Rooyesh\Message\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'name',
        'user_id',
        'mobile',
        'message',
        'token',
        'type'
    ];
    public function ww(){
        $this->belongsTo();
    }
}
