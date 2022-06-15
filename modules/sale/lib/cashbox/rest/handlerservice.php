<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Cashbox\CashboxRest;
use Bitrix\Sale\Cashbox\Manager;
use Bitrix\Sale\Internals\CashboxRestHandlerTable;
use Bitrix\Sale\Helpers;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class HandlerService
 * @package Bitrix\Sale\Cashbox\Rest
 */
class HandlerService extends RestService
{
	private const ERROR_HANDLER_ALREADY_EXISTS = 'ERROR_HANDLER_ALREADY_EXIST';
	private const ERROR_HANDLER_NOT_FOUND = 'ERROR_HANDLER_NOT_FOUND';
	private const ERROR_HANDLER_UPDATE = 'ERROR_HANDLER_UPDATE';
	private const ERROR_HANDLER_DELETE = 'ERROR_HANDLER_DELETE';

	/**
	 * @param $params
	 * @throws RestException
	 */
	private static function checkParamsBeforeAddHandler($params)
	{
		if (empty($params['CODE']))
		{
			throw new RestException('Parameter CODE is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['NAME']))
		{
			throw new RestException('Parameter NAME is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (empty($params['SETTINGS']) || !is_array($params['SETTINGS']))
		{
			throw new RestException('Parameter SETTINGS is not defined or empty', self::ERROR_CHECK_FAILURE);
		}

		self::checkHandlerSettingsBeforeAdd($params['SETTINGS']);

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'=CODE' => $params['CODE']
			]
		])->fetch();
		if ($handler)
		{
			throw new RestException('Handler already exists!', self::ERROR_HANDLER_ALREADY_EXISTS);
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsBeforeUpdateHandler($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'ID' => $params['ID']
			]
		])->fetch();
		if (!$handler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($handler['APP_ID']) && $handler['APP_ID'] !== $params['APP_ID'])
		{
			throw new AccessException();
		}

		if (empty($params['FIELDS']) || !is_array($params['FIELDS']))
		{
			throw new RestException('Parameter FIELDS is not defined', self::ERROR_CHECK_FAILURE);
		}

		if (isset($params['FIELDS']['SETTINGS']))
		{
			self::checkHandlerSettingsBeforeUpdate($params['FIELDS']['SETTINGS']);
		}
	}

	/**
	 * @param $params
	 * @throws RestException
	 * @throws AccessException
	 */
	private static function checkParamsBeforeDeleteHandler($params)
	{
		if (empty($params['ID']))
		{
			throw new RestException('Parameter ID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$handler = CashboxRestHandlerTable::getList([
			'filter' => [
				'ID' => $params['ID']
			]
		])->fetch();
		if (!$handler)
		{
			throw new RestException('Handler not found', self::ERROR_HANDLER_NOT_FOUND);
		}

		if ($params['APP_ID'] && !empty($handler['APP_ID']) && $handler['APP_ID'] !== $params['APP_ID'])
		{
			throw new AccessException();
		}

		$cashboxListResult = Manager::getList([
			'select' => ['ID', 'HANDLER', 'SETTINGS'],
			'filter' => [
				'=HANDLER' => '\\'.CashboxRest::class,
			],
		]);

		$cashboxIdList = [];
		while ($cashbox = $cashboxListResult->fetch())
		{
			if ($cashbox['SETTINGS']['REST']['REST_CODE'] === $handler['CODE'])
			{
				$cashboxIdList[] = $cashbox['ID'];
			}
		}

		if ($cashboxIdList)
		{
			throw new RestException(
				'There are cashboxes with this handler: '.implode(', ', $cashboxIdList),
				self::ERROR_CHECK_FAILURE
			);
		}
	}

	/**
	 * @param $settings
	 */
	private static function checkHandlerSettingsBeforeAdd($settings): void
	{
		self::checkRequiredSettingsFields($settings, ['PRINT_URL', 'CHECK_URL', 'CONFIG']);
		self::checkSettingsFieldValues($settings, ['HTTP_VERSION', 'CONFIG']);
	}

	/**
	 * @param $settings
	 */
	private static function checkHandlerSettingsBeforeUpdate($settings): void
	{
		self::checkSettingsFieldValues($settings, ['PRINT_URL', 'CHECK_URL', 'CONFIG', 'HTTP_VERSION']);
	}

	/**
	 * @param array $settings
	 * @param array $requiredFields
	 * @throws RestException
	 */
	private static function checkRequiredSettingsFields(array $settings, array $requiredFields): void
	{
		foreach ($requiredFields as $fieldName)
		{
			if (empty($settings[$fieldName]))
			{
				throw new RestException('Parameter SETTINGS[' . $fieldName . '] is not defined', self::ERROR_CHECK_FAILURE);
			}
		}
	}

	/**
	 * @param array $settings
	 * @param array $fields
	 * @throws RestException
	 */
	private static function checkSettingsFieldValues(array $settings, array $fields): void
	{
		foreach ($fields as $fieldName)
		{
			if ($fieldName === 'HTTP_VERSION' && array_key_exists('HTTP_VERSION', $settings))
			{
				$version = $settings['HTTP_VERSION'];
				if (
					$version !== Main\Web\HttpClient::HTTP_1_0
					&& $version !== Main\Web\HttpClient::HTTP_1_1
				)
				{
					throw new RestException('The value of SETTINGS[HTTP_VERSION] is not valid', self::ERROR_CHECK_FAILURE);
				}
			}
			elseif ($fieldName === 'CONFIG' && array_key_exists('CONFIG', $settings))
			{
				self::checkSettingsConfig($settings['CONFIG']);
			}
			elseif (array_key_exists($fieldName, $settings) && empty($settings[$fieldName]))
			{
				throw new RestException('The value of SETTINGS[' . $fieldName . '] is not valid', self::ERROR_CHECK_FAILURE);
			}
		}
	}

	/**
	 * @param array $config
	 * @throws RestException
	 */
	private static function checkSettingsConfig(array $config): void
	{
		foreach ($config as $group => $block)
		{
			foreach ($block['ITEMS'] as $code => $item)
			{
				try
				{
					\Bitrix\Sale\Internals\Input\Manager::getEditHtml('SETTINGS['.$group.']['.$code.']', $item);
				}
				catch (\Exception $exception)
				{
					throw new RestException('The config provided in SETTINGS[CONFIG] is not valid', self::ERROR_CHECK_FAILURE);
				}
			}
		}
	}

	/**
	 * @param $handlerId
	 * @param $newSettings
	 * @return array|null
	 */
	private static function mergeHandlerSettings($cashboxId, array $newSettings): array
	{
		$dbResult = $existingSettings = CashboxRestHandlerTable::getList([
			'select' => ['SETTINGS'],
			'filter' => ['=ID' => $cashboxId],
			'limit' => 1,
		])->fetch();

		if (!$dbResult)
		{
			return $newSettings;
		}

		$existingSettings = $dbResult['SETTINGS'];
		if (!$existingSettings)
		{
			return $newSettings;
		}

		return array_replace_recursive($existingSettings, $newSettings);
	}

	/**
	 * @param $params
	 * @param \CRestServer $server
	 * @return array|bool|int
	 */
	public static function addHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeAddHandler($params);

		if (!isset($params['SETTINGS']['SUPPORTS_FFD105']))
		{
			$params['SETTINGS']['SUPPORTS_FFD105'] = 'N';
		}

		$result = CashboxRestHandlerTable::add([
			'NAME' => $params['NAME'],
			'CODE' => $params['CODE'],
			'SORT' => $params['SORT'] ?: 100,
			'SETTINGS' => $params['SETTINGS'],
			'APP_ID' => $params['APP_ID'],
		]);

		if ($result->isSuccess())
		{
			return $result->getId();
		}

		return false;
	}

	/**
	 * @param $params
	 * @param $page
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeUpdateHandler($params);

		$handlerFields = $params['FIELDS'];
		if ($handlerFields['SETTINGS'])
		{
			$handlerFields['SETTINGS'] = self::mergeHandlerSettings($params['ID'], $handlerFields['SETTINGS']);
		}

		$result = CashboxRestHandlerTable::update($params['ID'], $handlerFields);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_HANDLER_UPDATE);
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteHandler($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareHandlerParams($params, $server);
		self::checkParamsBeforeDeleteHandler($params);

		$result = CashboxRestHandlerTable::delete($params['ID']);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_HANDLER_DELETE);
	}

	/**
	 * @return array
	 */
	public static function getHandlerList($params, $page, \CRestServer $server)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();

		$result = [];

		$dbRes = CashboxRestHandlerTable::getList([
			'select' => ['ID', 'NAME', 'CODE', 'SORT', 'SETTINGS'],
		]);
		while ($item = $dbRes->fetch())
		{
			$result[] = $item;
		}

		return $result;
	}
}