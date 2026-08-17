<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuizClient extends Model
{
    public const CUSTOM_NIVEL = 'custom';

    public const GENERATION_PENDING = 'pending';

    public const GENERATION_RUNNING = 'running';

    public const GENERATION_DONE = 'done';

    public const GENERATION_FAILED = 'failed';

    /** @var list<string> */
    public const RESERVED_SLUGS = [
        'login',
        'logout',
        'painel',
        'quiz',
        'jogo',
        'ao-vivo',
        'x1',
        'privacidade',
        'auth',
        'up',
        'admin',
        'api',
        'storage',
        'build',
        'clientes',
    ];

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'is_active',
        'questions_generation_status',
        'questions_generation_error',
        'questions_generation_total',
        'questions_generation_done',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'questions_generation_total' => 'integer',
            'questions_generation_done' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'client_id');
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'client_id');
    }

    public function x1Challenges(): HasMany
    {
        return $this->hasMany(X1Challenge::class, 'client_id');
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * @return array{title: string, subtitle: string, description: string, accent: string, age: string}
     */
    public function levelMeta(): array
    {
        return [
            'title' => $this->name,
            'subtitle' => 'Quiz personalizado',
            'description' => 'Perguntas exclusivas deste cliente.',
            'accent' => 'ocean',
            'age' => '',
        ];
    }

    public static function normalizeSlug(string $value): string
    {
        $slug = Str::slug($value);

        return mb_substr($slug, 0, 64);
    }

    public static function isReservedSlug(string $slug): bool
    {
        return in_array(Str::lower($slug), self::RESERVED_SLUGS, true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
