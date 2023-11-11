<?php

namespace Bitrix\Main\DI;

use Bitrix\Main\Config\Configuration;
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
			throw $this->buildNotFoundException($id);
		}

		[$class, $args] = $this->services[$id];

		if ($class instanceof \Closure)
		{
			$object = $class();
		}
		else
		{
			if ($args instanceof \Closure)
			{
				$args = $args();
			}
			$object = new $class(...array_values($args));
		}

		$this->instantiated[$id] = $object;

		return $object;
	}

	private function buildNotFoundException(string $id): ObjectNotFoundException|NotFoundExceptionInterface
	{
		return new class("Could not find service by code {$id}.") extends ObjectNotFoundException
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
}
