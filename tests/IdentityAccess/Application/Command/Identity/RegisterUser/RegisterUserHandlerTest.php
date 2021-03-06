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

namespace IdentityAccess\Application\Command\Identity\RegisterUser;

use Broadway\CommandHandling\CommandHandler;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use Common\Shared\Domain\ValueObject\DateTime;
use IdentityAccess\Application\Command\Identity\UserHandlerTestCase;
use IdentityAccess\Domain\Access\ValueObject\Roles;
use IdentityAccess\Domain\Identity\Event\UserRegistered;
use IdentityAccess\Domain\Identity\Exception\EmailAlreadyExistsException;
use IdentityAccess\Domain\Identity\PasswordEncoderInterface;
use IdentityAccess\Domain\Identity\Specification\UniqueEmailSpecificationInterface;
use IdentityAccess\Domain\Identity\ValueObject\Email;
use IdentityAccess\Domain\Identity\ValueObject\PlainPassword;
use IdentityAccess\Domain\Identity\ValueObject\UserId;
use IdentityAccess\Infrastructure\Identity\Command\RegisterUser\RegisterUserHandlerAdapter;
use IdentityAccess\Infrastructure\Identity\Password\NoopPasswordEncoder;
use IdentityAccess\Infrastructure\Identity\Repository\UserStore;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * Class RegisterUserHandlerTest.
 */
class RegisterUserHandlerTest extends UserHandlerTestCase
{
    private ?PasswordEncoderInterface $passwordEncoder;

    /**
     * @var UniqueEmailSpecificationInterface|InvocationMocker|null
     */
    private ?UniqueEmailSpecificationInterface $uniqueEmailSpecificationStub;

    /**
     * @test
     */
    public function itCanRegisterUser(): void
    {
        $id = $this->generateUserId();
        $email = 'alice@acme.com';
        $plainPassword = 'some password';
        $roles = [];
        $enabled = true;
        $registeredById = $this->generateUserId();
        $dateRegistered = DateTime::now();

        $this->uniqueEmailSpecificationStub->method('isUnique')
            ->willReturn(true);

        ClockMock::withClockMock($dateRegistered->toSeconds());

        $this->scenario
            ->when(new RegisterUserCommand(
                $id,
                $email,
                $plainPassword,
                $enabled,
                $roles,
                $registeredById
            ))
            ->then([
                new UserRegistered(
                    UserId::fromString($id),
                    Email::fromString($email),
                    $this->passwordEncoder->encode(PlainPassword::fromString($plainPassword)),
                    Roles::fromArray($roles),
                    $enabled,
                    UserId::fromString($registeredById),
                    $dateRegistered
                ),
            ])
        ;
    }

    /**
     * @test
     */
    public function itCantRegisterUserWithNonUniqueEmail(): void
    {
        $this->expectException(EmailAlreadyExistsException::class);

        $this->uniqueEmailSpecificationStub->method('isUnique')
            ->willReturn(false);

        $this->scenario
            ->when(new RegisterUserCommand(
                $this->generateUserId(),
                'alice@acme.com',
                'some password',
                true,
                [],
                $this->generateUserId()
            ))
        ;
    }

    protected function generateUserId(): string
    {
        return $this->generateUuid();
    }

    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $this->passwordEncoder = new NoopPasswordEncoder();
        $this->uniqueEmailSpecificationStub = $this->createStub(UniqueEmailSpecificationInterface::class);

        return new RegisterUserHandlerAdapter(
            new RegisterUserHandler(
                new UserStore($eventStore, $eventBus),
                $this->passwordEncoder,
                $this->uniqueEmailSpecificationStub
            )
        );
    }
}
