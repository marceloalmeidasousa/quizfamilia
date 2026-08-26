<?php

namespace App\Models;

use App\Models\Concerns\TracksQuestionGeneration;
use Illuminate\Database\Eloquent\Model;

class SystemState extends Model
{
    use TracksQuestionGeneration;

    public const KEY_FAMILY_QUESTIONS = 'family_questions';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'questions_generation_status',
        'questions_generation_error',
        'questions_generation_total',
        'questions_generation_done',
    ];

    protected function casts(): array
    {
        return [
            'questions_generation_total' => 'integer',
            'questions_generation_done' => 'integer',
        ];
    }

    public static function familyQuestions(): self
    {
        return static::query()->firstOrCreate(['key' => self::KEY_FAMILY_QUESTIONS]);
    }
}
