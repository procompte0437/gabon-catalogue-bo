<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value === null ? $default : $value;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public static function appName(): string
    {
        return static::get('app_name', 'Gabon Catalogue') ?: 'Gabon Catalogue';
    }

    /** Mot de passe général à 4 chiffres (0110 par défaut). */
    public static function masterCode(): string
    {
        return static::get('master_code') ?: (string) config('catalogue.master_code', '0110');
    }

    public static function whatsappDisplay(): string
    {
        return static::get('whatsapp_display', '062-70-47-45') ?: '062-70-47-45';
    }

    public static function whatsappLink(): string
    {
        $digits = preg_replace('/\D/', '', static::whatsappDisplay()) ?? '';

        if (str_starts_with($digits, '241')) {
            return 'https://wa.me/'.$digits;
        }

        $digits = preg_replace('/^0/', '', $digits) ?? '';

        return 'https://wa.me/241'.$digits;
    }
}
