<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Filter\EventCategory;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class ValidateSetMuteEventCategory extends ActionFilter\Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$request = $this->getAction()->getController()->getRequest();
		$eventId = $request->get('id');
		if (!$eventId)
		{
			$this->addError(new Error(
				message: 'id is required',
				code: 'id_required',
				customData: ['field_name' => 'id'],
			));
		}
		$muteState = $request->get('muteState');
		if (!$muteState)
		{
			$this->addError(new Error(
				message: 'muteState is required',
				code: 'mute_state_required',
				customData: ['field_name' => 'muteState'],
			));
		}
		if (!in_array($muteState, ['true', 'false'], true))
		{
			$this->addError(new Error(
				message: 'muteState invalid',
				code: 'mute_state_invalid',
				customData: ['field_name' => 'muteState'],
			));
		}

		return null;
	}
}
