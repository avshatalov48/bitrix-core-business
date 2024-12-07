<?php

namespace Bitrix\Iblock\Component;

use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\FacebookConversion;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

abstract class Element extends Base
{
	public function onPrepareComponentParams($params)
	{
		if (!is_array($params))
		{
			$params = [];
		}
		$params['ADD_SECTIONS_CHAIN'] = (isset($params['ADD_SECTIONS_CHAIN']) && $params['ADD_SECTIONS_CHAIN'] === 'N' ? 'N' : 'Y');
		$params['PRODUCT_DISPLAY_MODE'] = 'Y';
		$params = parent::onPrepareComponentParams($params);
		$params['IBLOCK_TYPE'] = trim((string)($params['IBLOCK_TYPE'] ?? ''));

		if ((int)$params['ELEMENT_ID'] > 0 && (int)$params['ELEMENT_ID'] != $params['ELEMENT_ID'] && Loader::includeModule('iblock'))
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_ELEMENT_NOT_FOUND'), self::ERROR_404));

			return $params;
		}

		$params['ELEMENT_ID'] = (int)$params['ELEMENT_ID'];
		$params['ELEMENT_CODE'] = trim((string)($params['ELEMENT_CODE'] ?? ''));

		$params['CHECK_SECTION_ID_VARIABLE'] = isset($params['CHECK_SECTION_ID_VARIABLE']) && $params['CHECK_SECTION_ID_VARIABLE'] === 'Y' ? 'Y' : 'N';
		$params['SECTION_ID_VARIABLE'] = trim((string)($params['SECTION_ID_VARIABLE'] ?? ''));
		if (
			$params['SECTION_ID_VARIABLE'] === ''
			|| !preg_match(self::PARAM_TITLE_MASK, $params['SECTION_ID_VARIABLE'])
		)
		{
			$params['SECTION_ID_VARIABLE'] = 'SECTION_ID';
		}

		$params['FROM_SECTION'] = '';
		if ($params['CHECK_SECTION_ID_VARIABLE'] === 'Y')
		{
			$params['FROM_SECTION'] = trim($this->request->get($params['SECTION_ID_VARIABLE']));
		}

		$params['SECTIONS_CHAIN_START_FROM'] = (int)($params['SECTIONS_CHAIN_START_FROM'] ?? 0);
		$params['META_KEYWORDS'] = trim((string)($params['META_KEYWORDS'] ?? ''));
		$params['META_DESCRIPTION'] = trim((string)($params['META_DESCRIPTION'] ?? ''));
		$params['BROWSER_TITLE'] = trim((string)($params['BROWSER_TITLE'] ?? ''));

		$params['BACKGROUND_IMAGE'] = trim((string)($params['BACKGROUND_IMAGE'] ?? ''));
		if ($params['BACKGROUND_IMAGE'] === '-')
		{
			$params['BACKGROUND_IMAGE'] = '';
		}

		$params['USE_MAIN_ELEMENT_SECTION'] = isset($params['USE_MAIN_ELEMENT_SECTION']) && $params['USE_MAIN_ELEMENT_SECTION'] === 'Y';
		$params['ADD_ELEMENT_CHAIN'] = isset($params['ADD_ELEMENT_CHAIN']) && $params['ADD_ELEMENT_CHAIN'] === 'Y';
		$params['LINK_IBLOCK_TYPE'] = trim((string)($params['LINK_IBLOCK_TYPE'] ?? ''));
		$params['LINK_IBLOCK_ID'] = (int)($params['LINK_IBLOCK_ID'] ?? 0);
		$params['LINK_PROPERTY_SID'] = trim((string)($params['LINK_PROPERTY_SID'] ?? ''));
		$params['LINK_ELEMENTS_URL'] = trim((string)($params['LINK_ELEMENTS_URL'] ?? ''));
		if ($params['LINK_ELEMENTS_URL'] === '')
		{
			$params['LINK_ELEMENTS_URL'] = 'link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#';
		}

		$params['SHOW_WORKFLOW'] = $this->request->get('show_workflow') === 'Y';
		if ($params['SHOW_WORKFLOW'])
		{
			$params['CACHE_TIME'] = 0;
		}

		$params['PRICE_VAT_SHOW_VALUE'] = isset($params['PRICE_VAT_SHOW_VALUE']) && $params['PRICE_VAT_SHOW_VALUE'] === 'Y';

		// compatibility to old HIDE_NOT_AVAILABLE parameter
		if (!isset($params['HIDE_NOT_AVAILABLE_OFFERS']) && isset($params['HIDE_NOT_AVAILABLE']))
		{
			$params['HIDE_NOT_AVAILABLE_OFFERS'] = $params['HIDE_NOT_AVAILABLE'];
		}

		if (
			!isset($params['HIDE_NOT_AVAILABLE_OFFERS'])
			|| ($params['HIDE_NOT_AVAILABLE_OFFERS'] !== 'Y' && $params['HIDE_NOT_AVAILABLE_OFFERS'] !== 'L')
		)
		{
			$params['HIDE_NOT_AVAILABLE_OFFERS'] = 'N';
		}

		$params['HIDE_NOT_AVAILABLE'] = 'N';
		$params['USE_ELEMENT_COUNTER'] = isset($params['USE_ELEMENT_COUNTER']) && $params['USE_ELEMENT_COUNTER'] === 'N' ? 'N' : 'Y';
		$params['SHOW_DEACTIVATED'] = isset($params['SHOW_DEACTIVATED']) && $params['SHOW_DEACTIVATED'] === 'Y' ? 'Y' : 'N';

		// default gifts
		if (empty($params['USE_GIFTS_DETAIL']))
		{
			$params['USE_GIFTS_DETAIL'] = 'Y';
		}

		if (empty($params['USE_GIFTS_MAIN_PR_SECTION_LIST']))
		{
			$params['USE_GIFTS_MAIN_PR_SECTION_LIST'] = 'Y';
		}

		if (empty($params['GIFTS_DETAIL_PAGE_ELEMENT_COUNT']))
		{
			$params['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
		}

		if (empty($params['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT']))
		{
			$params['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'] = 4;
		}

		$this->storage['IBLOCK_PARAMS'] = $this->getIblockParams($params);

		return $params;
	}

	protected function getIblockParams(&$params)
	{
		$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

		if (!isset($params['PROPERTY_CODE']) || !is_array($params['PROPERTY_CODE']))
		{
			$params['PROPERTY_CODE'] = array();
		}

		foreach ($params['PROPERTY_CODE'] as $k => $v)
		{
			if ($v == '')
			{
				unset($params['PROPERTY_CODE'][$k]);
			}
		}

		if (!isset($params['OFFERS_FIELD_CODE']))
		{
			$params['OFFERS_FIELD_CODE'] = array();
		}
		elseif (!is_array($params['OFFERS_FIELD_CODE']))
		{
			$params['OFFERS_FIELD_CODE'] = array($params['OFFERS_FIELD_CODE']);
		}

		foreach ($params['OFFERS_FIELD_CODE'] as $key => $value)
		{
			if ($value == '')
			{
				unset($params['OFFERS_FIELD_CODE'][$key]);
			}
		}

		if (!isset($params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'] = array();
		}
		elseif (!is_array($params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'] = array($params['OFFERS_PROPERTY_CODE']);
		}

		foreach ($params['OFFERS_PROPERTY_CODE'] as $key => $value)
		{
			if ($value == '')
			{
				unset($params['OFFERS_PROPERTY_CODE'][$key]);
			}
		}

		if (!in_array('PREVIEW_PICTURE', $params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'][] = 'PREVIEW_PICTURE';
		}

		if (!in_array('DETAIL_PICTURE', $params['OFFERS_PROPERTY_CODE']))
		{
			$params['OFFERS_PROPERTY_CODE'][] = 'DETAIL_PICTURE';
		}

		$cartProperties = [];
		$offersCartProperties = [];
		$offerTreeProperties = [];
		if (!$usePropertyFeatures)
		{
			if (!isset($params['PRODUCT_PROPERTIES']) || !is_array($params['PRODUCT_PROPERTIES']))
			{
				$params['PRODUCT_PROPERTIES'] = array();
			}

			foreach ($params['PRODUCT_PROPERTIES'] as $k => $v)
			{
				if ($v == '')
				{
					unset($params['PRODUCT_PROPERTIES'][$k]);
				}
			}
			$cartProperties = $params['PRODUCT_PROPERTIES'];

			if (!isset($params['OFFERS_CART_PROPERTIES']) || !is_array($params['OFFERS_CART_PROPERTIES']))
			{
				$params['OFFERS_CART_PROPERTIES'] = array();
			}

			foreach ($params['OFFERS_CART_PROPERTIES'] as $i => $pid)
			{
				if ($pid == '')
				{
					unset($params['OFFERS_CART_PROPERTIES'][$i]);
				}
			}
			$offersCartProperties = $params['OFFERS_CART_PROPERTIES'];

			if (!isset($params['OFFER_TREE_PROPS']))
			{
				$params['OFFER_TREE_PROPS'] = array();
			}
			elseif (!is_array($params['OFFER_TREE_PROPS']))
			{
				$params['OFFER_TREE_PROPS'] = array($params['OFFER_TREE_PROPS']);
			}

			foreach ($params['OFFER_TREE_PROPS'] as $key => $value)
			{
				$value = (string)$value;
				if ($value == '' || $value === '-')
				{
					unset($params['OFFER_TREE_PROPS'][$key]);
				}
			}

			if (empty($params['OFFER_TREE_PROPS']) && !empty($params['OFFERS_CART_PROPERTIES']))
			{
				$params['OFFER_TREE_PROPS'] = $params['OFFERS_CART_PROPERTIES'];
				foreach ($params['OFFER_TREE_PROPS'] as $key => $value)
				{
					if ($value === '-')
					{
						unset($params['OFFER_TREE_PROPS'][$key]);
					}
				}
			}
			$offerTreeProperties = $params['OFFER_TREE_PROPS'];
		}

		return array(
			$params['IBLOCK_ID'] => array(
				'PROPERTY_CODE' => $params['PROPERTY_CODE'],
				'CART_PROPERTIES' => $cartProperties,
				'OFFERS_FIELD_CODE' => $params['OFFERS_FIELD_CODE'],
				'OFFERS_PROPERTY_CODE' => $params['OFFERS_PROPERTY_CODE'],
				'OFFERS_CART_PROPERTIES' => $offersCartProperties,
				'OFFERS_TREE_PROPS' => $offerTreeProperties
			)
		);
	}

	protected function checkModules()
	{
		if ($success = parent::checkModules())
		{
			$this->storage['MODULES']['workflow'] = false;
		}

		return $success;
	}

	protected function initialLoadAction()
	{
		parent::initialLoadAction();

		if (!$this->hasErrors() && isset($this->arResult['ID']))
		{
			$this->initAdminIconsPanel();
			$this->sendCounters();
			$this->saveViewedProduct();
			$this->initMetaData();
		}
	}

	protected function processResultData()
	{
		if ($this->checkElementId())
		{
			parent::processResultData();
			$this->arResult['IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED'] = false;
			if (Loader::includeModule('sale'))
			{
				$this->arResult['IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED'] = FacebookConversion::isEventEnabled(
					'CustomizeProduct'
				);
			}

		}
		else
		{
			$this->abortResultCache();
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_ELEMENT_NOT_FOUND'), self::ERROR_404));
		}
	}

	/**
	 * Check by ID if element is correct.
	 *
	 * @return bool
	 */
	protected function checkElementId()
	{
		if ($this->arParams['ELEMENT_ID'] <= 0)
		{
			$findFilter = array(
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
				'IBLOCK_LID' => $this->getSiteId(),
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			);

			if ($this->arParams['SHOW_DEACTIVATED'] !== 'Y')
			{
				$findFilter['ACTIVE'] = 'Y';
			}

			$this->arParams['ELEMENT_ID'] = \CIBlockFindTools::GetElementID(
				$this->arParams['ELEMENT_ID'],
				$this->arParams['~ELEMENT_CODE'],
				$this->arParams['STRICT_SECTION_CHECK']? $this->arParams['SECTION_ID']: 0,
				$this->arParams['STRICT_SECTION_CHECK']? $this->arParams['~SECTION_CODE']: '',
				$findFilter
			);
		}

		return $this->arParams['ELEMENT_ID'] > 0;
	}

	protected function initCatalogInfo()
	{
		parent::initCatalogInfo();
		$useCatalogButtons = array();

		if (
			!empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
			&& is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
		)
		{
			$catalogType = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']]['CATALOG_TYPE'];
			if ($catalogType == \CCatalogSku::TYPE_CATALOG || $catalogType == \CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_product'] = true;
			}

			if ($catalogType == \CCatalogSku::TYPE_PRODUCT || $catalogType == \CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_sku'] = true;
			}
			unset($catalogType);
		}

		$this->arResult['USE_CATALOG_BUTTONS'] = $useCatalogButtons;
	}

	/**
	 * @return void
	 */
	protected function initIblockPropertyFeatures()
	{
		if (!Iblock\Model\PropertyFeature::isEnabledFeatures())
			return;

		$this->loadDisplayPropertyCodes($this->arParams['IBLOCK_ID']);
		$this->loadBasketPropertyCodes($this->arParams['IBLOCK_ID']);
		$this->loadOfferTreePropertyCodes($this->arParams['IBLOCK_ID']);
	}

	protected function checkProductIblock(array $product): bool
	{
		return ($product['PRODUCT_IBLOCK_ID'] == $this->arParams['IBLOCK_ID']);
	}

	/**
	 * @param int $iblockId
	 * @return void
	 */
	protected function loadDisplayPropertyCodes($iblockId)
	{
		$list = Iblock\Model\PropertyFeature::getDetailPageShowProperties(
			$iblockId,
			['CODE' => 'Y']
		);
		if ($list === null)
			$list = [];
		$this->storage['IBLOCK_PARAMS'][$iblockId]['PROPERTY_CODE'] = $list;
		if ($this->useCatalog)
		{
			$list = Iblock\Model\PropertyFeature::getDetailPageShowProperties(
				$this->getOffersIblockId($iblockId),
				['CODE' => 'Y']
			);
			if ($list === null)
				$list = [];
			$this->storage['IBLOCK_PARAMS'][$iblockId]['OFFERS_PROPERTY_CODE'] = $list;
		}
		unset($list);
	}

	protected function getSelect()
	{
		$selectFields = parent::getSelect();
		$selectFields[] = 'LIST_PAGE_URL';
		$selectFields[] = 'PROPERTY_*';

		if ($this->arParams['SET_CANONICAL_URL'] === 'Y')
		{
			$selectFields[] = 'CANONICAL_PAGE_URL';
		}

		return $selectFields;
	}

	protected function getFilter()
	{
		$filterFields = parent::getFilter();
		$filterFields['SHOW_HISTORY'] = $this->showWorkflowHistory();

		if ($this->arParams['SHOW_DEACTIVATED'] !== 'Y')
		{
			$filterFields['ACTIVE'] = 'Y';
		}

		return $filterFields;
	}

	protected function showWorkflowHistory()
	{
		$wfShowHistory = 'N';

		if ($this->arParams['SHOW_WORKFLOW'] && Loader::includeModule('workflow'))
		{
			$this->storage['modules']['workflow'] = true;

			$wfElementId = \CIBlockElement::WF_GetLast($this->arParams['ELEMENT_ID']);
			$wfStatusId = \CIBlockElement::WF_GetCurrentStatus($wfElementId, $wfStatusTitle);
			$wfStatusPermission = \CIBlockElement::WF_GetStatusPermission($wfStatusId);

			if ($wfStatusId == 1 || $wfStatusPermission < 1)
			{
				$wfElementId = $this->arParams['ELEMENT_ID'];
			}
			else
			{
				$wfShowHistory = 'Y';
			}

			$this->arParams['ELEMENT_ID'] = $wfElementId;
		}

		return $wfShowHistory;
	}

	protected function getElementList($iblockId, $products)
	{
		$section = $this->getSection();

		if ($this->arParams['STRICT_SECTION_CHECK'])
		{
			$sectionId = !empty($section) ? $section['ID'] : 0;

			if ($this->arParams['USE_MAIN_ELEMENT_SECTION'])
			{
				$this->filterFields['IBLOCK_SECTION_ID'] = $sectionId;
			}
			else
			{
				$this->filterFields['SECTION_ID'] = $sectionId;
				$this->filterFields['INCLUDE_SUBSECTIONS'] = 'N';
			}
		}

		$elementIterator = parent::getElementList($iblockId, $products);

		if (!empty($elementIterator) && !$this->arParams['USE_MAIN_ELEMENT_SECTION'])
		{
			$elementIterator->SetSectionContext($section);
		}

		$this->storage['SECTION'] = $section;

		return $elementIterator;
	}

	protected function getSection()
	{
		$section = false;
		$sectionFilter = array(
			'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
		);

		if ($this->arParams['SECTION_ID'] > 0 || $this->arParams['SECTION_CODE'] !== '')
		{
			if ($this->arParams['SECTION_ID'] > 0)
			{
				$sectionFilter['ID'] = $this->arParams['SECTION_ID'];
			}
			else
			{
				$sectionFilter['HAS_ELEMENT'] = $this->arParams['ELEMENT_ID'];
				$sectionFilter['=CODE'] = $this->arParams['SECTION_CODE'];
			}

			$sectionIterator = \CIBlockSection::GetList(array(), $sectionFilter);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$section = $sectionIterator->GetNext();
		}
		elseif ($this->arParams['CHECK_SECTION_ID_VARIABLE'] === 'Y' && $this->arParams['FROM_SECTION'] !== '')
		{
			if ((int)$this->arParams['FROM_SECTION'].'|' == $this->arParams['FROM_SECTION'].'|')
			{
				$sectionFilter['ID'] = $this->arParams['FROM_SECTION'];
			}
			else
			{
				$sectionFilter['HAS_ELEMENT'] = $this->arParams['ELEMENT_ID'];
				$sectionFilter['=CODE'] = $this->arParams['FROM_SECTION'];
			}

			$sectionIterator = \CIBlockSection::GetList(array(), $sectionFilter);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$section = $sectionIterator->GetNext();
		}

		return $section;
	}

	protected function getIblockElements($elementIterator)
	{
		$iblockElements = array();

		/** @var \CIBlockResult $elementIterator */
		if (!empty($elementIterator) && ($elementObject = $elementIterator->GetNextElement()))
		{
			$element = $elementObject->GetFields();
			$this->processElement($element);
			$iblockElements[$element['ID']] = $element;
		}
		else
		{
			$this->abortResultCache();
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_ELEMENT_NOT_FOUND'), self::ERROR_404));
		}

		return $iblockElements;
	}

	protected function modifyDisplayProperties($iblock, &$iblockElements)
	{
		if (!empty($iblockElements))
		{
			reset($iblockElements);
			$elementKey = key($iblockElements);
			$element =& $iblockElements[$elementKey];

			$iblockParams = $this->storage['IBLOCK_PARAMS'][$iblock];
			$propFilter = array(
				'ID' => array_keys($iblockElements),
				'IBLOCK_ID' => $iblock
			);
			\CIBlockElement::GetPropertyValuesArray($iblockElements, $iblock, $propFilter);

			if (!empty($iblockParams['PROPERTY_CODE']))
			{
				$propertyList = $this->getPropertyList($iblock, $iblockParams['PROPERTY_CODE']);
			}

			if ($this->useCatalog && $this->useDiscountCache)
			{
				if ($this->storage['USE_SALE_DISCOUNTS'])
					Catalog\Discount\DiscountManager::setProductPropertiesCache($element['ID'], $element["PROPERTIES"]);
				else
					\CCatalogDiscount::SetProductPropertiesCache($element['ID'], $element['PROPERTIES']);
			}

			if (!empty($propertyList))
			{
				foreach ($propertyList as $pid)
				{
					if (!isset($element['PROPERTIES'][$pid]))
						continue;

					$prop =& $element['PROPERTIES'][$pid];
					$isArr = is_array($prop['VALUE']);
					if (
						($isArr && !empty($prop['VALUE']))
						|| (!$isArr && (string)$prop['VALUE'] !== '')
						|| Tools::isCheckboxProperty($prop)
					)
					{
						$element['DISPLAY_PROPERTIES'][$pid] = \CIBlockFormatProperties::GetDisplayValue($element, $prop);
					}
					unset($prop);
				}
				unset($pid);

				\CIBlockFormatProperties::clearCache();
				Tools::clearCache();
			}

			if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($iblockParams['CART_PROPERTIES']))
			{
				$element['PRODUCT_PROPERTIES'] = \CIBlockPriceTools::GetProductProperties(
					$iblock,
					$element['ID'],
					$iblockParams['CART_PROPERTIES'],
					$element['PROPERTIES']
				);

				if (!empty($element['PRODUCT_PROPERTIES']))
				{
					$element['PRODUCT_PROPERTIES_FILL'] = \CIBlockPriceTools::getFillProductProperties($element['PRODUCT_PROPERTIES']);
				}
			}

			$element['BACKGROUND_IMAGE'] = false;

			if ($this->arParams['BACKGROUND_IMAGE'] != '' && isset($element['PROPERTIES'][$this->arParams['BACKGROUND_IMAGE']]))
			{
				if (!empty($element['PROPERTIES'][$this->arParams['BACKGROUND_IMAGE']]['VALUE']))
				{
					$element['BACKGROUND_IMAGE'] = \CFile::GetFileArray($element['PROPERTIES'][$this->arParams['BACKGROUND_IMAGE']]['VALUE']);
				}
			}

			$element['MORE_PHOTO'] = array();

			if (!empty($element['PROPERTIES']['MORE_PHOTO']['VALUE']) && is_array($element['PROPERTIES']['MORE_PHOTO']['VALUE']))
			{
				foreach ($element['PROPERTIES']['MORE_PHOTO']['VALUE'] as $file)
				{
					$file = \CFile::GetFileArray($file);
					if (is_array($file))
					{
						$element['MORE_PHOTO'][] = $file;
					}
				}
			}

			$element['LINKED_ELEMENTS'] = array();

			if (
				$this->arParams['LINK_PROPERTY_SID'] <> ''
				&& $this->arParams['LINK_IBLOCK_TYPE'] <> ''
				&& $this->arParams['LINK_IBLOCK_ID'] > 0
			)
			{
				$linkElementIterator = \CIBlockElement::GetList(
					array('SORT' => 'ASC'),
					array(
						'IBLOCK_ID' => $this->arParams['LINK_IBLOCK_ID'],
						'IBLOCK_ACTIVE' => 'Y',
						'ACTIVE_DATE' => 'Y',
						'ACTIVE' => 'Y',
						'CHECK_PERMISSIONS' => 'Y',
						'IBLOCK_TYPE' => $this->arParams['LINK_IBLOCK_TYPE'],
						'PROPERTY_'.$this->arParams['LINK_PROPERTY_SID'] => $element['ID'],
					),
					false,
					false,
					array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'IBLOCK_NAME')
				);
				while ($linkElement = $linkElementIterator->GetNext())
				{
					$element['LINKED_ELEMENTS'][] = $linkElement;
				}
			}

			if (!$this->storage['SECTION'] && $element['IBLOCK_SECTION_ID'] > 0)
			{
				$sectionFilter = array(
					'ID' => $element['IBLOCK_SECTION_ID'],
					'IBLOCK_ID' => $element['IBLOCK_ID'],
					'ACTIVE' => 'Y',
				);
				$sectionIterator = \CIBlockSection::GetList(array(), $sectionFilter);
				$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				$this->storage['SECTION'] = $sectionIterator->GetNext();
				unset($sectionIterator);
			}

			if (!empty($this->storage['SECTION']))
			{
				$blackList = array(
					'SEARCHABLE_CONTENT', '~SEARCHABLE_CONTENT',
					'TIMESTAMP_X', '~TIMESTAMP_X',
					'DATE_CREATE', '~DATE_CREATE',
				);
				foreach ($blackList as $fieldName)
				{
					if (array_key_exists($fieldName, $this->storage['SECTION']))
					{
						unset($this->storage['SECTION'][$fieldName]);
					}
				}
				unset($fieldName, $blackList);

				$this->storage['SECTION']['PATH'] = array();
				$pathIterator = \CIBlockSection::GetNavChain(
					$element['IBLOCK_ID'],
					$this->storage['SECTION']['ID'],
					array(
						'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
						'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
						'DEPTH_LEVEL', 'SECTION_PAGE_URL'
					)
				);
				$pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				while ($path = $pathIterator->GetNext())
				{
					if ($this->arParams["ADD_SECTIONS_CHAIN"])
					{
						$ipropValues = new Iblock\InheritedProperty\SectionValues($element['IBLOCK_ID'], $path['ID']);
						$path['IPROPERTY_VALUES'] = $ipropValues->getValues();
					}

					$this->storage['SECTION']['PATH'][] = $path;
				}
				unset($path, $pathIterator);

				if ($this->arParams['SECTIONS_CHAIN_START_FROM'] > 0)
				{
					$this->storage['SECTION']['PATH'] = array_slice($this->storage['SECTION']['PATH'], $this->arParams['SECTIONS_CHAIN_START_FROM']);
				}

				$element['SECTION'] = $this->storage['SECTION'];
			}

			unset($element);
		}
	}

	protected function chooseOffer($offers, $iblockId)
	{
		$uniqueSortHash = array();

		foreach ($offers as $offer)
		{
			$elementId = $offer['LINK_ELEMENT_ID'];

			if (!isset($this->elementLinks[$elementId]))
				continue;

			if (!isset($uniqueSortHash[$elementId]))
			{
				$uniqueSortHash[$elementId] = array();
			}

			$uniqueSortHash[$elementId][$offer['SORT_HASH']] = true;

			if ($this->elementLinks[$elementId]['OFFER_ID_SELECTED'] == 0 && $offer['CAN_BUY'])
			{
				$this->elementLinks[$elementId]['OFFER_ID_SELECTED'] = $offer['ID'];
			}
		}
		unset($elementId, $offer);

		foreach ($this->elementLinks as &$element)
		{
			if ($element['OFFER_ID_SELECTED'] == 0)
				continue;

			if (count($uniqueSortHash[$element['ID']]) < 2)
			{
				$element['OFFER_ID_SELECTED'] = 0;
			}
		}
	}

	protected function getCacheKeys()
	{
		$resultCacheKeys = array(
			'IBLOCK_ID',
			'ID',
			'IBLOCK_SECTION_ID',
			'NAME',
			'LIST_PAGE_URL',
			'CANONICAL_PAGE_URL',
			'SECTION',
			'IPROPERTY_VALUES',
			'TIMESTAMP_X',
			'BACKGROUND_IMAGE',
			'USE_CATALOG_BUTTONS'
		);

		$this->initAdditionalCacheKeys($resultCacheKeys);

		if (
			$this->arParams['SET_TITLE']
			|| $this->arParams['ADD_ELEMENT_CHAIN']
			|| $this->arParams['SET_BROWSER_TITLE'] === 'Y'
			|| $this->arParams['SET_META_KEYWORDS'] === 'Y'
			|| $this->arParams['SET_META_DESCRIPTION'] === 'Y'
		)
		{
			$this->arResult['META_TAGS'] = array();
			$resultCacheKeys[] = 'META_TAGS';

			$elementTitle = $this->arResult['NAME'];
			if (
				isset($this->arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
				&& $this->arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] !== ''
			)
			{
				$elementTitle = $this->arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'];
			}
			if ($this->arParams['SET_TITLE'])
			{
				$this->arResult['META_TAGS']['TITLE'] = $elementTitle;
			}

			if ($this->arParams['ADD_ELEMENT_CHAIN'])
			{
				$this->arResult['META_TAGS']['ELEMENT_CHAIN'] = $elementTitle;
			}

			if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
			{
				$browserTitle = Collection::firstNotEmpty(
					$this->arResult['PROPERTIES'], array($this->arParams['BROWSER_TITLE'], 'VALUE'),
					$this->arResult, $this->arParams['BROWSER_TITLE'],
					$this->arResult['IPROPERTY_VALUES'], 'ELEMENT_META_TITLE'
				);
				$this->arResult['META_TAGS']['BROWSER_TITLE'] = is_array($browserTitle)
					? implode(' ', $browserTitle)
					: $browserTitle;
				unset($browserTitle);
			}

			if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
			{
				$metaKeywords = Collection::firstNotEmpty(
					$this->arResult['PROPERTIES'], array($this->arParams['META_KEYWORDS'], 'VALUE'),
					$this->arResult['IPROPERTY_VALUES'], 'ELEMENT_META_KEYWORDS'
				);
				$this->arResult['META_TAGS']['KEYWORDS'] = is_array($metaKeywords)
					? implode(' ', $metaKeywords)
					: $metaKeywords;
				unset($metaKeywords);
			}

			if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
			{
				$metaDescription = Collection::firstNotEmpty(
					$this->arResult['PROPERTIES'], array($this->arParams['META_DESCRIPTION'], 'VALUE'),
					$this->arResult['IPROPERTY_VALUES'], 'ELEMENT_META_DESCRIPTION'
				);
				$this->arResult['META_TAGS']['DESCRIPTION'] = is_array($metaDescription)
					? implode(' ', $metaDescription)
					: $metaDescription;
				unset($metaDescription);
			}
		}

		return $resultCacheKeys;
	}

	/**
	 * Fill additional keys for component cache.
	 *
	 * @param array &$resultCacheKeys		Cached result keys.
	 * @return void
	 */
	protected function initAdditionalCacheKeys(&$resultCacheKeys)
	{
	}

	protected function initAdminIconsPanel()
	{
		global $APPLICATION, $USER;

		if (!$USER->IsAuthorized())
			return;

		$arResult =& $this->arResult;

		if (
			$APPLICATION->GetShowIncludeAreas()
			|| $this->arParams['SET_TITLE']
			|| isset($arResult[$this->arParams['BROWSER_TITLE']])
		)
		{
			if (Loader::includeModule('iblock'))
			{
				$returnUrl = array(
					'add_element' => \CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'DETAIL_PAGE_URL'),
					'delete_element' =>
						isset($arResult['SECTION'])
							? $arResult['SECTION']['SECTION_PAGE_URL']
							: $arResult['LIST_PAGE_URL'],
				);
				$buttonParams = array(
					'RETURN_URL' => $returnUrl,
					'CATALOG' => true
				);

				if (isset($arResult['USE_CATALOG_BUTTONS']))
				{
					$buttonParams['USE_CATALOG_BUTTONS'] = $arResult['USE_CATALOG_BUTTONS'];
					if (!empty($buttonParams['USE_CATALOG_BUTTONS']))
					{
						$buttonParams['SHOW_CATALOG_BUTTONS'] = true;
					}
				}

				$buttons = \CIBlock::GetPanelButtons(
					$arResult['IBLOCK_ID'],
					$arResult['ID'],
					$arResult['IBLOCK_SECTION_ID'],
					$buttonParams
				);
				unset($buttonParams);

				if ($APPLICATION->GetShowIncludeAreas())
				{
					$this->addIncludeAreaIcons(\CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
				}

				if ($this->arParams['SET_TITLE'] || isset($arResult[$this->arParams['BROWSER_TITLE']]))
				{
					$this->storage['TITLE_OPTIONS'] = null;
					if (isset($buttons['submenu']['edit_element']))
					{
						$this->storage['TITLE_OPTIONS'] = [
							'ADMIN_EDIT_LINK' => $buttons['submenu']['edit_element']['ACTION'],
							'PUBLIC_EDIT_LINK' => $buttons['edit']['edit_element']['ACTION'],
							'COMPONENT_NAME' => $this->getName(),
						];
					}
				}
			}
		}
	}

	protected function sendCounters()
	{
		if ($this->arParams['USE_ELEMENT_COUNTER'] !== 'N' && Loader::includeModule('iblock'))
		{
			\CIBlockElement::CounterInc($this->arResult['ID']);
		}
	}

	protected function saveViewedProduct()
	{
	}

	protected function initMetaData()
	{
		global $APPLICATION;
		$arResult =& $this->arResult;

		if ($this->arParams['SET_CANONICAL_URL'] === 'Y' && $arResult["CANONICAL_PAGE_URL"])
			$APPLICATION->SetPageProperty('canonical', $arResult["CANONICAL_PAGE_URL"]);

		if ($this->arParams['SET_TITLE'])
		{
			$APPLICATION->SetTitle($arResult["META_TAGS"]["TITLE"], $this->storage['TITLE_OPTIONS']);
		}

		if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
		{
			if ($arResult["META_TAGS"]["BROWSER_TITLE"] !== '')
			{
				$APPLICATION->SetPageProperty("title", $arResult["META_TAGS"]["BROWSER_TITLE"], $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
		{
			if ($arResult["META_TAGS"]["KEYWORDS"] !== '')
			{
				$APPLICATION->SetPageProperty("keywords", $arResult["META_TAGS"]["KEYWORDS"], $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
		{
			if ($arResult["META_TAGS"]["DESCRIPTION"] !== '')
			{
				$APPLICATION->SetPageProperty("description", $arResult["META_TAGS"]["DESCRIPTION"], $this->storage['TITLE_OPTIONS']);
			}
		}

		if (!empty($arResult['BACKGROUND_IMAGE']) && is_array($arResult['BACKGROUND_IMAGE']))
		{
			$APPLICATION->SetPageProperty(
				'backgroundImage',
				'style="background-image: url(\''.\CHTTP::urnEncode($arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8').'\')"'
			);
		}

		if ($this->arParams['ADD_SECTIONS_CHAIN'] && !empty($arResult['SECTION']['PATH']) && is_array($arResult['SECTION']['PATH']))
		{
			foreach ($arResult['SECTION']['PATH'] as $path)
			{
				if (isset($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE']) && $path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] !== '')
				{
					$APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
				}
				else
				{
					$APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
				}
			}
		}

		if ($this->arParams['ADD_ELEMENT_CHAIN'])
		{
			$APPLICATION->AddChainItem($arResult["META_TAGS"]["ELEMENT_CHAIN"]);
		}

		if ($this->arParams['SET_LAST_MODIFIED'] && $arResult['TIMESTAMP_X'])
		{
			Main\Context::getCurrent()->getResponse()->setLastModified(DateTime::createFromUserTime($arResult["TIMESTAMP_X"]));
		}
	}

	protected function getAdditionalCacheId()
	{
		return array(
			$this->productIdMap,
			$this->arParams['CACHE_GROUPS'] === 'N' ? false : $this->getUserGroupsCacheId()
		);
	}

	protected function getComponentCachePath()
	{
		return false;
	}

	protected function makeOutputResult()
	{
		parent::makeOutputResult();
		$this->arResult['CAT_PRICES'] = $this->storage['PRICES'];
		$this->arResult = array_merge($this->arResult, $this->elements[0]);
	}

	protected function prepareTemplateParams()
	{
		parent::prepareTemplateParams();
		$params =& $this->arParams;

		$params['DISPLAY_NAME'] = $params['DISPLAY_NAME'] === 'N' ? 'N' : 'Y';
		$params['USE_RATIO_IN_RANGES'] = $params['USE_RATIO_IN_RANGES'] === 'N' ? 'N' : 'Y';

		if (!isset($params['IMAGE_RESOLUTION']))
		{
			$params['IMAGE_RESOLUTION'] = '16by9';
		}

		if (!is_array($params['DETAIL_PICTURE_MODE']))
		{
			$params['DETAIL_PICTURE_MODE'] = array($params['DETAIL_PICTURE_MODE']);
		}

		$params['ADD_DETAIL_TO_SLIDER'] = $params['ADD_DETAIL_TO_SLIDER'] === 'Y' ? 'Y' : 'N';
		$displayPreviewTextMode = array('H' => true, 'E' => true, 'S' => true);
		if (!isset($displayPreviewTextMode[$params['DISPLAY_PREVIEW_TEXT_MODE']]))
		{
			$params['DISPLAY_PREVIEW_TEXT_MODE'] = 'E';
		}

		if (!is_array($params['ADD_TO_BASKET_ACTION']))
		{
			$params['ADD_TO_BASKET_ACTION'] = array($params['ADD_TO_BASKET_ACTION']);
		}

		$params['ADD_TO_BASKET_ACTION'] = array_filter($params['ADD_TO_BASKET_ACTION'], 'CIBlockParameters::checkParamValues');
		if (empty($params['ADD_TO_BASKET_ACTION']) || (!in_array('ADD', $params['ADD_TO_BASKET_ACTION']) && !in_array('BUY', $params['ADD_TO_BASKET_ACTION'])))
		{
			$params['ADD_TO_BASKET_ACTION'] = array('BUY');
		}

		if (!isset($params['ADD_TO_BASKET_ACTION_PRIMARY']) || !is_array($params['ADD_TO_BASKET_ACTION_PRIMARY']))
		{
			$params['ADD_TO_BASKET_ACTION_PRIMARY'] = array('BUY', 'ADD');
		}

		$params['USE_VOTE_RATING'] = $params['USE_VOTE_RATING'] === 'Y' ? 'Y' : 'N';

		if ($params['VOTE_DISPLAY_AS_RATING'] != 'vote_avg')
		{
			$params['VOTE_DISPLAY_AS_RATING'] = 'rating';
		}

		$params['USE_COMMENTS'] = $params['USE_COMMENTS'] === 'Y' ? 'Y' : 'N';
		$params['BLOG_USE'] = $params['BLOG_USE'] === 'Y' ? 'Y' : 'N';
		$params['VK_USE'] = $params['VK_USE'] === 'Y' && !empty($params['VK_API_ID']) ? 'Y' : 'N';
		$params['FB_USE'] = $params['FB_USE'] === 'Y' && !empty($params['FB_APP_ID']) ? 'Y' : 'N';

		if ($params['USE_COMMENTS'] === 'Y')
		{
			if ($params['BLOG_USE'] === 'N' && $params['VK_USE'] === 'N' && $params['FB_USE'] === 'N')
			{
				$params['USE_COMMENTS'] = 'N';
			}
		}

		$params['BRAND_USE'] = $params['BRAND_USE'] === 'Y' ? 'Y' : 'N';

		if ($params['BRAND_PROP_CODE'] == '')
		{
			$params['BRAND_PROP_CODE'] = array();
		}

		if (!is_array($params['BRAND_PROP_CODE']))
		{
			$params['BRAND_PROP_CODE'] = array($params['BRAND_PROP_CODE']);
		}

		if (empty($params['PRODUCT_INFO_BLOCK_ORDER']))
		{
			$params['PRODUCT_INFO_BLOCK_ORDER'] = 'sku,props,priceRanges';
		}

		if (is_string($params['PRODUCT_INFO_BLOCK_ORDER']))
		{
			$params['PRODUCT_INFO_BLOCK_ORDER'] = explode(',', $params['PRODUCT_INFO_BLOCK_ORDER']);
		}

		if (empty($params['PRODUCT_PAY_BLOCK_ORDER']))
		{
			$params['PRODUCT_PAY_BLOCK_ORDER'] = 'rating,price,quantityLimit,quantity,buttons';
		}

		if (is_string($params['PRODUCT_PAY_BLOCK_ORDER']))
		{
			$params['PRODUCT_PAY_BLOCK_ORDER'] = explode(',', $params['PRODUCT_PAY_BLOCK_ORDER']);
		}

		$this->getTemplateIblockParams($params);
	}

	protected function getTemplateIblockParams(&$params)
	{
		$params['ADD_PICT_PROP'] = isset($params['ADD_PICT_PROP']) ? trim($params['ADD_PICT_PROP']) : '';
		if ($params['ADD_PICT_PROP'] === '-')
		{
			$params['ADD_PICT_PROP'] = '';
		}

		if (!isset($params['LABEL_PROP']) || !is_array($params['LABEL_PROP']))
		{
			$params['LABEL_PROP'] = array();
		}

		if (!isset($params['LABEL_PROP_MOBILE']) || !is_array($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = array();
		}

		if (!empty($params['LABEL_PROP_MOBILE']))
		{
			$params['LABEL_PROP_MOBILE'] = array_flip($params['LABEL_PROP_MOBILE']);
		}
		$params['ENLARGE_PROP'] = isset($params['ENLARGE_PROP']) ? trim($params['ENLARGE_PROP']) : '';
		if ($params['ENLARGE_PROP'] === '-')
		{
			$params['ENLARGE_PROP'] = '';
		}

		$params['OFFER_ADD_PICT_PROP'] = isset($params['OFFER_ADD_PICT_PROP']) ? trim($params['OFFER_ADD_PICT_PROP']) : '';
		if ($params['OFFER_ADD_PICT_PROP'] === '-')
		{
			$params['OFFER_ADD_PICT_PROP'] = '';
		}

		if (!isset($params['MAIN_BLOCK_PROPERTY_CODE']) || !is_array($params['MAIN_BLOCK_PROPERTY_CODE']))
		{
			$params['MAIN_BLOCK_PROPERTY_CODE'] = array();
		}

		if (!empty($params['MAIN_BLOCK_PROPERTY_CODE']))
		{
			$params['MAIN_BLOCK_PROPERTY_CODE'] = array_flip($params['MAIN_BLOCK_PROPERTY_CODE']);
		}

		if (!isset($params['MAIN_BLOCK_OFFERS_PROPERTY_CODE']) || !is_array($params['MAIN_BLOCK_OFFERS_PROPERTY_CODE']))
		{
			$params['MAIN_BLOCK_OFFERS_PROPERTY_CODE'] = array();
		}

		if (!empty($params['MAIN_BLOCK_OFFERS_PROPERTY_CODE']))
		{
			$params['MAIN_BLOCK_OFFERS_PROPERTY_CODE'] = array_flip($params['MAIN_BLOCK_OFFERS_PROPERTY_CODE']);
		}

		if (!isset($this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]) || !is_array($this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]))
		{
			$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']] = array();
		}

		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['ADD_PICT_PROP'] = $params['ADD_PICT_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['LABEL_PROP'] = $params['LABEL_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['LABEL_PROP_MOBILE'] = $params['LABEL_PROP_MOBILE'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['ENLARGE_PROP'] = $params['ENLARGE_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['OFFERS_ADD_PICT_PROP'] = $params['OFFER_ADD_PICT_PROP'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['MAIN_BLOCK_PROPERTY_CODE'] = $params['MAIN_BLOCK_PROPERTY_CODE'];
		$this->storage['IBLOCK_PARAMS'][$params['IBLOCK_ID']]['MAIN_BLOCK_OFFERS_PROPERTY_CODE'] = $params['MAIN_BLOCK_OFFERS_PROPERTY_CODE'];

		unset($skuTreeProperties);
	}

	protected function getTemplateDefaultParams()
	{
		$defaultParams = parent::getTemplateDefaultParams();
		$defaultParams['DISPLAY_NAME'] = 'Y';
		$defaultParams['USE_RATIO_IN_RANGES'] = 'Y';
		$defaultParams['IMAGE_RESOLUTION'] = '16by9';
		$defaultParams['DETAIL_PICTURE_MODE'] = array('POPUP', 'MAGNIFIER');
		$defaultParams['ADD_DETAIL_TO_SLIDER'] = 'N';
		$defaultParams['DISPLAY_PREVIEW_TEXT_MODE'] = 'E';
		$defaultParams['ADD_TO_BASKET_ACTION'] = array('BUY');
		$defaultParams['ADD_TO_BASKET_ACTION_PRIMARY'] = array('BUY', 'ADD');
		$defaultParams['USE_VOTE_RATING'] = 'N';
		$defaultParams['VOTE_DISPLAY_AS_RATING'] = 'rating';
		$defaultParams['USE_COMMENTS'] = 'N';
		$defaultParams['BLOG_USE'] = 'N';
		$defaultParams['BLOG_URL'] = 'catalog_comments';
		$defaultParams['BLOG_EMAIL_NOTIFY'] = 'N';
		$defaultParams['VK_USE'] = 'N';
		$defaultParams['VK_API_ID'] = '';
		$defaultParams['FB_USE'] = 'N';
		$defaultParams['FB_APP_ID'] = '';
		$defaultParams['BRAND_USE'] = 'N';
		$defaultParams['BRAND_PROP_CODE'] = '';

		return $defaultParams;
	}

	protected function editTemplateData()
	{
		$this->arResult['DEFAULT_PICTURE'] = $this->getTemplateEmptyPreview();
		$this->arResult['SKU_PROPS'] = $this->getTemplateSkuPropList();
		$this->arResult['CURRENCIES'] = $this->getTemplateCurrencies();
		$this->editTemplateItems($this->arResult);
	}

	protected function editTemplateOfferProps(&$item)
	{
		$matrix = array();
		$newOffers = array();
		$double = array();
		$item['OFFER_GROUP'] = false;
		$item['OFFERS_PROP'] = false;

		$iblockParams = $this->storage['IBLOCK_PARAMS'][$item['IBLOCK_ID']];

		$boolSkuDisplayProps = false;
		$skuPropList = $this->arResult['SKU_PROPS'];
		$skuPropIds = array_keys($skuPropList);
		$matrixFields = array_fill_keys($skuPropIds, false);

		foreach ($item['OFFERS'] as $keyOffer => $offer)
		{
			$offer['ID'] = (int)$offer['ID'];

			if (isset($double[$offer['ID']]))
				continue;

			$offer['OFFER_GROUP'] = false;

			$row = array();
			foreach ($skuPropIds as $code)
			{
				$row[$code] = $this->getTemplatePropCell($code, $offer, $matrixFields, $skuPropList);
			}

			$matrix[$keyOffer] = $row;

			\CIBlockPriceTools::setRatioMinPrice($offer, false);
			$this->editTemplateOfferSlider($offer, $item['IBLOCK_ID'], 0, $this->arParams['ADD_DETAIL_TO_SLIDER'] === 'Y', $item['MORE_PHOTO']);

			if (\CIBlockPriceTools::clearProperties($offer['DISPLAY_PROPERTIES'], $iblockParams['OFFERS_TREE_PROPS']))
			{
				$boolSkuDisplayProps = true;
			}
			$offer['TREE'] = [];

			$double[$offer['ID']] = true;
			$newOffers[$keyOffer] = $offer;
		}

		$item['OFFERS'] = $newOffers;
		$item['SHOW_OFFERS_PROPS'] = $boolSkuDisplayProps;

		$usedFields = array();
		$sortFields = array();

		foreach ($skuPropIds as $propCode)
		{
			$boolExist = $matrixFields[$propCode];
			foreach ($matrix as $keyOffer => $row)
			{
				if ($boolExist)
				{
					$item['OFFERS'][$keyOffer]['TREE']['PROP_'.$skuPropList[$propCode]['ID']] = $matrix[$keyOffer][$propCode]['VALUE'];
					$item['OFFERS'][$keyOffer]['SKU_SORT_'.$propCode] = $matrix[$keyOffer][$propCode]['SORT'];
					$usedFields[$propCode] = true;
					$sortFields['SKU_SORT_'.$propCode] = SORT_NUMERIC;
				}
				else
				{
					unset($matrix[$keyOffer][$propCode]);
				}
			}
		}

		$item['OFFERS_PROP'] = $usedFields;
		$item['OFFERS_PROP_CODES'] = (!empty($usedFields) ? base64_encode(serialize(array_keys($usedFields))) : '');

		Collection::sortByColumn($item['OFFERS'], $sortFields);
	}

	protected function editTemplateProductSets(&$item)
	{
		$result = array();
		if (!Catalog\Config\Feature::isProductSetsEnabled())
			return $result;
		if (!isset($item['PRODUCT']['TYPE']))
			return $result;

		$parentBundle = ($item['PRODUCT']['BUNDLE'] == 'Y');
		if ($parentBundle)
			$result[$item['ID']] = true;
		if ($item['PRODUCT']['TYPE'] == Catalog\ProductTable::TYPE_SKU)
		{
			foreach ($item['OFFERS'] as $offer)
			{
				if (
					$parentBundle
					|| ($offer['PRODUCT']['BUNDLE'] == 'Y')
				)
				{
					$result[$offer['ID']] = true;
				}
			}
			unset($offer);
		}
		unset($parentBundle);

		$item['OFFER_GROUP'] = !empty($result);
		if (!empty($result))
			$item['OFFER_GROUP_VALUES'] = array_keys($result);

		return $result;
	}

	protected function editTemplateJsOffers(&$item, $offerSet)
	{
		$matrix = [];
		$intSelected = -1;

		$offerText = $this->arParams['SHOW_SKU_DESCRIPTION'] === 'Y';

		foreach ($item['OFFERS'] as $keyOffer => $offer)
		{
			if ($item['OFFER_ID_SELECTED'] > 0)
			{
				$foundOffer = ($item['OFFER_ID_SELECTED'] == $offer['ID']);
			}
			else
			{
				$foundOffer = $offer['CAN_BUY'];
			}

			if ($foundOffer && $intSelected == -1)
			{
				$intSelected = $keyOffer;
			}
			unset($foundOffer);

			$skuProps = false;
			if (!empty($offer['DISPLAY_PROPERTIES']))
			{
				$skuProps = [];
				foreach ($offer['DISPLAY_PROPERTIES'] as $oneProp)
				{
					if ($oneProp['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_FILE)
					{
						continue;
					}

					$skuProps[] = [
						'CODE' => $oneProp['CODE'],
						'NAME' => $oneProp['NAME'],
						'VALUE' => $oneProp['DISPLAY_VALUE'],
					];
				}
				unset($oneProp);
			}

			if (isset($offerSet[$offer['ID']]))
			{
				$offer['OFFER_GROUP'] = true;
				$item['OFFERS'][$keyOffer]['OFFER_GROUP'] = true;
			}

			$ratioSelectedIndex = $offer['ITEM_MEASURE_RATIO_SELECTED'];
			$firstPhoto = reset($offer['MORE_PHOTO']);
			$oneRow = [
				'ID' => $offer['ID'],
				'CODE' => $offer['CODE'],
				'NAME' => $offer['~NAME'] ?? $item['~NAME'],
				'TREE' => $offer['TREE'],
				'DISPLAY_PROPERTIES' => $skuProps,
				'PREVIEW_TEXT' => $offerText ? $offer['PREVIEW_TEXT'] : '',
				'PREVIEW_TEXT_TYPE' => $offerText ? $offer['PREVIEW_TEXT_TYPE'] : '',
				'DETAIL_TEXT' => $offerText ? $offer['DETAIL_TEXT'] : '',
				'DETAIL_TEXT_TYPE' => $offerText ? $offer['DETAIL_TEXT_TYPE'] : '',
				'ITEM_PRICE_MODE' => $offer['ITEM_PRICE_MODE'],
				'ITEM_PRICES' => $offer['ITEM_PRICES'],
				'ITEM_PRICE_SELECTED' => $offer['ITEM_PRICE_SELECTED'],
				'ITEM_QUANTITY_RANGES' => $offer['ITEM_QUANTITY_RANGES'],
				'ITEM_QUANTITY_RANGE_SELECTED' => $offer['ITEM_QUANTITY_RANGE_SELECTED'],
				'ITEM_MEASURE_RATIOS' => $offer['ITEM_MEASURE_RATIOS'],
				'ITEM_MEASURE_RATIO_SELECTED' => $ratioSelectedIndex,
				'PREVIEW_PICTURE' => $firstPhoto,
				'DETAIL_PICTURE' => $firstPhoto,
				'CHECK_QUANTITY' => $offer['CHECK_QUANTITY'],
				'MAX_QUANTITY' => $offer['PRODUCT']['QUANTITY'],
				'STEP_QUANTITY' => $offer['ITEM_MEASURE_RATIOS'][$ratioSelectedIndex]['RATIO'], // deprecated
				'QUANTITY_FLOAT' => is_float($offer['ITEM_MEASURE_RATIOS'][$ratioSelectedIndex]['RATIO']), // deprecated
				'MEASURE' => $offer['ITEM_MEASURE']['TITLE'],
				'OFFER_GROUP' => (isset($offerSet[$offer['ID']]) && $offerSet[$offer['ID']]),
				'CAN_BUY' => $offer['CAN_BUY'],
				'CATALOG_SUBSCRIBE' => $offer['PRODUCT']['SUBSCRIBE'],
				'SLIDER' => $offer['MORE_PHOTO'],
				'SLIDER_COUNT' => $offer['MORE_PHOTO_COUNT'],
			];
			unset($ratioSelectedIndex);

			$matrix[$keyOffer] = $oneRow;
		}

		if ($intSelected == -1)
		{
			$intSelected = 0;
		}

		$item['JS_OFFERS'] = $matrix;
		$item['OFFERS_SELECTED'] = $intSelected;

		if ($matrix[$intSelected]['SLIDER_COUNT'] > 0)
		{
			$item['MORE_PHOTO'] = $matrix[$intSelected]['SLIDER'];
			$item['MORE_PHOTO_COUNT'] = $matrix[$intSelected]['SLIDER_COUNT'];
		}

		$item['OFFERS_IBLOCK'] = $this->storage['SKU_IBLOCK_INFO']['IBLOCK_ID'];
	}

	public function getTemplateSkuPropList()
	{
		$skuPropList = array();

		if ($this->arResult['MODULES']['catalog'] && !empty($this->arParams['IBLOCK_ID']))
		{
			$sku = \CCatalogSku::GetInfoByProductIBlock($this->arParams['IBLOCK_ID']);
			$iblockParams = $this->storage['IBLOCK_PARAMS'][$this->arParams['IBLOCK_ID']];
			$boolSku = !empty($sku) && is_array($sku);
			if ($boolSku && !empty($iblockParams['OFFERS_TREE_PROPS']) && $this->arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
			{
				$this->storage['SKU_IBLOCK_INFO'] = $sku;

				$skuPropList = \CIBlockPriceTools::getTreeProperties(
					$sku,
					$iblockParams['OFFERS_TREE_PROPS'],
					array(
						'PICT' => $this->arResult['DEFAULT_PICTURE'],
						'NAME' => '-'
					)
				);

				if (empty($skuPropList))
				{
					$this->arParams['PRODUCT_DISPLAY_MODE'] = 'N';
				}
			}
		}

		return $skuPropList;
	}

	protected function editTemplateItems(&$item)
	{
		$skuPropList =& $this->arResult['SKU_PROPS'];

		if (!isset($item['CATALOG_QUANTITY']))
		{
			$item['CATALOG_QUANTITY'] = 0;
		}

		$item['CATALOG_QUANTITY'] = $item['CATALOG_QUANTITY'] > 0 && is_float($item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'])
			? (float)$item['CATALOG_QUANTITY']
			: (int)$item['CATALOG_QUANTITY'];

		$item['CATALOG'] = false;
		$item['CATALOG_SUBSCRIPTION'] = isset($item['CATALOG_SUBSCRIPTION']) && $item['CATALOG_SUBSCRIPTION'] === 'Y' ? 'Y' : 'N';

		\CIBlockPriceTools::getLabel($item, $this->arParams['LABEL_PROP']);
		$this->editTemplateProductSlider($item, $item['IBLOCK_ID'], 0, $this->arParams['ADD_DETAIL_TO_SLIDER'] === 'Y', array($this->arResult['DEFAULT_PICTURE']));
		$this->editTemplateCatalogInfo($item);

		$item['SHOW_OFFERS_PROPS'] = false;
		if ($item['CATALOG'] && !empty($item['OFFERS']))
		{
			$needValues = array();

			foreach ($item['OFFERS'] as &$offer)
			{
				foreach (array_keys($skuPropList) as $strOneCode)
				{
					if (isset($offer['DISPLAY_PROPERTIES'][$strOneCode]))
					{
						if (!isset($needValues[$skuPropList[$strOneCode]['ID']]))
						{
							$needValues[$skuPropList[$strOneCode]['ID']] = array();
						}

						$valueId = $skuPropList[$strOneCode]['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST
							? $offer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE_ENUM_ID']
							: $offer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE'];

						$needValues[$skuPropList[$strOneCode]['ID']][$valueId] = $valueId;
						unset($valueId);
					}
				}
				unset($strOneCode);
			}
			unset($offer);

			if (!empty($needValues))
				\CIBlockPriceTools::getTreePropertyValues($skuPropList, $needValues);
			unset($needValues);
			$this->editTemplateOfferProps($item);
			$offerSet = $this->editTemplateProductSets($item);
			$this->editTemplateJsOffers($item, $offerSet);
		}

		if ($item['MODULES']['catalog'] && $item['CATALOG'])
		{
			if (
				$item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_PRODUCT
				|| $item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SET
			)
			{
				if (isset($item['MIN_PRICE']))
				{
					\CIBlockPriceTools::setRatioMinPrice($item, false);
					$item['MIN_BASIS_PRICE'] = $item['MIN_PRICE'];
				}
			}

			if (
				Catalog\Config\Feature::isProductSetsEnabled()
				&& (
					$item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_PRODUCT
					|| $item['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SET
				)
			)
			{
				$item['OFFER_GROUP'] = (isset($item['PRODUCT']['BUNDLE']) && $item['PRODUCT']['BUNDLE'] === 'Y');
			}

			// fix warnings in templates for simple products
			$item['OFFERS_IBLOCK'] ??= ($this->storage['SKU_IBLOCK_INFO']['IBLOCK_ID'] ?? 0);
			$item['OFFERS_SELECTED'] ??= 0;
			// end fix
		}

		if (!empty($item['DISPLAY_PROPERTIES']))
		{
			foreach ($item['DISPLAY_PROPERTIES'] as $propKey => $displayProperty)
			{
				if ($displayProperty['PROPERTY_TYPE'] === 'F')
				{
					unset($item['DISPLAY_PROPERTIES'][$propKey]);
				}
			}
		}
	}
}
