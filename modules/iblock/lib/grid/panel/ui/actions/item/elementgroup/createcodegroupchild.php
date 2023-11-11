<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Helpers\CodeTranslator;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlock;
use CIBlockElement;
use CIBlockSection;
use CUtil;

/**
 * @see \Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem lang messages are loaded from there.
 */
final class CreateCodeGroupChild extends BaseGroupChild
{
	use CodeTranslator;

	public static function getId(): string
	{
		return ActionType::CODE_TRANSLIT;
	}

	public function getName(): string
	{
		return Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_CREATE_CODE_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows): ?Result
	{
		$result = new Result();

		if ($isSelectedAllRows)
		{
			$result->addErrors(
				$this->processCodeTranslitElements(true, [])->getErrors()
			);
			$result->addErrors(
				$this->processCodeTranslitSections(true, [])->getErrors()
			);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return null;
			}

			[$elementIds, $sectionIds] = RowType::parseIndexList($ids);

			if ($elementIds)
			{
				$result->addErrors(
					$this->processCodeTranslitElements(false, $elementIds)->getErrors()
				);
			}

			if ($sectionIds)
			{
				$result->addErrors(
					$this->processCodeTranslitSections(false, $sectionIds)->getErrors()
				);
			}
		}

		return $result;
	}

	protected function getOnchange(): Onchange
	{
		$confirmMessage = Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_CREATE_CODE_CONFIRM');

		return new Onchange([
			[
				'ACTION' => Actions::RESET_CONTROLS,
			],
			[
				'ACTION' => Actions::CREATE,
				'DATA' => [
					(new Snippet)->getSendSelectedButton($confirmMessage),
				],
			],
		]);
	}

	private function processCodeTranslitElements(bool $isSelectedAllRows, array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();
		$translitSettings = $this->getElementTranslitSettings();

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
				'NAME',
			]
		);
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];

			if (!$this->getIblockRightsChecker()->canEditElement($id))
			{
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_CREATE_CODE_GROUP_CHILD_ACCESS_DENIED_ELEMENT', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$fields = [
				'CODE' => CUtil::translit(
					$row['NAME'],
					LANGUAGE_ID,
					$translitSettings
				),
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

	private function processCodeTranslitSections(bool $isSelectedAllRows, array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockSection();
		$translitSettings = $this->getSectionTranslitSettings();

		$filter = [
			'IBLOCK_ID' => $this->getIblockId(),
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
				'NAME',
			]
		);
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];

			if (!$this->getIblockRightsChecker()->canEditSection($id))
			{
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_CREATE_CODE_GROUP_CHILD_ACCESS_DENIED_SECTION', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$fields = [
				'CODE' => CUtil::translit(
					$row['NAME'],
					LANGUAGE_ID,
					$translitSettings
				),
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
