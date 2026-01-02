<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Application\Context;
use Bitrix\Im\V2\Controller\Chat;
use Bitrix\Im\V2\Controller\UpdateState;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class UpdateStatus extends Base
{
	/**
	 * The names of the methods are listed in lowercase
	 * because REST, unlike AJAX actions, converts method names to lowercase.
	 */
	private const METHODS_REQUIRING_PREFILTER = [
		Chat::class => [
			'load' => true, /** @see Chat::loadAction() */
			'loadincontext' => true, /** @see Chat::loadInContextAction() */
			'read' => true, /** @see Chat::readAction() */
			'readall' => true, /** @see Chat::readAllAction() */
		],
		UpdateState::class => [
			'getstatedata' => true, /** @see UpdateState::getStateDataAction() */
		],
		Chat\Message::class => [
			'read' => true, /** @see Chat\Message::readAction() */
			'list' => true, /** @see Chat\Message::listAction() */
			'getcontext' => true, /** @see Chat\Message::getContextAction() */
			'tail' => true, /** @see Chat\Message::tailAction() */
		],
	];

	public function onBeforeAction(Event $event)
	{
		$this->updateStatus();
	}

	private function updateStatus(): void
	{
		if (!$this->shouldUpdateByAction())
		{
			return;
		}

		$userId = (int)CurrentUser::get()->getId();
		if (!$userId)
		{
			return;
		}

		\CIMContactList::SetOnline($userId);
		$context = Context::getCurrent();

		if ($context->isMobile() && Loader::includeModule('mobile'))
		{
			\Bitrix\Mobile\User::setOnline($userId);
		}

		if (!$context->isMobile())
		{
			\CIMStatus::Set($userId, Array('IDLE' => null));
		}

		if ($context->isDesktop())
		{
			\CIMMessenger::SetDesktopStatusOnline($userId);
		}
	}

	private function shouldUpdateByAction(): bool
	{
		$className = $this->getAction()->getController()::class;
		$methodName = mb_strtolower($this->getAction()->getName());

		return isset(self::METHODS_REQUIRING_PREFILTER[$className][$methodName]);
	}
}