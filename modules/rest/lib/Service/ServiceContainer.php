<?php

declare(strict_types=1);

namespace Bitrix\Rest\Service;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Rest\Repository\AppRepository;
use Bitrix\Rest\Repository\IntegrationRepository;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Bitrix\Rest\Contract;

class ServiceContainer implements ContainerInterface
{
	private static ServiceContainer $instance;
	private ServiceLocator $serviceLocator;
	private string $prefix;

	private function __construct()
	{
		$this->serviceLocator = ServiceLocator::getInstance();
		$this->prefix = 'rest.service.';
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

	public function getAPAuthPasswordService(): Contract\Service\APAuth\PasswordService
	{
		return $this->get('apauth.password');
	}

	public function getAppService(): Contract\Service\AppService
	{
		return $this->get('app');
	}

	public function getIntegrationService(): Contract\Service\IntegrationService
	{
		return $this->get('integration');
	}

	public function getAPAuthPermissionService(): Contract\Service\APAuth\PermissionService
	{
		return $this->get('apauth.permission');
	}
}