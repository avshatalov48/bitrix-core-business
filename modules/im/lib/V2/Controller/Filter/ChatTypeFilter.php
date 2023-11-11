<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class ChatTypeFilter extends Base
{
	private array $types;
	private bool $isAllowed;

	public function __construct(array $types, bool $isAllowed = true)
	{
		parent::__construct();
		$this->types = $types;
		$this->isAllowed = $isAllowed;
	}

	public function onBeforeAction(Event $event)
	{
		$chat = $this->getAction()->getArguments()['chat'] ?? null;
		if ($this->isAllowed)
		{
			foreach ($this->types as $type)
			{
				if ($chat instanceof $type)
				{
					return null;
				}
			}

			$this->addError(new ChatError(ChatError::WRONG_TYPE));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		foreach ($this->types as $type)
		{
			if ($chat instanceof $type)
			{
				$this->addError(new ChatError(ChatError::WRONG_TYPE));

				return new EventResult(EventResult::ERROR, null, null, $this);
			}
		}

		return null;
	}
}