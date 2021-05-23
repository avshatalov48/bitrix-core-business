<?php

namespace Bitrix\Sale\Cashbox\Rest;

use Bitrix\Main;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Cashbox\CheckManager;
use Bitrix\Sale\Helpers;
use Bitrix\Sale\Cashbox;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

/**
 * Class CheckService
 * @package Bitrix\Sale\Cashbox\Rest
 */
class CheckService extends RestService
{
	private const ERROR_CHECK_NOT_FOUND = 'ERROR_CHECK_NOT_FOUND';
	private const ERROR_CHECK_APPLY = 'ERROR_CHECK_APPLY';

	/**
	 * @param $params
	 * @throws Main\ArgumentException
	 * @throws RestException
	 */
	private static function checkParamsBeforeApplyCheck($params)
	{
		if (!$params['UUID'])
		{
			throw new RestException('Parameter UUID is not defined', self::ERROR_CHECK_FAILURE);
		}

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($params['UUID']);
		if (!$checkInfo)
		{
			throw new RestException('Check not found', self::ERROR_CHECK_NOT_FOUND);
		}
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws RestException
	 * @throws \Bitrix\Rest\AccessException
	 */
	public static function applyCheck($params)
	{
		Helpers\Rest\AccessChecker::checkAccessPermission();
		$params = self::prepareIncomingParams($params);
		self::checkParamsBeforeApplyCheck($params);

		$result = Cashbox\CashboxRest::applyCheckResult($params);
		if ($result->isSuccess())
		{
			return true;
		}

		$errors = implode("\n", $result->getErrorMessages());
		throw new RestException($errors, self::ERROR_CHECK_APPLY);
	}
}