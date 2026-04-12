<?php

declare(strict_types=1);

namespace Libra\Shell\Security\User;

class GuestUser implements AuthUserInterface
{
    public static function make(): self
    {
        return new self();
    }

    public function getId()
    {
        return 0;
    }
}