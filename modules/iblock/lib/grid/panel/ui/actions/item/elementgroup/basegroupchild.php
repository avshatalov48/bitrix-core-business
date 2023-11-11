<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Main\Grid\Panel\Action\Group\GroupChildAction;

abstract class BaseGroupChild extends GroupChildAction
{
	private int $iblockId;
	private IblockRightsChecker $rights;

	public function __construct(int $iblockId, IblockRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	final protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	final protected function getIblockRightsChecker(): IblockRightsChecker
	{
		return $this->rights;
	}
}
