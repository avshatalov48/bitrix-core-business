<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Engine\AutoWire;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationService;
use ReflectionParameter;

class ValidationParameter extends Parameter
{
	/**
	 * @throws BinderArgumentException
	 */
	public function constructValue(ReflectionParameter $parameter, Result $captureResult, $newThis = null)
	{
		$object = parent::constructValue($parameter, $captureResult, $newThis);

		/** @var ValidationService $service */
		$service = ServiceLocator::getInstance()->get('main.validation.service');

		$result = $service->validate($object);
		if ($result->isSuccess())
		{
			return $object;
		}

		throw new BinderArgumentException(
			"Could not construct parameter {{$parameter->getName()}}",
			addedErrorsFromClosure: $result->getErrors()
		);
	}
}