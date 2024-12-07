<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\EventCategory;

use Bitrix\Calendar\EventCategory\Validator\CommonEventCategoryValidators;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateEventCategoryUpdate extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();
		$name = $request->get('name');
		$nameErrors = CommonEventCategoryValidators::validateName($name, false);
		if ($nameErrors)
		{
			array_map(static fn (array $error) => $this->addError($error), $nameErrors);
		}

		$description = $request->get('description');
		$descriptionErrors = CommonEventCategoryValidators::validateDescription($description);
		if ($descriptionErrors)
		{
			array_map(static fn (array $error) => $this->addError($error), $descriptionErrors);
		}

		$closedRaw = $request->get('closed');
		$closedErrors = CommonEventCategoryValidators::validateClosed($closedRaw);
		if ($closedErrors)
		{
			array_map(static fn (array $error) => $this->addError($error), $closedErrors);
		}
		$closed = $request->get('closed') === 'true';

		$attendees = $request->get('attendees') ?? [];
		$attendeesErrors = CommonEventCategoryValidators::validateAttendees($closed, $attendees);
		if ($closedErrors)
		{
			array_map(static fn (array $error) => $this->addError($error), $attendeesErrors);
		}

		if ($this->getErrors())
		{
			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}
}
