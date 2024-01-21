<?php

namespace Bitrix\Mail\Integration\AI;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\AI;
use Bitrix\Main;

final class EventHandler
{
	public const SETTINGS_ITEM_MAIL_CODE = 'mail_copilot_item_enabled';
	public const SETTINGS_ITEM_MAIL_CRM_CODE = 'mail_crm_copilot_item_enabled';

	public static function onTuningLoad(): Main\Entity\EventResult
	{
		$result = new Main\Entity\EventResult();
		$items = [];

		if (!self::checkTextCategory())
		{
			$result->modifyFields([
				'items' => $items,
			]);

			return $result;
		}

		$items[self::SETTINGS_ITEM_MAIL_CODE] = [
			'group' => AI\Tuning\Defaults::GROUP_TEXT,
			'title' => Loc::getMessage('MAIL_INTEGRATION_AI_EVENTHANDLER_MAIL_SETTINGS_TITLE'),
			'header' => Loc::getMessage('MAIL_INTEGRATION_AI_EVENTHANDLER_MAIL_SETTINGS_SUBTITLE'),
			'type' => AI\Tuning\Type::BOOLEAN,
			'default' => true,
			'sort' => 400,
		];

		if (Loader::includeModule('crm') && class_exists('\Bitrix\Crm\Integration\AI\AIManager'))
		{
			$items[self::SETTINGS_ITEM_MAIL_CRM_CODE] = [
				'group' => AI\Tuning\Defaults::GROUP_TEXT,
				'title' => Loc::getMessage('MAIL_INTEGRATION_AI_EVENTHANDLER_MAIL_CRM_SETTINGS_TITLE'),
				'header' => Loc::getMessage('MAIL_INTEGRATION_AI_EVENTHANDLER_MAIL_CRM_SETTINGS_SUBTITLE'),
				'type' => AI\Tuning\Type::BOOLEAN,
				'default' => true,
				'sort' => 410,
			];
		}

		$result->modifyFields([
			'items' => $items,
		]);

		return $result;
	}

	private static function checkTextCategory():bool
	{
		return !empty(AI\Engine::getByCategory('text', AI\Context::getFake()));
	}
}