<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;

abstract class BaseItem extends \Bitrix\Main\Grid\Row\Action\BaseAction
{
	private int $iblockId;
	private IblockRightsChecker $rights;

	public function __construct(int $iblockId, IblockRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	protected function getIblockRightsChecker(): IblockRightsChecker
	{
		return $this->rights;
	}
}
