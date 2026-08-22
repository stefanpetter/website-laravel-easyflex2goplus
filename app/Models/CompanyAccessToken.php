<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'token',
    ];

    public static function generateToken(): string
    {
        return Str::random(40);
    }

    public static function findOrCreateForCompany(string $companyName): self
    {
        return static::query()->firstOrCreate(
            ['company_name' => $companyName],
            ['token' => static::generateToken()]
        );
    }
}
