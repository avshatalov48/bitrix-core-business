<?php

namespace Bitrix\Main\DI;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\DI\Exception\ServiceNotFoundException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ServiceLocator implements \Psr\Container\ContainerInterface
{
	/** @var string[][] */
	private array $services = [];
	private array $instantiated = [];
	private static ServiceLocator $instance;

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
	 * @return mixed
	 * @throws ObjectNotFoundException|NotFoundExceptionInterface
	 */
	public function get(string $id)
	{
		if (isset($this->instantiated[$id]))
		{
			return $this->instantiated[$id];
		}

		if (!isset($this->services[$id]))
		{
			if (!class_exists($id))
			{
				throw $this->buildNotFoundException("Could not find service by code {$id}.");
			}

			$object = $this->createItemByClassName($id);
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

	/**
	 * Create object by className with all dependencies on construct
	 *
	 * @param string $className
	 * @return object|mixed|string
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 */
	private function createItemByClassName(string $className): object
	{
		try
		{
			return $this->createObjectWithFullConstruct($className);
		}
		catch (\ReflectionException $exception)
		{
			throw new ServiceNotFoundException(
				$exception->getMessage()
			);
		}
	}

	/**
	 * Returns object from service config
	 */
	private function createItemByServiceName(string $serviceName): mixed
	{
		[$class, $args] = $this->services[$serviceName];

		if ($class instanceof \Closure)
		{
			return $class();
		}

		if ($args instanceof \Closure)
		{
			$args = $args();
		}

		return new $class(...array_values($args));
	}

	/**
	 * Returns object with dependencies on construct and save all dependencies and this object in container
	 *
	 * @param string $className
	 * @return object|mixed|string
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 * @throws \ReflectionException
	 */
	private function createObjectWithFullConstruct(string $className): object
	{
		$class = new \ReflectionClass($className);

		$constructor = $class->getConstructor();
		if (!empty($constructor) && !$constructor->isPublic())
		{
			throw new ServiceNotFoundException(
				$className . ' constructor must be is public'
			);
		}

		$params = $constructor?->getParameters();

		if (empty($params))
		{
			return new $className();
		}

		$paramsForClass = [];
		foreach ($params as $param)
		{
			$type = $param->getType();
			if (empty($type) || ($type instanceof \ReflectionNamedType) === false)
			{
				throw new ServiceNotFoundException(
					$className . ' All parameters in the constructor must have real class type'
				);
			}

			$classNameInParams = $type->getName();
			if (!class_exists($classNameInParams))
			{
				throw new ServiceNotFoundException(
					"For {$className} error in params: {$classNameInParams} must be an existing class"
				);
			}

			$paramsForClass[] = $this->get($classNameInParams);
		}

		$object = $class->newInstanceArgs($paramsForClass);

		if (empty($object))
		{
			throw new ServiceNotFoundException(
				'Failed to create component ' . $className
			);
		}

		return $object;
	}
}
