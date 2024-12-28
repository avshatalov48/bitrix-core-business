<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Analytics\Event\CopilotEvent;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\Result;

class CopilotAnalytics extends AbstractAnalytics
{
	protected const GENERATE = 'generate';
	protected const RECEIVED_RESULT = 'received_result';
	protected const ADD_USER = 'add_user';
	protected const DELETE_USER = 'delete_user';

	public function addGenerate(Result $result, ?string $promptCode = null): void
	{
		$this->async(function () use ($promptCode, $result) {
			$this
				->createCopilotEvent(self::GENERATE, $promptCode)
				?->send()
			;
			$this
				->createCopilotEvent(self::RECEIVED_RESULT, $promptCode)
				?->setCopilotStatus($result)
				?->send()
			;
		});
	}

	public function addAddUser(): void
	{
		$this
			->createCopilotEvent(self::ADD_USER)
			?->send()
		;
	}

	public function addDeleteUser(): void
	{
		$this
			->createCopilotEvent(self::DELETE_USER)
			?->send()
		;
	}

	protected function createCopilotEvent(
		string $eventName,
		?string $promptCode = null,
	): ?CopilotEvent
	{
		if (!$this->isCopilot())
		{
			return null;
		}

		return (new CopilotEvent($eventName, $this->chat))
			->setCopilotP1($promptCode)
		;
	}

	protected function isCopilot(): bool
	{
		return $this->chat instanceof Chat\CopilotChat;
	}
}
