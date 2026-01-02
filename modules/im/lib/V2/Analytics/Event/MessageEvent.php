<?php

namespace Bitrix\Im\V2\Analytics\Event;

use Bitrix\Im\V2\Integration\AiAssistant\AiAssistantService;
use Bitrix\Main\DI\ServiceLocator;

class MessageEvent extends ChatEvent
{
	public function setMessageP4(int $authorId): self
	{
		$aiAssistantService = ServiceLocator::getInstance()->get(AiAssistantService::class);
		if ($aiAssistantService->getBotId() > 0 && $aiAssistantService->getBotId() === $authorId)
		{
			$this->p4 = 'msgSender_aiAssistant';
		}

		return $this;
	}
}
