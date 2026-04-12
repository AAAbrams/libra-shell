<?php

declare(strict_types=1);

namespace Libra\Shell\Security\User;

use Bitrix\Main\Engine\CurrentUser;

final class UserContext
{
    /**
     * @var CurrentUser|AuthUserInterface
     */
    private $currentUser;
    public function __construct()
    {
        $this->currentUser = CurrentUser::get();
    }

    public static function guest(): self
    {
        $context = new self();
        $context->currentUser = GuestUser::make();
        return $context;
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser->getId() !== null;
    }

    public function getId(): int
    {
        return (int)$this->currentUser->getId();
    }

    public function toJwtClaims(int $authVersion): array
    {
        return [
            'sub' => (string)$this->currentUser->getId(),
            'av' => (string)$authVersion,
        ];
    }
}
