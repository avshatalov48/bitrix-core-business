<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\EventCategory;

use Bitrix\Calendar\EventCategory\Validator\CommonEventCategoryValidators;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateEventCategoryCreate extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();
		$name = $request->get('name');
		$nameErrors = CommonEventCategoryValidators::validateName($name);
		if ($nameErrors)
		{
			$this->addErrors($nameErrors);
		}

		$description = $request->get('description');
		$descriptionErrors = CommonEventCategoryValidators::validateDescription($description);
		if ($descriptionErrors)
		{
			$this->addErrors($descriptionErrors);
		}

		$closedRaw = $request->get('closed');
		$closedErrors = CommonEventCategoryValidators::validateClosed($closedRaw);
		if ($closedErrors)
		{
			$this->addErrors($closedErrors);
		}
		$closed = $request->get('closed') === 'true';

		$attendees = $request->get('attendees') ?? [];
		$attendeesErrors = CommonEventCategoryValidators::validateAttendees($closed, $attendees);
		if ($attendeesErrors)
		{
			$this->addErrors($attendeesErrors);
		}

		$departmentIds = $request->get('departmentIds') ?? [];
		$departmentIdsErrors = CommonEventCategoryValidators::validateDepartmentIds($closed, $departmentIds);
		if (!empty($departmentIdsErrors))
		{
			$this->addErrors($departmentIdsErrors);
		}

		$channel = $request->get('channel');
		if ($channel && !(int)$channel)
		{
			$this->addError(new Error('channel invalid', 'channel_invalid'));
		}

		if ($this->getErrors())
		{
			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}

	/**
	 * @param Error[] $errors
	 */
	protected function addErrors(array $errors): static
	{
		array_map(fn (Error $error) => $this->addError($error), $errors);

		return $this;
	}
}
