<?php

namespace Bitrix\Calendar\Internals\Helper;

use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\ArgumentException;

final class Analytics
{
	use SingletonTrait;

	protected const TOOL = 'calendar';

	protected const CALENDAR_CATEGORY = 'calendar';

	protected const EVENT = [
		'create_event' => 'create_event',
	];

	public const SECTION = [
		'calendar' => 'calendar',
		'chat' => 'chat',
		'collab' => 'collab',
		'project' => 'project',
		'crm' => 'crm',
		'tasks' => 'tasks',
	];

	public const SUB_SECTION = [
		'calendar_personal' => 'calendar_personal',
		'calendar_collab' => 'calendar_collab',
		'chat_textarea' => 'chat_textarea',
	];

	public const USER_TYPES = [
		'intranet' => 'user_intranet',
		'extranet' => 'user_extranet',
		'collaber' => 'user_collaber',
	];

	/**
	 * @param string $section
	 * @param string|null $subSection
	 * @param string|null $userType
	 * @param int|null $collabId
	 * @param int|null $chatId
	 *
	 * @return void
	 * @throws ArgumentException
	 */
	public function onEventCreate(
		string $section,
		?string $subSection,
		?string $userType = null,
		?int $collabId = 0,
		?int $chatId = 0,
	): void
	{
		$analyticsEvent = new AnalyticsEvent(
			event: self::EVENT['create_event'],
			tool: self::TOOL,
			category: self::CALENDAR_CATEGORY
		);

		$params = [];

		if (!empty($userType))
		{
			$params['p2'] = $userType;
		}

		if (!empty($collabId))
		{
			$params['p4'] = 'collabId_' . $collabId;
		}

		if (!empty($chatId))
		{
			$params['p5'] = 'chatId_' . $chatId;
		}

		$this->sendAnalytics($analyticsEvent, $section, $subSection, $params);
	}

	/**
	 * @param AnalyticsEvent $analyticsEvent
	 * @param string|null $section
	 * @param string|null $subSection
	 * @param array $params
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function sendAnalytics(
		AnalyticsEvent $analyticsEvent,
		?string $section = null,
		?string $subSection = null,
		array $params = [],
	): void
	{
		if (in_array($section, self::SECTION, true))
		{
			$analyticsEvent->setSection($section);
		}

		if (in_array($subSection, self::SUB_SECTION, true))
		{
			$analyticsEvent->setSubSection($subSection);
		}

		for ($i = 1; $i <= 5; $i++)
		{
			$pKey = 'p' . $i;
			if (!empty($params[$pKey]) && is_string($params[$pKey]))
			{
				$methodName = 'setP' . ($i);
				if (method_exists($analyticsEvent, $methodName))
				{
					$analyticsEvent->$methodName($params[$pKey]);
				}
			}
		}

		$analyticsEvent->send();
	}
}
