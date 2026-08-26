<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GameController;
use App\Models\Question;
use App\Services\QuestionGenerationService;
use App\Support\QuestionBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyQuestionController extends Controller
{
    public function __construct(private QuestionGenerationService $generations) {}

    public function index(Request $request): View
    {
        $categoria = trim((string) $request->query('categoria', ''));
        $nivel = trim((string) $request->query('nivel', ''));

        if ($nivel !== '' && ! array_key_exists($nivel, GameController::LEVELS)) {
            abort(404);
        }

        $categories = collect(QuestionBank::categoriesFamily())
            ->map(fn (array $cat) => (object) [
                'categoria' => $cat['nome'],
                'total' => $cat['total'],
            ]);

        $query = Question::query()
            ->whereNull('client_id')
            ->with('options')
            ->orderBy('nivel')
            ->orderBy('categoria')
            ->orderBy('id');

        if ($categoria !== '') {
            $query->where('categoria', $categoria);
        }

        if ($nivel !== '') {
            $query->where('nivel', $nivel);
        }

        return view('admin.questions.index', [
            'categories' => $categories,
            'categoria' => $categoria,
            'nivel' => $nivel,
            'levels' => GameController::LEVELS,
            'questions' => $query->paginate(25)->withQueryString(),
            'questionsCount' => Question::query()->whereNull('client_id')->count(),
            'generationSummary' => $this->generations->familySummary(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nivel' => ['required', 'string', 'in:'.implode(',', array_keys(GameController::LEVELS))],
            'prompt' => ['required', 'string', 'min:10', 'max:4000'],
            'total' => ['required', 'integer', 'min:1', 'max:100'],
            'categories' => ['nullable'],
        ], [
            'nivel.required' => 'Escolha o nível.',
            'prompt.required' => 'Informe o prompt.',
            'prompt.min' => 'O prompt precisa ter pelo menos 10 caracteres.',
            'total.required' => 'Informe o total de perguntas.',
            'total.min' => 'Gere pelo menos 1 pergunta.',
            'total.max' => 'No máximo 100 perguntas por execução.',
        ]);

        $useEmoji = $request->boolean('use_emoji');
        $useImages = $request->boolean('use_images');

        $raw = $data['categories'] ?? $request->input('categories');
        $parts = is_array($raw)
            ? $raw
            : (preg_split('/[\n,;]+/', (string) $raw) ?: []);

        $categories = collect($parts)
            ->map(fn ($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values()
            ->take(20)
            ->all();

        if ($categories === []) {
            return back()->withErrors(['categories' => 'Informe ao menos uma categoria.'])->withInput();
        }

        if (! config('services.openai.key')) {
            return back()->withErrors(['prompt' => 'Configure OPENAI_API_KEY no .env.'])->withInput();
        }

        $summary = $this->generations->familySummary();
        $this->generations->queueFamily(
            $data['nivel'],
            $data['prompt'],
            $categories,
            (int) $data['total'],
            $useEmoji,
            $useImages,
        );

        $queued = $summary['queued'] + ($summary['running'] ? 1 : 0) + 1;
        $message = $queued > 1
            ? "Geração enfileirada ({$queued} na fila). As execuções rodam em sequência."
            : 'Geração de perguntas enfileirada. Atualize a página em alguns minutos.';

        return redirect()
            ->route('admin.dashboard')
            ->with('status', $message);
    }
}
