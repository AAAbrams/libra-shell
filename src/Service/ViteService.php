<?php

declare(strict_types=1);

namespace Libra\Shell\Service;

use RuntimeException;

class ViteService
{
    /**
     * @var string
     */
    private $buildDir;
    /**
     * @var string
     */
    private $manifestPath;
    /**
     * @var string
     */
    private $devServer;
    /**
     * @var string
     */
    private $publicBuildPath;
    /**
     * @var bool
     */
    private $isDev;
    /**
     * @var array|null
     */
    private $manifest = null;

    public function __construct(
        string $buildDir = __DIR__ . '/../../public/build',
        string $publicBuildPath = '/build',
        string $devServer = 'http://localhost:5173',
        bool $isDev = true
    ) {
        $this->buildDir = rtrim($buildDir, '/');
        $this->manifestPath = $this->resolveManifestPath($this->buildDir);
        $this->publicBuildPath = '/' . trim($publicBuildPath, '/');
        $this->devServer = rtrim($devServer, '/');
        $this->isDev = $isDev;
    }

    public function isDev(): bool
    {
        return $this->isDev;
    }

    public function tags(string $entry): string
    {
        return $this->isDev
            ? $this->devTags($entry)
            : $this->prodTags($entry);
    }

    public function reactRefreshTags(): string
    {
        if (!$this->isDev) {
            return '';
        }

        return sprintf(
            '<script type="module">' . PHP_EOL .
            'import RefreshRuntime from "%s/@react-refresh"' . PHP_EOL .
            'RefreshRuntime.injectIntoGlobalHook(window)' . PHP_EOL .
            'window.$RefreshReg$ = () => {}' . PHP_EOL .
            'window.$RefreshSig$ = () => (type) => type' . PHP_EOL .
            'window.__vite_plugin_react_preamble_installed__ = true' . PHP_EOL .
            '</script>',
            $this->devServer
        );
    }

    private function devTags(string $entry): string
    {
        return sprintf(
            '<script type="module" src="%s/@vite/client"></script>' . PHP_EOL .
            '<script type="module" src="%s/%s"></script>',
            $this->devServer,
            $this->devServer,
            ltrim($entry, '/')
        );
    }

    private function prodTags(string $entry): string
    {
        $manifest = $this->getManifest();

        if (!isset($manifest[$entry])) {
            throw new RuntimeException("Entry '{$entry}' not found in manifest.");
        }

        $chunk = $manifest[$entry];

        $html = [];

        // CSS
        if (isset($chunk['css'])) {
            foreach ($chunk['css'] as $css) {
                $html[] = sprintf(
                    '<link rel="stylesheet" href="%s/%s">',
                    $this->publicBuildPath,
                    $css
                );
            }
        }

        // JS
        $html[] = sprintf(
            '<script type="module" src="%s/%s"></script>',
            $this->publicBuildPath,
            $chunk['file']
        );

        return implode(PHP_EOL, $html);
    }

    private function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if (!file_exists($this->manifestPath)) {
            throw new RuntimeException('Vite manifest not found.');
        }

        $this->manifest = json_decode(
            file_get_contents($this->manifestPath),
            true
        );

        return $this->manifest;
    }

    private function resolveManifestPath(string $buildDir): string
    {
        $manifestCandidates = [
            $buildDir . '/manifest.json',
            $buildDir . '/.vite/manifest.json',
        ];

        foreach ($manifestCandidates as $manifestPath) {
            if (file_exists($manifestPath)) {
                return $manifestPath;
            }
        }

        return $manifestCandidates[0];
    }

    public function asset(string $entry): string
    {
        if ($this->isDev) {
            return "{$this->devServer}/" . ltrim($entry, '/');
        }

        $manifest = $this->getManifest();

        if (!isset($manifest[$entry])) {
            throw new RuntimeException("Asset '{$entry}' not found.");
        }

        return $this->publicBuildPath . '/' . $manifest[$entry]['file'];
    }
}
