<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use CIMMessageParamAttach;
use CIMMessenger;

class AnalyticsEventLogger
{
	public static function sendToChat(int $chatId, array|Event $event): void
	{
		$attach = new CIMMessageParamAttach(null, CIMMessageParamAttach::CHAT);
		$messageText = Loc::getMessage('IM_ANALYTICS_TAG_WAS_SENT');

		$messageFields = [
			'SYSTEM' => 'Y',
			'URL_PREVIEW' => 'N',
			'MESSAGE' => $messageText . ' ' . (($event instanceof Event) ? 'backend' : 'frontend'),
			'FROM_USER_ID' => 0,
			'TO_CHAT_ID' => $chatId,
			'MESSAGE_TYPE' => IM_MESSAGE_CHAT,
		];

		if ($event instanceof Event)
		{
			/** @var AnalyticsEvent $analyticsEvent */
			$analyticsEvent = $event->getParameter('analyticsEvent');
			$event = $analyticsEvent->exportToArray();
		}

		$tool = $event['tool'] ?? '';
		$category = $event['category'] ?? '';
		$p5 = $event['p5'] ?? '';

		if ((($tool === 'im') || str_starts_with($category, 'chat')) && ($p5 === ('chatId_' . $chatId)))
		{
			return;
		}

		foreach ($event as $item => $value)
		{
			if ((null !== $value) && ($item !== 'userAgent'))
			{
				$attach->AddMessage($item . ': ' . $value);
			}
		}

		$messageFields['ATTACH'] = $attach;
		CIMMessenger::Add($messageFields);
	}
}
