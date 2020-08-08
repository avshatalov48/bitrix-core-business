<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Catalog\v2\IoC\Dependency;

/**
 * Class BaseIblockElementFactory
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseIblockElementFactory
{
	/** @var \Bitrix\Catalog\v2\IoC\ContainerContract */
	protected $container;
	/** @var \Bitrix\Catalog\v2\Iblock\IblockInfo */
	protected $iblockInfo;

	/**
	 * BaseIblockElementFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 * @param \Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo
	 */
	public function __construct(ContainerContract $container, IblockInfo $iblockInfo)
	{
		$this->container = $container;
		$this->iblockInfo = $iblockInfo;
	}

	/**
	 * @param string $entityClass
	 * @return \Bitrix\Catalog\v2\BaseIblockElementEntity
	 */
	abstract public function createEntity(string $entityClass): BaseIblockElementEntity;

	protected function makeEntity(string $entityClass): BaseIblockElementEntity
	{
		return $this->container->make($entityClass, [
			Dependency::IBLOCK_INFO => $this->iblockInfo,
		]);
	}
}