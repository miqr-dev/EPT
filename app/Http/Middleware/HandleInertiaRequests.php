<?php

namespace App\Http\Middleware;

use App\Models\CollaborationNews;
use App\Models\CollaborationSuggestion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user()?->load('participantProfile'),
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'collaborationNotifications' => $this->collaborationNotifications($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array{news: int, suggestions: int, total: int}
     */
    private function collaborationNotifications(Request $request): array
    {
        if (! in_array($request->user()?->role, ['admin', 'teacher'], true)) {
            return [
                'news' => 0,
                'suggestions' => 0,
                'total' => 0,
            ];
        }

        $news = CollaborationNews::count();
        $suggestions = CollaborationSuggestion::query()
            ->where('is_hidden', false)
            ->where('status', 'open')
            ->count();

        return [
            'news' => $news,
            'suggestions' => $suggestions,
            'total' => $news + $suggestions,
        ];
    }
}
