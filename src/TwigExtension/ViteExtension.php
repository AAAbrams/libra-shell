<?php

declare(strict_types=1);

namespace Libra\Shell\TwigExtension;

use Libra\Shell\Service\ViteService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteExtension extends AbstractExtension
{
    /**
     * @var ViteService
     */
    private $vite;

    public function __construct(ViteService $vite)
    {
        $this->vite = $vite;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite', [$this, 'vite'], ['is_safe' => ['html']]),
            new TwigFunction('vite_react_refresh', [$this, 'reactRefresh'], ['is_safe' => ['html']]),
            new TwigFunction('vite_asset', [$this, 'asset']),
        ];
    }

    public function vite(string $entry): string
    {
        return $this->vite->tags($entry);
    }

    public function reactRefresh(): string
    {
        return $this->vite->reactRefreshTags();
    }

    public function asset(string $entry): string
    {
        return $this->vite->asset($entry);
    }
}
