<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;
use CMain;

final class RemoveActionsItem extends \Bitrix\Main\Grid\Panel\Action\RemoveAction
{
	private int $iblockId;
	private IblockRightsChecker $rights;

	public static function getId(): string
	{
		return ActionType::DELETE;
	}

	public function __construct(int $iblockId, IblockRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows): ?Result
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$result = new Result();

		if ($isSelectedAllRows)
		{
			$ids = [];

			$rows = CIBlockElement::GetList(
				[],
				[
					'IBLOCK_ID' => $this->iblockId,
					'CHECK_PERMISSIONS' => 'N',
				],
				false,
				false,
				[
					'ID',
				]
			);
			while ($row = $rows->Fetch())
			{
				$ids[] = RowType::getIndex(
					RowType::ELEMENT,
					(int)$row['ID']
				);
			}

			$rows = CIBlockSection::GetList(
				[],
				[
					'IBLOCK_ID' => $this->iblockId,
					'CHECK_PERMISSIONS' => 'N',
				],
				false,
				[
					'ID',
				]
			);
			while ($row = $rows->Fetch())
			{
				$ids[] = RowType::getIndex(
					RowType::SECTION,
					(int)$row['ID']
				);
			}
		}
		else
		{
			$ids = $request->getPost('ID');
		}

		if (!is_array($ids) || empty($ids))
		{
			return $result;
		}

		foreach ($ids as $id)
		{
			[$type, $id] = RowType::parseIndex($id);

			if ($type === RowType::ELEMENT)
			{
				if (!$this->rights->canDeleteElement($id))
				{
					$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_REMOVE_ACTIONS_ITEM_ACCESS_DENIED', [
						'#ID#' => $id,
					]);
					$result->addError(
						new Error($message)
					);

					continue;
				}

				$deleteResult = CIBlockElement::Delete($id);
			}
			else
			{
				if (!$this->rights->canDeleteSection($id))
				{
					$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_REMOVE_ACTIONS_ITEM_ACCESS_DENIED', [
						'#ID#' => $id,
					]);
					$result->addError(
						new Error($message)
					);

					continue;
				}

				$deleteResult = CIBlockSection::Delete($id);
			}

			if (!$deleteResult)
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
				{
					$result->addError(
						new Error($ex->GetString())
					);
				}
				else
				{
					$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ITEM_REMOVE_ERROR_DELETE', [
						'#ID#' => $id,
					]);
					$result->addError(
						new Error($message)
					);
				}
			}
		}

		return $result;
	}
}
