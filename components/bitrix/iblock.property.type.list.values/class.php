<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Iblock\Component\Grid\GridComponent;
use Bitrix\Iblock\Controller\Property\Action\SaveAction;
use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Iblock\Integration\UI\Grid\Property\Type\ListValuesProvider;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Engine\Component\ComponentController;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\UI\PageNavigation;

class IblockPropertyTypeListValues extends GridComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private int $propertyId;
	private int $iblockId;
	private ?array $fields;
	private ListValuesProvider $gridProvider;

	public function onPrepareComponentParams($arParams)
	{
		$arParams['PROPERTY_ID'] =
			isset($arParams['PROPERTY_ID'])
				? (int)$arParams['PROPERTY_ID']
				: 0
		;

		$arParams['IBLOCK_ID'] =
			isset($arParams['IBLOCK_ID'])
				? (int)$arParams['IBLOCK_ID']
				: 0
		;

		return $arParams;
	}

	protected function init(): void
	{
		$this->errorCollection = new ErrorCollection();
	}

	private function getIblockId(): int
	{
		$this->iblockId ??= $this->arParams['IBLOCK_ID'];

		return $this->iblockId;
	}

	protected function checkReadPermissions(): bool
	{
		$iblockId = $this->getIblockId();
		if ($iblockId === 0)
		{
			return false;
		}

		return CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit');
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
		return PropertyEnumerationTable::getList($params);
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
	protected function getAdditionalRowsFilter(): ?array
	{
		return [
			'=PROPERTY_ID' => $this->getPropertyId(),
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getTotalCount(): int
	{
		return PropertyEnumerationTable::getCount($this->getRowsFilter());
	}

	/**
	 * @inheritDoc
	 */
	protected function getGridProvider(): BaseProvider
	{
		$this->gridProvider ??= new ListValuesProvider(
			$this->getPropertyId()
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
		$params[] = 'IBLOCK_ID';

		return $params;
	}

	/**
	 * Save property values.
	 *
	 * For correct work, also need to send `signedParameters` of the component.
	 *
	 * @param array $values
	 *
	 * @return bool
	 */
	public function saveAction(array $values): bool
	{
		$this->init();

		$controller = new ComponentController($this);
		$controller->setSourceParametersList([
			[
				'iblockId' => $this->getIblockId(),
				'propertyId' => $this->getPropertyId(),
				'fields' => [
					'VALUES' => $values,
				],
			],
		]);

		$action = new SaveAction('save', $controller);
		$action->runWithSourceParametersList();

		$errors = $action->getErrors();
		$this->errorCollection->add($errors);

		return empty($errors);
	}
}
