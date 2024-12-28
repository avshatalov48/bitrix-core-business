<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Activity;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;

class LastActivity implements Arrayable
{
	public function __construct(
		public readonly int $userId,
		public readonly int $collabId,
		public readonly DateTime $lastActivity,
	)
	{

	}

	public function toArray(): array
	{
		return [
			$this->collabId => [
				$this->userId => $this->lastActivity,
			],
		];
	}
}