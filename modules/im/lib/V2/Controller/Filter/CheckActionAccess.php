<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckActionAccess extends Base
{
	private string $actionName;
	/**
	 * @var null|\Closure(Base): mixed
	 */
	private ?\Closure $targetGetter;

	/**
	 * @param string $actionName
	 * @param null|\Closure(Base): mixed $targetGetter
	 */
	public function __construct(string $actionName, ?\Closure $targetGetter = null)
	{
		parent::__construct();
		$this->actionName = $actionName;
		$this->targetGetter = $targetGetter;
	}

	public function onBeforeAction(Event $event)
	{
		$targetGetter = $this->targetGetter;
		$target = $targetGetter ? $targetGetter($this) : null;

		$chat = $this->getChat();
		if (!$chat instanceof Chat)
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::NOT_FOUND));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		if (!$chat->canDo($this->actionName, $target))
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