<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Filter\Property;

use Bitrix\Iblock\Helpers\Admin\Property;
use Bitrix\Iblock\Integration\UI\Grid\Property\PropertyGridProvider;
use Bitrix\Main\Filter\EntityDataProvider;
use Bitrix\Main\Filter\Settings;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Collection;
use CIBlockProperty;

class PropertyFilterProvider extends EntityDataProvider
{
	private Settings $settings;
	private PropertyGridProvider $gridProvider;
	private int $iblockId;

	/**
	 * @param int $iblockId
	 * @param PropertyGridProvider $gridProvider
	 */
	public function __construct(int $iblockId, PropertyGridProvider $gridProvider)
	{
		$this->iblockId = $iblockId;
		$this->gridProvider = $gridProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function getSettings()
	{
		$this->settings ??= new Settings([
			'ID' => "iblock_property_{$this->iblockId}",
		]);

		return $this->settings;
	}

	/**
	 * @inheritDoc
	 */
	protected function getFieldName($fieldID)
	{
		return $fieldID;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFields()
	{
		return [
			'NAME' => $this->createField('NAME', [
				'name' => $this->gridProvider->getFieldName('NAME'),
				'default' => true,
			]),
			'CODE' => $this->createField('CODE', [
				'name' => $this->gridProvider->getFieldName('CODE'),
			]),
			'ACTIVE' => $this->createField('ACTIVE', [
				'name' => $this->gridProvider->getFieldName('ACTIVE'),
				'type' => 'list',
				'default' => true,
				'data' => [
					'items' => [
						'Y' => Loc::getMessage('IBLOCK_YES'),
						'N' => Loc::getMessage('IBLOCK_NO')
					],
				],
			]),
			'SEARCHABLE' => $this->createField('SEARCHABLE', [
				'name' => $this->gridProvider->getFieldName('SEARCHABLE'),
				'type' => 'list',
				'data' => [
					'items' => [
						'Y' => Loc::getMessage('IBLOCK_YES'),
						'N' => Loc::getMessage('IBLOCK_NO')
					],
				],
			]),
			'FILTRABLE' => $this->createField('FILTRABLE', [
				'name' => $this->gridProvider->getFieldName('FILTRABLE'),
				'type' => 'list',
				'data' => [
					'items' => [
						'Y' => Loc::getMessage('IBLOCK_YES'),
						'N' => Loc::getMessage('IBLOCK_NO')
					],
				],
			]),
			'IS_REQUIRED' => $this->createField('IS_REQUIRED', [
				'name' => $this->gridProvider->getFieldName('IS_REQUIRED'),
				'type' => 'list',
				'data' => [
					'items' => [
						'Y' => Loc::getMessage('IBLOCK_YES'),
						'N' => Loc::getMessage('IBLOCK_NO')
					],
				],
			]),
			'MULTIPLE' => $this->createField('MULTIPLE', [
				'name' => $this->gridProvider->getFieldName('MULTIPLE'),
				'type' => 'list',
				'data' => [
					'items' => [
						'Y' => Loc::getMessage('IBLOCK_YES'),
						'N' => Loc::getMessage('IBLOCK_NO')
					],
				],
			]),
			'XML_ID' => $this->createField('XML_ID', [
				'name' => $this->gridProvider->getFieldName('XML_ID'),
			]),
			'PROPERTY_TYPE' => $this->createField('PROPERTY_TYPE', [
				'name' => $this->gridProvider->getFieldName('PROPERTY_TYPE'),
				'type' => 'list',
				'default' => true,
				'data' => [
					'items' => $this->getPropertyTypes(),
				],
			]),
		];
	}

	/**
	 * Dropdown property type values.
	 *
	 * @return array
	 */
	private function getPropertyTypes(): array
	{
		$result = [];

		$baseTypes = Property::getBaseTypeList(true);
		foreach ($baseTypes as $type => $name)
		{
			$result[$type] = $name;
		}

		$userTypes = CIBlockProperty::GetUserType();
		Collection::sortByColumn($userTypes, [
			'DESCRIPTION' => SORT_STRING,
		]);

		foreach ($userTypes as $type => $item)
		{
			$key = "{$item['PROPERTY_TYPE']}:{$type}";
			$result[$key] = $item['DESCRIPTION'];
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFieldData($fieldID)
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFilterValue(array $rawFilterValue): array
	{
		$isEmpty = empty($rawFilterValue);
		$rawFilterValue['=IBLOCK_ID'] = $this->iblockId;

		if ($isEmpty)
		{
			return $rawFilterValue;
		}

		// parse property type
		if (isset($rawFilterValue['PROPERTY_TYPE']))
		{
			$parts = explode(':', $rawFilterValue['PROPERTY_TYPE']);
			if (count($parts) === 2)
			{
				$rawFilterValue['PROPERTY_TYPE'] = $parts[0];
				$rawFilterValue['USER_TYPE'] = $parts[1];
			}
		}

		// process fields operations
		$filterOperations = [
			'NAME' => '?',
			'CODE' => '?',
			'ACTIVE' => '=',
			'SEARCHABLE' => '=',
			'FILTRABLE' => '=',
			'IS_REQUIRED' => '=',
			'MULTIPLE' => '=',
			'XML_ID' => '=',
			'PROPERTY_TYPE' => '=',
			'USER_TYPE' => '=',
		];
		foreach ($rawFilterValue as $field => $value)
		{
			$operator = $filterOperations[$field] ?? null;
			if (isset($operator))
			{
				$rawFilterValue[$operator . $field] = $value;
				unset($rawFilterValue[$field]);
			}
		}

		// searchable
		if (isset($rawFilterValue['FIND']))
		{
			$rawFilterValue[] = [
				'?NAME' => $rawFilterValue['FIND'],
			];
		}

		return $rawFilterValue;
	}
}
