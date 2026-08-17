<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateClientQuestionsJob;
use App\Models\QuizClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuizClientController extends Controller
{
    public function index(): View
    {
        $clients = QuizClient::query()
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('clients', 'public');
        }

        unset($data['logo']);

        $client = QuizClient::query()->create($data);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Cliente criado.');
    }

    public function show(QuizClient $client): View
    {
        $client->loadCount('questions');
        $categories = $client->questions()
            ->selectRaw('categoria, count(*) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        return view('admin.clients.show', [
            'client' => $client,
            'categories' => $categories,
            'publicUrl' => url('/'.$client->slug),
        ]);
    }

    public function questions(Request $request, QuizClient $client): View
    {
        $categoria = trim((string) $request->query('categoria', ''));

        $categories = $client->questions()
            ->selectRaw('categoria, count(*) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        $query = $client->questions()
            ->with('options')
            ->orderBy('categoria')
            ->orderBy('id');

        if ($categoria !== '') {
            $query->where('categoria', $categoria);
        }

        return view('admin.clients.questions', [
            'client' => $client,
            'categories' => $categories,
            'categoria' => $categoria,
            'questions' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function edit(QuizClient $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, QuizClient $client): RedirectResponse
    {
        $data = $this->validated($request, $client);

        if ($request->boolean('remove_logo') && $client->logo_path) {
            Storage::disk('public')->delete($client->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($client->logo_path) {
                Storage::disk('public')->delete($client->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('clients', 'public');
        }

        unset($data['logo'], $data['remove_logo']);

        $client->update($data);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Cliente atualizado.');
    }

    public function generate(Request $request, QuizClient $client): RedirectResponse
    {
        if (in_array($client->questions_generation_status, [
            QuizClient::GENERATION_PENDING,
            QuizClient::GENERATION_RUNNING,
        ], true)) {
            return back()->withErrors(['prompt' => 'Já existe uma geração em andamento para este cliente.']);
        }

        $data = $request->validate([
            'prompt' => ['required', 'string', 'min:10', 'max:4000'],
            'total' => ['required', 'integer', 'min:1', 'max:100'],
            'categories' => ['required', 'array', 'min:1', 'max:20'],
            'categories.*' => ['string', 'min:1', 'max:80'],
        ]);

        $categories = collect($data['categories'])
            ->map(fn ($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($categories === []) {
            return back()->withErrors(['categories' => 'Informe ao menos uma categoria.'])->withInput();
        }

        if (! config('services.openai.key')) {
            return back()->withErrors(['prompt' => 'Configure OPENAI_API_KEY no .env.'])->withInput();
        }

        $client->update([
            'questions_generation_status' => QuizClient::GENERATION_PENDING,
            'questions_generation_error' => null,
            'questions_generation_total' => (int) $data['total'],
            'questions_generation_done' => 0,
        ]);

        GenerateClientQuestionsJob::dispatch(
            $client->id,
            $data['prompt'],
            $categories,
            (int) $data['total'],
        );

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Geração de perguntas enfileirada. Atualize a página em alguns minutos.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?QuizClient $client = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('quiz_clients', 'slug')->ignore($client?->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (QuizClient::isReservedSlug((string) $value)) {
                        $fail('Este slug é reservado pelo sistema.');
                    }
                },
            ],
            'is_active' => ['sometimes', 'boolean'],
            'use_system_categories' => ['sometimes', 'boolean'],
            'palette' => ['required', 'string', Rule::in(array_keys(QuizClient::PALETTES))],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        $data['slug'] = QuizClient::normalizeSlug($data['slug']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['use_system_categories'] = $request->boolean('use_system_categories', false);

        return $data;
    }
}
