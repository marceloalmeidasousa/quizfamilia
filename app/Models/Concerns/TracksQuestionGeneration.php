<?php

namespace App\Models\Concerns;

use App\Models\QuizClient;

trait TracksQuestionGeneration
{
    /**
     * @return array{label: string, class: string}|null
     */
    public function generationStatusMeta(): ?array
    {
        return match ($this->questions_generation_status) {
            QuizClient::GENERATION_DONE => [
                'label' => 'Concluído',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            QuizClient::GENERATION_PENDING, QuizClient::GENERATION_RUNNING => [
                'label' => 'Aguardando',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            QuizClient::GENERATION_FAILED => [
                'label' => 'Falhou',
                'class' => 'bg-red-50 text-red-700',
            ],
            default => null,
        };
    }
}
