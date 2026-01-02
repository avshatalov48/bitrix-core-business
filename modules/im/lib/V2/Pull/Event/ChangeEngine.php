<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;

class ChangeEngine extends BaseChatEvent
{
	use ContextCustomer;

	protected string $engineCode;
	protected string $engineName;

	public function __construct(Chat $chat, string $engineCode, string $engineName)
	{
		$this->engineCode = $engineCode;
		$this->engineName = $engineName;
		parent::__construct($chat);
	}
	protected function getBasePullParamsInternal(): array
	{
		return [
			'chatId' => $this->chat->getId(),
			'engineCode' => $this->engineCode,
			'engineName' => $this->engineName,
		];
	}

	protected function getType(): EventType
	{
		return EventType::ChangeEngine;
	}

	protected function getSkippedUserIds(): array
	{
		return $this->chat->getBotInChat();
	}
}