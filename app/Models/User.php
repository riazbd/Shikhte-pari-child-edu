<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Wishlist;
use App\Models\studentDetail;
use App\Models\Comment;
use App\Models\Reply;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class User extends Authenticatable
{
    // protected $guard_name = 'api';    
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'parent_id',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function hasPermissionTo($permission, $guardName): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }


    protected function getDefaultGuardName(): string
    {
        return 'api';
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function purchases()
    {
        return $this->hasMany(\App\Models\CoursePurchase::class);
    }
    public function studentDetails()
    {
        return $this->hasMany(StudentDetail::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function quizResults(){
        return $this->hasMany(\App\Models\QuizResult::class);
    }
}
