<?php

declare(strict_types=1);

namespace Libra\Shell\Service;

use Cherif\InertiaPsr15\Model\Page;
use Cherif\InertiaPsr15\Service\RootViewProviderInterface;

class SsrRootViewProvider implements RootViewProviderInterface
{
    /**
     * @var callable
     */
    private $renderer;
    /**
     * @var string
     */
    private $rootView;
    /**
     * @var SsrGateway
     */
    private $ssrGateway;

    public function __construct(callable $renderer, string $rootView, SsrGateway $ssrGateway)
    {
        $this->renderer = $renderer;
        $this->rootView = $rootView;
        $this->ssrGateway = $ssrGateway;
    }

    public function __invoke(Page $page): string
    {
        return $this->render($page);
    }

    public function render(Page $page): string
    {
        $renderer = $this->renderer;
        $ssrResponse = $this->ssrGateway->dispatch($page);

        return $renderer($this->rootView, [
            'page' => $page,
            'ssrHead' => $ssrResponse ? $ssrResponse->getHead() : '',
            'ssrBody' => $ssrResponse ? $ssrResponse->getBody() : '',
        ]);
    }
}
