<?php

namespace Bitrix\Main\DI;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;

final class ServiceLocator implements \Psr\Container\ContainerInterface
{
	/** @var string[][] */
	private $services = [];
	/** @var mixed[] */
	private $instantiated = [];
	/** @var self */
	private static $instance;

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
	public function addInstance(string $code, $service): void
	{
		$this->instantiated[$code] = $service;
	}

	/**
	 * Adds service with lazy initialization.
	 * @param string $code
	 * @param array $configuration
	 * @throws SystemException
	 * @return void
	 */
	public function addInstanceLazy(string $code, $configuration): void
	{
		if (!isset($configuration['className']) && !isset($configuration['constructor']))
		{
			throw $this->buildBadRegistrationExceptions($code);
		}

		$furtherClassMetadata = $configuration['className'] ?? $configuration['constructor'];

		$this->services[$code] = [$furtherClassMetadata, $configuration['constructorParams'] ?? []];
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
	 * @param string $code
	 * @return bool
	 */
	public function has($code): bool
	{
		return isset($this->services[$code]) || isset($this->instantiated[$code]);
	}

	/**
	 * Returns services by code.
	 * @param string $code
	 * @return mixed
	 * @throws ObjectNotFoundException|\Psr\Container\NotFoundExceptionInterface
	 */
	public function get($code)
	{
		if (isset($this->instantiated[$code]))
		{
			return $this->instantiated[$code];
		}

		if (!isset($this->services[$code]))
		{
			throw $this->buildNotFoundException($code);
		}

		[$class, $args] = $this->services[$code];

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

		$this->instantiated[$code] = $object;

		return $object;
	}

	private function buildNotFoundException(string $code)
	{
		return new class("Could not find service by code {$code}.") extends ObjectNotFoundException
			implements \Psr\Container\NotFoundExceptionInterface {}
		;
	}

	private function buildBadRegistrationExceptions(string $code)
	{
		$message =
			"Could not register service {{$code}}." .
			"There is no {className} to find class or {constructor} to build instance."
		;

		return new class($message) extends SystemException implements \Psr\Container\ContainerExceptionInterface {};
	}
}