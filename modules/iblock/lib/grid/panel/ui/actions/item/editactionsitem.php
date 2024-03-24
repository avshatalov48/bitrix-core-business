<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Iblock\Grid\ActionType;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Error;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CIBlockSection;

class EditActionsItem extends \Bitrix\Main\Grid\Panel\Action\EditAction
{
	private int $iblockId;
	private Columns $columns;
	private IblockRightsChecker $rights;
	private CIBlockElement $elementEntity;
	private CIBlockSection $sectionEntity;

	public static function getId(): string
	{
		return ActionType::EDIT;
	}

	public function __construct(int $iblockId, Columns $columns, IblockRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->columns = $columns;
		$this->rights = $rights;
	}

	private function getElementEntity(): CIBlockElement
	{
		if (!isset($this->elementEntity))
		{
			$this->elementEntity = new CIBlockElement();
			$this->elementEntity->setIblock($this->getIblockId());
		}

		return $this->elementEntity;
	}

	private function getSectionEntity(): CIBlockSection
	{
		if (!isset($this->sectionEntity))
		{
			$this->sectionEntity = new CIBlockSection();
			$this->sectionEntity->setIblock($this->getIblockId());
		}

		return $this->sectionEntity;
	}

	final protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	final protected function getColumns(): Columns
	{
		return $this->columns;
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result
	{
		$result = new Result();

		$rows = $request->getPost('FIELDS');
		if (!is_array($rows))
		{
			return $result;
		}
		$rows = $this->appendFilesToRows($request, $rows);

		foreach ($rows as $id => $fields)
		{
			$index = RowType::parseIndex($id);
			if ($index === null)
			{
				continue;
			}
			[$type, $id] = $index;

			$fields = $this->getColumns()->prepareEditableColumnsValues($fields);
			if (empty($fields))
			{
				continue;
			}

			if ($type === RowType::ELEMENT)
			{
				if (!$this->rights->canEditElement($id))
				{
					$message = Loc::getMessage('', [
						'#ID#' => $id,
					]);
					$result->addError(
						new Error($message)
					);

					continue;
				}

				$result->addErrors(
					$this->saveElement($id, $fields)->getErrors()
				);
			}
			elseif ($type === RowType::SECTION)
			{
				if (!$this->rights->canEditSection($id))
				{
					$message = Loc::getMessage('IBLOCK_GRID_PANEL_UI_EDIT_ACTIONS_ITEM_ACCESS_DENIED', [
						'#ID#' => $id,
					]);
					$result->addError(
						new Error($message)
					);

					continue;
				}

				$result->addErrors(
					$this->saveSection($id, $fields)->getErrors()
				);
			}
		}

		return $result;
	}

	private function splitElementFields(array $fields): array
	{
		$elementFields = [];
		$propertyFields = [];

		$propertyColumnIds = ElementPropertyProvider::getPropertyIdsFromColumnsIds(array_keys($fields));
		foreach ($fields as $name => $value)
		{
			$propertyId = $propertyColumnIds[$name] ?? null;
			if (isset($propertyId))
			{
				$propertyFields[$propertyId] = $value;
			}
			else
			{
				$elementFields[$name] = $value;
			}
		}

		return [$elementFields, $propertyFields];
	}

	protected function saveElement(int $id, array $fields): Result
	{
		$result = new Result();

		$fields = $this->prepareColumnsTypesValues($fields);

		[$elementFields, $propertyFields] = $this->splitElementFields($fields);

		if (!empty($elementFields))
		{
			$entity = $this->getElementEntity();
			$entity->Update($id, $fields);
			if ($entity->getLastError())
			{
				$result->addError(
					new Error($entity->getLastError())
				);
			}
		}

		if ($result->isSuccess() && !empty($propertyFields))
		{
			CIBlockElement::SetPropertyValuesEx($id, 0, $propertyFields);
		}

		return $result;
	}

	protected function saveSection(int $id, array $fields): Result
	{
		$result = new Result();

		$entity = $this->getSectionEntity();
		$entity->Update($id, $fields);

		if ($entity->getLastError())
		{
			$result->addError(
				new Error($entity->getLastError())
			);
		}

		return $result;
	}

	private function prepareColumnsTypesValues(array $fields): array
	{
		foreach ($this->getColumns() as $column)
		{
			$columnId = $column->getId();
			$value = $fields[$columnId] ?? null;
			if (!isset($value))
			{
				continue;
			}

			$editable = $column->getEditable();
			if (!isset($editable))
			{
				continue;
			}
			elseif ($editable->getType() === Types::MULTISELECT)
			{
				if (is_array($value))
				{
					$fields[$columnId] = array_column($value, 'VALUE');
				}
			}
			elseif ($editable->getType() === Types::IMAGE)
			{
				if ($value === 'null')
				{
					$fields[$columnId] = [
						'VALUE' => [
							'del' => 'Y',
						],
					];
				}
			}
		}

		return $fields;
	}

	protected function appendFilesToRows(HttpRequest $request, array $rows): array
	{
		$files = $request->getFile('FIELDS');
		if (empty($files['name']))
		{
			return $rows;
		}

		foreach ($files['name'] as $rowId => $fields)
		{
			foreach ($fields as $fieldName => $fieldValue)
			{
				$rows[$rowId] ??= [];
				$rows[$rowId][$fieldName] = [
					'name' => $fieldValue,
					'type' => $files['type'][$rowId][$fieldName] ?? null,
					'size' => $files['size'][$rowId][$fieldName] ?? null,
					'error' => $files['error'][$rowId][$fieldName] ?? null,
					'tmp_name' => $files['tmp_name'][$rowId][$fieldName] ?? null,
					'full_path' => $files['full_path'][$rowId][$fieldName] ?? null,
				];
			}
		}

		return $rows;
	}
}
