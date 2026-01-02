<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Broker\BrokerInterface;
use Bitrix\Main\Messenger\Internals\Broker\DbBroker;
use Bitrix\Main\Messenger\Internals\Config\QueueConfigRegistry;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessengerMessageTable;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;

/**
 * @internal
 */
class BrokerManager
{
	/**
	 * @var array<string, BrokerInterface>
	 */
	private array $brokers = [];

	/**
	 * @var array<string, array>
	 */
	private array $configs = [];

	/**
	 * @throws ConfigurationException
	 */
	public function __construct()
	{
		$this->loadConfig();
	}

	/**
	 * @throws ConfigurationException
	 */
	private function loadConfig(): void
	{
		$this->loadGlobalConfig();
		$this->loadModulesConfigs();
	}

	/**
	 * @throws ConfigurationException
	 */
	private function loadGlobalConfig(): void
	{
		$config = Configuration::getValue('messenger');

		if (!$config)
		{
			$config = [
				'brokers' => [
					'default' => [
						'type' => DbBroker::TYPE_CODE,
						'params' => [
							'table' => MessengerMessageTable::class,
						]
					]
				],
			];
		}

		if (empty($config['brokers']['default']))
		{
			throw new ConfigurationException('Default broker for messenger did not configured');
		}

		foreach ($config['brokers'] as $name => $broker)
		{
			$this->loadBrokerConfig($name, $broker);
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	private function loadModulesConfigs(): void
	{
		$modules = ModuleManager::getInstalledModules();

		foreach ($modules as $moduleId => $moduleData)
		{
			$settings = Configuration::getInstance($moduleId)->get('messenger');

			if (!empty($settings['brokers']) && is_array($settings['brokers']))
			{
				foreach ($settings['brokers'] as $name => $broker)
				{
					$this->loadBrokerConfig($name, $broker);
				}
			}
		}
	}

	/**
	 * @param string $brokerName
	 * @param array<string, mixed> $brokerConfig
	 *
	 * @throws ConfigurationException
	 */
	private function loadBrokerConfig(string $brokerName, array $brokerConfig): void
	{
		if (empty($brokerConfig['type']) || empty($brokerConfig['params']))
		{
			throw new ConfigurationException('Wrong configuration of broker ' . $brokerName);
		}

		// In future replace to use config of broker types (type => className)
		if (!in_array($brokerConfig['type'], [DbBroker::TYPE_CODE], true))
		{
			throw new ConfigurationException('Unknown broker type ' . $brokerConfig['type']);
		}

		$this->configs[$brokerName] = $brokerConfig;
	}

	/**
	 * @throws ConfigurationException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function getBroker(string $queueId): BrokerInterface
	{
		/** @var QueueConfigRegistry $registry */
		$registry = ServiceLocator::getInstance()->get(QueueConfigRegistry::class);

		$queueConfig = $registry->getQueueConfig($queueId);

		if (!$queueConfig)
		{
			throw new ConfigurationException(sprintf('Queue "%s" was not configured', $queueId));
		}

		$brokerCode = $queueConfig->brokerCode ?? 'default';

		return $this->brokers[$brokerCode] ?? $this->initBroker($brokerCode);
	}

	/**
	 * @throws ConfigurationException
	 */
	public function getDbBrokerTableClass(array $brokerConfig, string $brokerName): string
	{
		$tableClass = $brokerConfig['params']['table'] ?? null;

		if (empty($tableClass))
		{
			throw new ConfigurationException('Table class should be not empty for broker ' . $brokerName);
		}

		if ($tableClass !== MessengerMessageTable::class && !is_subclass_of($tableClass, MessengerMessageTable::class))
		{
			throw new ConfigurationException(
				sprintf('Table class "%s" should be in hierarchy of "%s"', $tableClass, MessengerMessageTable::class)
			);
		}

		return $tableClass;
	}

	/**
	 * @throws ConfigurationException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function initBroker(string $brokerCode): BrokerInterface
	{
		if (!isset($this->configs[$brokerCode]))
		{
			throw new ConfigurationException(sprintf('Broker "%s" did not configured', $brokerCode));
		}

		$brokerConfig = $this->configs[$brokerCode];

		if ($brokerConfig['type'] === DbBroker::TYPE_CODE)
		{
			if (isset($brokerConfig['params']['module']))
			{
				Loader::includeModule($brokerConfig['params']['module']);
			}

			/** @var MessengerMessageTable|string $tableClass */
			$tableClass = $this->getDbBrokerTableClass($brokerConfig, $brokerCode);

			try
			{
				$this->brokers[$brokerCode] = new DbBroker($tableClass::getEntity());

				return $this->brokers[$brokerCode];
			}
			catch (ArgumentException $e)
			{
				throw new ConfigurationException($e->getMessage(), $e);
			}
		}

		throw new ConfigurationException(sprintf('Broker "%s" could not be initialized', $brokerCode));
	}
}
