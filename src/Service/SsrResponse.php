<?php

declare(strict_types=1);

namespace Libra\Shell\Service;

class SsrResponse
{
    /**
     * @var string
     */
    private $head;
    /**
     * @var string
     */
    private $body;

    public function __construct(string $head, string $body)
    {
        $this->head = $head;
        $this->body = $body;
    }

    public function getHead(): string
    {
        return $this->head;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
