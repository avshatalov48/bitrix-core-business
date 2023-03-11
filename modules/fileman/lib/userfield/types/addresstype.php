<?php

namespace Bitrix\Fileman\UserField\Types;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Address\Converter\StringConverter;
use Bitrix\Location\Service\FormatService;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;

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
	 * @deprecated
	 * @return array|null
	 */
	public static function getTrialHint(): ?array
	{
		return null;
	}

	/**
	 * @return bool
	 */
	public static function canUseMap(): bool
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public static function checkRestriction(): bool
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public static function useRestriction(): bool
	{
		return false;
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

	public static function onBeforeSaveAll(array $userField, $value)
	{
		$result = [];
		foreach ($value as $item)
		{
			$processedValue = self::onBeforeSave($userField, $item);
			if ($processedValue)
			{
				$result[] = $processedValue;
			}
		}

		$fieldName = $userField['FIELD_NAME'];
		unset($_POST[$fieldName . '_manual_edit']);

		return $result;
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return string|null
	 * @throws LoaderException
	 */
	public static function onBeforeSave(array $userField, $value)
	{
		if (!$value)
		{
			self::clearManualEditFlag($userField);

			return null;
		}

		if (!Loader::includeModule('location') || self::isRawValue($value))
		{
			// if the value hasn't been set manually (e.g. from bizproc), then we have to remove the
			// address' id because otherwise we'll end up with multiple UF values pointing to a single address
			$fieldName = $userField['FIELD_NAME'];
			$isManualAddressEdit = $_POST[$fieldName . '_manual_edit'] ?? null;
			if (!$isManualAddressEdit)
			{
				$parsedValue = self::parseValue($value);
				$value = $parsedValue[0] . '|' . $parsedValue[1][0] . ';' . $parsedValue[1][1];
			}

			self::clearManualEditFlag($userField);

			return $value;
		}

		if (mb_strlen($value) > 4 && mb_substr($value, -4) === '_del')
		{
			$oldAddressId = (int)substr($value, 0, -4);
			$oldAddress = Address::load($oldAddressId);
			if ($oldAddress)
			{
				$oldAddress->delete();
			}

			self::clearManualEditFlag($userField);

			return '';
		}

		$address = null;
		try
		{
			$convertedValue = Encoding::convertEncoding($value, LANG_CHARSET, 'UTF-8');
			$address = Address::fromJson($convertedValue);
		}
		catch (ArgumentException | \TypeError $exception)
		{
			if (is_string($value))
			{
				$addressFields = self::getAddressFieldsFromString($value);
				$address = Address::fromArray($addressFields);
			}
		}

		if (!$address)
		{
			self::clearManualEditFlag($userField);

			return $value;
		}

		$saveResult = $address->save();
		if ($saveResult->isSuccess())
		{
			$value = self::formatAddressToString($address);
		}

		self::clearManualEditFlag($userField);

		return $value;
	}

	private static function clearManualEditFlag(array $userField): void
	{
		$fieldName = $userField['FIELD_NAME'];
		if ($userField['MULTIPLE'] !== 'Y')
		{
			unset($_POST[$fieldName . '_manual_edit']);
		}
	}

	/**
	 * @param string|null $value
	 * @return array
	 */
	public static function parseValue(?string $value): array
	{
		$coords = '';
		$addressId = null;
		if(mb_strpos($value, '|') !== false)
		{
			[$value, $coords, $addressId] = explode('|', $value);
			if ($addressId)
			{
				$addressId = (int)$addressId;
			}
			if($coords !== '' && mb_strpos($coords, ';') !== false)
			{
				$coords = explode(';', $coords);
			}
			else
			{
				$coords = '';
			}
		}

		return [$value, $coords, $addressId];
	}

	/**
	 * @param Address $address
	 * @return string
	 */
	private static function formatAddressToString(Address $address): string
	{
		return (
			$address->toString(FormatService::getInstance()->findDefault(LANGUAGE_ID), StringConverter::STRATEGY_TYPE_TEMPLATE_COMMA)
			. '|'
			. $address->getLatitude()
			. ';'
			. $address->getLongitude()
			. '|'
			. $address->getId()
		);
	}

	public static function isRawValue($value): bool
	{
		$valueParts = explode('|', $value);
		$valuePartsCount = count($valueParts);
		if ($valuePartsCount < 2)
		{
			return false;
		}

		if (mb_strpos($valueParts[1], ';') === false)
		{
			return false;
		}

		$possibleCoords = explode(';', $valueParts[1]);
		if (
			count($possibleCoords) !== 2
			|| (
				(!is_numeric($possibleCoords[0]) || !is_numeric($possibleCoords[1]))
				&& !($possibleCoords[0] === '' && $possibleCoords[1] === '')
			)
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $value
	 * @return array|null
	 */
	public static function getAddressFieldsByValue($value): ?array
	{
		if (!Loader::includeModule('location'))
		{
			return null;
		}

		$address = null;

		try
		{
			$convertedValue = Encoding::convertEncoding($value, LANG_CHARSET, 'UTF-8');
			$address = Address::fromJson($convertedValue);
		}
		catch (\Exception | \TypeError $exception)
		{
			// the value is not in JSON format, so we can try another format
		}

		if (!$address)
		{
			$addressId = self::parseValue($value)[2];
			if (is_numeric($addressId))
			{
				$address = Address::load((int)$addressId);
			}
		}

		if ($address)
		{
			return $address->toArray();
		}

		return self::getAddressFieldsFromString($value);
	}

	/**
	 * Compatibility
	 * @param string|null $addressString
	 * @return array|null
	 */
	private static function getAddressFieldsFromString(?string $addressString): ?array
	{
		if (!$addressString)
		{
			return null;
		}

		[$address, $coords] = self::parseValue($addressString);

		return [
			'latitude' => $coords[0],
			'longitude' => $coords[1],
			'fieldCollection' => [
				Address\FieldType::ADDRESS_LINE_2 => $address,
			],
			'languageId' => LANGUAGE_ID,
		];
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
