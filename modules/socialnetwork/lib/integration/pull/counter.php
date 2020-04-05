<?php
namespace Bitrix\Socialnetwork\Integration\Pull;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Counter
{
	const TYPE_LIVEFEED = 'livefeed';
	const MODULE_ID = 'socialnetwork';

	public static function onGetMobileCounterTypes(\Bitrix\Main\Event $event)
	{
		return new EventResult(EventResult::SUCCESS, Array(
			self::TYPE_LIVEFEED => Array(
				'NAME' => Loc::getMessage('SONET_COUNTER_TYPE_LIVEFEED'),
				'DEFAULT' => true
			)
		), self::MODULE_ID);
	}

	public static function onGetMobileCounter(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();

		$counters = \CUserCounter::getGroupedCounters(
			\CUserCounter::GetAllValues($params['USER_ID'])
		);

		$counter = isset($counters[$params['SITE_ID']][\CUserCounter::LIVEFEED_CODE])? $counters[$params['SITE_ID']][\CUserCounter::LIVEFEED_CODE]: 0;
		$counter = $counter > 0? $counter: 0;

		return new EventResult(EventResult::SUCCESS, Array(
			'TYPE' => self::TYPE_LIVEFEED,
			'COUNTER' => $counter
		), self::MODULE_ID);
	}
}