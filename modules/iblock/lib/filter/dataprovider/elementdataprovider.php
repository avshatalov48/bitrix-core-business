<?php

namespace Bitrix\Iblock\Filter\DataProvider;

use Bitrix\Iblock\Filter\DataProvider\Element\ElementFilterFields;
use Bitrix\Iblock\Filter\DataProvider\Settings\ElementSettings;
use Bitrix\Main\Filter\EntityDataProvider;
use Bitrix\Main\Localization\Loc;

class ElementDataProvider extends EntityDataProvider
{
	private ElementSettings $settings;
	private ElementFilterFields $fields;

	public function __construct(ElementSettings $settings)
	{
		$this->settings = $settings;
		$this->fields = ElementFilterFields::createFromElementSettings($settings);
	}

	public function getSettings(): ElementSettings
	{
		return $this->settings;
	}

	public function prepareFields()
	{
		$result = [];

		$fields = $this->fields->getElementFieldsParams();
		foreach ($fields as $id => $params)
		{
			$result[$id] = $this->createField($id, $params);
		}

		$properties = $this->fields->getElementPropertiesParams();
		foreach ($properties as $id => $params)
		{
			$result[$id] = $this->createField($id, $params);
		}

		return $result;
	}

	public function prepareFieldData($fieldID)
	{
		if ($fieldID === 'SECTION_ID')
		{
			return [
				'items' => $this->fields->getSectionListItems(),
			];
		}
		elseif ($fieldID === 'CREATED_BY' || $fieldID === 'MODIFIED_BY')
		{
			return $this->getUserEntitySelectorParams($fieldID . '_filter', ['fieldName' => $fieldID]);
		}
		elseif ($this->fields->isPropertyId($fieldID))
		{
			return $this->fields->getPropertyDescription($fieldID);
		}

		return null;
	}

	protected function getFieldName($fieldID)
	{
		return Loc::getMessage('IBLOCK_FILTER_ELEMENT_DATAPROVIDER_FIELD_' . $fieldID) ?? $fieldID;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFilterValue(array $rawFilterValue): array
	{
		$rawFilterValue = parent::prepareFilterValue($rawFilterValue);

		if (!empty($rawFilterValue['FIND']))
		{
			$rawFilterValue['?NAME'] = $rawFilterValue['FIND'];
		}

		return $this->fields->prepareFilterValue($rawFilterValue);
	}
}
