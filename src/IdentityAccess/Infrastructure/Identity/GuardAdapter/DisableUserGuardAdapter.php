<?php

/*
 * This file is part of invis1ble/ddd-es-cqrs-boilerplate.
 *
 * (c) Invis1ble <opensource.invis1ble@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace IdentityAccess\Infrastructure\Identity\GuardAdapter;

use IdentityAccess\Application\Query\Identity\UserInterface;
use IdentityAccess\Infrastructure\Access\Security\AbstractGuardAdapter;
use IdentityAccess\Ui\Access\AccessAttribute;
use IdentityAccess\Ui\Identity\DisableUser\DisableUserGuard;

/**
 * Class DisableUserGuardAdapter
 *
 * @package IdentityAccess\Infrastructure\Identity\GuardAdapter
 */
class DisableUserGuardAdapter extends AbstractGuardAdapter
{
    public function __construct(DisableUserGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof UserInterface
            && $attribute instanceof AccessAttribute
            && 'disable' === $attribute->attribute
            && null === $attribute->field
        ;
    }

}
