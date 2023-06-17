<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\Component\Grid\GridComponent;
use Bitrix\Iblock\Controller\Property\Directory\Action\SaveSettingsAction;
use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Iblock\Integration\UI\Grid\Property\Type\DirectoryItemsProvider;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Component\ComponentController;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\UI\PageNavigation;

class IblockPropertyTypeDirectorySettings extends GridComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private Entity $entity;
	private int $propertyId;
	private ?array $fields;
	private DirectoryItemsProvider $gridProvider;

	public function onPrepareComponentParams($arParams)
	{
		$arParams['PROPERTY_ID'] =
			isset($arParams['PROPERTY_ID'])
				? (int)$arParams['PROPERTY_ID']
				: 0
		;

		return $arParams;
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		Loader::requireModule('highloadblock');

		$this->errorCollection = new ErrorCollection();
	}

	private function getPropertyId(): int
	{
		$this->propertyId ??= $this->arParams['PROPERTY_ID'];

		return $this->propertyId;
	}

	private function getFields(): ?array
	{
		$this->fields ??=
			$this->getPropertyId() > 0
				? CIBlockProperty::GetByID($this->getPropertyId())->Fetch()
				: null
		;

		return $this->fields;
	}

	private function getHighloadBlock(): ?array
	{
		$tableName = Context::getCurrent()->getRequest()->get('directoryTableName');
		if (empty($tableName) && $this->propertyId > 0)
		{
			$propertyFields = CIBlockProperty::GetByID($this->propertyId)->Fetch();
			if (isset($propertyFields['USER_TYPE_SETTINGS']['TABLE_NAME']))
			{
				$tableName = $propertyFields['USER_TYPE_SETTINGS']['TABLE_NAME'];
			}
		}

		if (!empty($tableName))
		{
			return HighloadBlockTable::getRow([
				'filter' => [
					'=TABLE_NAME' => $tableName,
				],
			]);
		}

		return null;
	}

	private function getHighloadBlockId(): ?int
	{
		$row = $this->getHighloadBlock();
		if (isset($row))
		{
			return (int)$row['ID'];
		}

		return null;
	}

	private function getHighloadBlockEntity(): ?Entity
	{
		if (!isset($this->entity))
		{
			$row = $this->getHighloadBlock();
			if ($row)
			{
				/** @var Entity $entity */
				$entity = HighloadBlockTable::compileEntity($row);
				if ($entity)
				{
					$this->entity = $entity;

					return $this->entity;
				}
			}

			return null;
		}

		return $this->entity;
	}

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		if ($this->getPropertyId() === 0)
		{
			$this->includeComponentTemplate('new_property');
		}
		else
		{
			parent::executeComponent();
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getRawRows(array $params)
	{
		$result = [];

		$entity = $this->getHighloadBlockEntity();
		if (isset($entity))
		{
			$rows = $entity->getDataClass()::getList($params);
			if ($rows->getSelectedRowsCount() > 0)
			{
				$result = $rows;
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getFilter(): ?Filter
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	protected function getTotalCount(): int
	{
		$filter = $this->getRowsFilter();
		$entity = $this->getHighloadBlockEntity();
		if (isset($entity))
		{
			return $entity->getDataClass()::getCount($filter);
		}

		return 0;
	}

	/**
	 * @inheritDoc
	 */
	protected function getGridProvider(): BaseProvider
	{
		$this->gridProvider ??= new DirectoryItemsProvider(
			$this->getPropertyId(),
			$this->getHighloadBlockId(),
			$this->getHighloadBlockEntity()
		);

		return $this->gridProvider;
	}

	/**
	 * Without pagination.
	 *
	 * @return null
	 */
	protected function getRowsPagination(): ?PageNavigation
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	protected function initResult(): void
	{
		parent::initResult();

		$this->arResult['PROPERTY_NAME'] = $this->getFields()['NAME'] ?? null;
		$this->arResult['DIRECTORIES'] = $this->getDirectoryNamesDropdownFormat();
		$this->arResult['SELECTED_DIRECTORY'] = $this->getHighloadBlock()['TABLE_NAME'] ?? null;

		// implements remove action
		if (
			isset($this->arResult['GRID']['ACTION_PANEL']['GROUPS'])
			&& is_array($this->arResult['GRID']['ACTION_PANEL']['GROUPS'])
		)
		{
			foreach ($this->arResult['GRID']['ACTION_PANEL']['GROUPS'] as $g => $group)
			{
				$items = $group['ITEMS'] ?? null;
				if (!is_array($items))
				{
					continue;
				}

				foreach ($items as $i => $item)
				{
					if ($item['ID'] === 'grid_remove_button' && isset($item['ONCHANGE'][0]['DATA'][0]['JS']))
					{
						$item['ONCHANGE'][0]['DATA'][0]['JS'] = 'BX.Iblock.PropertyDirectorySettings.instance.removeGridSelectedRows();';

						$this->arResult['GRID']['ACTION_PANEL']['GROUPS'][$g]['ITEMS'][$i] = $item;

						break 2;
					}
				}
			}
		}
	}

	private function getDirectoryNamesDropdownFormat(): array
	{
		$result = [];

		$rows = HighloadBlockTable::getList(array(
			'select' => [
				'NAME',
				'TABLE_NAME',
				'NAME_LANG' => 'LANG.NAME',
			],
			'order' => [
				'NAME_LANG' => 'ASC',
				'NAME' => 'ASC',
			],
		));
		$excludeTableRegexp = '/^b_hlsys_/i';

		foreach ($rows as $row)
		{
			if (empty($row['NAME_LANG']))
			{
				$name = "{$row['NAME']} ({$row['TABLE_NAME']})";
			}
			else
			{
				$name = "{$row['NAME']} ({$row['NAME_LANG']})";
			}

			$tableName = $row['TABLE_NAME'];
			if (preg_match($excludeTableRegexp, $tableName) === 1)
			{
				continue;
			}

			$result[] = [
				'NAME' => $name,
				'VALUE' => $tableName,
			];
		}

		return $result;
	}

	//
	// AJAX
	//

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function listKeysSignedParameters()
	{
		$params = parent::listKeysSignedParameters() ?? [];
		$params[] = 'PROPERTY_ID';

		return $params;
	}

	public function saveAction(array $fields): bool
	{
		$controller = new ComponentController($this);
		$controller->setSourceParametersList([
			[
				'propertyId' => $this->getPropertyId(),
				'fields' => $fields,
			],
		]);

		$action = new SaveSettingsAction('savesettings', $controller);
		$action->runWithSourceParametersList();

		$errors = $action->getErrors();
		$this->errorCollection->add($errors);

		return empty($errors);
	}
}
