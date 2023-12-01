<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'verification_code',
        'verification_code_generated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_code_generated_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    /**
     * Check if the user's email is verified.
     *
     * @return bool
     */
    public function hasEmailVerified()
    {
        return $this->email_verified_at ? true : false;
    }

    /**
     * Validate a verification code for the user.
     *
     * @param string $code The verification code to validate.
     *
     * @return bool
     */
    public function validateVerificationCode($code)
    {
        // check if the verification code exists and is valid
        if (! $this->verification_code || ! Hash::check($code, $this->verification_code)) {
            return false;
        }

        // Get the verification code timeout from config
        $verificationCodeTimeout = config('custom.verification_code_timeout');
        $verificationCodeExpiresAt = $this->verification_code_generated_at->addMinutes($verificationCodeTimeout);

        // Check if the verification code has expired
        if ($verificationCodeExpiresAt->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Relationship: User has many todos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }
}
