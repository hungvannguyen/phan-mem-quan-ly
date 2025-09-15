<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'setting_key';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'setting_key' => 'string',
            'setting_value' => 'string',
        ];
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, string $default = ''): string
    {
        $setting = self::find($key);
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, string $value): void
    {
        self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Common setting keys constants.
     */
    public const SCHOOL_NAME = 'SchoolName';
    public const ADDRESS = 'Address';
    public const PHONE = 'Phone';
    public const EMAIL = 'Email';
    public const WEBSITE = 'Website';
}
