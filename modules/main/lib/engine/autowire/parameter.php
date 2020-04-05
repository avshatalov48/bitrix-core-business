<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Parameter
{
	/** @var string */
	private $className;
	/** @var \Closure */
	private $constructor;
	/** @var \Closure */
	private $externalNameConstructor;

	public function __construct($className, \Closure $constructor, \Closure $externalNameConstructor = null)
	{
		$this
			->setClassName($className)
			->setConstructor($constructor)
		;

		$reflectionFunction = new \ReflectionFunction($constructor);
		if ($reflectionFunction->getNumberOfParameters() > 1)
		{
			if ($externalNameConstructor === null)
			{
				$externalNameConstructor = function(\ReflectionParameter $parameter){
					return $parameter->getName() . 'Id';
				};
			}

			$this->setExternalNameConstructor($externalNameConstructor);
		}

	}

	public function getPriority()
	{
		if (!$this->needToMapExternalData())
		{
			return 1;
		}

		return 2;
	}

	public function constructValue(\ReflectionParameter $parameter, Result $captureResult)
	{
		$paramsToInvoke = array_merge(
			[$parameter->getClass()->getName()],
			$captureResult->getData()
		);

		return call_user_func_array($this->getConstructor(), $paramsToInvoke);
	}

	public function captureData(\ReflectionParameter $parameter, array $sourceParameters)
	{
		if (!$this->needToMapExternalData())
		{
			return new Result();
		}

		$result = new Result();
		$externalName = $this->generateExternalName($parameter);
		$value = $this->findParameterInSourceList($externalName, $sourceParameters, $status);

		if ($status === Binder::STATUS_NOT_FOUND)
		{
			$result->addError(new Error("Could not find value for {{$externalName}}"));
		}
		else
		{
			$result->setData([
				$value
			]);
		}

		return $result;
	}

	protected function findParameterInSourceList($name, array $sourceParameters, &$status)
	{
		$status = Binder::STATUS_FOUND;
		foreach ($sourceParameters as $source)
		{
			if (isset($source[$name]))
			{
				return $source[$name];
			}

			if ($source instanceof \ArrayAccess && $source->offsetExists($name))
			{
				return $source[$name];
			}
			elseif (is_array($source) && array_key_exists($name, $source))
			{
				return $source[$name];
			}
		}
		$status = Binder::STATUS_NOT_FOUND;

		return null;
	}
	
	public function match(\ReflectionParameter $parameter)
	{
		$class = $parameter->getClass();

		return
			$class->isSubclassOf($this->getClassName()) ||
			$class->name === ltrim($this->getClassName(), '\\')
		;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @param string $className
	 *
	 * @return Parameter
	 */
	public function setClassName($className)
	{
		$this->className = $className;

		return $this;
	}

	/**
	 * @return \Closure
	 */
	public function getConstructor()
	{
		return $this->constructor;
	}

	/**
	 * @param \Closure $constructor
	 *
	 * @return Parameter
	 */
	public function setConstructor(\Closure $constructor)
	{
		$this->constructor = $constructor;

		return $this;
	}

	/**
	 * @return \Closure
	 */
	public function getExternalNameConstructor()
	{
		return $this->externalNameConstructor;
	}

	/**
	 * @param \Closure $externalNameConstructor
	 *
	 * @return Parameter
	 */
	public function setExternalNameConstructor(\Closure $externalNameConstructor)
	{
		$this->externalNameConstructor = $externalNameConstructor;

		return $this;
	}

	protected function needToMapExternalData()
	{
		return $this->externalNameConstructor !== null;
	}

	public function generateExternalName()
	{
		return call_user_func_array($this->getExternalNameConstructor(), func_get_args());
	}
}