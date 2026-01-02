<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat\Event;

class BeforeCreateEvent extends Event
{
	public function __construct(array $fields)
	{
		$entityType = $fields['ENTITY_TYPE'] ?? '';
		$parameters = [
			'fields' => $fields
		];

		parent::__construct($entityType, $parameters);
	}

	public function getFields(): array
	{
		return $this->parameters['fields'];
	}

	protected function getActionName(): string
	{
		return 'BeforeCreate';
	}
}