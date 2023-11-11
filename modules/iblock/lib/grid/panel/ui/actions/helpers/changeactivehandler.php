<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;

trait ChangeActiveHandler
{
	abstract protected function getIblockId(): int;

	abstract protected function getIblockRightsChecker(): IblockRightsChecker;

	protected function processSetActive(HttpRequest $request, bool $isSelectedAllRows, bool $isActivate): ?Result
	{
		$result = new Result();

		if ($isSelectedAllRows)
		{
			$result->addErrors(
				$this->processSetActiveElements(true, [], $isActivate)->getErrors()
			);
			$result->addErrors(
				$this->processSetActiveSections(true, [], $isActivate)->getErrors()
			);
		}
		else
		{
			$elementIds = [];
			$sectionIds = [];

			$ids = $request->getPost('rows');
			if (empty($ids) || !is_array($ids))
			{
				return null;
			}

			foreach ($ids as $id)
			{
				[$type, $id] = RowType::parseIndex($id);

				if ($type === RowType::ELEMENT)
				{
					$elementIds[] = $id;
				}
				else
				{
					$sectionIds[] = $id;
				}
			}

			if ($elementIds)
			{
				$result->addErrors(
					$this->processSetActiveElements(false, $elementIds, $isActivate)->getErrors()
				);
			}

			if ($sectionIds)
			{
				$result->addErrors(
					$this->processSetActiveSections(false, $sectionIds, $isActivate)->getErrors()
				);
			}
		}

		return $result;
	}

	private function processSetActiveElements(bool $isSelectedAllRows, array $ids, bool $isActivate): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();

		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
			'ACTIVE' => $isActivate ? 'N' : 'Y',
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
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_CHANGE_ACTIVE_HANDLER_ACCESS_DENIED_ELEMENT', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$updateResult = $entity->Update($id, [
				'ACTIVE' => $isActivate ? 'Y' : 'N',
			]);
			if (!$updateResult && $entity->LAST_ERROR)
			{
				$result->addError(
					new Error($entity->LAST_ERROR)
				);
			}
		}

		return $result;
	}

	private function processSetActiveSections(bool $isSelectedAllRows, array $ids, bool $isActivate): Result
	{
		$result = new Result();
		$entity = new CIBlockSection();

		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
			'ACTIVE' => $isActivate ? 'N' : 'Y',
		];
		if (!$isSelectedAllRows)
		{
			$filter['ID'] = $ids;
		}

		$rows = CIBlockSection::GetList(
			[],
			$filter + ['CHECK_PERMISSIONS' => 'N'],
			false,
			[
				'ID',
			]
		);
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];

			if (!$this->getIblockRightsChecker()->canEditSection($id))
			{
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_CHANGE_ACTIVE_HANDLER_ACCESS_DENIED_SECTION', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$updateResult = $entity->Update($id, [
				'ACTIVE' => $isActivate ? 'Y' : 'N',
			]);
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
