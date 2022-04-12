<?php

namespace Bitrix\Sender\Integration\Crm;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

final class CrmTileMap
{
	private static function getFacebookConversionList() : array
	{
		if (!Loader::includeModule('crm') || !Loader::includeModule('seo'))
		{
			return [];
		}

		if (Integration\Bitrix24\Service::isRegionRussian(true))
		{
			return [];
		}

		return [
			[
				'CODE' => 'facebook_conversion_deal',
				'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_FACEBOOK_TILE_MAP_CONVERSION_DEAL'),
				'IS_AVAILABLE' => true,
				'ICON_CLASS' => 'ui-icon ui-icon-service-fb',
			],
			[
				'CODE' => 'facebook_conversion_webform',
				'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_FACEBOOK_TILE_MAP_CONVERSION_WEBFORM'),
				'IS_AVAILABLE' => true,
				'ICON_CLASS' => 'ui-icon ui-icon-service-fb'
			],
			[
				'CODE' => 'facebook_conversion_payment',
				'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_FACEBOOK_TILE_MAP_CONVERSION_PAYMENT'),
				'IS_AVAILABLE' => true,
				'ICON_CLASS' => 'ui-icon ui-icon-service-fb'
			],
			[
				'CODE' => 'facebook_conversion_lead',
				'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_FACEBOOK_TILE_MAP_CONVERSION_LEAD'),
				'IS_AVAILABLE' => true,
				'ICON_CLASS' => 'ui-icon ui-icon-service-fb'
			],
		];
	}

	public static function getFacebookConversion() : array
	{
		return [
			'LIST' => $list = static::getFacebookConversionList(),
			'FEATURED_LIST' => [],
			'OTHER_LIST' => [],
			'TILES' => array_map(
				static function(array $item) : array {
					return [
						'id' => $item['CODE'],
						'name' => $item['NAME'],
						'selected' => $item['IS_AVAILABLE'],
						'iconClass' => $item['ICON_CLASS'],
						'data' => [
							'code' => $item['CODE']
						],
					];
				},$list)
		];
	}
}