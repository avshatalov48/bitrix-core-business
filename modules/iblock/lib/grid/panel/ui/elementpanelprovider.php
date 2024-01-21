<?php

namespace Bitrix\Iblock\Grid\Panel\UI;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\Entity\ElementSettings;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\EditActionsItem;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\RemoveActionsItem;
use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Panel\Action\ForAllCheckboxAction;
use Bitrix\Main\Grid\Panel\Action\DataProvider;

/**
 * Action panel for ui grid. If need action panel for admin grid see `GroupAction`.
 *
 * @see \Bitrix\Iblock\Grid\Panel\GroupAction
 *
 * @method ElementSettings getSettings()
 */
class ElementPanelProvider extends DataProvider
{
	private Columns $columns;
	private IblockRightsChecker $rights;

	public function __construct(
		ElementSettings $settings,
		Columns $columns,
		IblockRightsChecker $rights
	)
	{
		parent::__construct($settings);

		$this->columns = $columns;
		$this->rights = $rights;
	}

	final protected function getIblockId(): int
	{
		return $this->getSettings()->getIblockId();
	}

	final protected function getListMode(): string
	{
		return $this->getSettings()->getListMode();
	}

	final protected function getIblockRightsChecker(): IblockRightsChecker
	{
		return $this->rights;
	}

	final protected function getColumns(): Columns
	{
		return $this->columns;
	}

	public function prepareActions(): array
	{
		$result = [];

		if ($this->rights->canEditElements())
		{
			$result[] = new EditActionsItem($this->getIblockId(), $this->columns, $this->rights);
		}

		if ($this->rights->canDeleteElements())
		{
			$result[] = new RemoveActionsItem($this->getIblockId(), $this->rights, $this->getListMode());
		}

		$result[] = new ElementGroupActionsItem($this->getIblockId(), $this->rights, $this->getListMode());

		if (!empty($result))
		{
			$result[] = new ForAllCheckboxAction();
		}

		return $result;
	}
}
