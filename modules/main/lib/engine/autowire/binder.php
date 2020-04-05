<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\ArgumentException;

final class Binder
{
	const STATUS_FOUND     = true;
	const STATUS_NOT_FOUND = false;

	private $instance;
	private $method;
	/** @var array */
	private $configuration = [];
	/** @var \SplObjectStorage|Parameter[] */
	private static $globalAutoWiredParameters;
	/** @var Parameter[] */
	private $autoWiredParameters = [];
	/** @var \ReflectionFunctionAbstract */
	private $reflectionFunctionAbstract;
	/** @var array */
	private $methodParams = null;
	/** @var array */
	private $args = null;

	public function __construct($instance, $method, $configuration = [])
	{
		$this->instance = $instance;
		$this->method = $method;
		$this->configuration = $configuration;

		if ($this->instance === null)
		{
			$this->buildReflectionFunction();
		}
		else
		{
			$this->buildReflectionMethod();
		}

	}

	final public static function buildForFunction($callable, $configuration = [])
	{
		return new static(null, $callable, $configuration);
	}

	final public static function buildForMethod($instance, $method, $configuration = [])
	{
		return new static($instance, $method, $configuration);
	}

	private function buildReflectionMethod()
	{
		$this->reflectionFunctionAbstract = new \ReflectionMethod($this->instance, $this->method);
		$this->reflectionFunctionAbstract->setAccessible(true);
	}

	private function buildReflectionFunction()
	{
		$this->reflectionFunctionAbstract = new \ReflectionFunction($this->method);
	}

	/**
	 * @return mixed
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return array
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * @param array $configuration
	 *
	 * @return Binder
	 */
	public function setConfiguration($configuration)
	{
		$this->configuration = $configuration;

		return $this;
	}

	/**
	 * @param Parameter[] $parameters
	 *
	 * @return $this
	 */
	public function setAutoWiredParameters(array $parameters)
	{
		$this->autoWiredParameters = [];
		
		foreach ($parameters as $parameter)
		{
			$this->appendAutoWiredParameter($parameter);
		}
		
		return $this;
	}

	public function appendAutoWiredParameter(Parameter $parameter)
	{
		$this->autoWiredParameters[] = $parameter;

		return $this;
	}

	/**
	 * Register globally auto wired parameter. The method was added in backwards compatibility reason.
	 * @param Parameter $parameter
	 * @return void
	 */
	public static function registerGlobalAutoWiredParameter(Parameter $parameter)
	{
		if (static::$globalAutoWiredParameters === null)
		{
			static::$globalAutoWiredParameters = new \SplObjectStorage();
		}

		if (!static::$globalAutoWiredParameters->contains($parameter))
		{
			static::$globalAutoWiredParameters[$parameter] = $parameter;
		}
	}

	private function getPriorityByParameter(Parameter $parameter)
	{
		return $parameter->getPriority();
	}

	/**
	 * @return Parameter[]
	 */
	public function getAutoWiredParameters()
	{
		return $this->autoWiredParameters;
	}

	public function setSourcesParametersToMap(array $parameters)
	{
		$this->configuration['sourceParameters'] = $parameters;

		return $this;
	}

	public function getSourcesParametersToMap()
	{
		return $this->configuration['sourceParameters']?: [];
	}

	public function appendSourcesParametersToMap(array $parameter)
	{
		if (!isset($this->configuration['sourceParameters']))
		{
			$this->configuration['sourceParameters'] = [];
		}

		$this->configuration['sourceParameters'][] = $parameter;

		return $this;
	}

	/**
	 * Invokes method with binded parameters.
	 * return @mixed
	 */
	final public function invoke()
	{
		try
		{
			if($this->reflectionFunctionAbstract instanceof \ReflectionMethod)
			{
				return $this->reflectionFunctionAbstract->invokeArgs($this->instance, $this->getArgs());
			}
			elseif ($this->reflectionFunctionAbstract instanceof \ReflectionFunction)
			{
				return $this->reflectionFunctionAbstract->invokeArgs($this->getArgs());
			}
		}
		catch (\TypeError $exception)
		{
			throw $exception;
//			$this->processException($exception);
		}
		catch (\ErrorException $exception)
		{
			throw $exception;
//			$this->processException($exception);
		}

		return null;
	}

	/**
	 * Returns list of method params.
	 * @return array
	 */
	final public function getMethodParams()
	{
		if ($this->methodParams === null)
		{
			$this->bindParams();
		}

		return $this->methodParams;
	}

	/**
	 * Sets list of method params.
	 * @param array $params List of parameters.
	 *
	 * @return $this
	 */
	final public function setMethodParams(array $params)
	{
		$this->methodParams = $params;
		$this->args = array_values($params);

		return $this;
	}

	/**
	 * Returns list of method params which possible use in call_user_func_array().
	 * @return array
	 */
	final public function getArgs()
	{
		if ($this->args === null)
		{
			$this->bindParams();
		}

		return $this->args;
	}

	private function bindParams()
	{
		$this->args = $this->methodParams = [];

		foreach ($this->reflectionFunctionAbstract->getParameters() as $param)
		{
			$value = $this->getParameterValue($param);
			$this->args[] = $this->methodParams[$param->getName()] = $value;
		}

		return $this->args;
	}

	/**
	 * @param \ReflectionParameter $reflectionParameter
	 *
	 * @return \SplPriorityQueue|Parameter[]
	 */
	private function getAutoWiredByClass(\ReflectionParameter $reflectionParameter)
	{
		$result = new \SplPriorityQueue();
		foreach ($this->getAllAutoWiredParameters() as $parameter)
		{
			if ($parameter->match($reflectionParameter))
			{
				$result->insert($parameter, $this->getPriorityByParameter($parameter));
			}
		}

		return $result;
	}

	/**
	 * @return Parameter[]
	 */
	private function getAllAutoWiredParameters()
	{
		$list = $this->getAutoWiredParameters();
		foreach (static::$globalAutoWiredParameters as $globalAutoWiredParameter)
		{
			$list[] = $globalAutoWiredParameter;
		}

		return $list;
	}

	private function getParameterValue(\ReflectionParameter $parameter)
	{
		$sourceParameters = $this->getSourcesParametersToMap();
		if ($parameter->getClass())
		{
			foreach ($this->getAutoWiredByClass($parameter) as $autoWireParameter)
			{
				$result = $autoWireParameter->captureData($parameter, $sourceParameters);
				if (!$result->isSuccess())
				{
					continue;
				}

				return $autoWireParameter->constructValue($parameter, $result);
			}

			if ($parameter->isDefaultValueAvailable())
			{
				return $parameter->getDefaultValue();
			}

			$exceptionMessage = "Could not find value for parameter to build auto wired argument {{$parameter->getClass()->getName()} \${$parameter->getName()}}";
			if ($result !== null && $result->getErrorMessages())
			{
				$exceptionMessage = $result->getErrorMessages()[0];
			}

			throw new ArgumentException(
				$exceptionMessage,
				$parameter
			);
		}

		$value = $this->findParameterInSourceList($parameter->getName(), $status);
		if ($status === self::STATUS_NOT_FOUND)
		{
			if ($parameter->isDefaultValueAvailable())
			{
				$value = $parameter->getDefaultValue();
			}
			else
			{
				throw new ArgumentException(
					"Could not find value for parameter {{$parameter->getName()}}",
					$parameter
				);
			}
		}

		if ($parameter->isArray())
		{
			$value = (array)$value;
		}

		return $value;
	}

	private function findParameterInSourceList($name, &$status)
	{
		$status = self::STATUS_FOUND;
		foreach ($this->getSourcesParametersToMap() as $source)
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

		$status = self::STATUS_NOT_FOUND;

		return null;
	}
}