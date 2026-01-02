<?php

namespace Bitrix\Rest\Engine\ActionFilter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @package Bitrix\Rest\Engine\ActionFilter
 */
class UserCanDoOperation extends Base
{
	public function __construct(protected readonly array $operations)
	{
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		global $USER;

		foreach ($this->operations as $operation)
		{
			if (!$USER->CanDoOperation($operation))
			{
				$this->addError(new Error(
					'The current user cannot perform operation: ' . $operation,
					\CRestApiServer::STATUS_FORBIDDEN
				));

				return new EventResult(EventResult::ERROR, null, null, $this);
			}
		}

		return new EventResult(EventResult::SUCCESS, null, null, $this);
	}
}