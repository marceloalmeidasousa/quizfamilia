<?php

namespace App\Jobs;

use App\Models\QuestionGenerationRun;
use App\Services\FamilyQuestionPersister;
use App\Services\OpenAiQuestionGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateFamilyQuestionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public int $runId) {}

    public function handle(OpenAiQuestionGenerator $generator, FamilyQuestionPersister $persister): void
    {
        $run = QuestionGenerationRun::query()->find($this->runId);

        if (! $run || $run->scope !== QuestionGenerationRun::SCOPE_FAMILY) {
            return;
        }

        $total = max(1, min(100, $run->total));
        $categories = array_values(array_filter(array_map('trim', $run->categories ?? [])));

        if ($categories === []) {
            $categories = ['Geral'];
        }

        $run->update([
            'status' => QuestionGenerationRun::STATUS_RUNNING,
            'error' => null,
            'done' => 0,
        ]);

        $done = 0;

        try {
            while ($done < $total) {
                $batchSize = min(10, $total - $done);
                $items = $generator->generateBatch(
                    $run->prompt,
                    $categories,
                    $batchSize,
                    $run->use_emoji,
                    $run->use_images,
                    (string) $run->nivel,
                );
                $saved = $persister->persist((string) $run->nivel, $items, $run->use_emoji);
                $done += $saved;

                $run->update([
                    'done' => min($done, $total),
                ]);
            }

            $run->update([
                'status' => QuestionGenerationRun::STATUS_DONE,
                'done' => $done,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            Log::error('GenerateFamilyQuestionsJob failed', [
                'run_id' => $this->runId,
                'message' => $e->getMessage(),
            ]);

            $run->update([
                'status' => QuestionGenerationRun::STATUS_FAILED,
                'error' => mb_substr($e->getMessage(), 0, 2000),
                'done' => $done,
            ]);

            throw $e;
        }
    }
}
