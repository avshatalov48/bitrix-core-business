<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Iblock\Component\Grid\GridComponent;
use Bitrix\Iblock\Component\Property\ComponentLinksBuilder;
use Bitrix\Iblock\Integration\UI\Grid\Filter\Property\PropertyFilter;
use Bitrix\Iblock\Integration\UI\Grid\Property\PropertyGridProvider;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\UI\Buttons\CreateButton;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

class IblockPropertyGrid extends GridComponent
{
	private ?AccessController $accessController;
	private ComponentLinksBuilder $linksBuilder;
	private PropertyGridProvider $gridProvider;
	private PropertyFilter $filter;
	private array $iblockFields;

	/**
	 * Iblock.
	 *
	 * @return int
	 */
	private function getIblockId(): int
	{
		return (int)$this->arParams['IBLOCK_ID'];
	}

	/**
	 * Iblock name.
	 *
	 * @return string|null
	 */
	private function getIblockName(): ?string
	{
		return $this->getIblockFields()['NAME'] ?? null;
	}

	/**
	 * Iblock fields.
	 *
	 * @return array|null
	 */
	private function getIblockFields(): ?array
	{
		if (!isset($this->iblockFields))
		{
			$iblockId = $this->getIblockId();
			if ($iblockId > 0)
			{
				$iblock = CIBlock::GetByID($iblockId)->Fetch();
				if (is_array($iblock))
				{
					$this->iblockFields = $iblock;
				}
			}
		}

		return $this->iblockFields ?? null;
	}

	/**
	 * @inheritDoc
	 */
	protected function getGridProvider(): PropertyGridProvider
	{
		return $this->gridProvider;
	}

	/**
	 * @inheritDoc
	 *
	 * @return PropertyFilter
	 */
	protected function getFilter(): PropertyFilter
	{
		return $this->filter;
	}

	/**
	 * @inheritDoc
	 */
	protected function init(): void
	{
		Loader::requireModule('ui');
		Loader::requireModule('iblock');

		$this->linksBuilder = new ComponentLinksBuilder();
		$this->gridProvider = new PropertyGridProvider(
			$this->getIblockId(),
			$this->linksBuilder
		);
		$this->filter = PropertyFilter::create(
			$this->getIblockId(),
			$this->gridProvider
		);

		if (Loader::includeModule('catalog'))
		{
			$this->accessController = AccessController::getCurrent();
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function checkReadPermissions(): bool
	{
		if (isset($this->accessController))
		{
			return $this->accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getRowsSelect(): array
	{
		$result = parent::getRowsSelect();
		$result[] = 'USER_TYPE';

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getRawRows(array $params)
	{
		return PropertyTable::getList($params);
	}

	/**
	 * @inheritDoc
	 */
	protected function getTotalCount(): int
	{
		return PropertyTable::getCount($this->getRowsFilter());
	}

	/**
	 * @inheritDoc
	 */
	protected function initToolbar(): void
	{
		// filter
		$filterOptions = $this->filter->toArray();
		$filterOptions['GRID_ID'] = $this->gridProvider->getId();

		Toolbar::addFilter($filterOptions);

		// buttons
		$button = CreateButton::create([
			'text' => Loc::getMessage('IBLOCK_PROPERTY_LIST_BUTTON_CREATE'),
			'click' => $this->linksBuilder->getActionCreateClick(),
		]);

		Toolbar::addButton($button, ButtonLocation::AFTER_TITLE);
	}

	/**
	 * @inheritDoc
	 */
	protected function initResult(): void
	{
		parent::initResult();

		$this->arResult['IBLOCK_NAME'] = $this->getIblockName();
	}

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		if (!$this->existsIblock())
		{
			$this->arResult['ERROR'] = Loc::getMessage('IBLOCK_PROPERTY_LIST_ERROR_NOT_FOUND_IBLOCK');
			$this->includeComponentTemplate('error');

			return;
		}

		parent::executeComponent();
	}

	/**
	 * Iblock exists.
	 *
	 * @return bool
	 */
	private function existsIblock(): bool
	{
		return isset($this->arParams['IBLOCK_ID']) && $this->getIblockFields() !== null;
	}

	/**
	 * @inheritDoc
	 */
	protected function processActionGrid(string $actionName, HttpRequest $request): ?Result
	{
		if ($actionName === 'edit')
		{
			$rows = $request->getPost('FIELDS');
			if (!is_array($rows) || empty($rows))
			{
				return null;
			}

			return $this->updateProperties($rows);

		}
		elseif ($actionName === 'delete')
		{
			$ids = $request->getPost('ID');
			if (!is_array($ids))
			{
				return null;
			}

			return $this->deleteProperties($ids);
		}

		return null;
	}

	/**
	 * Update properties.
	 *
	 * @param array $rows
	 *
	 * @return Result
	 */
	private function updateProperties(array $rows): Result
	{
		$result = new Result();

		foreach ($rows as $propertyId => $fields)
			{
				$propertyId = is_numeric($propertyId) ? (int)$propertyId : 0;
				if ($propertyId <= 0)
				{
					continue;
				}

				$fields = $this->gridProvider->cleanFields($fields);
				if (empty($fields))
				{
					continue;
				}

				$property = new CIBlockProperty();
				$property->Update($propertyId, $fields);
				if ($property->LAST_ERROR)
				{
					$message = Loc::getMessage('IBLOCK_PROPERTY_LIST_ERROR_SAVE_PROPERTY', [
						'#ID#' => $propertyId,
						'#ERROR#' => $property->LAST_ERROR,
					]);
					$result->addError(
						new Error($message)
					);
				}
			}

		return $result;
	}

	/**
	 * Delete properties.
	 *
	 * @param array $ids
	 *
	 * @return Result
	 */
	private function deleteProperties(array $ids): Result
	{
		global $APPLICATION;

		/**
		 * @var \CMain $APPLICATION
		 */

		$result = new Result();

		foreach ($ids as $propertyId)
		{
			if (!is_numeric($propertyId))
			{
				continue;
			}

			$propertyId = (int)$propertyId;
			$ret = CIBlockProperty::Delete($propertyId);
			if (!$ret)
			{
				/**
				 * @var CAdminException $ex
				 */
				$ex = $APPLICATION->GetException();
				$message = Loc::getMessage('IBLOCK_PROPERTY_LIST_ERROR_DELETE_PROPERTY', [
					'#ID#' => $propertyId,
					'#ERROR#' => $ex ? $ex->GetString() : 'Unknown error',
				]);
				$result->addError(
					new Error($message)
				);
			}
		}

		return $result;
	}
}
