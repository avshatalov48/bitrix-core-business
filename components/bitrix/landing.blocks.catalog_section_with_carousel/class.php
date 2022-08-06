<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}


/** @noinspection PhpUnused */

class LandingBlocksCatalogSectionWithCarousel extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 * @noinspection PhpMissingParentCallCommonInspection
	 */
	public function executeComponent(): void
	{
		$this->clearEscapingParams();

		$this->arResult['ADDITIONAL'] = $this->arParams['ADDITIONAL'];
		unset($this->arParams['ADDITIONAL']);

		$this->arResult['EDIT_MODE'] = $this->arResult['ADDITIONAL']['EDIT_MODE'];
		$this->arResult['SHOW_ELEMENT_SECTION'] = $this->arResult['ADDITIONAL']['SHOW_ELEMENT_SECTION'];

		// first
		$firstComponentParams = array_merge($this->getDefaultComponentParams(), $this->arParams);
		$firstComponentParams['SECTIONS_FILTER_NAME'] = $this->arResult['ADDITIONAL']['SECTIONS_FILTER_NAME'];
		$firstComponentParams['SECTIONS_CHAIN_START_FROM'] = 1;
		$firstComponentParams['SECTION_ID'] = $this->arResult['ADDITIONAL']['SECTION_ID'];
		$firstComponentParams['SECTION_CODE'] = $this->arResult['ADDITIONAL']['SECTION_CODE'];
		$firstComponentParams['FILTER_NAME'] = $this->arResult['ADDITIONAL']['FILTER_NAME'];
		$firstComponentParams['SHOW_ALL_WO_SECTION'] = 'N';
		$firstComponentParams['TEMPLATE_THEME'] = 'store_v3';
		$firstComponentParams['OFFER_TREE_PROPS'] = [];
		$firstComponentParams['SET_META_KEYWORDS'] = 'Y';
		$firstComponentParams['SET_META_DESCRIPTION'] = 'Y';
		$firstComponentParams['SET_TITLE'] = $this->arResult['ADDITIONAL']['SET_TITLE'];
		$firstComponentParams['ALLOW_SEO_DATA'] = $this->arResult['ADDITIONAL']['ALLOW_SEO_DATA'];
		$firstComponentParams['SET_BROWSER_TITLE'] = 'Y';
		$firstComponentParams['LOAD_ON_SCROLL'] = $this->arResult['ADDITIONAL']['EDIT_MODE'] ? "N" : "Y";
		$firstComponentParams['DEFERRED_LOAD'] = 'N';
		$firstComponentParams['CYCLIC_LOADING'] = 'N';
		$firstComponentParams['CYCLIC_LOADING_COUNTER_NAME'] = 'sectionCycleCount';

		// second
		$secondComponentParams = array_merge($this->getDefaultComponentParams(), $this->arParams);
		$secondComponentParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,price,quantity,buttons,quantityLimit,compare';
		$secondComponentParams['ENLARGE_PRODUCT'] = 'STRICT';
		$secondComponentParams['SECTION_ID'] = $this->arResult['ADDITIONAL']['LANDING_SECTION_ID'];
		$secondComponentParams['SECTION_CODE'] = '';
	    $secondComponentParams['FILTER_NAME'] = $this->arResult['ADDITIONAL']['CATALOG_FILTER_NAME'];
		$secondComponentParams['SHOW_ALL_WO_SECTION'] = 'Y';
		$secondComponentParams['TEMPLATE_THEME'] = 'vendor';
		$secondComponentParams['OFFER_TREE_PROPS'] = [
			0 => 'COLOR_REF',
			1 => 'SIZES_SHOES',
			2 => 'SIZES_CLOTHES',
		];
	    $secondComponentParams['SET_META_KEYWORDS'] = 'N';
		$secondComponentParams['SET_META_DESCRIPTION'] = 'N';
		$secondComponentParams['SET_TITLE'] = 'N';
		$secondComponentParams['ALLOW_SEO_DATA'] = 'N';
		$secondComponentParams['SET_BROWSER_TITLE'] = 'N';
		$secondComponentParams['LOAD_ON_SCROLL'] = 'Y';
		$secondComponentParams['DEFERRED_LOAD'] = $this->arResult['ADDITIONAL']['SHOW_ELEMENT_SECTION'] ? 'Y' : 'N';
		$secondComponentParams['CYCLIC_LOADING'] = 'Y';
		$secondComponentParams['CYCLIC_LOADING_COUNTER_NAME'] = 'catalogCycleCount';

		$this->arResult['FIRST_COMPONENT_PARAMS'] = $firstComponentParams;
		$this->arResult['SECOND_COMPONENT_PARAMS'] = $secondComponentParams;

		$this->includeComponentTemplate();
	}

	protected function clearEscapingParams()
	{
		$clear = static function ($params) use (&$clear)
		{
			foreach($params as $key => $value)
			{
				if (strpos($key, '~') === 0)
				{
					unset($params[$key]);
				}
				else if (is_array($value))
				{
					$params[$key] = $clear($value);
				}
			}
			return $params;
		};
		$this->arParams = $clear($this->arParams);
	}

	protected function getDefaultComponentParams(): array
	{
		return [
			'IBLOCK_TYPE' => '',
			'SECTION_USER_FIELDS' => [],
			'ELEMENT_SORT_FIELD' => 'sort',
			'ELEMENT_SORT_ORDER' => 'desc',
			'ELEMENT_SORT_FIELD2' => '',
			'ELEMENT_SORT_ORDER2' => '',
			'INCLUDE_SUBSECTIONS' => 'Y',
			'PAGE_ELEMENT_COUNT' => '6',
			'LINE_ELEMENT_COUNT' => '1',
			'PROPERTY_CODE' => [],
			'OFFERS_FIELD_CODE' => [
				0 => 'NAME'
			],
			'OFFERS_PROPERTY_CODE' => [],
			'OFFERS_SORT_FIELD' => 'sort',
			'OFFERS_SORT_ORDER' => 'desc',
			'OFFERS_LIMIT' => '0',
			'PRODUCT_DISPLAY_MODE' => 'Y',
			'ADD_PICT_PROP' => 'MORE_PHOTO',
			'LABEL_PROP' => [
				0 => 'NEWPRODUCT',
				1 => 'SALELEADER',
				2 => 'SPECIALOFFER',
			],
			'OFFER_ADD_PICT_PROP' => 'MORE_PHOTO',
			'MESS_BTN_BUY' => '',
			'MESS_BTN_ADD_TO_BASKET' => '',
			'MESS_BTN_SUBSCRIBE' => '',
			'MESS_BTN_DETAIL' => '',
			'MESS_NOT_AVAILABLE' => '',
			'SECTION_ID_VARIABLE' => 'SECTION_CODE',
			'AJAX_MODE' => 'N',
			'AJAX_OPTION_JUMP' => 'Y',
			'AJAX_OPTION_STYLE' => 'Y',
			'AJAX_OPTION_HISTORY' => 'N',
			'CACHE_TYPE' => 'A',
			'CACHE_TIME' => '36000000',
			'CACHE_GROUPS' => 'Y',
			'CACHE_FILTER' => 'Y',
			'META_KEYWORDS' => '',
			'META_DESCRIPTION' => '',
			'BROWSER_TITLE' => '-',
			'ADD_SECTIONS_CHAIN' => 'N',
			'SET_STATUS_404' => 'N',
			'CONVERT_CURRENCY' => 'Y',
			'BASKET_URL' => '#system_order',
			'ACTION_COMPARE_VARIABLE' => 'compare',
			'PRODUCT_ID_VARIABLE' => 'id',
			'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
			'ADD_PROPERTIES_TO_BASKET' => 'N',
			'PRODUCT_PROPS_VARIABLE' => 'prop',
			'PARTIAL_PRODUCT_PROPERTIES' => 'Y',
			'PRODUCT_PROPERTIES' => [],
			'OFFERS_CART_PROPERTIES' => [],
			'PAGER_TEMPLATE' => 'round',
			'DISPLAY_TOP_PAGER' => 'N',
			'DISPLAY_BOTTOM_PAGER' => 'N',
			'PAGER_TITLE' => '',
			'PAGER_SHOW_ALWAYS' => 'N',
			'PAGER_DESC_NUMBERING' => 'N',
			'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
			'PAGER_SHOW_ALL' => 'N',
			'AJAX_OPTION_ADDITIONAL' => '',
			'SHOW_CLOSE_POPUP' => 'Y',
			'MESS_BTN_COMPARE' => '',
			'ADD_TO_BASKET_ACTION' => 'BUY',
			'COMPONENT_TEMPLATE' => 'store_v3',
			'SEF_MODE' => 'N',
			'SET_LAST_MODIFIED' => 'N',
			'USE_MAIN_ELEMENT_SECTION' => 'N',
			'PAGER_BASE_LINK_ENABLE' => 'N',
			'SHOW_404' => 'N',
			'MESSAGE_404' => '',
			'PAGER_BASE_LINK' => '',
			'PAGER_PARAMS_NAME' => 'arrPager',
			'BACKGROUND_IMAGE' => 'UF_BACKGROUND_IMAGE',
			'DISABLE_INIT_JS_IN_COMPONENT' => 'N',
			'CUSTOM_FILTER' => '',
			'SHOW_SLIDER' => 'Y',
			'LABEL_PROP_MOBILE' => [
				0 => 'NEWPRODUCT',
				1 => 'SALELEADER',
				2 => 'SPECIALOFFER',
			],
			'LABEL_PROP_POSITION' => 'top-left',
			'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
			'RCM_TYPE' => 'personal',
			'RCM_PROD_ID' => '',
			'USE_OFFER_NAME' => 'Y',
			'LAZY_LOAD' => 'Y',
			'SECTIONS_OFFSET_MODE' => 'F',
			'PROPERTY_CODE_MOBILE' => [
				0 => 'ARTNUMBER',
				1 => 'MANUFACTURER',
				2 => 'MATERIAL',
			],
			'SLIDER_INTERVAL' => '3000',
			'SLIDER_PROGRESS' => 'Y',
			'MESS_BTN_LAZY_LOAD' => '',
			'SHOW_MAX_QUANTITY' => 'M',
			'SHOW_FROM_SECTION' => 'Y',
			'COMPATIBLE_MODE' => 'N',
			'COMPOSITE_FRAME_MODE' => 'A',
			'COMPOSITE_FRAME_TYPE' => 'AUTO',
			'COMPARE_NAME' => 'CATALOG_COMPARE_LIST',
			'MESS_SHOW_MAX_QUANTITY' => '',
			'RELATIVE_QUANTITY_FACTOR' => '5',
			'MESS_RELATIVE_QUANTITY_MANY' => '',
			'MESS_RELATIVE_QUANTITY_FEW' => '',
			'USE_COMPARE_LIST' => 'N',
			'STRICT_SECTION_CHECK' => 'N',
			'CHECK_LANDING_PRODUCT_SECTION' => 'Y',
			'PREDICT_ELEMENT_COUNT' => 'Y',
			'COMPARE_PATH' => '#system_compare',
			'PRODUCT_SUBSCRIPTION' => 'N',
			'DISPLAY_COMPARE' => 'N',
			'SHOW_SECTIONS' => 'Y'
		];
	}
}