<?php

namespace Bitrix\Sale\Controller\Action;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Rest;

/**
 * Class BaseAction
 * @package Bitrix\Sale\Controller\Action
 */
class BaseAction extends Main\Engine\Action
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
		$converter = new Main\Engine\Response\Converter(
			Main\Engine\Response\Converter::KEYS
			| Main\Engine\Response\Converter::RECURSIVE
			| Main\Engine\Response\Converter::TO_SNAKE
			| Main\Engine\Response\Converter::TO_SNAKE_DIGIT
			| Main\Engine\Response\Converter::TO_UPPER
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
			$result->addError(
				new Main\Error(
					$ex->getMessage(),
					Sale\Controller\ErrorEnumeration::BASE_ACTION_ACCESS_DENIED
				)
			);
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
						Sale\Controller\ErrorEnumeration::BASE_ACTION_UPPERCASE_KEY + $count
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

			return Main\Engine\Response\Converter::toJson()->process($result);
		}

		return $result;
	}
}
