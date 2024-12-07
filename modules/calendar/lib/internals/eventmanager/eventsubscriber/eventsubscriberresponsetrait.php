<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber;

use Bitrix\Calendar\Core\Common;
use Bitrix\Main\EventResult;
use phpDocumentor\Reflection\Types\This;

trait EventSubscriberResponseTrait
{
	private function makeSuccessResponse($parameters = []): EventResult
	{
		return $this->makeCommonResponse(EventResult::SUCCESS, $parameters);
	}

	private function makeUndefinedResponse($parameters = []): EventResult
	{
		return $this->makeCommonResponse(EventResult::UNDEFINED, $parameters);
	}

	private function makeCommonResponse(string $type, $parameters = []): EventResult
	{
		return new EventResult(
			type: $type,
			parameters: $parameters,
			moduleId: Common::CALENDAR_MODULE_ID,
			handler: static::class,
		);
	}
}
