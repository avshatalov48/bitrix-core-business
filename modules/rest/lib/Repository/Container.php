<?php

declare(strict_types=1);

namespace Bitrix\Rest\Repository;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;
use Psr\Container\NotFoundExceptionInterface;

class Container
{
	private static Container $instance;
	private ServiceLocator $serviceLocator;
	private string $prefix;

	private function __construct()
	{
		$this->serviceLocator = ServiceLocator::getInstance();
		$this->prefix = 'rest.repository.';
	}

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

	public function has(string $id): bool
	{
		return $this->serviceLocator->has($this->prefix . $id);
	}

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 */
	public function get(string $id): mixed
	{
		return $this->serviceLocator->get($this->prefix . $id);
	}

	public function getApplicationRepository(): AppRepository
	{
		return $this->get('app');
	}

	public function getIntegrationRepository(): IntegrationRepository
	{
		return $this->get('integration');
	}
}
