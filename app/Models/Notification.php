<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Table name (if different from default plural "notifications")
    protected $table = 'notifications';

    // Primary key
    protected $primaryKey = 'id';

    // If you don’t have created_at / updated_at columns
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'customer_id',
        'notification',
    ];

    // Example: Relation to Customer (if you have a users/customers table)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }
}
