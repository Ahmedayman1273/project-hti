<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الأعمدة القابلة للتعديل من خلال الـ mass assignment
     * حالياً فقط profile_image يمكن تعديله من المستخدم نفسه
     */
    protected $fillable = [
    'name',
    'email',
    'password',
    'type',
    'major',
    'phone_number',
    'personal_email',
    'profile_photo_path', // ← لازم تضيفه هنا
];


    /**
     * الأعمدة اللي يتم إخفاؤها عند تحويل البيانات إلى JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * التحويلات التلقائية للأنواع في الأعمدة
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // علاقات المستخدم

    public function posts()
    {
        return $this->hasMany(Post::class, 'posted_by');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'evented_by');
    }

    public function certificateRequests()
    {
        return $this->hasMany(CertificateRequest::class);
    }

    public function enrollmentProofRequests()
    {
        return $this->hasMany(EnrollmentProofRequest::class);
    }

}
