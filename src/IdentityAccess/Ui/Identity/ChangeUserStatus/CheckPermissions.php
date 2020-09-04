<?php

declare(strict_types=1);

namespace IdentityAccess\Ui\Identity\ChangeUserStatus;

use IdentityAccess\Application\Command\Identity\ChangeUserStatusCommandInterface;
use IdentityAccess\Application\Query\Identity\UserInterface;
use IdentityAccess\Ui\Access\AccessCheckerInterface;
use IdentityAccess\Ui\Access\AccessDeniedException;

/**
 * Class CheckPermissions
 *
 * @package IdentityAccess\Ui\Identity\ChangeUserStatus
 */
class CheckPermissions extends ChangeUserStatusRequestTransformerDecorator
{
    private AccessCheckerInterface $accessChecker;

    public function __construct(
        ChangeUserStatusRequestTransformerInterface $transformer,
        AccessCheckerInterface $accessChecker
    )
    {
        parent::__construct($transformer);

        $this->accessChecker = $accessChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ChangeUserStatusRequest $request, UserInterface $user): ChangeUserStatusCommandInterface
    {
        if (!$this->accessChecker->isGranted('change', $user, 'status')) {
            throw new AccessDeniedException('User status change denied.');
        }

        return ($this->transformer)($request, $user);
    }

}
