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

namespace Common\Shared\Infrastructure\Event\Query;

use Broadway\Domain\DomainMessage;
use Common\Shared\Domain\Repository\EventRepositoryInterface;
use Common\Shared\Infrastructure\Query\Repository\ElasticRepository;

/**
 * Class EventElasticRepository.
 */
class EventElasticRepository extends ElasticRepository implements EventRepositoryInterface
{
    public function store(DomainMessage $message): void
    {
        $document = [
            'type' => $message->getType(),
            'payload' => $message->getPayload()->serialize(),
            'occurred_on' => $message->getRecordedOn()->toString(),
        ];

        $this->add($document);
    }

    protected function index(): string
    {
        return 'events';
    }
}
