<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers\ItemFinder;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;
use CMain;

final class RemoveActionsItem extends \Bitrix\Main\Grid\Panel\Action\RemoveAction
{
	use ItemFinder;

	private int $iblockId;
	private IblockRightsChecker $rights;
	private string $listMode;

	public static function getId(): string
	{
		return ActionType::DELETE;
	}

	public function __construct(int $iblockId, IblockRightsChecker $rights, string $listMode)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
		$this->listMode = $listMode;
	}

	final protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	final protected function getListMode(): string
	{
		return $this->listMode;
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): ?Result
	{
		$result = new Result();

		[$elementIds, $sectionIds] = $this->prepareItemIds($request, $isSelectedAllRows, $filter);

		if ($elementIds)
		{
			$result->addErrors(
				$this->removeElements($elementIds)->getErrors()
			);
		}

		if ($sectionIds)
		{
			$result->addErrors(
				$this->removeSections($sectionIds)->getErrors()
			);
		}

		return $result;
	}

	private function removeElements(array $ids): Result
	{
		/**
		 * @var CMain $APPLICATION
		 */
		global $APPLICATION;

		$result = new Result();

		$conn = Application::getConnection();

		foreach ($ids as $id)
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

			$conn->startTransaction();
			$error = '';
			try
			{
				if (!CIBlockElement::Delete($id))
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$error = $ex->GetString();
					}
					else
					{
						$error = Loc::getMessage(
							'IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_DELETE_INTERNAL_ERROR',
							[
								'#ID#' => $id,
							]
						);
					}
					unset($ex);
				}
			}
			catch (SqlQueryException)
			{
				$error = Loc::getMessage(
					'IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_DELETE_INTERNAL_ERROR',
					[
						'#ID#' => $id,
					]
				);
			}
			if ($error === '')
			{
				$conn->commitTransaction();
			}
			else
			{
				$conn->rollbackTransaction();
				$result->addError(new Error($error));
			}
		}

		return $result;
	}

	private function removeSections(array $ids): Result
	{
		/**
		 * @var CMain $APPLICATION
		 */
		global $APPLICATION;

		$result = new Result();

		$conn = Application::getConnection();

		foreach ($ids as $id)
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

			$conn->startTransaction();
			$error = '';
			try
			{
				if (!CIBlockSection::Delete($id))
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$error = $ex->GetString();
					}
					else
					{
						$error = Loc::getMessage(
							'IBLOCK_GRID_PANEL_UI_ACTIONS_SECTION_DELETE_INTERNAL_ERROR',
							[
								'#ID#' => $id,
							]
						);
					}
					unset($ex);
				}
			}
			catch (SqlQueryException)
			{
				$error = Loc::getMessage(
					'IBLOCK_GRID_PANEL_UI_ACTIONS_SECTION_DELETE_INTERNAL_ERROR',
					[
						'#ID#' => $id,
					]
				);
			}
			if ($error === '')
			{
				$conn->commitTransaction();
			}
			else
			{
				$conn->rollbackTransaction();
				$result->addError(new Error($error));
			}
		}

		return $result;
	}
}
