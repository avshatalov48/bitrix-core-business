<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Dictionary\IntegrationSection;

Loc::loadMessages(__FILE__);

/**
 * Class Section
 * @package Bitrix\Rest\Preset\Data
 */
class Section extends Base
{
	private const DEFAULT_DATA = [
		'migration' => [
			'CODE' => 'migration',
			'SECTION_CODE' => 'migration',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-arrows',
			'ICON_I_BG_COLOR' => '#2fc6f5',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_MIGRATION',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_MIGRATION',
		],
		'external' => [
			'CODE' => 'external',
			'SECTION_CODE' => 'external',
			'ACTIVE' => 'Y',
			'ADMIN_ONLY' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-wheel',
			'ICON_I_BG_COLOR' => '#55d0e0',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_EXTERNAL_SYSTEMS',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_EXTERNAL_SYSTEMS',
		],
		'auto-sales' => [
			'CODE' => 'auto-sales',
			'SECTION_CODE' => 'auto-sales',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-cart',
			'ICON_I_BG_COLOR' => '#9dcf01',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_AUTOMATE_SALES',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_AUTOMATE_SALES',
		],
		'auto-control' => [
			'CODE' => 'auto-control',
			'SECTION_CODE' => 'auto-control',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-play',
			'ICON_I_BG_COLOR' => '#1eae43',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_AUTOMATE_CONTROL',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_AUTOMATE_CONTROL',
		],
		'widget' => [
			'CODE' => 'widget',
			'SECTION_CODE' => 'widget',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-widget',
			'ICON_I_BG_COLOR' => '#ffa901',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_WIDGET',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_WIDGET',
		],
		'chat-bot' => [
			'CODE' => 'chat-bot',
			'SECTION_CODE' => 'chat-bot',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-chatbot',
			'ICON_I_BG_COLOR' => '#ff5752',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_CHAT_BOT',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_CHAT_BOT',
		],
		'standard' => [
			'CODE' => 'standard',
			'SECTION_CODE' => 'standard',
			'ACTIVE' => 'Y',
			'ICON_CLASS' => 'ui-icon ui-icon-service-other',
			'ICON_I_BG_COLOR' => '#c374d1',
			'TITLE.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_NAME_STANDARD',
			'DESCRIPTION.MESSAGE_CODE' => 'REST_INTEGRATION_OPTION_SECTION_DESCRIPTION_STANDARD',
		],
	];
	private const CACHE_DIR = '/rest/integration/section/';

	/**
	 * @return array
	 * @throws ArgumentException
	 */
	public static function get() : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'sectionsIndexPage' . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$dictionary = new IntegrationSection();
			foreach ($dictionary as $el)
			{
				if (!empty($el['option']))
				{
					$data = Json::decode(base64_decode($el['option']));
					if (is_array($data))
					{
						$data = static::changeMessage($data);
						$data['CODE'] = $data['SECTION_CODE'];
						$result[$data['CODE']] = $data;
					}
				}
			}

			if (empty($result))
			{
				$result = static::changeMessage(static::DEFAULT_DATA);
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}