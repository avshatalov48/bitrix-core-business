<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Main\Application;

abstract class AbstractAnalytics
{
	use ContextCustomer;

	protected Chat $chat;
	protected int $userId;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
		$this->userId = $this->getContext()->getUserId();
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
