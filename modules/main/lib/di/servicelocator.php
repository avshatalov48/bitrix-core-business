<?php

namespace Bitrix\Main\DI;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\DI\Exception\ServiceNotFoundException;
use Bitrix\Main\DI\Exception\CircularDependencyException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

final class ServiceLocator implements ContainerInterface
{
	/** @var string[][] */
	private array $services = [];
	private array $instantiated = [];
	private static ServiceLocator $instance;

	private array $callStack = [];

	private function __construct()
	{}

	private function __clone()
	{}

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds service to locator.
	 * @param string $code
	 * @param mixed $service
	 */
	public function addInstance(string $code, mixed $service): void
	{
		$this->instantiated[$code] = $service;
	}

	/**
	 * Adds service with lazy initialization.
	 * @param string $id
	 * @param array $configuration
	 * @return void
	 * @throws SystemException
	 */
	public function addInstanceLazy(string $id, $configuration): void
	{
		if (!isset($configuration['className']) && !isset($configuration['constructor']))
		{
			throw $this->buildBadRegistrationExceptions($id);
		}

		$furtherClassMetadata = $configuration['className'] ?? $configuration['constructor'];

		$this->services[$id] = [$furtherClassMetadata, $configuration['constructorParams'] ?? []];
	}

	/**
	 * Registers services by module settings, which is stored in {moduleName}/.settings.php.
	 * @param string $moduleName
	 * @throws SystemException
	 */
	public function registerByModuleSettings(string $moduleName): void
	{
		$configuration = Configuration::getInstance($moduleName);
		$services = $configuration['services'] ?? [];
		foreach ($services as $code => $config)
		{
			if ($this->has($code))
			{
				//It means that there is overridden service in global .setting.php or extra settings.
				//Or probably service was registered manually.
				continue;
			}

			$this->addInstanceLazy($code, $config);
		}
	}

	/**
	 * Registers services by project settings, which is stored .settings.php.
	 * @throws SystemException
	 */
	public function registerByGlobalSettings(): void
	{
		$configuration = Configuration::getInstance();
		$services = $configuration['services'] ?? [];
		foreach ($services as $code => $config)
		{
			$this->addInstanceLazy($code, $config);
		}
	}

	/**
	 * Checks whether the service with code exists.
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id): bool
	{
		return isset($this->services[$id]) || isset($this->instantiated[$id]);
	}

	/**
	 * Returns services by code.
	 *
	 * @param string $id
	 *
	 * @return mixed
	 * @throws ObjectNotFoundException|NotFoundExceptionInterface|ServiceNotFoundException|CircularDependencyException
	 */
	public function get(string $id): mixed
	{
		if (isset($this->instantiated[$id]))
		{
			return $this->instantiated[$id];
		}

		if ($this->isInterfaceKey($id) || $this->isAbstractClassKey($id))
		{
			$object = $this->resolveInterfaceOrAbstractClass($id);
		}
		elseif ($this->isClassKey($id))
		{
			$object = $this->resolveClass($id);
		}
		else
		{
			$object = $this->createItemByServiceName($id);
		}

		$this->instantiated[$id] = $object;

		return $object;
	}

	private function buildNotFoundException(string $msg): ObjectNotFoundException|NotFoundExceptionInterface
	{
		return new class($msg) extends ObjectNotFoundException
			implements NotFoundExceptionInterface {}
		;
	}

	private function buildBadRegistrationExceptions(string $id): SystemException|ContainerExceptionInterface
	{
		$message =
			"Could not register service {{$id}}." .
			"There is no {className} to find class or {constructor} to build instance."
		;

		return new class($message) extends SystemException implements ContainerExceptionInterface {};
	}

	private function resolveInterfaceOrAbstractClass(string $id): object
	{
		[$classOrClosure, $args] = $this->services[$id];
		if ($classOrClosure instanceof Closure)
		{
			return $classOrClosure(...(is_array($args) ? $args : []));
		}

		return $this->createItemByClassName($classOrClosure);
	}

	private function resolveClass(string $id): object
	{
		if (!class_exists($id))
		{
			throw $this->buildNotFoundException("Could not find service by code {$id}.");
		}
		return $this->createItemByClassName($id);
	}

	private function createItemByClassName(string $className): object
	{
		try
		{
			return $this->createObjectWithFullConstruct($className);
		}
		catch (ReflectionException $exception)
		{
			throw new ServiceNotFoundException(
				$exception->getMessage()
			);
		}
	}

	/**
	 * Returns object from service config
	 */
	private function createItemByServiceName(string $serviceName): object
	{
		[$class, $args] = $this->services[$serviceName];

		if ($class instanceof Closure)
		{
			return $class();
		}

		if ($args instanceof Closure)
		{
			$args = $args();
		}

		return new $class(...array_values($args));
	}

	private function checkCircularDependency(string $className): void
	{
		if ($this->isCallStacked($className))
		{
			$path = implode(' -> ', $this->callStack) . " -> $className";

			throw new CircularDependencyException(
				'Cyclic dependency detected for service: ' . $path
			);
		}

		$this->addCallStack($className);
	}

	private function getConstructorParams(ReflectionClass $class): array
	{
		$constructor = $class->getConstructor();
		if ($constructor !== null && !$constructor->isPublic())
		{
			throw new ServiceNotFoundException(
				$class->getName() . ' constructor must be is public'
			);
		}

		return $constructor?->getParameters() ?? [];
	}

	private function resolveConstructorDependencies(array $params, string $className): array
	{
		if (empty($params))
		{
			return [];
		}

		$paramsForClass = [];
		foreach ($params as $param)
		{
			$type = $param->getType();
			if (!$type instanceof ReflectionNamedType)
			{
				throw new ServiceNotFoundException(
					$className . ' All parameters in the constructor must have real class type'
				);
			}

			$classNameInParams = $type->getName();
			if (!class_exists($classNameInParams) && !interface_exists($classNameInParams))
			{
				throw new ServiceNotFoundException(
					"For {$className} error in params: {$classNameInParams} must be an existing class, interface or abstract class"
				);
			}

			$paramsForClass[] = $this->get($classNameInParams);
		}

		return $paramsForClass;
	}

	private function createInstance(ReflectionClass $class, array $paramsForClass, string $className): object
	{
		$object = $class->newInstanceArgs($paramsForClass);
		if (empty($object))
		{
			throw new ServiceNotFoundException(
				'Failed to create component ' . $className
			);
		}

		return $object;
	}

	private function createObjectWithFullConstruct(string $className): object
	{
		try
		{
			$this->checkCircularDependency($className);

			$class = new ReflectionClass($className);
			$params = $this->getConstructorParams($class);
			if (empty($params))
			{
				return new $className();
			}

			$paramsForClass = $this->resolveConstructorDependencies($params, $className);

			return $this->createInstance($class, $paramsForClass, $className);
		}
		finally
		{
			$this->popCallStack();
		}
	}

	private function addCallStack(string $className): void
	{
		$this->callStack[] = $className;
	}

	private function popCallStack(): void
	{
		array_pop($this->callStack);
	}

	private function isCallStacked(string $className): bool
	{
		return in_array($className, $this->callStack, true);
	}

	private function isInterfaceKey(string $id): bool
	{
		return interface_exists($id) && isset($this->services[$id]);
	}

	private function isAbstractClassKey(string $id): bool
	{
		return isset($this->services[$id]) && class_exists($id) && (new ReflectionClass($id))->isAbstract();
	}

	private function isClassKey(string $id): bool
	{
		return !isset($this->services[$id]);
	}
}
