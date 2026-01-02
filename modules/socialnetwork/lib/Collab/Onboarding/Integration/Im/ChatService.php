<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class ChatService
{
	use InstanceTrait;

	private function __construct()
	{
	}

	public function isExistUserMessageByPeriod(int $chatId, DateTime $from, DateTime $to): bool
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		$messages = MessageCollection::find(
			[
				'CHAT_ID' => $chatId,
				'DATE_FROM' => $from,
				'DATE_TO' => $to,
				'WITHOUT_SYSTEM_MESSAGE' => true,
			],
			['ID' => 'ASC'],
			1,
			null,
			['ID'],
		);

		return !$messages->isEmpty();
	}
}
