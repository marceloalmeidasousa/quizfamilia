<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuizClient extends Model
{
    public const CUSTOM_NIVEL = 'custom';

    public const GENERATION_PENDING = 'pending';

    public const GENERATION_RUNNING = 'running';

    public const GENERATION_DONE = 'done';

    public const GENERATION_FAILED = 'failed';

    public const DEFAULT_PALETTE = 'violet';

    /**
     * Paletas da página pública do cliente (header, fundo, botões).
     *
     * @var array<string, array{label: string, brand: string, accent: string, canvas: string}>
     */
    public const PALETTES = [
        'violet' => [
            'label' => 'Roxo',
            'brand' => '#46178f',
            'accent' => '#7b2cbf',
            'canvas' => '#f2f2f7',
        ],
        'ocean' => [
            'label' => 'Azul',
            'brand' => '#1d4ed8',
            'accent' => '#2563eb',
            'canvas' => '#eef3fb',
        ],
        'forest' => [
            'label' => 'Verde',
            'brand' => '#0f766e',
            'accent' => '#0d9488',
            'canvas' => '#eef6f3',
        ],
    ];

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
        'palette',
        'use_system_categories',
        'questions_generation_status',
        'questions_generation_error',
        'questions_generation_total',
        'questions_generation_done',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'use_system_categories' => 'boolean',
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

        $path = ltrim(str_replace('\\', '/', $this->logo_path), '/');

        return '/storage/'.$path;
    }

    public function paletteKey(): string
    {
        $key = (string) ($this->palette ?: self::DEFAULT_PALETTE);

        return array_key_exists($key, self::PALETTES) ? $key : self::DEFAULT_PALETTE;
    }

    /**
     * @return array{label: string, brand: string, accent: string, canvas: string}
     */
    public function paletteColors(): array
    {
        return self::PALETTES[$this->paletteKey()];
    }

    public function paletteStyle(): string
    {
        $palette = $this->paletteColors();

        return implode(';', [
            '--color-brand-deep:'.$palette['brand'],
            '--color-coral:'.$palette['accent'],
            '--color-brand:'.$palette['accent'],
            '--color-brand-strong:'.$palette['brand'],
            '--color-fg-brand:'.$palette['accent'],
            '--color-fg-brand-strong:'.$palette['brand'],
            '--color-canvas:'.$palette['canvas'],
        ]);
    }

    public function usesSystemCategories(): bool
    {
        return (bool) $this->use_system_categories;
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
