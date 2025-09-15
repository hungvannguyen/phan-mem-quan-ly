<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaBlankType extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'type_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_name',
        'prefix',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type_name' => 'string',
            'prefix' => 'string',
        ];
    }

    /**
     * Get the diploma blanks of this type.
     */
    public function diplomaBlanks()
    {
        return $this->hasMany(DiplomaBlank::class, 'type_id', 'type_id');
    }
}