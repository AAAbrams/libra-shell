<?php

declare(strict_types=1);

namespace Libra\Shell\Service;

use Cherif\InertiaPsr15\Model\Page;

class SsrGateway
{
    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var float
     */
    private $timeout;

    public function __construct(string $endpoint, bool $enabled = false, float $timeout = 2.0)
    {
        $this->endpoint = $endpoint;
        $this->enabled = $enabled;
        $this->timeout = $timeout;
    }

    public function dispatch(Page $page): ?SsrResponse
    {
        if (!$this->enabled) {
            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]),
                'content' => json_encode($page),
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($this->endpoint, false, $context);

        if ($response === false) {
            return null;
        }

        $payload = json_decode($response, true);

        if (!is_array($payload) || !isset($payload['body'])) {
            return null;
        }

        $head = $payload['head'] ?? [];

        if (is_array($head)) {
            $head = implode("\n", $head);
        }

        return new SsrResponse((string) $head, (string) $payload['body']);
    }
}
