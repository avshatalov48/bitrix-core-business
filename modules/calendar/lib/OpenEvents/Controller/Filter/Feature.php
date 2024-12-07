<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class Feature extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		if (\Bitrix\Calendar\OpenEvents\Feature::getInstance()->isAvailable())
		{
			return null;
		}

		$this->addError(
			new Error(
				message: 'access denied',
			)
		);

		return new EventResult(type: EventResult::ERROR, handler: $this);
	}
}
