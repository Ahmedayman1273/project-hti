<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateRequest extends Model
{
    use HasFactory;

    protected $table = 'certificate_requests';

    protected $fillable = [
        'user_id',
        'status',
        'notes',
    ];

    // العلاقة بين الشهادة والمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
