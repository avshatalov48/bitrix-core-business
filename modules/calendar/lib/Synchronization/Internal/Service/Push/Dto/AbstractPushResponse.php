<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push\Dto;

use Bitrix\Main\Type\DateTime;

abstract class AbstractPushResponse
{
	public function __construct(public readonly string $id, public readonly string $resourceId)
	{
	}

	abstract public function getExpirationDateTime(): ?DateTime;
}
