<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Permission;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Permission\GlobalAction;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckActionAccess extends Base
{
	private Action|GlobalAction $actionToDo;
	/**
	 * @var null|\Closure(Base): mixed
	 */
	private ?\Closure $targetGetter;

	/**
	 * @param Action|GlobalAction $action
	 * @param null|\Closure(Base): mixed $targetGetter
	 */
	public function __construct(Action|GlobalAction $action, ?\Closure $targetGetter = null)
	{
		parent::__construct();
		$this->actionToDo = $action;
		$this->targetGetter = $targetGetter;
	}

	public function onBeforeAction(Event $event)
	{
		$targetGetter = $this->targetGetter;
		$target = $targetGetter ? $targetGetter($this) : null;

		if ($this->actionToDo instanceof GlobalAction)
		{
			return $this->canDoGlobalAction($this->actionToDo, $target);
		}

		return $this->canDoAction($this->actionToDo, $target);
	}

	private function canDoAction(Action $action, mixed $target): ?EventResult
	{
		$chat = $this->getChat();
		if (!$chat instanceof Chat)
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::NOT_FOUND));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		if (!$chat->canDo($action, $target))
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function canDoGlobalAction(GlobalAction $action, mixed $target): ?EventResult
	{
		$userId = (int)$this->getAction()->getCurrentUser()?->getId();
		if (!Permission::canDoGlobalAction($userId, $action, $target))
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function getChat(): ?Chat
	{
		$arguments = $this->getAction()->getArguments();

		return $arguments['chat'] ?? $arguments['message']?->getChat() ?? $arguments['messages']?->getCommonChat() ?? null;
	}
}