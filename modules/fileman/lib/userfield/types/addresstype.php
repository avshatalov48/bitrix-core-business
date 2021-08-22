<?php

namespace Bitrix\Fileman\UserField\Types;

use Bitrix\Bitrix24\RestrictionCounter;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use CUserTypeManager;
use Bitrix\Main\UserField\Types\BaseType;

Loc::loadMessages(__FILE__);

/**
 * Class AddressType
 * @package Bitrix\Fileman\UserField\Types
 */
class AddressType extends BaseType
{
	public const
		USER_TYPE_ID = 'address',
		RENDER_COMPONENT = 'bitrix:fileman.field.address',
		BITRIX24_RESTRICTION = 100,
		BITRIX24_RESTRICTION_CODE = 'uf_address';

	protected static $restrictionCount = null;

	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_ADDRESS_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/**
	 * @return string|null
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public static function getApiKey(): ?string
	{
		$apiKey = Option::get('fileman', 'google_map_api_key', '');
		if(Loader::includeModule('bitrix24') && \CBitrix24::isCustomDomain())
		{
			$apiKey = null;
			$key = Option::get('bitrix24', 'google_map_api_key', '');
			$keyHost = Option::get('bitrix24', 'google_map_api_key_host', '');
			if(defined('BX24_HOST_NAME') && $keyHost === BX24_HOST_NAME)
			{
				$apiKey = $key;
			}
		}

		return $apiKey;
	}

	/**
	 * @return string
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public static function getApiKeyHint(): string
	{
		$hint = '';
		if(static::getApiKey() === null)
		{
			if(Loader::includeModule('bitrix24'))
			{
				if(\CBitrix24::isCustomDomain())
				{
					$hint = Loc::getMessage(
						'USER_TYPE_ADDRESS_NO_KEY_HINT_B24',
						['#settings_path#' => \CBitrix24::PATH_CONFIGS]
					);
				}
			}
			else
			{
				if(defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					$settingsPath = '/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=fileman';
				}
				else
				{
					$settingsPath = SITE_DIR . 'configs/';
				}

				if(
					!File::isFileExists($_SERVER['DOCUMENT_ROOT'] . $settingsPath)
					||
					!Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'] . $settingsPath)
				)
				{
					$settingsPath = SITE_DIR . 'settings/configs/';
				}

				$hint = Loc::getMessage(
					'USER_TYPE_ADDRESS_NO_KEY_HINT',
					['#settings_path#' => $settingsPath]
				);
			}
		}

		return $hint;
	}

	/**
	 * @return array|null
	 * @throws LoaderException
	 */
	public static function getTrialHint(): ?array
	{
		if(static::useRestriction() && !static::checkRestriction())
		{
			return [
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL_TITLE'),
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL')
			];
		}

		return null;
	}

	/**
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws LoaderException
	 */
	public static function canUseMap(): bool
	{
		return (static::getApiKey() !== null && static::checkRestriction());
	}

	/**
	 * @return bool
	 * @throws LoaderException
	 */
	public static function checkRestriction(): bool
	{
		if(
			static::useRestriction()
			&&
			static::$restrictionCount === null
			&&
			Loader::includeModule('bitrix24')
		)
		{
			static::$restrictionCount = RestrictionCounter::get(static::BITRIX24_RESTRICTION_CODE);
		}

		return (static::$restrictionCount < static::BITRIX24_RESTRICTION);
	}

	/**
	 * @return bool
	 * @throws LoaderException
	 */
	public static function useRestriction(): bool
	{
		return (
			Loader::includeModule('bitrix24')
			&&
			!\CBitrix24::IsLicensePaid()
			&&
			!\CBitrix24::IsNfrLicense()
			&&
			!\CBitrix24::IsDemoLicense()
		);
	}

	public static function getDbColumnType(): string
	{
		return 'text';
	}

	public static function prepareSettings(array $userField): array
	{
		return [
			'SHOW_MAP' => ($userField['SETTINGS']['SHOW_MAP'] === 'N' ? 'N' : 'Y')
		];
	}

	public static function checkFields(array $userField, $value): array
	{
		return [];
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return string|null
	 * @throws LoaderException
	 */
	public static function onBeforeSave(array $userField, $value)
	{
		if(
			$value !== ''
			&&
			static::useRestriction()
			&&
			static::checkRestriction()
			&&
			mb_strpos($value, '|') >= 0
		)
		{
			if($userField['MULTIPLE'] === 'Y')
			{
				$increment = (!is_array($userField['VALUE']) || !in_array($value, $userField['VALUE']));
			}
			else
			{
				$increment = ($userField['VALUE'] !== $value);
			}

			if($increment && Loader::includeModule('bitrix24'))
			{
				RestrictionCounter::increment(static::BITRIX24_RESTRICTION_CODE);
			}
		}

		return $value;
	}

	/**
	 * @param string|null $value
	 * @return array
	 */
	public static function parseValue(?string $value):array
	{
		$coords = '';
		if(mb_strpos($value, '|') !== false)
		{
			list($value, $coords) = explode('|', $value);
			if($coords !== '' && mb_strpos($coords, ';') !== false)
			{
				$coords = explode(';', $coords);
			}
			else
			{
				$coords = '';
			}
		}

		return [$value, $coords];
	}

	/**
	 * @param null|array $userField
	 * @param array $additionalSettings
	 * @return array
	 */
	public static function getFilterData(?array $userField, array $additionalSettings): array
	{
		return [
			'id' => $additionalSettings['ID'],
			'name' => $additionalSettings['NAME'],
			'filterable' => ''
		];
	}

	/**
	 * @param array $userField
	 * @return string|null
	 */
	public static function onSearchIndex(array $userField): ?string
	{
		if(is_array($userField['VALUE']))
		{
			$result = implode('\r\n', $userField['VALUE']);
		}
		else
		{
			$result = $userField['VALUE'];
		}

		return $result;
	}
}