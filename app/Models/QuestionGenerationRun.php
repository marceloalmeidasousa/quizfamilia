<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionGenerationRun extends Model
{
    public const SCOPE_FAMILY = 'family';

    public const SCOPE_CLIENT = 'client';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'scope',
        'client_id',
        'nivel',
        'prompt',
        'categories',
        'total',
        'done',
        'use_emoji',
        'use_images',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'total' => 'integer',
            'done' => 'integer',
            'use_emoji' => 'boolean',
            'use_images' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(QuizClient::class, 'client_id');
    }

    /**
     * @return array{label: string, class: string}|null
     */
    public function statusMeta(): ?array
    {
        return match ($this->status) {
            self::STATUS_DONE => [
                'label' => 'Concluído',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            self::STATUS_QUEUED, self::STATUS_RUNNING => [
                'label' => $this->status === self::STATUS_QUEUED ? 'Na fila' : 'Em execução',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            self::STATUS_FAILED => [
                'label' => 'Falhou',
                'class' => 'bg-red-50 text-red-700',
            ],
            default => null,
        };
    }
}
