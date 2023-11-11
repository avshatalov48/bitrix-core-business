<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
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
	public static function getId(): string
	{
		return ActionType::CLEAR_COUNTER;
	}

	public function getName(): string
	{
		return Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_CLEAR_COUNTER_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows): ?Result
	{
		$result = new Result();

		if ($isSelectedAllRows)
		{
			$result->addErrors(
				$this->processClearCounterElements(true, [])->getErrors()
			);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return null;
			}

			[$elementIds,] = RowType::parseIndexList($ids);

			if ($elementIds)
			{
				$result->addErrors(
					$this->processClearCounterElements(false, $elementIds)->getErrors()
				);
			}
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

	private function processClearCounterElements(bool $isSelectedAllRows, array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();

		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
		];
		if (!$isSelectedAllRows)
		{
			$filter['ID'] = $ids;
		}

		$rows = CIBlockElement::GetList(
			[],
			$filter + ['CHECK_PERMISSIONS' => 'N'],
			false,
			false,
			[
				'ID',
			]
		);
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];

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
			if (!$updateResult && $entity->LAST_ERROR)
			{
				$result->addError(
					new Error($entity->LAST_ERROR)
				);
			}
		}

		return $result;
	}
}
