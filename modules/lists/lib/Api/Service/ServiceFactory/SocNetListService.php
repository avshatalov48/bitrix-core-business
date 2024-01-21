<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Lists\Api\Data\IBlockService\IBlockElementFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;
use Bitrix\Main\Config\Option;

final class SocNetListService extends ServiceFactory
{
	private static string $iBlockTypeId = '';
	private int $socNetGroupId;

	public static function getIBlockTypeId(): string
	{
		if (empty(self::$iBlockTypeId))
		{
			self::$iBlockTypeId = Option::get('lists', 'socnet_iblock_type_id');
		}

		return self::$iBlockTypeId;
	}

	public function setSocNetGroupId(int $socNetGroupId): SocNetListService
	{
		if ($socNetGroupId > 0)
		{
			$this->socNetGroupId = $socNetGroupId;
		}

		return $this;
	}

	protected function fillCatalogFilter(IBlockListFilter $filter): void
	{
		$filter
			->setSocNetGroupId($this->socNetGroupId)
			->setCheckPermission(false)
		;
	}

	protected function fillElementListFilter(IBlockElementFilter $filter): void
	{}

	protected function fillElementDetailInfoFilter(IBlockElementFilter $filter): void
	{}
}
