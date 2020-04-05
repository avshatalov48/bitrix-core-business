<?php
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Landing;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_ERR_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class IblockSelectorLanding extends Iblock\Component\Selector\Element
{
	protected $landingIncluded = null;

	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params['USE_MODE'] = parent::MODE_SLIDER;
		$params['RESULT_ACTION_TYPE'] = parent::RESULT_ACTION_TYPE_SLIDER;
		$params['RESULT_DATA_TYPE'] = parent::RESULT_DATA_TYPE_FILTER;

		return parent::onPrepareComponentParams($params);
	}

	/**
	 * @return void
	 */
	protected function checkModules()
	{
		if ($this->landingIncluded === null)
			$this->landingIncluded = Loader::includeModule('landing');
	}

	/**
	 * @return array
	 */
	protected function prepareGridFilterCurrentPreset()
	{
		$preset = [];
		if (
			$this->landingIncluded
			&& $this->request->isPost()
			&& !$this->request->isAjaxRequest()
		)
		{
			$data = $this->request->getPost('filter');
			if (!empty($data) && is_array($data))
			{
				$sourceFilter = new Landing\Source\UiFilterPreset();
				$sourceFilter->setFields($this->getGridFilterDefinition());
				$preset = $sourceFilter->create($data);
				unset($sourceFilter);
			}
			unset($data);
		}
		return $preset;
	}

	/**
	 * @return array
	 */
	protected function getProductFieldsFilterDefinition()
	{
		$result = parent::getProductFieldsFilterDefinition();

		if (isset($this->arParams['SIMPLE_PRODUCTS']) && $this->arParams['SIMPLE_PRODUCTS'] === 'Y')
		{
			if (isset($result['TYPE']))
				unset($result['TYPE']);
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function prepareResult()
	{
		parent::prepareResult();

		$this->arResult['SETTINGS']['FILTER']['DEFAULT'] = [
			[
				'name' => $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'FILTER_ALL'),
				'key' => 'IBLOCK_ID',
				'value' => $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID')
			]
		];
		if (
			(isset($this->arParams['SIMPLE_PRODUCTS']) && $this->arParams['SIMPLE_PRODUCTS'] === 'Y')
			&& $this->catalogIncluded
			&& (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'OFFERS_IBLOCK_ID') > 0
		)
		{
			$this->arResult['SETTINGS']['FILTER']['INTERNAL'] = [
				[
					'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_SLIDER_FILTER_SIMPLE_PRODUCTS'),
					'key' => 'TYPE',
					'value' => [
						[
							'VALUE' => Catalog\ProductTable::TYPE_PRODUCT
						],
						[
							'VALUE' => Catalog\ProductTable::TYPE_SET
						]
					]
				]
			];
		}
		if ($this->getQuickSearchField() !== null)
		{
			$this->arResult['SETTINGS']['FILTER']['QUICK_SEARCH_FIELD'] = $this->getQuickSearchDescription();
		}
	}

	/**
	 * @return array
	 */
	protected function getClientExtensions()
	{
		return array_merge(
			parent::getClientExtensions(),
			['landing.uifilterconverter']
		);
	}

	/**
	 * @return array
	 */
	protected function getInternalFilter()
	{
		return $this->simpleProductFilter(parent::getInternalFilter());
	}

	/**
	 * @return array
	 */
	protected function getOfferPropertyFilterDefinition()
	{
		if (isset($this->arParams['SIMPLE_PRODUCTS']) && $this->arParams['SIMPLE_PRODUCTS'] === 'Y')
			return [];
		return parent::getOfferPropertyFilterDefinition();
	}

	/**
	 * Temporary method.
	 *
	 * @param array $filter
	 * @return array
	 */
	private function simpleProductFilter(array $filter)
	{
		if (
			(isset($this->arParams['SIMPLE_PRODUCTS']) && $this->arParams['SIMPLE_PRODUCTS'] === 'Y')
			&& $this->catalogIncluded
			&& (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'OFFERS_IBLOCK_ID') > 0
		)
		{
			$filter['=TYPE'] = [Catalog\ProductTable::TYPE_PRODUCT, Catalog\ProductTable::TYPE_SET];
		}
		return $filter;
	}
}