<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EnrollmentProofRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'notes',
    ];

    /**
     * علاقة الطلب بالمستخدم اللي أنشأه
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
