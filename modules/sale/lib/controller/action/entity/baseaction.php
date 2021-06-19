<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Sale;
use Bitrix\Rest;

/*
 * Error code notation x(category1) xxx(category2) xxx(code category) xxxxx(code) - 2 000 403 00010
 * # category1 (x):
 * Check arguments in BaseAction - 1
 * Action - 2
 *
 * # category2 (xxx):
 * BaseAction - 018
 * AddBasketItemAction - 019
 * DeleteBasketItemAction - 020
 * UpdateBasketItemAction - 021
 * SaveOrderAction - 022
 * GetBasketAction - 023
 * UserConsentRequestAction - 024
 *
 * # code category (xxx) - http status
 *
 * # code (xxxxx) - any value
 * SaveOrderAction - check fields - 00000 - 09999
 * SaveOrderAction - person type - 01000 - 01999
 * SaveOrderAction - basket - 02000 - 02999
 * SaveOrderAction - properties - 03000 - 03999
 * SaveOrderAction - trading platform - 04000 - 04999
 * SaveOrderAction - user - 05000 - 05999
 * SaveOrderAction - user profile - 06000 - 06999
 * SaveOrderAction - final - 07000 - 07999
 * SaveOrderAction - save - 08000 - 08999
 */

/**
 * Class BaseAction
 * @package Bitrix\Sale\Controller\Action\Entity
 */
class BaseAction extends Action
{
	/**
	 * @return bool
	 */
	protected function onBeforeRun()
	{
		$checkPermissionResult = $this->checkPermission();
		if (!$checkPermissionResult->isSuccess())
		{
			$this->addErrors($checkPermissionResult->getErrors());
			return false;
		}

		$arguments = $this->getArguments();

		$checkCaseArgumentsResult = $this->checkArguments($arguments);
		if (!$checkCaseArgumentsResult->isSuccess())
		{
			$this->addErrors($checkCaseArgumentsResult->getErrors());
			return false;
		}

		// convert keys
		$converter = new Converter(
			Converter::KEYS
			| Converter::RECURSIVE
			| Converter::TO_SNAKE
			| Converter::TO_SNAKE_DIGIT
			| Converter::TO_UPPER
		);
		$arguments = $converter->process($arguments);

		$this->setArguments($arguments);

		return parent::onBeforeRun();
	}

	private function checkPermission(): Sale\Result
	{
		$result = new Sale\Result();

		try
		{
			Sale\Helpers\Rest\AccessChecker::checkAccessPermission();
		}
		catch (Rest\AccessException $ex)
		{
			$result->addError(new Main\Error($ex->getMessage(), 201840300001));
		}

		return $result;
	}

	private function checkArguments(array $arguments): Sale\Result
	{
		$result = new Sale\Result();

		$checkCaseOfKeysResult = $this->checkCaseOfKeys($arguments);
		if (!$checkCaseOfKeysResult->isSuccess())
		{
			$result->addErrors($checkCaseOfKeysResult->getErrors());
		}

		return $result;
	}

	private function checkCaseOfKeys(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		static $count = 0;

		foreach ($fields as $key => $value)
		{
			if (\is_int($key))
			{
				continue;
			}

			if (mb_strtoupper($key) === $key)
			{
				$result->addError(
					new Main\Error(
						"key \"{$key}\" has UPPERCASE notation. Use lowerCamelCase notation",
						101840000001 + $count
					)
				);

				$count++;
			}

			if (\is_array($value))
			{
				$internalCheckResult = $this->checkCaseOfKeys($value);
				if (!$internalCheckResult->isSuccess())
				{
					$result->addErrors($internalCheckResult->getErrors());
				}
			}
		}

		return $result;
	}

	public function runWithSourceParametersList()
	{
		$result = parent::runWithSourceParametersList();

		if ($this->errorCollection->isEmpty())
		{
			if (\is_array($result) && Main\Loader::includeModule('rest'))
			{
				$result = Rest\Integration\Externalizer::multiSortKeysArray($result);
			}

			return Converter::toJson()->process($result);
		}

		return $result;
	}
}
