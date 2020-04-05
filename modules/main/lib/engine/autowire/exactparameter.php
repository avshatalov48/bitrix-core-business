<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use http\Exception\InvalidArgumentException;

class ExactParameter extends Parameter
{
	/** @var string */
	private $parameterName;

	public function __construct($className, $parameterName, \Closure $constructor)
	{
		if (!$this->validateConstructor($constructor))
		{
			throw new InvalidArgumentException('$constructor closure must have more than one argument');
		}

		parent::__construct($className, $constructor);
		$this->parameterName = $parameterName;
	}

	protected function validateConstructor(\Closure $constructor)
	{
		$reflectionFunction = new \ReflectionFunction($constructor);
		if ($reflectionFunction->getNumberOfParameters() < 2)
		{
			return false;
		}

		return true;
	}

	public function captureData(\ReflectionParameter $parameter, array $sourceParameters)
	{
		if (!$this->needToMapExternalData())
		{
			return new Result();
		}

		$result = new Result();
		$capturedParameters = [];
		foreach ($this->fetchParametersToMapExternalNamesFromClosure() as $externalParameter)
		{
			$value = $this->findParameterInSourceList($externalParameter->getName(), $sourceParameters, $status);
			if ($status === Binder::STATUS_NOT_FOUND)
			{
				if ($externalParameter->isDefaultValueAvailable())
				{
					$value = $externalParameter->getDefaultValue();
				}
				else
				{
					$result->addError(new Error("Could not find value for {{$externalParameter->getName()}}"));
					break;
				}
			}

			$capturedParameters[] = $value;
		}
		$result->setData($capturedParameters);

		return $result;
	}

	/**
	 * @return \ReflectionParameter[]
	 * @throws \ReflectionException
	 */
	private function fetchParametersToMapExternalNamesFromClosure()
	{
		$params = [];
		$reflectionFunction = new \ReflectionFunction($this->getConstructor());
		foreach ($reflectionFunction->getParameters() as $i => $reflectionParameter)
		{
			if ($i === 0)
			{
				continue;
			}

			$params[] = $reflectionParameter;
		}

		return $params;
	}

	public function match(\ReflectionParameter $parameter)
	{
		return
			parent::match($parameter) &&
			$parameter->getName() === $this->getParameterName()
		;
	}

	/**
	 * @return string
	 */
	public function getParameterName()
	{
		return $this->parameterName;
	}

	public function getPriority()
	{
		return parent::getPriority() + 1;
	}
}