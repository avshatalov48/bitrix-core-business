<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\ActivateGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\AddToSectionGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\ClearCounterGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\CreateCodeGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\DeactivateGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\MoveToSectionGroupChild;
use Bitrix\Main\Grid\Panel\Action\GroupAction;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ElementGroupActionsItem extends GroupAction
{
	protected int $iblockId;
	protected IblockRightsChecker $rights;

	public function __construct(int $iblockId, IblockRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	protected function prepareChildItems(): array
	{
		if ($this->rights->canEditElements())
		{
			return [
				new ActivateGroupChild($this->iblockId, $this->rights),
				new DeactivateGroupChild($this->iblockId, $this->rights),
				new CreateCodeGroupChild($this->iblockId, $this->rights),
				new ClearCounterGroupChild($this->iblockId, $this->rights),
				new MoveToSectionGroupChild($this->iblockId, $this->rights),
				new AddToSectionGroupChild($this->iblockId, $this->rights),
			];
		}

		return [];
	}
}
