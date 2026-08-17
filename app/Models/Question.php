<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'client_id',
        'nivel',
        'code',
        'categoria',
        'emoji',
        'pergunta',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(QuizClient::class, 'client_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    /**
     * Formato usado pelo Quiz e pelo Ao Vivo.
     *
     * @return array<string, mixed>
     */
    public function toBankItem(): array
    {
        $opcoes = [];
        $opcoesEmoji = [];
        $correta = 0;
        $hasEmoji = false;

        foreach ($this->options as $i => $option) {
            $opcoes[] = $option->texto;
            if ($option->emoji) {
                $hasEmoji = true;
            }
            $opcoesEmoji[] = $option->emoji ?? '';
            if ($option->is_correct) {
                $correta = $i;
            }
        }

        $item = [
            'id' => $this->code,
            'categoria' => $this->categoria,
            'emoji' => $this->emoji,
            'pergunta' => $this->pergunta,
            'opcoes' => $opcoes,
            'correta' => $correta,
        ];

        if ($hasEmoji) {
            $item['opcoesEmoji'] = $opcoesEmoji;
        }

        return $item;
    }
}
