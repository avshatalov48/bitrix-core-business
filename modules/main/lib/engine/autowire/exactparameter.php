<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class ExactParameter extends Parameter
{
	private string $parameterName;

	public function __construct(string $className, string $parameterName, \Closure $constructor)
	{
		if (!$this->validateConstructor($constructor))
		{
			throw new BinderArgumentException('$constructor closure must have one argument to bind class name.');
		}

		parent::__construct($className, $constructor);
		$this->parameterName = $parameterName;
	}

	protected function validateConstructor(\Closure $constructor)
	{
		$reflectionFunction = new \ReflectionFunction($constructor);

		return $reflectionFunction->getNumberOfParameters() >= 1;
	}

	public function constructValue(\ReflectionParameter $parameter, Result $captureResult, $newThis = null)
	{
		return $this->callConstructor(
			$this->getConstructor(),
			$captureResult->getData(),
			$newThis,
		);
	}

	public function captureData(\ReflectionParameter $parameter, array $sourceParameters, array $autoWiredParameters = [])
	{
		$result = new Result();

		if (!$this->needToMapExternalData())
		{
			return $result;
		}

		$binder = Binder::buildForFunction($this->getConstructor());
		$binder->setAutoWiredParameters($autoWiredParameters);

		$reflectionClass = $this->buildReflectionClass($parameter);
		if (!$reflectionClass)
		{
			throw new BinderArgumentException("Could not retrieve \\ReflectionClass for {$parameter->getName()}.");
		}

		array_unshift($sourceParameters, ['className' => $reflectionClass->getName()]);
		$binder->setSourcesParametersToMap($sourceParameters);
		try
		{
			$capturedParameters = $binder->getArgs();
			$result->setData($capturedParameters);
		}
		catch (BinderArgumentException $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
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