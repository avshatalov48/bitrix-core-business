<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\Grid\Helpers\CodeTranslator;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;
use CUtil;

final class CreateCodeItem extends BaseItem
{
	use CodeTranslator;

	public static function getId(): string
	{
		return 'code_translit';
	}

	protected function getText(): string
	{
		return Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_NAME_MSGVER_1');
	}

	public function getControl(array $rawFields): ?array
	{
		if (empty($rawFields['ID']))
		{
			return null;
		}

		$rowId = (int)$rawFields['ID'];
		$rowType = (string)($rawFields['ROW_TYPE'] ?? RowType::ELEMENT);

		if ($rowType === RowType::SECTION)
		{
			if (
				empty($this->getSectionTranslitSettings())
				|| !$this->getIblockRightsChecker()->canEditSection($rowId)
			)
			{
				return null;
			}
		}
		elseif ($rowType === RowType::ELEMENT)
		{
			if (
				empty($this->getElementTranslitSettings())
				|| !$this->getIblockRightsChecker()->canEditElement($rowId)
			)
			{
				return null;
			}
		}
		else
		{
			// unknown type
			return null;
		}

		$actionId = self::getId();
		$data = CUtil::PhpToJSObject([
			'id' => RowType::getIndex($rowType, $rowId),
		]);

		if ($rowType === RowType::SECTION)
		{
			$confirmMessageTitle = \CUtil::JSEscape(Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_SECTION_CONFIRM_TITLE'));
			$confirmMessageContent = \CUtil::JSEscape(Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_SECTION_CONFIRM_CONTENT'));
		}
		else
		{
			$confirmMessageTitle = \CUtil::JSEscape(Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_ELEMENT_CONFIRM_TITLE'));
			$confirmMessageContent = \CUtil::JSEscape(Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_ELEMENT_CONFIRM_CONTENT'));
		}

		$confirmButtonMessage = \CUtil::JSEscape(
			Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_CONFIRM_BUTTON')
		);
		$backButtonMessage = \CUtil::JSEscape(
			Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_BACK_BUTTON')
		);

		$this->onclick = "IblockGridInstance.sendMediumPopupWithConfirm("
		. "'{$actionId}', "
		. "{$data}, "
		. "'{$confirmMessageTitle}', "
		. "'{$confirmMessageContent}', "
		. "'{$confirmButtonMessage}', "
		. "'{$backButtonMessage}')";

		return parent::getControl($rawFields);
	}

	public function processRequest(HttpRequest $request): ?Result
	{
		$result = new Result();

		$rowIndex = (string)$request->get('id');
		if (empty($rowIndex))
		{
			return $result;
		}

		$index = RowType::parseIndex($rowIndex);
		if ($index === null)
		{
			return $result;
		}
		[$type, $id] = $index;

		if ($type === RowType::SECTION)
		{
			$result->addErrors(
				$this->processCodeTranslitSection($id)->getErrors()
			);
		}
		elseif ($type === RowType::ELEMENT)
		{
			$result->addErrors(
				$this->processCodeTranslitElement($id)->getErrors()
			);
		}

		return $result;
	}

	/**
	 * @param int $id
	 *
	 * @return Result
	 */
	private function processCodeTranslitElement(int $id): Result
	{
		$result = new Result();

		if (!$this->getIblockRightsChecker()->canEditElement($id))
		{
			$message = Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_ERROR_ACCESS_DENIED_ELEMENT', [
				'#ID#' => $id,
			]);
			$result->addError(
				new Error($message)
			);

			return $result;
		}

		$row = ElementTable::getRow([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'=ID' => $id,
				'=IBLOCK_ID' => $this->getIblockId(),
			],
		]);
		if (empty($row))
		{
			return $result;
		}

		$entity = new CIBlockElement();
		$translitSettings = $this->getElementTranslitSettings();

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

		return $result;
	}

	/**
	 * @param int $id
	 *
	 * @return Result
	 */
	private function processCodeTranslitSection(int $id): Result
	{
		$result = new Result();

		if (!$this->getIblockRightsChecker()->canEditSection($id))
		{
			$message = Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CREATE_CODE_ERROR_ACCESS_DENIED_SECTION', [
				'#ID#' => $id,
			]);
			$result->addError(
				new Error($message)
			);

			return $result;
		}

		$row = SectionTable::getRow([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'=ID' => $id,
				'=IBLOCK_ID' => $this->getIblockId(),
			],
		]);
		if (empty($row))
		{
			return $result;
		}

		$entity = new CIBlockSection();
		$translitSettings = $this->getSectionTranslitSettings();

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

		return $result;
	}
}
