<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content
 * @property int $customer_id
 * @property bool $received
 */
class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'customer_id',
        'received',
    ];

    protected $attributes = [
        'received' => false,
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Setter للـ content
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
    }

    // Setter للـ received
    public function setReceivedAttribute($value)
    {
        $this->attributes['received'] = $value;
    }
}
