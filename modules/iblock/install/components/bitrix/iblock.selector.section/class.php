<?php
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock\Component;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_SELECTOR_SECTION_ERR_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class IblockSelectorSection extends Component\EntitySelector
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->useImplicitPageNavigation();
	}

	/**
	 * @return array
	 */
	protected function getGridFilterDescription()
	{
		$result = parent::getGridFilterDescription();

		return $result;
	}

	/**
	 * @return void
	 */
	protected function getData()
	{
		$this->rows = [];

		$selectedFieldMap = $this->getStorageItem(self::STORAGE_GRID, 'VISIBLE_COLUMNS_MAP');
		$binaryStates = $this->getBinaryDictionary();

		$iterator = \CIBlockSection::GetList(
			$this->getDataOrder(),
			$this->getDataFilter(),
			false,
			$this->getDataFields(),
			$this->getGridNavigationParams()
		);
		$iterator->bShowAll = false;
		while ($row = $iterator->Fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if (isset($selectedFieldMap['ACTIVE']))
				$row['ACTIVE'] = ($row['ACTIVE'] == 'Y' ? $binaryStates['Y'] : $binaryStates['N']);
			if (isset($selectedFieldMap['NAME']))
				$row['NAME'] = htmlspecialcharsEx((string)$row['NAME']);
			if (isset($selectedFieldMap['SORT']))
				$row['SORT'] = (int)$row['SORT'];
			if (isset($selectedFieldMap['CODE']))
				$row['CODE'] = htmlspecialcharsbx((string)$row['CODE']);
			if (isset($selectedFieldMap['XML_ID']))
				$row['XML_ID'] = htmlspecialcharsbx((string)$row['XML_ID']);
			$this->rows[] = $row;
		}
		unset($row);

		$this->setImplicitNavigationData($iterator);
		unset($iterator);
	}

	/**
	 * @return string
	 */
	protected function getNavigationTitle()
	{
		$title = $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_SECTIONS_NAME');
		if (!empty($title))
			return $title;
		return Loc::getMessage('IBLOCK_SELECTOR_SECTION_GRID_PAGENAVIGATION_TITLE');
	}

	/**
	 * @return array
	 */
	protected function getInternalFilter()
	{
		$filter = parent::getInternalFilter();

		$filter['CHECK_PERMISSIONS'] = 'Y';
		$filter['MIN_PERMISSION'] = 'R';

		return $filter;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function getSliderResultDataSet(array $row)
	{
		$result = [];
		$title = $this->getDataTitleField();
		if ($title == '' || !isset($row[$title]))
			return $result;
		foreach ($this->resultAction['DATA_SET'] as $index => $field)
		{
			if (!array_key_exists($field, $row))
				continue;
			$result[] = [
				'name' => $row[$title],
   				'value' => $row[$field],
   				'key' => (is_numeric($index) ? $field : $index)
			];
		}
		unset($index, $field);
		return $result;
	}
}