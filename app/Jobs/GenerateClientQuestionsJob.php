<?php

namespace App\Jobs;

use App\Models\QuizClient;
use App\Services\ClientQuestionPersister;
use App\Services\OpenAiQuestionGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateClientQuestionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    /**
     * @param  list<string>  $categories
     */
    public function __construct(
        public int $clientId,
        public string $prompt,
        public array $categories,
        public int $total,
        public bool $useEmoji = true,
        public bool $useImages = false,
    ) {}

    public function handle(OpenAiQuestionGenerator $generator, ClientQuestionPersister $persister): void
    {
        $client = QuizClient::query()->find($this->clientId);
        if (! $client) {
            return;
        }

        $total = max(1, min(100, $this->total));
        $categories = array_values(array_filter(array_map('trim', $this->categories)));
        if ($categories === []) {
            $categories = ['Geral'];
        }

        $client->update([
            'questions_generation_status' => QuizClient::GENERATION_RUNNING,
            'questions_generation_error' => null,
            'questions_generation_total' => $total,
            'questions_generation_done' => 0,
        ]);

        $done = 0;

        try {
            while ($done < $total) {
                $batchSize = min(10, $total - $done);
                $items = $generator->generateBatch(
                    $this->prompt,
                    $categories,
                    $batchSize,
                    $this->useEmoji,
                    $this->useImages,
                );
                $saved = $persister->persist($client->fresh(), $items, $this->useEmoji);
                $done += $saved;

                $client->update([
                    'questions_generation_done' => min($done, $total),
                ]);
            }

            $client->update([
                'questions_generation_status' => QuizClient::GENERATION_DONE,
                'questions_generation_done' => $done,
                'questions_generation_error' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('GenerateClientQuestionsJob failed', [
                'client_id' => $this->clientId,
                'message' => $e->getMessage(),
            ]);

            $client->update([
                'questions_generation_status' => QuizClient::GENERATION_FAILED,
                'questions_generation_error' => mb_substr($e->getMessage(), 0, 2000),
                'questions_generation_done' => $done,
            ]);

            throw $e;
        }
    }
}
