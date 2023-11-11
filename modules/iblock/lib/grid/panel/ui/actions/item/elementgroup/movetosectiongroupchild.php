<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup;

use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\Helpers\SectionSelectControl;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Error;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;

/**
 * @see \Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem lang messages are loaded from there.
 */
final class MoveToSectionGroupChild extends BaseGroupChild
{
	use SectionSelectControl;

	public static function getId(): string
	{
		return ActionType::MOVE_TO_SECTION;
	}

	public function getName(): string
	{
		return Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ELEMENT_GROUP_MOVE_TO_SECTION_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows): ?Result
	{
		$result = new Result();

		$controls = $request->getPost('controls');
		if (!is_array($controls))
		{
			return $result;
		}

		$sectionId = (int)($controls['section_id'] ?? 0);
		if ($sectionId <= 0)
		{
			return $result;
		}

		if ($isSelectedAllRows)
		{
			$result->addErrors(
				$this->moveElementsToSection($sectionId, true, [])->getErrors()
			);
			$result->addErrors(
				$this->moveSectionsToSection($sectionId, true, [])->getErrors()
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
					$this->moveElementsToSection($sectionId, false, $elementIds)->getErrors()
				);
			}

			if ($sectionIds)
			{
				$result->addErrors(
					$this->moveSectionsToSection($sectionId, false, $sectionIds)->getErrors()
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
					$this->getSectionSelectControl(true),
					(new Snippet)->getSendSelectedButton(),
				],
			],
		]);
	}

	private function moveElementsToSection(int $sectionId, bool $isSelectedAllRows, array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockElement();

		if (!$this->getIblockRightsChecker()->canBindElementToSection($sectionId))
		{
			$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_MOVE_TO_SECTION_GROUP_CHILD_ACCESS_DENIED_BIND_ELEMENT', [
				'#ID#' => $sectionId,
			]);
			$result->addError(
				new Error($message)
			);

			return $result;
		}

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
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_MOVE_TO_SECTION_GROUP_CHILD_ACCESS_DENIED_EDIT_ELEMENT', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$fields = [
				'IBLOCK_SECTION_ID' => $sectionId,
				'IBLOCK_SECTION' => [
					$sectionId,
				],
			];
			$updateResult = $entity->Update($id, $fields);
			if (!$updateResult)
			{
				if ($entity->LAST_ERROR)
				{
					$result->addError(
						new Error($entity->LAST_ERROR)
					);
				}
			}
			else
			{
				$ipropValues = new ElementValues($this->getIblockId(), $id);
				$ipropValues->clearValues();
			}
		}

		return $result;
	}

	private function moveSectionsToSection(int $sectionId, bool $isSelectedAllRows, array $ids): Result
	{
		$result = new Result();
		$entity = new CIBlockSection();

		if (!$this->getIblockRightsChecker()->canBindElementToSection($sectionId))
		{
			$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_MOVE_TO_SECTION_GROUP_CHILD_ACCESS_DENIED_BIND_ELEMENT', [
				'#ID#' => $sectionId,
			]);
			$result->addError(
				new Error($message)
			);

			return $result;
		}

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
			]
		);
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];

			if (!$this->getIblockRightsChecker()->canEditElement($id))
			{
				$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_MOVE_TO_SECTION_GROUP_CHILD_ACCESS_DENIED_EDIT_ELEMENT', [
					'#ID#' => $id,
				]);
				$result->addError(
					new Error($message)
				);

				continue;
			}

			$fields = [
				'IBLOCK_SECTION_ID' => $sectionId,
			];
			$updateResult = $entity->Update($id, $fields);
			if (!$updateResult)
			{
				if ($entity->LAST_ERROR)
				{
					$result->addError(
						new Error($entity->LAST_ERROR)
					);
				}
			}
			else
			{
				$ipropValues = new ElementValues($this->getIblockId(), $id);
				$ipropValues->clearValues();
			}
		}

		return $result;
	}
}
