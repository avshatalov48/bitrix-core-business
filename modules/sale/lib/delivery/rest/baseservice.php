<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Rest;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

class BaseService extends \IRestService
{
	protected const ALLOW_HANDLERS = [
		'\\' . \Sale\Handlers\Delivery\RestHandler::class,
		'\\' . \Sale\Handlers\Delivery\RestProfile::class,
	];

	public static function onRestAppDelete(array $fields): void
	{
		if (!Main\Loader::includeModule('rest'))
		{
			return;
		}

		if (empty($fields['APP_ID']) || empty($fields['CLEAN']) || $fields['CLEAN'] !== true)
		{
			return;
		}

		$app = Rest\AppTable::getByClientId($fields['APP_ID']);
		if (!$app)
		{
			return;
		}

		$restHandlerResult = Internals\DeliveryRestHandlerTable::getList([
			'select' => ['ID', 'CODE'],
			'filter' => [
				'=APP_ID' => $app['CLIENT_ID'],
			],
		]);
		while ($restHandler = $restHandlerResult->fetch())
		{
			$deliveryResult = Sale\Delivery\Services\Manager::getList([
				'select' => ['ID', 'CONFIG'],
				'filter' => [
					'@CLASS_NAME' => self::ALLOW_HANDLERS,
				],
			]);
			while ($delivery = $deliveryResult->fetch())
			{
				$handlerCode = self::getRestCodeFromConfig($delivery['CONFIG']);
				if ($handlerCode === $restHandler['CODE'])
				{
					Sale\Delivery\Services\Manager::delete($delivery['ID'], false);
				}
			}

			Sale\Delivery\Rest\Internals\DeliveryRestHandlerTable::delete($restHandler['ID']);
		}
	}

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

	protected static function checkDeliveryPermission(): void
	{
		Sale\Helpers\Rest\AccessChecker::checkAccessPermission();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function prepareIncomingParams(array $data): array
	{
		return self::replaceIncomingKeys($data);
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

	protected static function hasAccessToDelivery(array $deliveryData, string $appId = null): bool
	{
		$className = $deliveryData['CLASS_NAME'];
		if (self::isRestHandler($className))
		{
			$handlerCode = self::getRestCodeFromConfig($deliveryData['CONFIG']);
			$handlerData = self::getHandlerData($handlerCode);
			if ($appId && !empty($handlerData['APP_ID']) && $handlerData['APP_ID'] !== $appId)
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	protected static function isRestHandler(string $className): bool
	{
		return in_array($className, self::ALLOW_HANDLERS, true);
	}

	protected static function getRestCodeFromConfig(array $config): string
	{
		$handlerCode = '';

		foreach ($config as $configItem)
		{
			if (!empty($configItem['REST_CODE']))
			{
				$handlerCode = (string)$configItem['REST_CODE'];
				break;
			}
		}

		return $handlerCode;
	}

	protected static function getHandlerData(string $code): ?array
	{
		static $result = [];

		if (!empty($result[$code]))
		{
			return $result[$code];
		}

		$handlerData = Internals\DeliveryRestHandlerTable::getList([
			'filter' => ['CODE' => $code],
			'limit' => 1,
		])->fetch();
		if ($handlerData)
		{
			$result[$code] = $handlerData;
		}

		return $result[$code] ?? null;
	}
}