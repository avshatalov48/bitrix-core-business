<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Application;

abstract class AbstractAnalytics
{
	protected Chat $chat;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
	}

	protected function async(callable $job): void
	{
		Application::getInstance()->addBackgroundJob($job);
	}

	protected function isChatTypeAllowed(Chat $chat): bool
	{
		if ($chat instanceof Chat\OpenLineLiveChat || $chat instanceof Chat\OpenLineChat)
		{
			return false;
		}

		return true;
	}
}
