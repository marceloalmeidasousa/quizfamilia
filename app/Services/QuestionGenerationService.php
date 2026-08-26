<?php

namespace App\Services;

use App\Jobs\GenerateClientQuestionsJob;
use App\Jobs\GenerateFamilyQuestionsJob;
use App\Models\QuestionGenerationRun;
use App\Models\QuizClient;
use Illuminate\Support\Collection;

class QuestionGenerationService
{
    /**
     * @param  list<string>  $categories
     */
    public function queueFamily(
        string $nivel,
        string $prompt,
        array $categories,
        int $total,
        bool $useEmoji = true,
        bool $useImages = false,
    ): QuestionGenerationRun {
        $run = QuestionGenerationRun::query()->create([
            'scope' => QuestionGenerationRun::SCOPE_FAMILY,
            'nivel' => $nivel,
            'prompt' => $prompt,
            'categories' => $categories,
            'total' => $total,
            'use_emoji' => $useEmoji,
            'use_images' => $useImages,
            'status' => QuestionGenerationRun::STATUS_QUEUED,
        ]);

        GenerateFamilyQuestionsJob::dispatch($run->id);

        return $run;
    }

    /**
     * @param  list<string>  $categories
     */
    public function queueClient(
        QuizClient $client,
        string $prompt,
        array $categories,
        int $total,
        bool $useEmoji = true,
        bool $useImages = false,
    ): QuestionGenerationRun {
        $run = QuestionGenerationRun::query()->create([
            'scope' => QuestionGenerationRun::SCOPE_CLIENT,
            'client_id' => $client->id,
            'prompt' => $prompt,
            'categories' => $categories,
            'total' => $total,
            'use_emoji' => $useEmoji,
            'use_images' => $useImages,
            'status' => QuestionGenerationRun::STATUS_QUEUED,
        ]);

        GenerateClientQuestionsJob::dispatch($run->id);

        return $run;
    }

    /**
     * @return array{
     *     queued: int,
     *     running: QuestionGenerationRun|null,
     *     latest: QuestionGenerationRun|null
     * }
     */
    public function familySummary(): array
    {
        return $this->summary(QuestionGenerationRun::SCOPE_FAMILY);
    }

    /**
     * @return array{
     *     queued: int,
     *     running: QuestionGenerationRun|null,
     *     latest: QuestionGenerationRun|null
     * }
     */
    public function clientSummary(QuizClient $client): array
    {
        return $this->summary(QuestionGenerationRun::SCOPE_CLIENT, $client->id);
    }

    /**
     * @return array{
     *     queued: int,
     *     running: QuestionGenerationRun|null,
     *     latest: QuestionGenerationRun|null
     * }
     */
    private function summary(string $scope, ?int $clientId = null): array
    {
        $base = QuestionGenerationRun::query()
            ->where('scope', $scope)
            ->when($clientId !== null, fn ($q) => $q->where('client_id', $clientId));

        /** @var Collection<int, QuestionGenerationRun> $active */
        $active = (clone $base)
            ->whereIn('status', [
                QuestionGenerationRun::STATUS_QUEUED,
                QuestionGenerationRun::STATUS_RUNNING,
            ])
            ->orderBy('id')
            ->get();

        return [
            'queued' => $active->where('status', QuestionGenerationRun::STATUS_QUEUED)->count(),
            'running' => $active->firstWhere('status', QuestionGenerationRun::STATUS_RUNNING),
            'latest' => (clone $base)->latest('id')->first(),
        ];
    }
}
