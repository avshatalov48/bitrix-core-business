<?php

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class ProductSearchComponent extends \CBitrixComponent
{
	const TABLE_ID_PREFIX = 'tbl_product_search';

	protected $iblockId;
	protected $arProps;
	protected $arSkuProps;
	protected $offersCatalog;
	protected $arPrices;
	protected $arHeaders;
	protected $activeSectionLabel;
	protected $simpleSearch;
	protected $offersIblockId;
	protected $iblockList;
	protected $gridOprtions;
	protected $visibleColumns;
	protected $visiblePrices;
	protected $vilibleProperties;

	protected $checkPermissions = true;

	protected $activeSectionNavChain = array();
	protected $offers = array();

	protected static $elementsNamesCache = array();
	protected static $sectionsNamesCache = array();

	/**
	 * @param $ID
	 * @return mixed
	 */
	public static function getElementName($ID)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		if (!isset(static::$elementsNamesCache[$ID]))
		{
			$rsElement = \CIBlockElement::GetList(array(), array("ID" => $ID, "SHOW_HISTORY" => "Y"), false, false, array("ID", "IBLOCK_ID", "NAME"));
			$element = $rsElement->Fetch();
			static::$elementsNamesCache[$ID] = $element? $element['NAME'] : '';
		}
		return static::$elementsNamesCache[$ID];
	}

	public static function getSectionName($ID)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		if (!isset(static::$sectionsNamesCache[$ID]))
		{
			static::$sectionsNamesCache[$ID] = '';
			$sectionsIterator = Iblock\SectionTable::getList(array(
				'select' => array('ID', 'NAME'),
				'filter' => array('=ID' => $ID)
			));
			if ($section = $sectionsIterator->fetch())
				static::$sectionsNamesCache[$ID] = $section['NAME'];
			unset($section, $sectionsIterator);
		}
		return static::$sectionsNamesCache[$ID];
	}

	public static function getPropertyFieldSections($linkIblockId)
	{
		$linkIblockId = (int)$linkIblockId;
		if ($linkIblockId <= 0)
			return false;

		$ttl = 10000;
		$cache_id = 'catalog_store_sections';
		$cache_dir = '/bx/catalog_store_sections';
		$obCache = new CPHPCache;

		if ($obCache->InitCache($ttl, $cache_id, $cache_dir))
			$res = $obCache->GetVars();
		else
			$res = array();

		if (!isset($res[$linkIblockId]))
		{
			$res[$linkIblockId] = array();
			$sectionsIterator = Iblock\SectionTable::getList(array(
				'select' => array('*'),
				'filter' => array('=IBLOCK_ID' => $linkIblockId),
				'order' => array('LEFT_MARGIN' => 'ASC')
			));
			while ($section = $sectionsIterator->fetch())
				$res[$linkIblockId][] = $section;
			unset($section, $sectionsIterator);
			if ($obCache->StartDataCache())
				$obCache->EndDataCache($res);
		}
		return $res[$linkIblockId];
	}

	public function onIncludeComponentLang()
	{
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['IBLOCK_ID'] = isset($params['IBLOCK_ID']) ? (int)$params['IBLOCK_ID'] : 0;
		if (!empty($_REQUEST['IBLOCK_ID']))
			$params['IBLOCK_ID'] = (int)$_REQUEST['IBLOCK_ID'];
		$params['SECTION_ID'] = isset($_REQUEST['SECTION_ID']) ? (int)$_REQUEST['SECTION_ID'] : 0;

		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'change_iblock')
		{
			$params['SECTION_ID'] = 0;
			unset($_REQUEST['mode']);
		}

		$params['LID'] = isset($_REQUEST['LID']) ? htmlspecialcharsbx($_REQUEST['LID']) : '';
		if ($params['LID'] == '')
			$params['LID'] = false;
		$params['func_name'] = isset($_REQUEST["func_name"]) ? preg_replace("/[^a-zA-Z0-9_\.]/is", "", $_REQUEST["func_name"]) : '';
		$params['event'] = isset($_REQUEST['event']) ? preg_replace("/[^a-zA-Z0-9_\.]/is", "", $_REQUEST['event']) : '';
		$params['caller'] = isset($_REQUEST["caller"]) ? preg_replace("/[^a-zA-Z0-9_\-]/is", "", $_REQUEST["caller"]) : '';
		$params['subscribe'] = (isset($_REQUEST['subscribe']) && $_REQUEST['subscribe'] == 'Y');
		$params['store_from_id'] = isset($_REQUEST["STORE_FROM_ID"]) ? (int)$_REQUEST["STORE_FROM_ID"] : 0;
		if ($params['store_from_id'] < 0)
			$params['store_from_id'] = 0;
		$params['allow_select_parent'] = (isset($_REQUEST['allow_select_parent']) && $_REQUEST['allow_select_parent'] == 'Y' ? 'Y' : 'N');

		if (!empty($_REQUEST['del_filter']))
		{
			ClearVars('filter_');
			foreach ($_REQUEST as $key => $value)
			{
				if (strpos($key,'filter_') === 0)
					unset($_REQUEST[$key]);
			}
		}

		$this->simpleSearch = (string)\Bitrix\Main\Config\Option::get('catalog', 'product_form_simple_search') == 'Y';

		if (isset($params['CHECK_PERMISSIONS']) && $params['CHECK_PERMISSIONS'] == 'N')
			$this->checkPermissions = false;

		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		$userOptions = \CUserOptions::getOption('catalog', self::TABLE_ID_PREFIX . '_' . $params['caller'], false, $this->getUserId());
		if (is_array($userOptions))
		{
			if (!$params['IBLOCK_ID'])
				$params['IBLOCK_ID'] = (int)$userOptions['IBLOCK_ID'];
			if (!$params['SECTION_ID'] && !isset($_REQUEST['SECTION_ID']) && $params['IBLOCK_ID'] === (int)$userOptions['IBLOCK_ID'])
				$params['SECTION_ID'] = (int)$userOptions['SECTION_ID'];
			if (!isset($_REQUEST['QUERY']) && (!isset($_REQUEST['mode']) || $_REQUEST['mode'] != 'list') && isset($userOptions['QUERY']))
				$_REQUEST['QUERY'] = $userOptions['QUERY'];
		}

		return $params;
	}

	public function executeComponent()
	{
		$this->checkAccess();
		$this->loadModules();
		$this->checkIblockAccess();

		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'open_section')
		{
			$this->arResult = array(
				'SECTIONS' => $this->getSectionsTree($this->getIblockId(), $_REQUEST['section_id'],$_REQUEST['active_id']),
				'LEVEL' => (int)$_REQUEST['level'],
				'TABLE_ID' => $this->getTableId(),
				'OPEN_SECTION_MODE' => true,
				'IS_ADMIN_SECTION' => $this->isAdminSection()
			);
		}
		else
		{
			$this->prepareComponentResult();
			$this->saveState();
		}
		$this->includeComponentTemplate();
	}

	protected function isAdminSection()
	{
		return \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->isAdminSection();
	}

	protected function isFiltering()
	{
		foreach ($_REQUEST as $key => $value)
		{
			if (strpos($key,'filter_') === 0)
				return true;
		}
		return false;
	}

	protected function isExternalContext()
	{
		return !empty($_REQUEST['externalcontext']);
	}

	protected function getGridOptions()
	{
		if ($this->gridOprtions === null)
			$this->gridOprtions = new \CGridOptions($this->getTableId());

		return $this->gridOprtions;
	}

	protected function checkAccess()
	{
		global $USER, $APPLICATION;

		if (!$this->checkPermissions)
			return true;

		if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_view')))
		{
			$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
			return false;
		}
		return true;
	}

	protected function loadModules()
	{
		if (!Main\Loader::includeModule('catalog'))
		{
			ShowError(Loc::getMessage('BX_CPS_COMP_ERR_CATALOG_MODULE_NOT_INSTALL'));
			die();
		}
		Main\Loader::includeModule('search');
		Main\Loader::includeModule('fileman');
	}

	protected function getMixedList($arOrder = array('SORT' => 'ASC'), $arFilter = array(), $bIncCnt = false, $arSelectedFields = false)
	{
		$arResult = array();
		$notFound = false;
		if (is_array($arFilter["S_ID"]) && sizeof($arFilter["S_ID"]) == 1)
		{
			$notFound = $arFilter['S_ID'][0] == 0;
		}
		if (!$notFound && !$this->isFiltering())
		{
			$arSectionFilter = array(
				"IBLOCK_ID" => $arFilter["IBLOCK_ID"],
				"=ID" => $arFilter["S_ID"],
				"?NAME" => $arFilter["NAME"],
				">=TIMESTAMP_X" => $arFilter["DATE_MODIFY_FROM"],
				"<=TIMESTAMP_X" => $arFilter["DATE_MODIFY_TO"],
				"CODE" => $arFilter["CODE"],
				"ACTIVE" => $arFilter["ACTIVE"]
			);
			if (isset($arFilter["CHECK_PERMISSIONS"]))
			{
				$arSectionFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
				$arSectionFilter['MIN_PERMISSION'] = (isset($arFilter['MIN_PERMISSION']) ? $arFilter['MIN_PERMISSION'] : 'R');
			}
			if (array_key_exists("SECTION_ID", $arFilter))
			{
				if (!array_key_exists("INCLUDE_SUBSECTIONS", $arFilter))
				{
					$arSectionFilter['SECTION_ID'] = $arFilter['SECTION_ID'];
				}
				elseif (!$this->isAdvancedSearchAvailable() && $margin = $this->getSectionMargin($arFilter['SECTION_ID']))
				{
					$arSectionFilter['>LEFT_MARGIN'] = $margin['LEFT_MARGIN'];
					$arSectionFilter['<RIGHT_MARGIN'] = $margin['RIGHT_MARGIN'];
					$arSectionFilter['>DEPTH_LEVEL'] = $margin['DEPTH_LEVEL'];
				}
			}

			$obSection = new \CIBlockSection;
			$rsSection = $obSection->GetList($arOrder, $arSectionFilter, $bIncCnt);
			while ($arSection = $rsSection->Fetch())
			{
				$arSection["TYPE"] = "S";
				$arResult[] = $arSection;
			}
		}
		$notFound = false;
		if (is_array($arFilter["ID"]) && sizeof($arFilter["ID"]) == 1)
		{
			$notFound = $arFilter['ID'][0] == 0;
		}
		if (!$notFound)
		{
			$arElementFilter = array(
				"IBLOCK_ID" => $arFilter["IBLOCK_ID"],
				"?NAME" => $arFilter["NAME"],
				"SECTION_ID" => $arFilter["SECTION_ID"],
				"=ID" => $arFilter["ID"],
				">=TIMESTAMP_X" => $arFilter["DATE_MODIFY_FROM"],
				"<=TIMESTAMP_X" => $arFilter["DATE_MODIFY_TO"],
				"CODE" => $arFilter["CODE"],
				"ACTIVE" => $arFilter["ACTIVE"],
				"WF_STATUS" => $arFilter["WF_STATUS"],
				'INCLUDE_SUBSECTIONS' => $arFilter["INCLUDE_SUBSECTIONS"]
			);

			if(!empty($arFilter['XML_ID']))
				$arElementFilter['XML_ID'] = $arFilter['XML_ID'];
			if(!empty($arFilter['>=ID']))
				$arElementFilter['>=ID'] = $arFilter['>=ID'];
			if(!empty($arFilter['<=ID']))
				$arElementFilter['<=ID'] = $arFilter['<=ID'];

			if (isset($arFilter["CHECK_PERMISSIONS"]))
			{
				$arElementFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
				$arElementFilter['MIN_PERMISSION'] = (isset($arFilter['MIN_PERMISSION']) ? $arFilter['MIN_PERMISSION'] : 'S');
			}

			foreach ($arFilter as $key => $value)
			{
				$op = \CIBlock::MkOperationFilter($key);
				$newkey = strtoupper($op["FIELD"]);
				if (
					substr($newkey, 0, 9) == "PROPERTY_"
					|| substr($newkey, 0, 8) == "CATALOG_"
				)
				{
					$arElementFilter[$key] = $value;
				}
			}

			if (strlen($arFilter["SECTION_ID"]) <= 0)
				unset($arElementFilter["SECTION_ID"]);

			if (!is_array($arSelectedFields))
				$arSelectedFields = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "ACTIVE", "SORT", "NAME",
					"PREVIEW_PICTURE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_PICTURE", "DETAIL_TEXT", "DETAIL_TEXT_TYPE",
					"SHOW_COUNTER", "SHOW_COUNTER_START", "CODE", "EXTERNAL_ID"
				);

			if (isset($arFilter["CHECK_BP_PERMISSIONS"]))
				$arElementFilter["CHECK_BP_PERMISSIONS"] = $arFilter["CHECK_BP_PERMISSIONS"];
			$obElement = new \CIBlockElement;
			$rsElement = $obElement->GetList($arOrder, $arElementFilter, false, false, $arSelectedFields);
			while ($arElement = $rsElement->Fetch())
			{
				$arElement["TYPE"] = "E";
				$arResult[] = $arElement;
			}
		}
		$rsResult = new \CDBResult;
		$rsResult->InitFromArray($arResult);

		if ($this->isAdminSection())
		{
			$rsResult = new \CAdminResult($rsResult, $this->getTableId());
			$rsResult->NavStart();
		}
		else
			$rsResult->NavStart($this->getGridOptions()->GetNavParams());

		return $rsResult;
	}

	protected function getOffersIblockId()
	{
		if ($this->offersIblockId===null)
		{
			$this->offersIblockId = 0;
			$catalog = $this->getOffersCatalog();
			if ($catalog)
				$this->offersIblockId = (int)$catalog["IBLOCK_ID"];
		}
		return $this->offersIblockId;
	}

	protected function loadAllSku(array $productIds)
	{
		$arOffersIblock = $this->getOffersIblockId();
		if ($arOffersIblock > 0 && $productIds)
		{
			$propFilter = array();
			$props = $this->getSkuProps(true);
			if (!empty($props))
			{
				$visibleProperties = $this->getVisibleProperties();
				foreach($props as $prop)
				{
					if (in_array($prop['ID'], $visibleProperties))
						$propFilter['ID'][] = $prop['ID'];
				}
				unset($prop);
			}
			unset($props);

			$select = array('NAME',  'ACTIVE', 'CATALOG_QUANTITY');
			$visible = $this->getVisibleColumns();
			if (in_array('PREVIEW_PICTURE', $visible))
				$select[] = 'PREVIEW_PICTURE';
			if (in_array('DETAIL_PICTURE', $visible))
				$select[] = 'DETAIL_PICTURE';
			$this->offers = \CCatalogSku::getOffersList($productIds, $this->getIblockId(), array(), $select, $propFilter);
			if (!empty($this->offers))
			{
				$offersIds = array();
				$offersLink = array();
				foreach ($this->offers as $productId => $productOffers)
				{
					if (empty($productOffers))
						continue;
					$productOffersIds = array_keys($productOffers);
					foreach ($productOffersIds as $oneId)
					{
						$offersIds[] = $oneId;
						$offersLink[$oneId] = &$this->offers[$productId][$oneId];
					}
					unset($oneId);
				}
				unset($productId, $productOffers);
				if (!empty($offersIds))
				{
					$ratioResult = Catalog\ProductTable::getCurrentRatioWithMeasure($offersIds);
					if (!empty($ratioResult))
					{
						foreach ($ratioResult as $oneOfferId => $ratioData)
						{
							if (!isset($offersLink[$oneOfferId]))
								continue;
							$offersLink[$oneOfferId]['MEASURE_RATIO'] = $ratioData['RATIO'];
							$offersLink[$oneOfferId]['MEASURE'] = $ratioData['MEASURE'];
						}
						unset($oneOfferId, $ratioData);
					}
					unset($ratioResult);
				}
				unset($offersLink, $offersIds);
			}
		}
	}
	/**
	 * @param array $arProduct
	 * @return array|bool
	 */
	protected function getProductSku($arProduct)
	{
		$productId = (int)$arProduct['ID'];
		$productName = trim($arProduct['NAME']);
		if ($productId <= 0)
			return false;
		$arResult = array();
		if (!empty($this->offers[$productId]))
		{
			$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
			$minImageSize = array('W' => 1, 'H' => 1);
			$maxImageSize = array(
				'W' => $listImageSize,
				'H' => $listImageSize,
			);
			unset($listImageSize);

			$arSku = array();
			$skuIdsList = array();

			foreach ($this->offers[$productId] as $index => $arOffer)
			{
				$arSkuTmp = array();
				$arSkuTmp['PROPERTIES'] = array();
				$arOffer["CAN_BUY"] = "N";

				if (!empty($arOffer['PROPERTIES']))
				{
					foreach ($arOffer['PROPERTIES'] as $pid => $property)
					{
						$viewValues = array();
						$property['USER_TYPE'] = (string)$property['USER_TYPE'];

						$userType = ($property['USER_TYPE'] !== '' ? CIBlockProperty::GetUserType($property['USER_TYPE']) : array());

						if ($property['MULTIPLE'] == 'N' || !is_array($property['VALUE']))
							$valueIdList = array($property['PROPERTY_VALUE_ID']);
						else
							$valueIdList = $property['PROPERTY_VALUE_ID'];

						if (isset($userType['GetAdminListViewHTML']))
						{
							if ($property['MULTIPLE'] == 'N' || !is_array($property['~VALUE']))
								$valueList = array($property['~VALUE']);
							else
								$valueList = $property['~VALUE'];
						}
						else
						{
							if ($property['MULTIPLE'] == 'N' || !is_array($property['VALUE']))
								$valueList = array($property['VALUE']);
							else
								$valueList = $property['VALUE'];
						}

						foreach ($valueList as $valueIndex => $value)
						{
							if (isset($userType['GetAdminListViewHTML']))
							{
								$viewValues[] = call_user_func_array(
									$userType['GetAdminListViewHTML'],
									array(
										$property,
										array(
											'VALUE' => $value
										),
										array()
									));
							}
							else
							{
								switch ($property['PROPERTY_TYPE'])
								{
									case Iblock\PropertyTable::TYPE_SECTION:
										$viewValues[] = static::getSectionName($value);
										break;
									case Iblock\PropertyTable::TYPE_ELEMENT:
										$viewValues[] = static::getElementName($value);
										break;
									case Iblock\PropertyTable::TYPE_FILE:
										$viewValues[] = CFileInput::Show(
											'NO_FIELDS['.$valueIdList[$valueIndex].']',
											$value,
											array(
												'IMAGE' => 'Y',
												'PATH' => 'N',
												'FILE_SIZE' => 'N',
												'DIMENSIONS' => 'N',
												'IMAGE_POPUP' => 'Y',
												'MAX_SIZE' => $maxImageSize,
												'MIN_SIZE' => $minImageSize,
											),
											array(
												'upload' => false,
												'medialib' => false,
												'file_dialog' => false,
												'cloud' => false,
												'del' => false,
												'description' => false,
											)
										);
										break;
									case Iblock\PropertyTable::TYPE_LIST:
									case Iblock\PropertyTable::TYPE_NUMBER:
									case Iblock\PropertyTable::TYPE_STRING:
									default:
										$viewValues[] = $value;
										break;
								}
							}
						}
						unset($value, $valueList, $valueIdList);
						unset($userType);

						$arSkuTmp['PROPERTIES'][$property['ID']] = $viewValues;
						unset($viewValues);
					}
				}

				$arSkuTmp["BALANCE"] = $arOffer["CATALOG_QUANTITY"];
				$arSkuTmp["USER_ID"] = $this->getUserId();
				$arSkuTmp["ID"] = $arOffer["ID"];
				$arSkuTmp["TYPE"] = $arOffer["CATALOG_TYPE"];
				$arSkuTmp["NAME"] = $arOffer["NAME"];
				$arSkuTmp["PRODUCT_NAME"] = $productName;
				$arSkuTmp["PRODUCT_ID"] = $productId;
				$arSkuTmp["CAN_BUY"] = $arOffer["CAN_BUY"];
				$arSkuTmp["ACTIVE"] = $arOffer["ACTIVE"];
				$arSkuTmp["EXTERNAL_ID"] = $arOffer['EXTERNAL_ID'];
				if (isset($arOffer['PREVIEW_PICTURE']))
					$arSkuTmp['PREVIEW_PICTURE'] = $arOffer['PREVIEW_PICTURE'];
				if (isset($arOffer['DETAIL_PICTURE']))
					$arSkuTmp['DETAIL_PICTURE'] = $arOffer['DETAIL_PICTURE'];
				if (isset($arOffer['MEASURE_RATIO']))
					$arSkuTmp['MEASURE_RATIO'] = $arOffer['MEASURE_RATIO'];
				if (isset($arOffer['MEASURE']))
					$arSkuTmp['MEASURE'] = $arOffer['MEASURE'];
				$arSku[] = $arSkuTmp;
				$skuIdsList[] = $arOffer["ID"];
			}
			$arResult["SKU_ELEMENTS"] = $arSku;
			$arResult["SKU_ELEMENTS_ID"] = $skuIdsList;

			unset($skuIdsList, $arSku);
		}

		return $arResult;
	}
	/**
	 * @param  \CDBResult $dbResultList
	 * @return array
	 */
	protected function makeItemsFromDbResult(\CDBResult $dbResultList)
	{
		$arItemsResult = $arProductIds = array();
		while ($arItem = $dbResultList->Fetch())
		{
			if ($arItem['TYPE'] != 'S')
			{
				$arProductIds[] = $arItem['ID'];
				$arItem['PROPERTIES'] = array();
				$arItemsResult[$arItem['ID']] = $arItem;
			}
			else
				$arItemsResult['S'.$arItem['ID']] = $arItem;
		}

		if (!empty($arProductIds))
		{
			$arPropFilter = array(
				'ID' => $arProductIds,
				'IBLOCK_ID' => $this->getIblockId()
			);
			CIBlockElement::GetPropertyValuesArray(
				$arItemsResult,
				$this->getIblockId(),
				$arPropFilter,
				array('ID' => $this->getVisibleProperties()),
				array(
					'PROPERTY_FIELDS' => array(
						'ID', 'NAME', 'SORT', 'PROPERTY_TYPE',
						'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'USER_TYPE_SETTINGS'
					)
				)
			);

			$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
			$minImageSize = array('W' => 1, 'H' => 1);
			$maxImageSize = array(
				'W' => $listImageSize,
				'H' => $listImageSize,
			);
			unset($listImageSize);

			foreach ($arItemsResult as $item)
			{
				if (!empty($item['PROPERTIES']))
				{
					foreach ($item['PROPERTIES'] as $pid => $property)
					{
						$viewValues = array();
						$property['USER_TYPE'] = (string)$property['USER_TYPE'];

						$userType = ($property['USER_TYPE'] !== '' ? CIBlockProperty::GetUserType($property['USER_TYPE']) : array());

						if ($property['MULTIPLE'] == 'N' || !is_array($property['VALUE']))
							$valueIdList = array($property['PROPERTY_VALUE_ID']);
						else
							$valueIdList = $property['PROPERTY_VALUE_ID'];

						if (isset($userType['GetAdminListViewHTML']))
						{
							if ($property['MULTIPLE'] == 'N' || !is_array($property['~VALUE']))
								$valueList = array($property['~VALUE']);
							else
								$valueList = $property['~VALUE'];
						}
						else
						{
							if ($property['MULTIPLE'] == 'N' || !is_array($property['VALUE']))
								$valueList = array($property['VALUE']);
							else
								$valueList = $property['VALUE'];
						}

						foreach ($valueList as $valueIndex => $value)
						{
							if (isset($userType['GetAdminListViewHTML']))
							{
								$viewValues[] = call_user_func_array(
									$userType['GetAdminListViewHTML'],
									array(
										$property,
										array(
											'VALUE' => $value
										),
										array()
									));
							}
							else
							{
								switch ($property['PROPERTY_TYPE'])
								{
									case Iblock\PropertyTable::TYPE_SECTION:
										$viewValues[] = static::getSectionName($value);
										break;
									case Iblock\PropertyTable::TYPE_ELEMENT:
										$viewValues[] = static::getElementName($value);
										break;
									case Iblock\PropertyTable::TYPE_FILE:
										$viewValues[] = CFileInput::Show(
											'NO_FIELDS['.$valueIdList[$valueIndex].']',
											$value,
											array(
												'IMAGE' => 'Y',
												'PATH' => 'N',
												'FILE_SIZE' => 'N',
												'DIMENSIONS' => 'N',
												'IMAGE_POPUP' => 'Y',
												'MAX_SIZE' => $maxImageSize,
												'MIN_SIZE' => $minImageSize,
											),
											array(
												'upload' => false,
												'medialib' => false,
												'file_dialog' => false,
												'cloud' => false,
												'del' => false,
												'description' => false,
											)
										);
										break;
									case Iblock\PropertyTable::TYPE_LIST:
									case Iblock\PropertyTable::TYPE_NUMBER:
									case Iblock\PropertyTable::TYPE_STRING:
									default:
										$viewValues[] = $value;
										break;
								}
							}
						}
						unset($value, $valueList, $valueIdList);
						unset($userType);

						$arItemsResult[$item['ID']]['PROPERTIES'][$property['ID']] = $viewValues;
						unset($viewValues);
					}
				}
			}
			unset($item);

			$iterator = Catalog\ProductTable::getList(array(
				'select' => array('ID', 'TYPE', 'QUANTITY', 'AVAILABLE', 'MEASURE'),
				'filter' => array('@ID' => $arProductIds)
			));
			while ($row = $iterator->fetch())
				$arItemsResult[$row['ID']]['PRODUCT'] = $row;

			$offersExistsIds = \CCatalogSku::getExistOffers($arProductIds, $this->getIblockId());
			$noOffersIds = array();

			if (empty($offersExistsIds))
			{
				$noOffersIds = $arProductIds;
			}
			else
			{
				$this->loadAllSku(array_keys(array_filter($offersExistsIds)));
				foreach ($offersExistsIds as $id => $bExists)
				{
					$arItem = &$arItemsResult[$id];
					if ($bExists)
						$arItem['SKU_ITEMS'] = $this->getProductSku($arItem);
					else
						$noOffersIds[] = $id;
				}
				unset($id, $bExists);
			}

			if (!empty($noOffersIds))
			{
				$productRatioList = Catalog\ProductTable::getCurrentRatioWithMeasure($noOffersIds);
				if (!empty($productRatioList))
				{
					foreach ($productRatioList as $productId => $productRatio)
					{
						if (!isset($arItemsResult[$productId]['PRODUCT']))
							continue;
						$arItemsResult[$productId]['PRODUCT']['MEASURE_RATIO'] = $productRatio['RATIO'];
						$arItemsResult[$productId]['PRODUCT']['MEASURE'] = $productRatio['MEASURE'];
					}
					unset($productRatio, $productId);
				}
				unset($productRatioList);

				$priceIds = $this->getVisiblePrices();
				foreach ($priceIds as $priceId)
				{
					$dbPrice = \CPrice::GetListEx(
						array(),
						array(
							'PRODUCT_ID' => $noOffersIds, 'CATALOG_GROUP_ID' => $priceId
						),
						false,
						false,
						array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO')
					);
					while ($arPrice = $dbPrice->fetch())
					{
						$arPrice['QUANTITY_FROM'] = (int)$arPrice['QUANTITY_FROM'];
						$arPrice['QUANTITY_TO'] = (int)$arPrice['QUANTITY_TO'];
						if (
							!isset($arItemsResult[$arPrice["PRODUCT_ID"]]['PRICES'][$priceId])
							|| ($arItemsResult[$arPrice["PRODUCT_ID"]]['PRICES'][$priceId]['QUANTITY_FROM'] > $arPrice['QUANTITY_FROM'])
						)
						{
							$arItemsResult[$arPrice["PRODUCT_ID"]]['PRICES'][$priceId] = array(
								'PRICE' => $arPrice['PRICE'],
								'CURRENCY' => $arPrice['CURRENCY'],
								'QUANTITY_FROM' => $arPrice['QUANTITY_FROM'],
								'QUANTITY_TO' => $arPrice['QUANTITY_TO']
							);
						}
					}
					unset($arPrice, $dbPrice);
				}

				if ($this->getStoreId())
				{
					$dbStoreProduct = \CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $noOffersIds, "STORE_ID" => $this->getStoreId()));
					while ($arStoreProduct = $dbStoreProduct->Fetch())
					{
						$arItemsResult[$arStoreProduct["PRODUCT_ID"]]['PRODUCT']['STORE_AMOUNT'] = $arStoreProduct["AMOUNT"];
					}
				}

				$groupsIterator = CCatalogProductSet::getList(
					array(),
					array('OWNER_ID' => $noOffersIds, 'SET_ID' => 0, 'TYPE' => \CCatalogProductSet::TYPE_GROUP),
					false,
					false,
					array('ID', 'OWNER_ID', 'ITEM_ID', 'SET_ID', 'TYPE')
				);
				while ($group = $groupsIterator->Fetch())
				{
					if ($group['OWNER_ID'] == $group['ITEM_ID'])
					{
						$arItemsResult[$group['OWNER_ID']]['PRODUCT']['IS_GROUP'] = true;
					}
				}

			}
		}
		return $arItemsResult;
	}

	protected function getSkuPrices()
	{
		$result = array();
		if ($this->offers)
		{
			$ids = array();
			foreach ($this->offers as $id => $offers)
				foreach ($offers as $offer)
					$ids[] = $offer['ID'];
			if ($ids)
			{
				$priceIds = $this->getVisiblePrices();
				foreach ($priceIds as $id)
				{
					$dbPrice = \CPrice::getListEx(
						array(),
						array('PRODUCT_ID' => $ids, 'CATALOG_GROUP_ID' => $id),
						false,
						false,
						array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO')
					);
					while ($arPrice = $dbPrice->fetch())
					{
						$arPrice['QUANTITY_FROM'] = (int)$arPrice['QUANTITY_FROM'];
						$arPrice['QUANTITY_TO'] = (int)$arPrice['QUANTITY_TO'];
						if (
							!isset($result[$id][$arPrice["PRODUCT_ID"]])
							|| ($result[$id][$arPrice["PRODUCT_ID"]]['QUANTITY_FROM'] > $arPrice['QUANTITY_FROM'])
						)
						{
							$result[$id][$arPrice["PRODUCT_ID"]] = array(
								'PRICE' => $arPrice['PRICE'],
								'CURRENCY' => $arPrice['CURRENCY'],
								'QUANTITY_FROM' => $arPrice['QUANTITY_FROM'],
								'QUANTITY_TO' => $arPrice['QUANTITY_TO']
							);
						}
					}
					unset($arPrice, $dbPrice);
				}
			}
		}
		return $result;
	}

	protected function getJsCallbackName()
	{
		return $this->arParams['func_name'];
	}

	protected function getJsEventName()
	{
		return $this->arParams['event'];
	}

	protected function getUserId()
	{
		global $USER;
		return intval($USER->GetID());
	}

	protected function getCaller()
	{
		return $this->arParams['caller'];
	}

	protected function getLid()
	{
		return $this->arParams['LID'];
	}

	protected function getTableId()
	{
		return self::TABLE_ID_PREFIX . '_' . $this->getCaller();
	}

	protected function getSubscription()
	{
		return $this->arParams['subscribe'];
	}

	protected function getStoreId()
	{
		return $this->arParams['store_from_id'];
	}

	protected function getIblockId()
	{
		if ($this->iblockId === null)
		{
			$iblockList = $this->getIblockList();
			if (isset($iblockList[$this->arParams['IBLOCK_ID']]))
				$this->iblockId = $this->arParams['IBLOCK_ID'] ;
			else
			{
				$first = current($iblockList);
				if (is_array($first))
					$this->iblockId = $first['ID'];
			}
			$this->iblockId = (int)$this->iblockId;
		}
		return $this->iblockId;
	}

	protected function getIblock()
	{
		$id = $this->getIblockId();
		return isset($this->iblockList[$id])? $this->iblockList[$id] : false;
	}

	protected function isAdvancedSearchAvailable()
	{
		if ($this->simpleSearch)
			return false;
		$iblock = $this->getIblock();
		if ($iblock['INDEX_ELEMENT'] != 'Y' || $iblock['INDEX_SECTION'] != 'Y')
			return false;
		return true;
	}

	protected function getSectionId()
	{
		return $this->arParams['SECTION_ID'];
	}

	protected function getActiveSectionLabel()
	{
		return $this->activeSectionLabel;
	}

	protected function checkIblockAccess()
	{
		if (!$this->checkPermissions)
			return true;

		$id = $this->getIblockId();
		$error = '';
		if ($id)
		{
			if (!\CIBlockRights::UserHasRightTo($id, $id, "element_read"))
			{
				$error = Loc::getMessage("SPS_NO_PERMS");
			}
		}
		else
		{
			$error = Loc::getMessage("SPS_NO_CATALOGS");
		}
		if ($error)
		{
			$this->arResult['ERROR'] = $error;
			$this->arResult['IS_EXTERNALCONTEXT'] = $this->isExternalContext();
			$this->includeComponentTemplate('error');
			exit;
		}
		return true;
	}

	protected function getFilterFields()
	{
		$arFilterFields = array(
			"filter_timestamp_from",
			"filter_timestamp_to",
			"filter_active",
			"filter_code",
			'filter_id_start',
			'filter_id_end',
			'filter_xml_id'
		);

		return $arFilterFields;
	}

	protected function getFilterLabels()
	{
		$arFindFields = array(
			"find_code" => GetMessage("SPS_CODE"),
			"find_time" => GetMessage("SPS_TIMESTAMP"),
			"find_active" => GetMessage("SPS_ACTIVE"),
			"find_id" => "ID (".GetMessage("SPS_ID_FROM_TO").")",
			"find_xml_id" => GetMessage("SPS_XML_ID"),
		);
		foreach ($this->getProps() as $arProp)
			$arFindFields["filter_el_property_".$arProp["ID"]] = $arProp["NAME"];
		foreach ($this->getSkuProps() as $arProp)
			$arFindFields["filter_sub_el_property_".$arProp["ID"]] = $arProp["NAME"]. ' ('.GetMessage("SPS_OFFER").')';
		return $arFindFields;
	}

	protected function getOffersCatalog()
	{
		if ($this->offersCatalog === null)
			$this->offersCatalog = \CCatalogSku::GetInfoByProductIBlock($this->getIblockId());

		return $this->offersCatalog;
	}

	protected function getProps($flagAll = false)
	{
		if ($this->arProps === null)
			$this->arProps = $this->getPropsList($this->getIblockId());

		return $flagAll ? $this->arProps : $this->filterProps($this->arProps);
	}

	protected function getSkuProps($flagAll = false)
	{
		if ($this->arSkuProps === null)
		{
			$arCatalog = $this->getOffersCatalog();
			$this->arSkuProps = $arCatalog? $this->getPropsList($arCatalog["IBLOCK_ID"], $arCatalog['SKU_PROPERTY_ID']) : array();
		}
		return $flagAll ? $this->arSkuProps : $this->filterProps($this->arSkuProps);
	}

	protected function getPrices()
	{
		if ($this->arPrices === null)
		{
			$this->arPrices = array();
			$priceTypeIterator = Catalog\GroupTable::getList(array(
				'select' => array('ID', 'BASE', 'NAME', 'NAME_LANG' => 'CURRENT_LANG.NAME'),
				'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
			));
			while ($priceType = $priceTypeIterator->fetch())
			{
				$priceType['ID'] = (int)$priceType['ID'];
				$priceType['NAME_LANG'] = (string)$priceType['NAME_LANG'];
				$this->arPrices[] = $priceType;
			}
			unset($priceType, $priceTypeIterator);
		}
		return $this->arPrices;
	}

	protected function getHeaders()
	{
		if ($this->arHeaders === null)
		{
			$balanceTitle = Loc::getMessage($this->getStoreId() > 0 ? "SOPS_BALANCE" : "SOPS_BALANCE2");
			$this->arHeaders = array(
				array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
				array("id" => "ACTIVE", "content" => Loc::getMessage("SOPS_ACTIVE"), "sort" => "ACTIVE", "default" => true),
				array("id" => "DETAIL_PICTURE", "default" => true, "content" => Loc::getMessage("SPS_FIELD_DETAIL_PICTURE"), "align" => "center"),
				array("id" => "NAME", "content" => Loc::getMessage("SPS_NAME"), "sort" => "NAME", "default" => true),
				array("id" => "BALANCE", "content" => $balanceTitle, "default" => true, "align" => "right"),
				array("id" => "CODE", "content" => Loc::getMessage("SPS_FIELD_CODE"), "sort" => "CODE"),
				array("id" => "EXTERNAL_ID", "content" => Loc::getMessage("SPS_FIELD_XML_ID"), "sort" => "EXTERNAL_ID"),
				array("id" => "SHOW_COUNTER", "content" => Loc::getMessage("SPS_FIELD_SHOW_COUNTER"), "sort" => "SHOW_COUNTER", "align" => "right"),
				array("id" => "SHOW_COUNTER_START", "content" => Loc::getMessage("SPS_FIELD_SHOW_COUNTER_START"), "sort" => "SHOW_COUNTER_START", "align" => "right"),
				array("id" => "PREVIEW_PICTURE", "content" => Loc::getMessage("SPS_FIELD_PREVIEW_PICTURE"), "align" => "right"),
				array("id" => "PREVIEW_TEXT", "content" => Loc::getMessage("SPS_FIELD_PREVIEW_TEXT")),
				array("id" => "DETAIL_TEXT", "content" => Loc::getMessage("SPS_FIELD_DETAIL_TEXT")),
				array("id" => "EXPAND", "content" => Loc::getMessage("SPS_EXPAND"), "sort" => "", "default" => true),
				array("id" => "QUANTITY", "content" =>  Loc::getMessage("SPS_QUANTITY")),
				array("id" => "ACTION", "content" =>  Loc::getMessage("SPS_FIELD_ACTION"),  "default" => true),
			);
			$arProps = $this->getProps(true);
			if (!empty($arProps))
			{
				foreach ($arProps as &$prop)
				{
					$this->arHeaders[] = array(
						"id" => "PROPERTY_".$prop['ID'], "content" => $prop['NAME'],
						"align" => ($prop["PROPERTY_TYPE"] == 'N' ? "right" : "left"),
						"sort" => ($prop["MULTIPLE"] != 'Y' ? "PROPERTY_".$prop['ID'] : "")
					);
				}
				unset($prop);
			}
			$arProps = $this->getSkuProps(true);
			if (!empty($arProps))
			{
				foreach ($arProps as $prop)
				{
					$this->arHeaders[] = array(
						"id" => "PROPERTY_".$prop['ID'], "content" => $prop['NAME'].' ('.Loc::getMessage("SPS_OFFER").')',
						"align" => ($prop["PROPERTY_TYPE"] == 'N' ? "right" : "left"),
					);
				}
				unset($prop);
			}
			unset($arProps);
			$arPrices = $this->getPrices();
			if (!empty($arPrices))
			{
				foreach ($arPrices as $price)
				{
					$this->arHeaders[] = array(
						"id" => "PRICE".$price["ID"],
						"content" => (!empty($price["NAME_LANG"]) ? $price["NAME_LANG"] : $price["NAME"]),
						"default" => $price["BASE"] == 'Y'
					);
				}
				unset($price);
			}
			unset($arPrices);
		}
		return $this->arHeaders;
	}

	protected function getVisibleColumns()
	{
		if ($this->visibleColumns === null)
		{
			$this->visibleColumns = array();

			if ($this->isAdminSection())
			{
				$aOptions = CUserOptions::GetOption("list", $this->getTableId(), array());
				$aColsTmp = explode(",", $aOptions["columns"]);
			}
			else
			{
				$aColsTmp = $this->getGridOptions()->GetVisibleColumns();
			}

			$aCols = array();
			foreach ($aColsTmp as $col)
			{
				$col = trim($col);
				if ($col <> "")
					$aCols[] = $col;
			}
			$headers = $this->getHeaders();
			$useDefault = empty($aCols);
			foreach ($headers as $param)
			{
				if (($useDefault && $param["default"]) || in_array($param["id"], $aCols))
				{
					$this->visibleColumns[] = $param["id"];
				}
			}
		}
		return $this->visibleColumns;
	}

	protected function getVisiblePrices()
	{
		if ($this->visiblePrices === null)
		{
			$this->visiblePrices = array();
			$columns = $this->getVisibleColumns();
			foreach ($columns as $column)
			{
				if (strpos($column,'PRICE') === 0)
					$this->visiblePrices[] = (int)str_replace('PRICE','',$column);
			}
		}
		return $this->visiblePrices;
	}

	protected function getVisibleProperties()
	{
		if ($this->vilibleProperties === null)
		{
			$this->vilibleProperties = array();
			$columns = $this->getVisibleColumns();
			foreach ($columns as $column)
			{
				if (strpos($column,'PROPERTY_') === 0)
					$this->vilibleProperties[] = (int)str_replace('PROPERTY_','',$column);
			}
		}
		return $this->vilibleProperties;
	}

	protected function getFilter()
	{
		$arFilter = array(
			"IBLOCK_ID" => $this->getIblockId(),
			'SECTION_ID' => $this->getSectionId() > 0 ? $this->getSectionId() : 0,
			'ACTIVE' => empty($_REQUEST['filter_active']) ? 'Y' : $_REQUEST['filter_active'],
			"WF_PARENT_ELEMENT_ID" => false,
			"SHOW_NEW" => "Y"
		);
		//TODO: remove this hack for store docs after refactoring
		if ($this->getCaller() == 'storeDocs')
			$arFilter['!CATALOG_TYPE'] = Catalog\ProductTable::TYPE_SET;

		if ($arFilter['ACTIVE'] == '*')
			unset($arFilter['ACTIVE']);

		if(!empty($_REQUEST['filter_xml_id']))
			$arFilter['XML_ID'] = $_REQUEST['filter_xml_id'];
		if(!empty($_REQUEST['filter_id_start']))
			$arFilter['>=ID'] = $_REQUEST['filter_id_start'];
		if(!empty($_REQUEST['filter_id_end']))
			$arFilter['<=ID'] = $_REQUEST['filter_id_end'];

		if ($arProps = $this->getProps(true))
		{
			$filtered = null;
			foreach ($arProps as $arProp)
			{
				$filterValueName = 'filter_el_property_'.$arProp['ID'];
				if (isset($arProp["PROPERTY_USER_TYPE"]['AddFilterFields']))
				{
					call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
						$arProp,
						array("VALUE" => $filterValueName),
						&$arFilter,
						&$filtered,
					));
				}
				elseif (isset($_REQUEST[$filterValueName]))
				{
					$value = $_REQUEST[$filterValueName];
					if (is_array($value) || strlen($value))
					{
						if ($value === "NOT_REF")
							$value = false;
						$arFilter["?PROPERTY_".$arProp["ID"]] = $value;
					}
					unset($value);
				}
				unset($filterValueName);
			}
			unset($arProp);
		}

		$arCatalog = array();
		$arSubQuery = array();
		if ($arSKUProps = $this->getSkuProps())
		{
			$arCatalog = $this->getOffersCatalog();
			$arSubQuery = array("IBLOCK_ID" => $arCatalog['IBLOCK_ID']);
			$filtered = null;

			foreach ($arSKUProps as $arProp)
			{
				if ($arProp['ID'] == $arCatalog['SKU_PROPERTY_ID'])
					continue;
				$filterValueName = 'filter_sub_el_property_'.$arProp['ID'];
				if (isset($arProp["PROPERTY_USER_TYPE"]['AddFilterFields']))
				{
					call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
						$arProp,
						array("VALUE" => $filterValueName),
						&$arSubQuery,
						&$filtered,
					));
				}
				elseif (isset($_REQUEST[$filterValueName]))
				{
					$value = $_REQUEST[$filterValueName];
					if (is_array($value) || strlen($value))
					{
						if ($value === "NOT_REF")
							$value = false;
						$arSubQuery["?PROPERTY_".$arProp["ID"]] = $value;
					}
					unset($value);
				}
				unset($filterValueName);
			}
			unset($arProp);
		}

		if (!empty($_REQUEST["filter_timestamp_from"]))
			$arFilter["DATE_MODIFY_FROM"] = $_REQUEST["filter_timestamp_from"];
		if (!empty($_REQUEST["filter_timestamp_to"]))
			$arFilter["DATE_MODIFY_TO"] = $_REQUEST["filter_timestamp_to"];
		if (!empty($_REQUEST["filter_code"]))
			$arFilter["CODE"] = $_REQUEST["filter_code"];

		$arSearchedIds = $arSearchedSectionIds = null;

		if (!empty($_REQUEST['QUERY']))
		{
			$arFilter['QUERY'] = $_REQUEST['QUERY'];
			$arSearchedIds = $arSearchedSectionIds = array();

			if (preg_match('#^[0-9\s]+$#', $_REQUEST['QUERY']))
			{
				$barcode = preg_replace('#[^0-9]#', '', $_REQUEST['QUERY']);
				if (strlen($barcode) > 8)
				{
					$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
					while ($res = $rsBarCode->Fetch())
					{
						$res2 = \CCatalogSku::GetProductInfo($res["PRODUCT_ID"]);
						$arSearchedIds[] = $res2 ? $res2['ID'] : $res['PRODUCT_ID'];
					}
				}
			}
			if ($this->isAdvancedSearchAvailable())
			{
				$arFilter['PARAM2'] = $this->getIblockId();
				if (!empty($arFilter['SECTION_ID']))
				{
					$arFilter['PARAMS'] = array('iblock_section' => $arFilter['SECTION_ID']);
				}
				$obSearch = new \CSearch();
				$obSearch->Search($arFilter);

				$cnt = 0;
				$activeSectionId = $this->getSectionId();
				while ($ar = $obSearch->Fetch())
				{
					if (strpos($ar['ITEM_ID'], 'S') === 0)
					{
						$sectionId = preg_replace('#[^0-9]+#', '', $ar['ITEM_ID']);
						if ($sectionId != $activeSectionId)
							$arSearchedSectionIds[] = $sectionId;
					}
					else
						$arSearchedIds[] = $ar['ITEM_ID'];
					if (++$cnt >= 100)
						break;
				}
			}
			else
			{
				if (!empty($arSearchedIds))
					$arFilter['ID'] = $arSearchedIds;
				else
					$arFilter['NAME'] = $_REQUEST['QUERY'];
				$arSearchedIds = $arSearchedSectionIds = null;
			}
		}
		if (sizeof($arSubQuery) > 1)
		{
			$arFilteredIds = array(0);
			$db = \CIBlockElement::GetList(array(), $arSubQuery, false, false, array('PROPERTY_' . $arCatalog['SKU_PROPERTY_ID']));
			while ($res = $db->Fetch())
			{
				$arFilteredIds[] = $res['PROPERTY_' . $arCatalog['SKU_PROPERTY_ID'] . '_VALUE'];
			}
			$arFilter['ID'] = is_array($arSearchedIds) ? array_intersect($arFilteredIds, $arSearchedIds) : $arFilteredIds;
		}
		elseif (!empty($arSearchedIds))
		{
			$arFilter['ID'] = $arSearchedIds;
		}
		if (!empty($arSearchedSectionIds))
			$arFilter['S_ID'] = $arSearchedSectionIds;

		unset($arFilter['PARAM1'], $arFilter['PARAM2'],$arFilter['PARAMS']);

		if ($this->isFiltering() || !empty($_REQUEST['QUERY']))
		{
			$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
			if (isset($arFilter['SECTION_ID']) && $arFilter['SECTION_ID'] == 0)
			{
				unset($arFilter["SECTION_ID"]);
			}
		}

		return $arFilter;
	}

	protected function getSectionsTree($iblockId, $rootSectionId = 0, $activeSectionId = 0)
	{
		$iblockId = (int)$iblockId;
		$rootSectionId = (int)$rootSectionId;
		$activeSectionId = (int)$activeSectionId;
		$this->activeSectionNavChain = array();
		if ($activeSectionId)
		{
			$rsSections = \CIBlockSection::GetNavChain($iblockId, $activeSectionId, array('ID', 'IBLOCK_ID', 'NAME'));
			while ($arSection = $rsSections->Fetch())
			{
				$this->activeSectionNavChain[$arSection["ID"]] = $arSection;
			}

		}

		$arSections = array();
		$rsSections = \CIBlockSection::GetList(array(
			"left_margin" => "ASC",
		), array(
			"IBLOCK_ID" => $iblockId,
			"SECTION_ID" => $rootSectionId,
		), false, array(
			"ID",
			"IBLOCK_SECTION_ID",
			"NAME",
			"LEFT_MARGIN",
			"RIGHT_MARGIN",
		));
		while ($arSection = $rsSections->Fetch())
		{
			$arSectionTmp = array(
				"text" => $arSection["NAME"],
				"title" => $arSection["NAME"],
				"icon" => "iblock_menu_icon_sections",
				"dynamic" => (($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1),
				"items" => array(),
				"id" => $arSection["ID"]
			);

			if (isset($this->activeSectionNavChain[$arSection["ID"]]))
			{
				$arSectionTmp["items"] = $this->getSectionsTree($iblockId, $arSection["ID"], $activeSectionId);
				if ($arSectionTmp['items'])
					$arSectionTmp['open'] = true;
			}

			if ($arSection['ID'] == $activeSectionId)
			{
				$arSectionTmp['active'] = true;
				$this->activeSectionLabel = $arSectionTmp['text'];
			}

			$arSections[] = $arSectionTmp;
		}
		return $arSections;
	}

	protected function getSectionMargin($sectionId)
	{
		$iterator = \CIBlockSection::GetList(array(), array(
			'IBLOCK_ID' => $this->getIblockId(),
			'ID' => $sectionId,
		), false, array(
			'LEFT_MARGIN',
			'RIGHT_MARGIN',
			'DEPTH_LEVEL'
		));
		return $iterator->fetch();
	}

	protected function getIblockList()
	{
		if ($this->iblockList === null)
		{
			$this->iblockList = array();
			$ids = array();
			$filter = array();
			if ($this->getSubscription())
				$filter['=SUBSCRIPTION'] = 'Y';

			$showOffersIBlock = \Bitrix\Main\Config\Option::get('catalog', 'product_form_show_offers_iblock');

			$catalogIterator = Catalog\CatalogIblockTable::getList(array(
				'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID', 'SUBSCRIPTION'),
				'filter' => $filter
			));
			while ($catalog = $catalogIterator->fetch())
			{
				$catalog['IBLOCK_ID'] = (int)$catalog['IBLOCK_ID'];
				$catalog['PRODUCT_IBLOCK_ID'] = (int)$catalog['PRODUCT_IBLOCK_ID'];
				$catalog['SKU_PROPERTY_ID'] = (int)$catalog['SKU_PROPERTY_ID'];
				if ($catalog['SUBSCRIPTION'] == 'N' && $catalog['PRODUCT_IBLOCK_ID'] > 0)
				{
					$ids[$catalog['PRODUCT_IBLOCK_ID']] = $catalog['PRODUCT_IBLOCK_ID'];
					if ($showOffersIBlock == 'Y')
					{
						$ids[$catalog['IBLOCK_ID']] = $catalog['IBLOCK_ID'];
					}
				}
				else
				{
					$ids[$catalog['IBLOCK_ID']] = $catalog['IBLOCK_ID'];
				}
			}
			unset($catalog, $catalogIterator);

			if (!empty($ids))
			{
				$filter = array("ID" => $ids, 'ACTIVE' => 'Y');
				if ($this->checkPermissions)
					$filter['MIN_PERMISSION'] = 'S';
				if ($this->getLid())
					$filter["LID"] = $this->getLid();
				$res = \CIBlock::GetList(
					array("ID" => "ASC"),
					$filter,
					false
				);

				while ($row = $res->Fetch())
					$this->iblockList[$row['ID']] = $row;
				unset($row, $res);

				if (count($this->iblockList) > 1)
				{
					$siteTable = new \Bitrix\Iblock\IblockSiteTable();
					$siteResult = $siteTable->getList(array(
						'select' => array('IBLOCK_ID', 'SITE_NAME' => 'SITE.NAME', 'SITE_ID', 'SITE_SORT' => 'SITE.SORT'),
						'filter' => array('@IBLOCK_ID' => array_keys($this->iblockList)),
						'order' => array('SITE_SORT' => 'ASC')
					));
					while ($row = $siteResult->fetch())
					{
						$iblockId = $row['IBLOCK_ID'];
						if (!isset($this->iblockList[$iblockId]))
							continue;
						if (!isset($this->iblockList[$iblockId]['SITE_LIST']))
							$this->iblockList[$iblockId]['SITE_LIST'] = array();
						$this->iblockList[$iblockId]['SITE_LIST'][] = $row['SITE_NAME'].' ('.$row['SITE_ID'].')';
						unset($iblockId);
					}
					unset($row, $siteResult);
					foreach (array_keys($this->iblockList) as $index)
					{
						$this->iblockList[$index]['SITE_NAME'] = '';
						if (isset($this->iblockList[$index]['SITE_LIST']))
						{
							$this->iblockList[$index]['SITE_NAME'] = implode(' | ', $this->iblockList[$index]['SITE_LIST']);
							unset($this->iblockList[$index]['SITE_LIST']);
						}
					}
					unset($index);
				}
			}

		}
		return $this->iblockList;
	}

	protected function getParentSectionId()
	{
		if (!$this->getSectionId()) return -1;
		$nav = \CIBlockSection::GetNavChain($this->getIblockId(), $this->getSectionId(), array('ID', 'IBLOCK_ID', 'NAME'));
		$navIds = array();
		while ($tmp = $nav->Fetch())
			$navIds[] = $tmp["ID"];
		array_pop($navIds);
		$parentId = 0;
		if ($navIds)
			$parentId = end($navIds);
		return $parentId;
	}

	protected function getListSort()
	{
		if ($this->isAdminSection())
		{
			$sorting = new CAdminSorting($this->getTableId(), 'ID', 'ASC');
			$sort = array($GLOBALS[$sorting->by_name] => $GLOBALS[$sorting->ord_name]);
			unset($sorting);
		}
		else
		{
			$res = $this->getGridOptions()->GetSorting();
			$sort = $res['sort'];
		}
		return $sort;
	}

	protected function prepareComponentResult()
	{
		$filter = $this->getFilter();
		$dbResultList = $this->getMixedList($this->getListSort(), $filter);
		$this->arResult = array(
			'DB_RESULT_LIST' => $dbResultList,
			'PRODUCTS' => $this->makeItemsFromDbResult($dbResultList),
			'TABLE_ID' => $this->getTableId(),
			'STORE_ID' => $this->getStoreId(),
			'IBLOCK_ID' => $this->getIblockId(),
			'SECTION_ID' => $this->getSectionId(),
			'JS_CALLBACK' => $this->getJsCallbackName(),
			'JS_EVENT' => $this->getJsEventName(),
			'FILTER' => $filter,
			'PROPS' => $this->getProps(true),
			'SKU_PROPS' => $this->getSkuProps(true),
			'SKU_PRICES' => $this->getSkuPrices(),
			'CALLER' => $this->getCaller(),
			'LID' => $this->getLid(),
			'FILTER_FIELDS' => $this->getFilterFields(),
			'FILTER_LABELS' => $this->getFilterLabels(),
			'SECTIONS' => $this->getSectionsTree($this->getIblockId(), 0, $this->getSectionId()),
			'PRICES' => $this->getPrices(),
			'HEADERS' => $this->getHeaders(),
			'VISIBLE_COLUMNS' => $this->getVisibleColumns(),
			'IBLOCKS' => $this->getIblockList(),
			'SKU_CATALOG' => $this->getOffersCatalog(),
			'PARENT_SECTION_ID' => $this->getParentSectionId(),
			'SECTION_LABEL' => $this->getActiveSectionLabel(),
			'BREADCRUMBS' => $this->activeSectionNavChain,
			'RELOAD' => !empty($_REQUEST['action']) && $_REQUEST['action'] == 'change_iblock',
			'SUBSCRIPTION' => $this->getSubscription(),
			'IS_ADMIN_SECTION' => $this->isAdminSection(),
			'IS_EXTERNALCONTEXT' => $this->isExternalContext(),
			'ALLOW_SELECT_PARENT' => $this->arParams['allow_select_parent']
		);
	}

	protected function saveState()
	{
		if ($this->getIblockId())
		{
			\CUserOptions::SetOption("catalog", $this->getTableId(),
				array('IBLOCK_ID' =>$this->getIblockId(),
					'SECTION_ID' =>$this->getSectionId(),
					'QUERY' => isset($_REQUEST['QUERY'])?  $_REQUEST['QUERY'] : ''
				),
				false, $this->getUserId());
		}
	}

	private function getPropsList($iblockId, $skuPropertyId = 0)
	{
		$arResult = array();
		$dbrFProps = \CIBlockProperty::GetList(
			array(
				"SORT" => "ASC",
				"NAME" => "ASC"
			),
			array(
				"IBLOCK_ID" => $iblockId,
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "N",
			)
		);
		while ($arProp = $dbrFProps->Fetch())
		{
			if ($skuPropertyId == $arProp['ID'])
				continue;
			$arProp["PROPERTY_USER_TYPE"] = (!empty($arProp["USER_TYPE"]) ? \CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
			$arResult[] = $arProp;
		}
		return $arResult;
	}

	private function filterProps(&$props)
	{
		$result = array();
		if (empty($props) || !is_array($props))
			return $result;
		foreach ($props as $prop)
		{
			if ($prop['FILTRABLE'] == 'Y' && $prop['PROPERTY_TYPE'] != 'F')
				$result[] = $prop;
		}

		return $result;
	}

	/* deprecated methods */

	/**
	 * Return properties from product.
	 *
	 * @deprecated deprecated since catalog 16.5.3
	 *
	 * @param int $id		Product id.
	 * @return array
	 */
	protected function getItemProperies($id)
	{
		$arProperties = array();
		$propIds = $this->getVisibleProperties();
		if ($propIds)
		{
			$rsProperties = \CIBlockElement::GetProperty($this->getIblockId(), $id, 'SORT', 'ASC', array('ID' => $propIds));
			while ($ar = $rsProperties->Fetch())
			{
				if (!array_key_exists($ar["ID"], $arProperties))
					$arProperties[$ar["ID"]] = array();
				if ($ar["PROPERTY_TYPE"] === "L")
					$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE_ENUM"];
				else
					$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE"];
			}
		}
		return $arProperties;
	}
}