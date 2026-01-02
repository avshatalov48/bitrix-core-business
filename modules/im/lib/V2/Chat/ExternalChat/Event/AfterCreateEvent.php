<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\Add\AddResult;
use Bitrix\Im\V2\Chat\ExternalChat\Event;

class AfterCreateEvent extends Event
{
	public function __construct(string $entityType, AddResult $createResult)
	{
		parent::__construct($entityType, ['createResult' => $createResult]);
	}

	public function getCreateResult(): AddResult
	{
		return $this->parameters['createResult'];
	}

	protected function getActionName(): string
	{
		return 'AfterCreate';
	}
}