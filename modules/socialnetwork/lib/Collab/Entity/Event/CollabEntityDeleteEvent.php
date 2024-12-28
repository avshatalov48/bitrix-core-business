<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;

class CollabEntityDeleteEvent extends Event
{
	public function __construct(CollabEntity $entity)
	{
		$parameters = [
			'entity' => $entity
		];

		parent::__construct('socialnetwork', 'OnCollabEntityDelete', $parameters);
	}

	public function getEntity(): CollabEntity
	{
		return $this->parameters['entity'];
	}
}