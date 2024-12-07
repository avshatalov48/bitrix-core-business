<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class UpdateStatus extends Base
{
	public function onAfterAction(Event $event): void
	{
		$this->updateStatus();
	}

	public function onBeforeAction(Event $event)
	{
		$this->updateStatus(false);
	}

	private function updateStatus(bool $desktopCache = true): void
	{
		$userId = (int)CurrentUser::get()->getId();
		if (!$userId)
		{
			return;
		}

		\CIMContactList::SetOnline($userId);

		if ($this->isMobile() && Loader::includeModule('mobile'))
		{
			\Bitrix\Mobile\User::setOnline($userId);
		}

		if (!$this->isMobile())
		{
			\CIMStatus::Set($userId, Array('IDLE' => null));
		}

		if ($this->isDesktop())
		{
			\CIMMessenger::SetDesktopStatusOnline($userId, $desktopCache);
		}
	}

	private function isDesktop(): bool
	{
		return $this->containInUserAgent('BitrixDesktop');
	}

	private function isMobile(): bool
	{
		return $this->containInUserAgent('BitrixMobile');
	}

	private function containInUserAgent(string $userAgent): bool
	{
		$context = Context::getCurrent();

		if ($context === null)
		{
			return false;
		}

		return false !== stripos($context->getRequest()->getUserAgent(), $userAgent);
	}
}