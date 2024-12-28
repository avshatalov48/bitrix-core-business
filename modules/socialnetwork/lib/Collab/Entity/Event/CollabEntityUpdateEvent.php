<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;

class CollabEntityUpdateEvent extends Event
{
	public function __construct(?CollabEntity $entity, array $changes)
	{
		$parameters = [
			'entity' => $entity,
			'changes' => $changes,
		];

		parent::__construct('socialnetwork', 'OnCollabEntityUpdate', $parameters);
	}

	public function getEntity(): ?CollabEntity
	{
		return $this->parameters['entity'];
	}

	public function getChanges(): array
	{
		return $this->parameters['changes'];
	}
}