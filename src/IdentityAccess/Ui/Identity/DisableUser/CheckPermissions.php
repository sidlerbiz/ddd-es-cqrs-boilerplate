<?php

declare(strict_types=1);

namespace IdentityAccess\Ui\Identity\DisableUser;

use IdentityAccess\Application\Command\Identity\DisableUser\DisableUserCommand;
use IdentityAccess\Application\Query\Identity\UserInterface;
use IdentityAccess\Ui\Access\AccessCheckerInterface;
use IdentityAccess\Ui\Access\AccessDeniedException;
use IdentityAccess\Ui\Identity\ChangeUserStatus\ChangeUserStatusRequest;

/**
 * Class CheckPermissions
 *
 * @package IdentityAccess\Ui\Identity\DisableUser
 */
class CheckPermissions extends DisableUserRequestTransformerDecorator
{
    private AccessCheckerInterface $accessChecker;

    public function __construct(
        DisableUserRequestTransformerInterface $transformer,
        AccessCheckerInterface $accessChecker
    )
    {
        parent::__construct($transformer);

        $this->accessChecker = $accessChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ChangeUserStatusRequest $request, UserInterface $user): DisableUserCommand
    {
        if (!$this->accessChecker->isGranted('disable', $user)) {
            throw new AccessDeniedException('User disabling denied.');
        }

        return ($this->transformer)($request, $user);
    }

}
