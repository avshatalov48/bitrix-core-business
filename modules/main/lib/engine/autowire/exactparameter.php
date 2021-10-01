<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class ExactParameter extends Parameter
{
	/** @var string */
	private $parameterName;

	public function __construct($className, $parameterName, \Closure $constructor)
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
		if ($reflectionFunction->getNumberOfParameters() < 1)
		{
			return false;
		}

		return true;
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

		array_unshift($sourceParameters, ['className' => $parameter->getClass()->getName()]);
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