<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class DiskQuickAccessGrantor extends Base
{
	public function onBeforeAction(Event $event)
	{
		if (!Loader::includeModule('disk'))
		{
			return null;
		}

		if (!ServiceLocator::getInstance()->has('disk.scopeTokenService'))
		{
			return null;
		}

		$scopeTokenService = ServiceLocator::getInstance()->get('disk.scopeTokenService');

		$chat = $this->getAction()->getArguments()['chat'] ?? null;
		if (!($chat instanceof Chat) || !$chat->getId())
		{
			return null;
		}

		$scopeTokenService->grantAccessToScope(FileItem::getQuickAccessScope($chat->getId()));
	}
}