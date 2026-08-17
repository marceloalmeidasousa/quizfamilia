<?php

namespace App\Support;

class LiveStickers
{
    public const DEFAULT = '😎';

    /** @var list<string> */
    public const ALL = [
        '😎', '😂', '🤩', '🥳', '🤓', '😇', '🤠', '🫠',
        '👻', '🎃', '🦄', '🐸', '🐼', '🦊', '🐯', '🐲',
        '🍕', '🌮', '⚽', '🎮', '🚀', '⭐', '🔥', '🌈',
        '🎯', '🧠', '📚', '🏆', '💀', '😜', '🫡', '😺',
    ];

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);

        return in_array($value, self::ALL, true) ? $value : self::DEFAULT;
    }
}
