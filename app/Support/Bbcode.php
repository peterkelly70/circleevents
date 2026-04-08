<?php

namespace App\Support;

class Bbcode
{
    public static function render(?string $text): string
    {
        if (! $text) {
            return '';
        }

        $escaped = e($text);

        $escaped = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[u\](.*?)\[\/u\]/is', '<span class="underline">$1</span>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<blockquote class="border-l-2 border-white/15 pl-4 italic text-stone-400">$1</blockquote>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[code\](.*?)\[\/code\]/is', '<code class="rounded bg-black/30 px-1 py-0.5 text-amber-200">$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[url=(https?:\/\/[^\]\s]+)\](.*?)\[\/url\]/is', '<a href="$1" class="text-amber-300 underline" target="_blank" rel="noopener noreferrer">$2</a>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[url\](https?:\/\/.*?)\[\/url\]/is', '<a href="$1" class="text-amber-300 underline" target="_blank" rel="noopener noreferrer">$1</a>', $escaped) ?? $escaped;

        return nl2br($escaped);
    }
}
