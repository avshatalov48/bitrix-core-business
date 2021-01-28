<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main,
	Bitrix\Rest\AccessException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

class BaseService extends \IRestService
{
	/**
	 * @return string[]
	 */
	protected static function getIncomingFieldsMap(): array
	{
		return [];
	}

	/**
	 * @return string[]
	 */
	protected static function getOutcomingFieldsMap(): array
	{
		return [];
	}

	/**
	 * @throws AccessException
	 * @throws Main\LoaderException
	 */
	protected static function checkDeliveryPermission(): void
	{
		\Bitrix\Sale\Helpers\Rest\AccessChecker::checkAccessPermission();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function prepareIncomingParams(array $data): array
	{
		return self::replaceIncomingKeys(self::arrayChangeKeyCaseRecursive($data));
	}

	private static function arrayChangeKeyCaseRecursive($arr, $case = CASE_UPPER)
	{
		return array_map(static function($item) use ($case) {
			if (is_array($item))
			{
				$item = self::arrayChangeKeyCaseRecursive($item, $case);
			}
			return $item;
		}, array_change_key_case($arr, $case));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function prepareOutcomingFields(array $data): array
	{
		return self::replaceOutcomingKeys($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function replaceIncomingKeys(array $data): array
	{
		return self::replaceKeys($data, static::getIncomingFieldsMap());
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function replaceOutcomingKeys(array $data): array
	{
		return self::replaceKeys($data, static::getOutcomingFieldsMap());
	}

	/**
	 * @param array $data
	 * @param array $map
	 * @return array
	 */
	private static function replaceKeys(array $data, array $map): array
	{
		foreach ($map as $key => $newKey)
		{
			if (array_key_exists($key, $data))
			{
				$data[$newKey] = $data[$key];
				unset($data[$key]);
			}

			if (isset($data['FIELDS']) && array_key_exists($key, $data['FIELDS']))
			{
				$data['FIELDS'][$newKey] = $data['FIELDS'][$key];
				unset($data['FIELDS'][$key]);
			}
		}

		return $data;
	}
}