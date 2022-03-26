<?php

namespace Bitrix\Im\Controller\Call\Filter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\EventResult;

/**
 * Prefilter checks if the upload folder ID belongs to the chat and the user has access to this chat.
 *
 * Class DiskFolderAccessCheck
 * @package Bitrix\Im\Controller\Call\Filter
 */
class DiskFolderAccessCheck extends Base
{
	public function onBeforeAction(Event $event)
	{
		$chatId = $this->action->getController()->getRequest()->getHeader('Call-Chat-Id');
		if (!$chatId)
		{
			$this->addError(new Error("Header Call-Chat-Id can't be empty"));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		if (!\Bitrix\Im\Chat::hasAccess($chatId))
		{
			$this->addError(new Error("You don't have access to this chat"));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}