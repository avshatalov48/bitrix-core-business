<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers\ItemFinder;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;

/**
 * @see \Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem lang messages are loaded from there.
 */
final class ClearCounterGroupChild extends BaseGroupChild
{
	use ItemFinder;

	public static function getId(): string
	{
		return ActionType::CLEAR_COUNTER;
	}

	public function getName(): string
	{
		return Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_CLEAR_COUNTER_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): ?Result
	{
		$result = new Result();

		if ($isSelectedAllRows)
		{
			$elementIds = $this->getElementIdsByFilter($filter);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return null;
			}

			[$elementIds,] = RowType::parseIndexList($ids);
			$elementIds = $this->validateElementIds($elementIds);
		}
		if ($elementIds)
		{
			$result->addErrors(
				$this->processClearCounterElements($elementIds)->getErrors()
			);
		}

		return $result;
	}

	protected function getOnchange(): Onchange
	{
		return new Onchange([
			[
				'ACTION' => Actions::RESET_CONTROLS,
			],
			[
				'ACTION' => Actions::CREATE,
				'DATA' => [
					(new Snippet)->getSendSelectedButton(),
				],
			],
		]);
	}

	private function processClearCounterElements(array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();

		foreach ($ids as $id)
		{
			if (!$this->getIblockRightsChecker()->canEditElement($id))
			{
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_ELEMENT_ACTION_PANEL_ERROR_ACCESS_DENIED', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$fields = [
				'SHOW_COUNTER' => false,
				'SHOW_COUNTER_START' => false,
			];
			$updateResult = $entity->Update($id, $fields);
			if (!$updateResult && $entity->getLastError())
			{
				$result->addError(
					new Error($entity->getLastError())
				);
			}
		}

		return $result;
	}
}
