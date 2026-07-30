<?php

namespace App\Services;

use App\Models\GamePlay;
use App\Models\LiveSession;
use App\Models\SiteVisit;
use App\Models\X1Challenge;
use Illuminate\Support\Collection;

class AdminStatsService
{
    /**
     * @return array{
     *     total_plays: int,
     *     solo_plays: int,
     *     live_plays: int,
     *     x1_plays: int,
     *     total_visits: int,
     *     live_finished: int,
     *     x1_total: int,
     *     x1_awaiting: int,
     *     x1_finished: int,
     *     recent_plays: Collection<int, GamePlay>,
     *     recent_visits: Collection<int, SiteVisit>,
     *     recent_x1: Collection<int, X1Challenge>
     * }
     */
    public function dashboard(): array
    {
        return [
            'total_plays' => GamePlay::query()->count(),
            'solo_plays' => GamePlay::query()->where('type', GamePlay::TYPE_SOLO)->count(),
            'live_plays' => GamePlay::query()->where('type', GamePlay::TYPE_LIVE)->count(),
            'x1_plays' => GamePlay::query()->where('type', GamePlay::TYPE_X1)->count(),
            'total_visits' => SiteVisit::query()->count(),
            'live_finished' => LiveSession::query()
                ->where('status', LiveSession::STATUS_FINISHED)
                ->count(),
            'x1_total' => X1Challenge::query()->count(),
            'x1_awaiting' => X1Challenge::query()
                ->where('status', X1Challenge::STATUS_AWAITING_OPPONENT)
                ->count(),
            'x1_finished' => X1Challenge::query()
                ->where('status', X1Challenge::STATUS_FINISHED)
                ->count(),
            'recent_plays' => GamePlay::query()
                ->latest('started_at')
                ->limit(40)
                ->get(),
            'recent_visits' => SiteVisit::query()
                ->latest('visited_at')
                ->limit(40)
                ->get(),
            'recent_x1' => X1Challenge::query()
                ->latest('id')
                ->limit(30)
                ->get(),
        ];
    }
}
