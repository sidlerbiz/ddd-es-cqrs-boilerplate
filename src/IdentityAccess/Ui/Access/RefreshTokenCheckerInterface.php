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

namespace IdentityAccess\Ui\Access;

use IdentityAccess\Application\Query\Access\RefreshTokenInterface;

/**
 * Interface RefreshTokenCheckerInterface
 *
 */
interface RefreshTokenCheckerInterface
{
    /**
     * @param RefreshTokenInterface|null $refreshToken
     *
     * @throws AuthenticationExceptionInterface
     */
    public function __invoke(?RefreshTokenInterface $refreshToken): void;
}
