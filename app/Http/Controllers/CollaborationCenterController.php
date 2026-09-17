<?php

namespace App\Http\Controllers;

use App\Models\CollaborationNews;
use App\Models\CollaborationSuggestion;
use App\Models\CollaborationSuggestionVote;
use App\Models\CollaborationTodo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationCenterController extends Controller
{
    private const NEWS_ALLOWED_TAGS = [
        'a',
        'b',
        'blockquote',
        'br',
        'div',
        'em',
        'h3',
        'h4',
        'i',
        'li',
        'ol',
        'p',
        'span',
        'strong',
        'u',
        'ul',
    ];

    private const NEWS_REMOVE_WITH_CONTENT_TAGS = [
        'embed',
        'iframe',
        'math',
        'object',
        'script',
        'style',
        'svg',
    ];

    private const NEWS_ALLOWED_STYLES = [
        'color',
        'font-size',
        'font-style',
        'font-weight',
        'text-decoration',
    ];

    public function index(Request $request): Response
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);

        return Inertia::render('CollaborationCenter', [
            'newsItems' => CollaborationNews::with('author:id,name,city_id', 'author.city:id,name')->latest()->get(),
            'todos' => CollaborationTodo::with('author:id,name,city_id', 'author.city:id,name')->latest()->get(),
            'suggestions' => CollaborationSuggestion::with(['author:id,name,city_id', 'author.city:id,name', 'votes.user:id,name,city_id', 'votes.user.city:id,name'])
                ->where('is_hidden', false)
                ->where('status', 'open')
                ->latest()
                ->get(),
        ]);
    }

    public function storeNews(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $this->validatedNewsData($request);
        CollaborationNews::create($data + ['created_by' => $request->user()->id]);

        return back();
    }

    public function updateNews(Request $request, CollaborationNews $news): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $this->validatedNewsData($request);
        $news->update($data);

        return back();
    }

    public function deleteNews(Request $request, CollaborationNews $news): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $news->delete();

        return back();
    }

    public function storeTodo(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['task' => 'required|string']);
        CollaborationTodo::create($data + ['created_by' => $request->user()->id]);

        return back();
    }

    public function updateTodo(Request $request, CollaborationTodo $todo): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['task' => 'sometimes|string', 'is_completed' => 'sometimes|boolean']);
        $todo->update($data);

        return back();
    }

    public function deleteTodo(Request $request, CollaborationTodo $todo): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $todo->delete();

        return back();
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        $data = $request->validate(['content' => 'required|string']);
        CollaborationSuggestion::create(['title' => 'Vorschlag', 'content' => $data['content'], 'created_by' => $request->user()->id]);

        return back();
    }

    public function voteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        $data = $request->validate([
            'vote' => 'nullable|in:like,dislike',
            'comment' => 'nullable|string',
            'delete_comment' => 'nullable|boolean',
        ]);

        if (empty($data['vote'])) {
            CollaborationSuggestionVote::where('suggestion_id', $suggestion->id)->where('user_id', $request->user()->id)->delete();

            return back();
        }

        $existingVote = CollaborationSuggestionVote::where('suggestion_id', $suggestion->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $comment = $existingVote?->comment;

        if ($data['vote'] === 'like') {
            $comment = null;
        } elseif ($data['vote'] === 'dislike') {
            if (array_key_exists('comment', $data)) {
                $comment = $data['comment'];
            }
        }

        if (($data['delete_comment'] ?? false) === true) {
            $comment = null;
        }

        CollaborationSuggestionVote::updateOrCreate(
            ['suggestion_id' => $suggestion->id, 'user_id' => $request->user()->id],
            ['vote' => $data['vote'], 'comment' => $comment]
        );

        return back();
    }

    public function promoteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $todo = CollaborationTodo::firstOrNew(['suggestion_id' => $suggestion->id]);

        if (! $todo->exists) {
            $todo->forceFill([
                'task' => $suggestion->title.': '.$suggestion->content,
                'created_by' => $suggestion->created_by,
                'created_at' => $suggestion->created_at,
                'updated_at' => $suggestion->updated_at,
            ])->save();
        }

        $suggestion->update([
            'status' => 'promoted',
            'is_hidden' => true,
        ]);

        return back();
    }

    public function deleteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        abort_unless($suggestion->created_by === $request->user()->id, 403);

        $suggestion->delete();

        return back();
    }

    /**
     * @return array{title: string, content: string}
     */
    private function validatedNewsData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $data['content'] = $this->sanitizeNewsContent($data['content']);

        $plainText = preg_replace('/\s+/u', '', html_entity_decode(strip_tags($data['content']), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';

        if ($plainText === '') {
            throw ValidationException::withMessages([
                'content' => __('The content field is required.'),
            ]);
        }

        return $data;
    }

    private function sanitizeNewsContent(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        if (! str_contains($content, '<')) {
            return nl2br(e($content), false);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="news-content">'.$content.'</div>', LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $container = $document->getElementById('news-content');

        if (! $container) {
            return '';
        }

        $this->sanitizeNewsNode($container);

        $html = '';

        foreach ($container->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    private function sanitizeNewsNode(\DOMNode $node): void
    {
        for ($child = $node->firstChild; $child !== null; $child = $next) {
            $next = $child->nextSibling;

            if ($child instanceof \DOMText) {
                continue;
            }

            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);

                continue;
            }

            $tagName = strtolower($child->tagName);

            if (in_array($tagName, self::NEWS_REMOVE_WITH_CONTENT_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tagName, self::NEWS_ALLOWED_TAGS, true)) {
                $this->sanitizeNewsNode($child);

                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }

                $node->removeChild($child);

                continue;
            }

            $this->sanitizeNewsElement($child);
            $this->sanitizeNewsNode($child);
        }
    }

    private function sanitizeNewsElement(\DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute;
        }

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if ($name === 'style') {
                $style = $this->sanitizeNewsStyle($value);

                if ($style === '') {
                    $element->removeAttribute($attribute->name);
                } else {
                    $element->setAttribute('style', $style);
                }

                continue;
            }

            if ($tagName === 'a' && $name === 'href') {
                if ($this->isSafeNewsUrl($value)) {
                    $element->setAttribute('href', $value);
                } else {
                    $element->removeAttribute($attribute->name);
                }

                continue;
            }

            if ($tagName === 'a' && $name === 'target') {
                if (in_array($value, ['_blank', '_self'], true)) {
                    $element->setAttribute('target', $value);
                } else {
                    $element->removeAttribute($attribute->name);
                }

                continue;
            }

            $element->removeAttribute($attribute->name);
        }

        if ($tagName === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function sanitizeNewsStyle(string $style): string
    {
        $declarations = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = preg_replace('/\s+/u', ' ', $value) ?? '';

            if (! in_array($property, self::NEWS_ALLOWED_STYLES, true) || ! $this->isSafeNewsStyleValue($property, $value)) {
                continue;
            }

            $declarations[] = $property.': '.$value;
        }

        return implode('; ', $declarations);
    }

    private function isSafeNewsStyleValue(string $property, string $value): bool
    {
        if ($value === '' || preg_match('/(expression|javascript:|url\s*\(|[<>])/i', $value)) {
            return false;
        }

        return match ($property) {
            'color' => preg_match('/^(#[0-9a-f]{3,8}|rgba?\(\s*[\d.]+%?\s*,\s*[\d.]+%?\s*,\s*[\d.]+%?(?:\s*,\s*(0|1|0?\.\d+))?\s*\)|[a-z]+)$/i', $value) === 1,
            'font-size' => preg_match('/^(1[2-9]|2[0-8])px$/', $value) === 1,
            'font-style' => in_array($value, ['italic', 'normal'], true),
            'font-weight' => in_array($value, ['bold', 'normal'], true) || preg_match('/^[1-9]00$/', $value) === 1,
            'text-decoration' => in_array($value, ['line-through', 'none', 'underline'], true),
            default => false,
        };
    }

    private function isSafeNewsUrl(string $value): bool
    {
        if ($value === '' || preg_match('/[\x00-\x1F\x7F\s]/', $value)) {
            return false;
        }

        return str_starts_with($value, '#')
            || str_starts_with($value, '/')
            || preg_match('/^(https?:|mailto:)/i', $value) === 1;
    }
}
