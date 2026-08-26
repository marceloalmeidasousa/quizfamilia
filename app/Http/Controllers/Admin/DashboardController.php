<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GameController;
use App\Models\Question;
use App\Services\AdminStatsService;
use App\Services\QuestionGenerationService;
use App\Support\QuestionBank;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminStatsService $stats, QuestionGenerationService $generations): View
    {
        $categories = collect(QuestionBank::categoriesFamily())
            ->map(fn (array $cat) => (object) [
                'categoria' => $cat['nome'],
                'total' => $cat['total'],
            ]);

        return view('admin.dashboard', [
            ...$stats->dashboard(),
            'categories' => $categories,
            'questionsCount' => Question::query()->whereNull('client_id')->count(),
            'levels' => GameController::LEVELS,
            'generationSummary' => $generations->familySummary(),
        ]);
    }
}
