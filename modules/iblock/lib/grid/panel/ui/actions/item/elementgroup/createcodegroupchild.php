<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Helpers\CodeTranslator;
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
	use ItemFinder;

	public static function getId(): string
	{
		return ActionType::CODE_TRANSLIT;
	}

	public function getName(): string
	{
		return Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_CREATE_CODE_NAME_MSGVER_1');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): ?Result
	{
		$result = new Result();

		$select = [
			'ID',
			'NAME',
		];

		if ($isSelectedAllRows)
		{
			[$elements, $sections] = $this->getItemsByFilter($select, $filter);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return null;
			}

			[$elementIds, $sectionIds] = RowType::parseIndexList($ids);

			$elements = $this->getElementsByIdList($select, $elementIds);
			$sections = $this->getSectionsByIdList($select, $sectionIds);

			unset($elementIds, $sectionIds);
		}

		if ($elements)
		{
			$result->addErrors(
				$this->processCodeTranslitElements($elements)->getErrors()
			);
		}

		if ($sections)
		{
			$result->addErrors(
				$this->processCodeTranslitSections($sections)->getErrors()
			);
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

	private function processCodeTranslitElements(array $elements): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();
		$translitSettings = $this->getElementTranslitSettings();

		foreach ($elements as $row)
		{
			$id = $row['ID'];

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
			if (!$updateResult && $entity->getLastError())
			{
				$result->addError(
					new Error($entity->getLastError())
				);
			}
		}

		return $result;
	}

	private function processCodeTranslitSections(array $sections): Result
	{
		$result = new Result();
		$entity = new CIBlockSection();
		$translitSettings = $this->getSectionTranslitSettings();

		foreach ($sections as $row)
		{
			$id = $row['ID'];

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
