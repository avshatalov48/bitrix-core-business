<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatManageUpdate extends Base
{
	public function onBeforeAction(Event $event)
	{
		$arguments = $this->getAction()->getArguments();
		$arguments['rightsLevel'] = (new Converter(Converter::TO_UPPER))->process($arguments['rightsLevel'] ?? '');
		$this->getAction()->setArguments($arguments);
		$rightsLevel = $arguments['rightsLevel'];
		$actionName = $this->getAction()->getName();
		if ($this->inArrayCaseInsensitive($actionName, ['setManageUsersAdd', 'setManageUsersDelete', 'setManageUI'], true))
		{
			if (in_array(
				$rightsLevel,
				[Chat::MANAGE_RIGHTS_MEMBER, Chat::MANAGE_RIGHTS_MANAGERS, Chat::MANAGE_RIGHTS_OWNER],
				true
			))
			{
				return null;
			}
		}

		if ($actionName === 'setManageSettings' || $actionName === 'setmanagesettings')
		{
			if (in_array(
				$rightsLevel,
				[Chat::MANAGE_RIGHTS_MANAGERS, Chat::MANAGE_RIGHTS_OWNER],
				true
			))
			{
				return null;
			}
		}

		$this->addError(new ChatError(
			ChatError::WRONG_PARAMETER
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}

	/**
	 * @param string $needle
	 * @param string[] $haystack
	 * @param bool $strict
	 * @return bool
	 */
	private function inArrayCaseInsensitive(string $needle, array $haystack, bool $strict = true): bool
	{
		$needle = mb_strtolower($needle);
		$haystack = array_map('mb_strtolower', $haystack);

		return in_array($needle, $haystack, $strict);
	}
}
