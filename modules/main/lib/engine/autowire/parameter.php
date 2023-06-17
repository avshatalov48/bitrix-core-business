<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Parameter
{
	private string $className;
	private \Closure $constructor;
	/** @var \Closure */
	private $externalNameConstructor;

	public function __construct(string $className, \Closure $constructor, \Closure $externalNameConstructor = null)
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
				$externalNameConstructor = static function(\ReflectionParameter $parameter){
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

	public function constructValue(\ReflectionParameter $parameter, Result $captureResult, $newThis = null)
	{
		$reflectionClass = $this->buildReflectionClass($parameter);
		if (!$reflectionClass)
		{
			throw new BinderArgumentException("Could not retrieve \\ReflectionClass for {$parameter->getName()}.");
		}

		$paramsToInvoke = array_merge(
			[$reflectionClass->getName()],
			$captureResult->getData()
		);

		return $this->callConstructor(
			$this->getConstructor(),
			$paramsToInvoke,
			$newThis,
		);
	}

	protected function callConstructor(\Closure $constructor, array $params, $newThis = null)
	{
		if ($newThis && $this->isBindable($constructor))
		{
			$constructor->bindTo($newThis);
		}

		return call_user_func_array($constructor, $params);
	}

	private function isBindable(\Closure $closure): bool
	{
		$reflectionClosure = new \ReflectionFunction($closure);
		$isBindable = ($reflectionClosure->getClosureThis() !== null || $reflectionClosure->getClosureScopeClass() === null);

		return $isBindable;
	}

	public function captureData(\ReflectionParameter $parameter, array $sourceParameters, array $autoWiredParameters = [])
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

			if (($source instanceof \ArrayAccess) && $source->offsetExists($name))
			{
				return $source[$name];
			}

			if (is_array($source) && array_key_exists($name, $source))
			{
				return $source[$name];
			}
		}
		$status = Binder::STATUS_NOT_FOUND;

		return null;
	}

	protected function buildReflectionClass(\ReflectionParameter $parameter): ?\ReflectionClass
	{
		$namedType = $parameter->getType();
		if (!($namedType instanceof \ReflectionNamedType))
		{
			return null;
		}
		if ($namedType->isBuiltin())
		{
			return null;
		}

		return new \ReflectionClass($namedType->getName());
	}
	
	public function match(\ReflectionParameter $parameter)
	{
		$class = $this->buildReflectionClass($parameter);
		if (!$class)
		{
			return false;
		}

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