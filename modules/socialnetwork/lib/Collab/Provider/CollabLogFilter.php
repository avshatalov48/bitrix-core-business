<?php

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;

class CollabLogFilter
{
	public function __construct(
		public readonly ?int $collabId = null,
		public readonly ?CollabEntity $entity = null,
		public readonly ?DateTime $from = null,
		public readonly ?DateTime $to = null,
		public readonly ?int $userId = null,
		public readonly int $limit = 20,
		public readonly int $offset = 0,
	)
	{
	}
}
