<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'major_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'major_name',
        'major_code',
        // Commented preserved field from Training model - uncomment when needed
        // 'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'major_name' => 'string',
            'major_code' => 'string',
            // Commented preserved cast - uncomment when needed
            // 'description' => 'string',
        ];
    }

    /**
     * Get the students that belong to this major.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'major_id', 'major_id');
    }
}
