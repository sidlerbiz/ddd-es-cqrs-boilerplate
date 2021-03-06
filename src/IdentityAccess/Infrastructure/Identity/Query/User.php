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

namespace IdentityAccess\Infrastructure\Identity\Query;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Assert\AssertionFailedException;
use Broadway\ReadModel\SerializableReadModel;
use Common\Shared\Domain\Exception\DateTimeException;
use Common\Shared\Domain\ValueObject\DateTime;
use IdentityAccess\Application\Query\Identity\EnableableUserInterface;
use IdentityAccess\Application\Query\Identity\UserInterface;
use IdentityAccess\Domain\Access\ValueObject\Roles;
use IdentityAccess\Domain\Identity\ValueObject\Email;
use IdentityAccess\Domain\Identity\ValueObject\HashedPassword;
use IdentityAccess\Domain\Identity\ValueObject\UserId;
use IdentityAccess\Ui\Access\ChangeRoles\ChangeRolesRequest;
use IdentityAccess\Ui\Identity\ChangeEmail\ChangeEmailRequest;
use IdentityAccess\Ui\Identity\ChangePassword\ChangePasswordRequest;
use IdentityAccess\Ui\Identity\ChangeUserStatus\ChangeUserStatusRequest;
use IdentityAccess\Ui\Identity\RegisterUser\RegisterUserRequest;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User.
 *
 * @see http://schema.org/Person Documentation on Schema.org
 *
 * @ApiResource(
 *     iri="http://schema.org/Person",
 *     order={},
 *     mercure=true,
 *     messenger="input",
 *     collectionOperations={
 *         "get"={
 *             "normalization_context"={
 *                 "groups"={"user:list"},
 *                 "swagger_definition_name"="list",
 *             },
 *             "openapi_context"={
 *                 "summary"="Retrieves Users.",
 *                 "description"="Retrieves the collection of Users.",
 *             },
 *         },
 *         "register"={
 *             "method"="POST",
 *             "input"=RegisterUserRequest::class,
 *             "normalization_context"={
 *                 "groups"={"user:id"},
 *                 "swagger_definition_name"="id",
 *             },
 *             "openapi_context"={
 *                 "summary"="Registers User.",
 *                 "description"="Registers new User.",
 *             },
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={
 *                 "groups"={"user:details"},
 *                 "swagger_definition_name"="details",
 *             },
 *             "openapi_context"={
 *                 "summary"="Retrieves User.",
 *                 "description"="Retrieves a User.",
 *             },
 *         },
 *         "changeStatus"={
 *             "method"="PUT",
 *             "path"="/users/{id}/status",
 *             "input"=ChangeUserStatusRequest::class,
 *             "output"=false,
 *             "openapi_context"={
 *                 "summary"="Updates User status.",
 *                 "description"="Enables or disables User.",
 *             },
 *         },
 *         "changeEmail"={
 *             "method"="PUT",
 *             "path"="/users/{id}/email",
 *             "input"=ChangeEmailRequest::class,
 *             "output"=false,
 *             "openapi_context"={
 *                 "summary"="Updates User email.",
 *                 "description"="Updates User email address.",
 *             },
 *         },
 *         "changePassword"={
 *             "method"="PUT",
 *             "path"="/users/{id}/password",
 *             "input"=ChangePasswordRequest::class,
 *             "output"=false,
 *             "openapi_context"={
 *                 "summary"="Updates User password.",
 *                 "description"="Updates User password.",
 *             },
 *         },
 *         "changeRoles"={
 *             "method"="PUT",
 *             "path"="/users/{id}/roles",
 *             "input"=ChangeRolesRequest::class,
 *             "output"=false,
 *             "openapi_context"={
 *                 "summary"="Updates User roles.",
 *                 "description"="Updates User roles.",
 *             },
 *         },
 *     },
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={"email", "enabled", "dateRegistered"},
 *     arguments={"orderParameterName"="_order"},
 * )
 * @ApiFilter(
 *     PropertyFilter::class,
 *     arguments={
 *         "parameterName"="_properties",
 *         "overrideDefaultProperties"=false,
 *         "whitelist"={
 *             "id",
 *             "email",
 *             "roles",
 *             "enabled",
 *             "registeredById",
 *             "dateRegistered",
 *         },
 *     },
 * )
 */
class User implements UserInterface, EnableableUserInterface, SecurityUserInterface, SerializableReadModel
{
    /**
     * User ID.
     */
    private UuidInterface $id;

    /**
     * User email.
     *
     * @ApiFilter(SearchFilter::class, strategy="partial")
     */
    private ?string $email;

    private ?string $hashedPassword;

    /**
     * User roles.
     *
     * @var string[]|null
     */
    private ?array $roles;

    /**
     * Account status.
     *
     * @ApiFilter(BooleanFilter::class)
     */
    private ?bool $enabled;

    /**
     * User which registered this user.
     */
    private ?UuidInterface $registeredById;

    /**
     * Date when user was registered.
     *
     * @ApiFilter(DateFilter::class)
     */
    private ?DateTime $dateRegistered;

    public function __construct(
        UserId $id,
        ?Email $email = null,
        ?HashedPassword $hashedPassword = null,
        ?Roles $roles = null,
        ?bool $enabled = null,
        ?UserId $registeredById = null,
        ?DateTime $dateRegistered = null
    ) {
        $this->id = Uuid::fromString($id->toString());
        $this->email = null === $email ? null : $email->toString();
        $this->hashedPassword = null === $hashedPassword ? null : $hashedPassword->toString();
        $this->roles = null === $roles ? null : $roles->toArray();
        $this->enabled = $enabled;
        $this->registeredById = null === $registeredById ? null : Uuid::fromString($registeredById->toString());
        $this->dateRegistered = $dateRegistered;
    }

    /**
     * @Groups({"user:id", "user:details", "user:list"})
     */
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function setEmail(Email $email)
    {
        $this->email = $email->toString();

        return $this;
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @Groups({"user:details", "user:list"})
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setHashedPassword(HashedPassword $hashedPassword)
    {
        $this->hashedPassword = $hashedPassword->toString();

        return $this;
    }

    /**
     * @ApiProperty(readable=false, writable=false)
     */
    public function getHashedPassword(): ?string
    {
        return $this->hashedPassword;
    }

    public function setRoles(Roles $roles)
    {
        $this->roles = $roles->toArray();

        return $this;
    }

    /**
     * @ApiProperty()
     * @Groups({"user:details", "user:list"})
     * @return string[]
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @ApiProperty()
     * @Groups({"user:details", "user:list"})
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @ApiProperty()
     * @Groups({"user:details"})
     */
    public function getRegisteredById(): ?string
    {
        return null === $this->registeredById ? null : $this->registeredById->toString();
    }

    /**
     * @ApiProperty()
     * @Groups({"user:details", "user:list"})
     */
    public function getDateRegistered(): ?\DateTimeImmutable
    {
        if (null === $this->dateRegistered) {
            return null;
        }

        $dateRegistered = $this->dateRegistered->toNative();
        /* @var $dateRegistered \DateTimeImmutable */

        return $dateRegistered;
    }

    public function getPassword(): ?string
    {
        return $this->hashedPassword;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // no op
    }

    /**
     * @throws AssertionFailedException
     * @throws DateTimeException
     */
    public static function deserialize(array $data)
    {
        return new self(
            UserId::fromString($data['id']),
            isset($data['email']) ? Email::fromString($data['email']) : null,
            isset($data['hashedPassword']) ? HashedPassword::fromString($data['hashedPassword']) : null,
            isset($data['roles']) ? Roles::fromArray($data['roles']) : null,
            $data['enabled'] ?? null,
            isset($data['registeredById']) ? UserId::fromString($data['registeredById']) : null,
            isset($data['dateRegistered']) ? DateTime::fromString($data['dateRegistered']) : null
        );
    }

    public function serialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'email' => $this->email,
            'hashedPassword' => $this->hashedPassword,
            'roles' => $this->roles,
            'enabled' => $this->enabled,
            'registeredById' => null === $this->registeredById ? null : $this->registeredById->toString(),
            'dateRegistered' => null === $this->dateRegistered ? null : $this->dateRegistered->toString(),
        ];
    }
}
