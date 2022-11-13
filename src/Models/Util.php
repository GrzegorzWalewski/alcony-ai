<?php

namespace Grzojda\AlconyAi\Models;

class Util
{
    public function getUrlFromText($text): string
    {
        if ($this->checkTextContainUrl($text)) {
            $httpPos = strpos($text, 'http');
            $urlEnd = strpos($text,'.mp4', $httpPos);

            return substr($text, $httpPos, $urlEnd - $httpPos + 4);
        }

        return '';
    }

    public function checkTextContainUrl($text): bool
    {
        return str_contains($text, '.mp4') && str_contains($text, 'http');
    }

    public function generateFileName(string $prefix, string $extension): string
    {
        return $prefix . '_' . time() . '.' . $extension;
    }
}