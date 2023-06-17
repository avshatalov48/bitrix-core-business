<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Property\Type;

use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Main\Localization\Loc;

/**
 * Provider of values for list property types.
 */
final class ListValuesProvider extends BaseProvider
{
	private int $propertyId;

	/**
	 * @param int $propertyId
	 */
	public function __construct(int $propertyId)
	{
		$this->propertyId = $propertyId;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return "iblock_property_{$this->propertyId}_list_values";
	}

	/**
	 * @inheritDoc
	 */
	public function getColumns(): array
	{
		return [
			[
				'id' => 'ID',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_LIST_VALUES_PROVIDER_ID'),
				'default' => true,
			],
			[
				'id' => 'XML_ID',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_LIST_VALUES_PROVIDER_XML_ID'),
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'VALUE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_LIST_VALUES_PROVIDER_VALUE'),
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'SORT',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_LIST_VALUES_PROVIDER_SORT'),
				'default' => true,
				'editable' => true,
				'type' => 'number',
			],
			[
				'id' => 'DEF',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_LIST_VALUES_PROVIDER_DEF'),
				'default' => true,
				'editable' => true,
				'type' => 'checkbox',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getActionPanel(): ?array
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		$result['SHOW_GRID_SETTINGS_MENU'] = false;

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getTemplateRow(): ?array
	{
		return [
			'data' => [
				'SORT' => 500,
				'DEF' => 'N',
			],
		];
	}
}
