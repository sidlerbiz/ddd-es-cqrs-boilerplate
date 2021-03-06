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

namespace IdentityAccess\Ui\Identity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class UserView.
 */
final class UserView
{
    /**
     * @Groups({"list", "details"})
     */
    public string $id;

    /**
     * @Groups({"list", "details"})
     */
    public string $email;

    /**
     * @Groups({"list", "details"})
     */
    public array $roles;

    /**
     * @Groups({"list", "details"})
     */
    public bool $enabled;

    /**
     * @Groups({"details"})
     */
    public ?string $registeredById;

    /**
     * @Groups({"list", "details"})
     */
    public \DateTimeImmutable $dateRegistered;

    public function __construct(
        string $id,
        string $email,
        array $roles,
        bool $enabled,
        ?string $registeredById,
        \DateTimeImmutable $dateRegistered
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->registeredById = $registeredById;
        $this->dateRegistered = $dateRegistered;
    }
}
