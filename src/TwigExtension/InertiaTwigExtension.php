<?php

declare(strict_types=1);

namespace Libra\Shell\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class InertiaTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('inertia', [$this, 'inertia'], ['is_safe' => ['html']]),
            new TwigFunction('inertia_head', [$this, 'head'], ['is_safe' => ['html']]),
        ];
    }

    public function head(string $head = ''): string
    {
        return $head;
    }

    public function inertia($page, string $ssrBody = ''): Markup
    {
        if ($ssrBody !== '') {
            return new Markup($ssrBody, 'UTF-8');
        }

        $payload = json_encode(
            $page,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
        );

        if ($payload === false) {
            $payload = '{}';
        }

        $payload = str_replace('</', '<\/', $payload);

        return new Markup(
            '<script data-page="app" type="application/json">' . $payload . '</script>' .
            '<div id="app"></div>',
            'UTF-8'
        );
    }
}
