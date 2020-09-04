<?php

declare(strict_types=1);

namespace IdentityAccess\Domain\Identity\Event;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Common\Shared\Domain\Exception\DateTimeException;
use Common\Shared\Domain\ValueObject\DateTime;
use IdentityAccess\Domain\Identity\ValueObject\UserId;

/**
 * Class UserDisabled
 *
 * @package IdentityAccess\Domain\Identity\Event
 */
final class UserDisabled extends UserIdAwareEvent
{
    private ?UserId $disabledBy;

    private DateTime $dateDisabled;

    public function __construct(
        UserId $id,
        ?UserId $disabledBy,
        DateTime $dateDisabled
    )
    {
        parent::__construct($id);

        $this->disabledBy = $disabledBy;
        $this->dateDisabled = $dateDisabled;
    }

    public function disabledBy(): ?UserId
    {
        return $this->disabledBy;
    }

    public function dateDisabled(): DateTime
    {
        return $this->dateDisabled;
    }

    /**
     * @param array $data
     *
     * @return self
     * @throws AssertionFailedException
     * @throws DateTimeException
     */
    public static function deserialize(array $data): self
    {
        Assertion::keyExists($data, 'id');
        Assertion::nullOrKeyExists($data, 'disabledBy');
        Assertion::keyExists($data, 'dateDisabled');

        return new self(
            UserId::fromString($data['id']),
            isset($data['disabledBy']) ? UserId::fromString($data['disabledBy']) : null,
            DateTime::fromString($data['dateDisabled']),
        );
    }

    public function serialize(): array
    {
        return parent::serialize() + [
            'dateDisabled' => $this->dateDisabled->toString(),
            'disabledBy' => null === $this->disabledBy ? null : $this->disabledBy->toString(),
        ];
    }

}
