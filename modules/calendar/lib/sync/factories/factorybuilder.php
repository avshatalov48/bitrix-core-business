<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Sync;

class FactoryBuilder implements Builder
{
	/**
	 * @var FactoryBase
	 */
	private FactoryBase $factory;

	/**
	 * @param string $accountType
	 * @param Sync\Connection\Connection $connection
	 * @param Sync\Util\Context $context
	 *
	 * @return FactoryBase
	 * @todo an unsupported type may arrive. In this case return null
	 */
	public static function create(
		string $accountType,
		Sync\Connection\Connection $connection,
		Sync\Util\Context $context
	): ?FactoryBase
	{
		return self::getClassName($accountType)
			? (new self($accountType, $connection, $context))->build()
			: null;
	}

	/**
	 * @param string $accountType
	 * @param Sync\Connection\Connection $connection
	 * @param Sync\Util\Context $context
	 *
	 * @throws BaseException
	 */
	public function __construct(
		string $accountType,
		Sync\Connection\Connection $connection,
		Sync\Util\Context $context
	)
	{
		$className = self::getClassName($accountType);
		if (!$className)
		{
			throw new BaseException('Factory for accout type is not found');
		}
		$this->factory = new $className($connection, $context);
	}

	/**
	 * @param string $parentService
	 * @return array
	 */
	public static function getAvailableServices(string $parentService): array
	{
		return array_diff(array_keys(self::getServiceMap()), [$parentService]);
	}

	/**
	 * @return string[]
	 */
	private static function getServiceMap(): array
	{
		return [
			Sync\Google\Factory::SERVICE_NAME => Sync\Google\Factory::class,
			Sync\Office365\Factory::SERVICE_NAME => Sync\Office365\Factory::class,
			Sync\Icloud\Factory::SERVICE_NAME => Sync\Icloud\Factory::class,
		];
	}

	/**
	 * @param string $accountType
	 * @return string|null
	 */
	public static function getClassName(string $accountType): ?string
	{
		return self::getServiceMap()[$accountType] ?? null;
	}

	/**
	 * @return FactoryBase
	 */
	public function build(): FactoryBase
	{
		return $this->factory;
	}

	/**
	 * @param $serviceName
	 * @return bool
	 */
	public static function checkService($serviceName): bool
	{
		return array_key_exists($serviceName, self::getServiceMap());
	}
}
