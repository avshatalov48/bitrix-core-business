<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Action\Group\GroupChildAction;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

abstract class GroupAction implements Action
{
	/**
	 * @var GroupChildAction[]
	 */
	private array $items;

	/**
	 * @inheritDoc
	 *
	 * @return string
	 */
	final public static function getId(): string
	{
		return 'group_action';
	}

	/**
	 * @return GroupChildAction[]
	 */
	abstract protected function prepareChildItems(): array;

	/**
	 * Group child items.
	 *
	 * @return GroupChildAction[]
	 */
	private function getChildItems(): array
	{
		$this->items ??= $this->prepareChildItems();

		return $this->items;
	}

	/**
	 * @inheritDoc
	 */
	public function getControl(): ?array
	{
		$items = $this->getGroupDropdownItems();
		if (empty($items))
		{
			return null;
		}

		return [
			'TYPE' => Types::DROPDOWN,
			'ID' => static::getId(),
			'NAME' => static::getId(),
			'ITEMS' => [
				[
					'NAME' => Loc::getMessage('MAIN_GRID_PANEL_GROUP_ACTIONS_ITEM_PLACEHOLDER'),
					'VALUE' => 'default',
					'ONCHANGE' => [
						[
							'ACTION' => Actions::RESET_CONTROLS,
						],
					],
				],
				... $items,
			],
		];
	}

	/**
	 * Dropdown with child items.
	 *
	 * @return array[]
	 */
	private function getGroupDropdownItems(): array
	{
		$result = [];

		foreach ($this->getChildItems() as $item)
		{
			$result[] = $item->getDropdownItem();
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	final public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result
	{
		$controls = $request->getPost('controls');
		$itemId = (string)($controls[static::getId()] ?? '');
		if (empty($itemId))
		{
			return null;
		}

		foreach ($this->getChildItems() as $item)
		{
			if ($item::getId() === $itemId)
			{
				return $item->processRequest($request, $isSelectedAllRows, $filter);
			}
		}

		return null;
	}
}
