<?php

namespace App\Helper;

use HTMLPurifier;
use HTMLPurifier_Config;

class EditorSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return self::getPurifier()->purify($html);
    }

    private static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        $config->set('HTML.Allowed', implode(',', [
            'p[class|style]',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'strike',
            'ul[class]',
            'ol[class]',
            'li[class]',
            'a[href|target|rel|class|style]',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'blockquote[class|style]',
            'span[class|style]',
            'img[src|alt|width|height|class|style]',
            'iframe[src|class|frameborder|width|height|title]',
        ]));

        $config->set('CSS.AllowedProperties', [
            'color',
            'background-color',
            'background',
            'text-align',
            'direction',
        ]);

        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
        ]);

        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');

        $cachePath = storage_path('app/htmlpurifier');

        if (! is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }

        $config->set('Cache.SerializerPath', $cachePath);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
