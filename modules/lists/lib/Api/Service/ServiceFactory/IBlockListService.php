<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Lists\Api\Data\IBlockService\IBlockElementFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;

final class IBlockListService extends ServiceFactory
{
	private string $iBlockTypeId = '';

	public function setIBlockTypeId(string $iBlockTypeId): self
	{
		$this->iBlockTypeId = $iBlockTypeId;

		return $this;
	}

	public function getInnerIBlockTypeId(): string
	{
		return $this->iBlockTypeId;
	}

	public static function getIBlockTypeId(): string
	{
		return '';
	}

	protected function fillCatalogFilter(IBlockListFilter $filter): void
	{
		$filter->setSite(SITE_ID);
	}

	protected function fillElementListFilter(IBlockElementFilter $filter): void
	{}

	protected function fillElementDetailInfoFilter(IBlockElementFilter $filter): void
	{}
}
