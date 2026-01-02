<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\InputAction;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class StatusMessageProvider
{
	private const EVENT_ON_GET_MESSAGES = 'OnGetInputActionStatusMessages';
	private const MODULE_ID = 'im';

	public static function get(Platform $platform = Platform::WEB): array
	{
		$event = new Event(
			self::MODULE_ID,
			self::EVENT_ON_GET_MESSAGES,
			[
				'platform' => $platform->value,
			]
		);
		$event->send();

		$messages = self::extractMessagesFromEventResults($event);

		// TODO: remove after implementation
		if (empty($messages))
		{
			$path =
				Application::getDocumentRoot()
				. '/bitrix/modules/aiassistant/lib/Core/Enum/AiClientStatusMessage.php'
			;
			$messages = Loc::loadLanguageFile($path);
		}

		return $messages;
	}

	private static function extractMessagesFromEventResults(Event $event): array
	{
		$messages = [];
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === EventResult::SUCCESS)
			{
				$eventResultMessages = $eventResult->getParameters();
				if (is_array($eventResultMessages))
				{
					$messages[] = self::filterMessages($eventResultMessages);
				}
			}
		}

		return empty($messages) ? [] : array_merge(...$messages);
	}

	private static function filterMessages(array $messages): array
	{
		return array_filter(
			$messages,
			function ($text)
			{
				return is_string($text);
			}
		);
	}
}
