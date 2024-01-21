<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;

trait ChangeActiveHandler
{
	use ItemFinder;

	abstract protected function getIblockRightsChecker(): IblockRightsChecker;

	protected function processSetActive(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter, bool $isActivate): ?Result
	{
		$result = new Result();

		[$elementIds, $sectionIds] = $this->prepareItemIds($request, $isSelectedAllRows, $filter);

		if ($elementIds)
		{
			$result->addErrors(
				$this->processSetActiveElements($elementIds, $isActivate)->getErrors()
			);
		}

		if ($sectionIds)
		{
			$result->addErrors(
				$this->processSetActiveSections($sectionIds, $isActivate)->getErrors()
			);
		}

		return $result;
	}

	private function processSetActiveElements(array $ids, bool $isActivate): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();

		foreach ($ids as $id)
		{
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
			if (!$updateResult && $entity->getLastError())
			{
				$result->addError(
					new Error($entity->getLastError())
				);
			}
		}

		return $result;
	}

	private function processSetActiveSections(array $ids, bool $isActivate): Result
	{
		$result = new Result();
		$entity = new CIBlockSection();

		foreach ($ids as $id)
		{
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
