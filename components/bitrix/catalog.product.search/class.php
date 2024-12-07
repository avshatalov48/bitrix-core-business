<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class ProductSearchComponent extends \CBitrixComponent
{
	const TABLE_ID_PREFIX = 'tbl_product_search';

	protected ?int $iblockId = null;
	protected ?array $arProps = null;
	protected ?array $arSkuProps = null;
	protected $offersCatalog;
	protected ?array $arPrices = null;
	/* Grid headers */
	protected ?array $arHeaders = null;
	/* Rows field list */
	protected array $dataFields = [];
	protected string $activeSectionLabel = '';
	protected bool $simpleSearch = false;
	protected ?int $offersIblockId = null;
	protected ?array $iblockList = null;
	protected ?CGridOptions $gridOprtions = null;

	protected ?array $visibleColumns = null;

	protected ?array $visiblePrices = null;

	protected ?array $visibleProperties = null;

	protected ?array $visibleFields = null;

	protected bool $checkPermissions = true;

	protected array $activeSectionNavChain = array();
	protected array $offers = array();

	protected static array $elementsNamesCache = array();
	protected static array $sectionsNamesCache = array();

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
		$params['iblockfix'] = (isset($params['iblockfix']) && $params['iblockfix'] === 'Y' ? 'Y' : 'N');
		if (isset($_REQUEST['iblockfix']) && $_REQUEST['iblockfix'] === 'Y')
		{
			$params['iblockfix'] = 'Y';
		}
		if ($params['IBLOCK_ID'] <= 0)
		{
			$params['iblockfix'] = 'N';
		}

		$params['SECTION_ID'] = isset($_REQUEST['SECTION_ID']) ? (int)$_REQUEST['SECTION_ID'] : 0;

		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'change_iblock')
		{
			$params['SECTION_ID'] = 0;
		}

		$params['LID'] = isset($_REQUEST['LID']) ? htmlspecialcharsbx($_REQUEST['LID']) : '';
		if ($params['LID'] == '')
			$params['LID'] = false;
		$params['func_name'] = isset($_REQUEST["func_name"]) ? preg_replace("/[^a-zA-Z0-9_\.]/is", "", $_REQUEST["func_name"]) : '';
		$params['event'] = isset($_REQUEST['event']) ? preg_replace("/[^a-zA-Z0-9_\.]/is", "", $_REQUEST['event']) : '';
		$params['caller'] = isset($_REQUEST["caller"]) ? preg_replace("/[^a-zA-Z0-9_\-]/is", "", $_REQUEST["caller"]) : '';
		$params['multiple_select'] = $_REQUEST['multiple_select'] ?? 'N';
		$params['multiple_select'] = $params['multiple_select'] === 'Y';
		$params['subscribe'] = (isset($_REQUEST['subscribe']) && $_REQUEST['subscribe'] == 'Y');
		$params['store_from_id'] = (int)($_REQUEST["STORE_FROM_ID"] ??  0);
		if ($params['store_from_id'] < 0)
		{
			$params['store_from_id'] = 0;
		}
		$params['allow_select_parent'] = $_REQUEST['allow_select_parent'] ?? 'N';
		if ($params['allow_select_parent'] !== 'Y')
		{
			$params['allow_select_parent'] = 'N';
		}
		if ($params['caller'] === 'discount')
		{
			$params['allow_select_parent'] = 'Y';
		}

		if (!empty($_REQUEST['del_filter']))
		{
			ClearVars('filter_');
			foreach ($_REQUEST as $key => $value)
			{
				if (mb_strpos($key, 'filter_') === 0)
					unset($_REQUEST[$key]);
			}
		}

		$this->simpleSearch = \Bitrix\Main\Config\Option::get('catalog', 'product_form_simple_search') === 'Y';

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
			if (!isset($_REQUEST['USE_SUBSTRING_QUERY']) && (!isset($_REQUEST['mode']) || $_REQUEST['mode'] != 'list') && isset($userOptions['USE_SUBSTRING_QUERY']))
				$_REQUEST['USE_SUBSTRING_QUERY'] = $userOptions['USE_SUBSTRING_QUERY'];
		}

		return $params;
	}

	public function executeComponent()
	{
		$this->loadModules();
		$this->checkAccess();
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

	protected function isAdminSection(): bool
	{
		return \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->isAdminSection();
	}

	protected function isFiltering(): bool
	{
		foreach ($_REQUEST as $key => $value)
		{
			if (mb_strpos($key, 'filter_') === 0)
				return true;
		}
		return false;
	}

	protected function isExternalContext(): bool
	{
		return !empty($_REQUEST['externalcontext']);
	}

	protected function getGridOptions(): CGridOptions
	{
		if ($this->gridOprtions === null)
			$this->gridOprtions = new CGridOptions($this->getTableId());

		return $this->gridOprtions;
	}

	protected function checkAccess(): bool
	{
		global $APPLICATION;

		if (!$this->checkPermissions)
			return true;

		$accessController = AccessController::getCurrent();
		if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)))
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

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool $bIncCnt
	 * @param bool|array $arSelectedFields
	 * @return CAdminResult|CDBResult
	 */
	protected function getMixedList(array $arOrder = array('SORT' => 'ASC'), array $arFilter = array(), bool $bIncCnt = false, $arSelectedFields = false)
	{
		$arResult = array();

		if (!is_array($arOrder))
			$arOrder = array("SORT"=>"ASC");

		//TODO: change to \CIBlockSection::getElementInherentFilter after update will be stable
		$elementInherentFilter = self::getElementInherentFilter($arFilter);

		$notFound = false;
		if (self::checkLoadSections($elementInherentFilter) && !isset($arFilter['USE_BARCODE']))
		{
			if (isset($arFilter["S_ID"]) && is_array($arFilter["S_ID"]) && count($arFilter["S_ID"]) == 1)
			{
				$notFound = $arFilter['S_ID'][0] == 0;
			}
			if (!$notFound && !$this->isFiltering())
			{
				$arSectionFilter = [
					"IBLOCK_ID" => $arFilter["IBLOCK_ID"]
				];
				if (isset($arFilter["S_ID"]))
					$arSectionFilter["=ID"] = $arFilter["S_ID"];
				if (isset($arFilter["NAME"]))
				{
					if ($arFilter['USE_SUBSTRING_QUERY'] == 'Y')
						$arSectionFilter["%NAME"] = $arFilter["NAME"];
					else
						$arSectionFilter["?NAME"] = $arFilter["NAME"];
				}
				if (isset($arFilter["DATE_MODIFY_FROM"]))
					$arSectionFilter[">=TIMESTAMP_X"] = $arFilter["DATE_MODIFY_FROM"];
				if (isset($arFilter["DATE_MODIFY_TO"]))
					$arSectionFilter["<=TIMESTAMP_X"] = $arFilter["DATE_MODIFY_TO"];
				if (isset($arFilter["CODE"]))
					$arSectionFilter["CODE"] = $arFilter["CODE"];
				if (isset($arFilter["ACTIVE"]))
					$arSectionFilter["ACTIVE"] = $arFilter["ACTIVE"];

				if (isset($arFilter["CHECK_PERMISSIONS"]))
				{
					$arSectionFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
					$arSectionFilter['MIN_PERMISSION'] = ($arFilter['MIN_PERMISSION'] ?? 'R');
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
				$rsSection = $obSection->GetList($arOrder, $arSectionFilter, $bIncCnt, $arSelectedFields);
				while ($arSection = $rsSection->Fetch())
				{
					$arSection["TYPE"] = "S";
					$arResult[] = $arSection;
				}
				unset($arSection, $rsSection, $obSection);
			}
		}
		$notFound = false;
		if (isset($arFilter["ID"]) && is_array($arFilter["ID"]) && sizeof($arFilter["ID"]) == 1)
		{
			$notFound = $arFilter['ID'][0] == 0;
		}
		if (!$notFound)
		{
			$arElementFilter = [
				"IBLOCK_ID" => $arFilter["IBLOCK_ID"]
			];
			if (isset($arFilter["NAME"]))
			{
				if ($arFilter['USE_SUBSTRING_QUERY'] == 'Y')
					$arElementFilter["%NAME"] = $arFilter["NAME"];
				else
					$arElementFilter["?NAME"] = $arFilter["NAME"];
			}
			if (isset($arFilter["SECTION_ID"]))
				$arElementFilter["SECTION_ID"] = $arFilter["SECTION_ID"];
			if (isset($arFilter["ID"]))
				$arElementFilter["=ID"] = $arFilter["ID"];
			if (isset($arFilter["DATE_MODIFY_FROM"]))
				$arElementFilter[">=TIMESTAMP_X"] = $arFilter["DATE_MODIFY_FROM"];
			if (isset($arFilter["DATE_MODIFY_TO"]))
				$arElementFilter["<=TIMESTAMP_X"] = $arFilter["DATE_MODIFY_TO"];
			if (isset($arFilter["CODE"]))
				$arElementFilter["CODE"] = $arFilter["CODE"];
			if (isset($arFilter["ACTIVE"]))
				$arElementFilter["ACTIVE"] = $arFilter["ACTIVE"];
			if (isset($arFilter["WF_STATUS"]))
				$arElementFilter["WF_STATUS"] = $arFilter["WF_STATUS"];
			if (isset($arFilter["INCLUDE_SUBSECTIONS"]))
				$arElementFilter['INCLUDE_SUBSECTIONS'] = $arFilter["INCLUDE_SUBSECTIONS"];

			if(!empty($arFilter['XML_ID']))
				$arElementFilter['XML_ID'] = $arFilter['XML_ID'];
			if(!empty($arFilter['>=ID']))
				$arElementFilter['>=ID'] = $arFilter['>=ID'];
			if(!empty($arFilter['<=ID']))
				$arElementFilter['<=ID'] = $arFilter['<=ID'];

			if (isset($arFilter["CHECK_PERMISSIONS"]))
			{
				$arElementFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
				$arElementFilter['MIN_PERMISSION'] = ($arFilter['MIN_PERMISSION'] ?? 'S');
			}

			if (!empty($elementInherentFilter))
				$arElementFilter = $arElementFilter + $elementInherentFilter;

			if ($arFilter["SECTION_ID"] == '')
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
			unset($arElement, $rsElement, $obElement);
		}

		unset($elementInherentFilter);

		$rsResult = new \CDBResult;
		$rsResult->InitFromArray($arResult);

		if ($this->isAdminSection())
		{
			$rsResult = new \CAdminResult($rsResult, $this->getTableId());
			$rsResult->NavStart();
		}
		else
		{
			$navParams = $this->getGridOptions()->GetNavParams();
			$navParams['bShowAll'] = false;
			$rsResult->NavStart($navParams);
			unset($navParams);
		}

		return $rsResult;
	}

	protected function getOffersIblockId(): int
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

			$select = array('NAME',  'ACTIVE', 'QUANTITY', 'TYPE');
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
							$offersLink[$oneOfferId]['DEFAULT_QUANTITY'] = $ratioData['RATIO'];
							$offersLink[$oneOfferId]['MEASURE'] = $ratioData['MEASURE'];
						}
						unset($oneOfferId, $ratioData);
					}
					unset($ratioResult);

					$priceIds = $this->getVisiblePrices();
					foreach ($priceIds as $id)
					{
						$iterator = Catalog\PriceTable::getList(array(
							'select' => array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO'),
							'filter' => array('@PRODUCT_ID' => $offersIds, '=CATALOG_GROUP_ID' => $id),
						));
						while ($row = $iterator->fetch())
						{
							$productId = (int)$row['PRODUCT_ID'];
							$row['QUANTITY_FROM_SORT'] = ($row['QUANTITY_FROM'] === null ? 0 : (int)$row['QUANTITY_FROM']);
							$row['QUANTITY_TO_SORT'] = ($row['QUANTITY_TO'] === null ? INF : (int)$row['QUANTITY_TO']);
							$row['QUANTITY_FROM'] = (int)$row['QUANTITY_FROM'];
							$row['QUANTITY_TO'] = (int)$row['QUANTITY_TO'];

							$ratio = $offersLink[$productId]['MEASURE_RATIO'];
							if ($ratio > $row['QUANTITY_TO_SORT'])
								continue;
							if ($ratio < $row['QUANTITY_FROM_SORT'])
							{
								$newRatio = $ratio * ((int)($row['QUANTITY_FROM_SORT']/$ratio));
								if ($newRatio < $row['QUANTITY_FROM_SORT'])
									$newRatio += $ratio;
								if ($newRatio > $row['QUANTITY_TO_SORT'])
									continue;
								$ratio = $newRatio;
							}

							if (
								!isset($offersLink[$productId]['PRICES'][$id])
								|| ($offersLink[$productId]['PRICES'][$id]['QUANTITY_FROM'] > $row['QUANTITY_FROM'])
							)
							{
								$offersLink[$productId]['DEFAULT_QUANTITY'] = $ratio;
								$offersLink[$productId]['PRICES'][$id] = array(
									'PRICE' => $row['PRICE'],
									'CURRENCY' => $row['CURRENCY'],
									'QUANTITY_FROM' => $row['QUANTITY_FROM'],
									'QUANTITY_TO' => $row['QUANTITY_TO']
								);
							}
						}
						unset($row, $iterator);
					}
				}
				unset($offersLink, $offersIds);
			}
		}
	}
	/**
	 * @param array $arProduct
	 * @return array|bool
	 */
	protected function getProductSku(array $arProduct)
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

			foreach ($this->offers[$productId] as $arOffer)
			{
				$arSkuTmp = array();
				$arSkuTmp['PROPERTIES'] = array();
				$arOffer["CAN_BUY"] = "N";

				if (!empty($arOffer['PROPERTIES']))
				{
					foreach ($arOffer['PROPERTIES'] as $property)
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

				$arSkuTmp["BALANCE"] = $arOffer["QUANTITY"];
				$arSkuTmp["ID"] = $arOffer["ID"];
				$arSkuTmp["TYPE"] = $arOffer["TYPE"];
				$arSkuTmp["NAME"] = $arOffer["NAME"];
				$arSkuTmp["PRODUCT_NAME"] = $productName;
				$arSkuTmp["PRODUCT_ID"] = $productId;
				$arSkuTmp["CAN_BUY"] = $arOffer["CAN_BUY"];
				$arSkuTmp["ACTIVE"] = $arOffer["ACTIVE"];
				if (isset($arOffer['PREVIEW_PICTURE']))
					$arSkuTmp['PREVIEW_PICTURE'] = $arOffer['PREVIEW_PICTURE'];
				if (isset($arOffer['DETAIL_PICTURE']))
					$arSkuTmp['DETAIL_PICTURE'] = $arOffer['DETAIL_PICTURE'];
				if (isset($arOffer['MEASURE_RATIO']))
					$arSkuTmp['MEASURE_RATIO'] = $arOffer['MEASURE_RATIO'];
				else
					$arSkuTmp['MEASURE_RATIO'] = 1;
				if (isset($arOffer['DEFAULT_QUANTITY']))
					$arSkuTmp['DEFAULT_QUANTITY'] = $arOffer['DEFAULT_QUANTITY'];
				else
					$arSkuTmp['DEFAULT_QUANTITY'] = 1;
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
	protected function makeItemsFromDbResult(\CDBResult $dbResultList): array
	{
		$arItemsResult = array();
		$arProductIds = array();
		$sectionIds = array();
		while ($arItem = $dbResultList->Fetch())
		{
			if ($arItem['TYPE'] == 'S')
			{
				$sectionIds[] = $arItem['ID'];
				$arItemsResult['S'.$arItem['ID']] = $arItem;
			}
			else
			{
				$arProductIds[] = $arItem['ID'];
				$arItem['PROPERTIES'] = array();
				$arItemsResult[$arItem['ID']] = $arItem;
			}
		}

		$iblockId = $this->getIblockId();
		$fields = $this->getVisibleFields();

		if (!empty($sectionIds))
		{
			sort($sectionIds);
			foreach (array_chunk($sectionIds, 500) as $pageIds)
			{
				$sectionFilter = [
					'IBLOCK_ID' => $iblockId,
					'ID' => $pageIds,
					'CHECK_PERMISSIONS' => 'N'
				];
				$iterator = \CIBlockSection::GetList(array(), $sectionFilter, false, $fields);
				while ($row = $iterator->Fetch())
				{
					$arItemsResult['S'.$row['ID']] = $arItemsResult['S'.$row['ID']] + $row;
				}
			}
			unset($row);
			unset($iterator);
			unset($sectionFilter);
			unset($pageIds);
		}

		if (!empty($arProductIds))
		{
			sort($arProductIds);
			foreach (array_chunk($arProductIds, 500) as $pageIds)
			{
				$elementFilter = [
					'IBLOCK_ID' => $iblockId,
					'ID' => $pageIds,
					'CHECK_PERMISSIONS' => 'N',
					'SHOW_NEW' => 'Y'
				];
				$iterator = \CIBlockElement::GetList(array(), $elementFilter, false, false, $fields);
				while ($row = $iterator->Fetch())
				{
					$arItemsResult[$row['ID']] = $arItemsResult[$row['ID']] + $row;
				}
			}
			unset($row);
			unset($iterator);
			unset($elementFilter);
			unset($pageIds);

			$propertyIds = $this->getVisibleProperties();
			if (!empty($propertyIds))
			{
				$arPropFilter = array(
					'ID' => $arProductIds,
					'IBLOCK_ID' => $iblockId
				);
				CIBlockElement::GetPropertyValuesArray(
					$arItemsResult,
					$iblockId,
					$arPropFilter,
					array('ID' => $propertyIds),
					array(
						'PROPERTY_FIELDS' => array(
							'ID', 'NAME', 'SORT', 'PROPERTY_TYPE',
							'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'USER_TYPE_SETTINGS'
						)
					)
				);
			}
			unset($propertyIds);

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
					foreach ($item['PROPERTIES'] as $property)
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

			$skuIds = [];
			$iterator = Catalog\ProductTable::getList(array(
				'select' => array('ID', 'TYPE', 'QUANTITY', 'AVAILABLE', 'MEASURE'),
				'filter' => array('@ID' => $arProductIds)
			));
			while ($row = $iterator->fetch())
			{
				if ((int)$row['TYPE'] === Catalog\ProductTable::TYPE_SKU)
				{
					$skuIds[] = $row['ID'];
				}
				$arItemsResult[$row['ID']]['PRODUCT'] = $row;
			}
			unset($row, $iterator);

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

			$priceIds = $this->getVisiblePrices();

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
						$arItemsResult[$productId]['PRODUCT']['DEFAULT_QUANTITY'] = $productRatio['RATIO'];
						$arItemsResult[$productId]['PRODUCT']['MEASURE'] = $productRatio['MEASURE'];
					}
					unset($productRatio, $productId);
				}
				unset($productRatioList);

				foreach ($priceIds as $priceId)
				{
					$iterator = Catalog\PriceTable::getList(array(
						'select' => array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO'),
						'filter' => array('@PRODUCT_ID' => $noOffersIds, '=CATALOG_GROUP_ID' => $priceId)
					));
					while ($row = $iterator->fetch())
					{
						$productId = (int)$row['PRODUCT_ID'];
						$row['QUANTITY_FROM_SORT'] = ($row['QUANTITY_FROM'] === null ? 0 : (int)$row['QUANTITY_FROM']);
						$row['QUANTITY_TO_SORT'] = ($row['QUANTITY_TO'] === null ? INF : (int)$row['QUANTITY_TO']);
						$row['QUANTITY_FROM'] = (int)$row['QUANTITY_FROM'];
						$row['QUANTITY_TO'] = (int)$row['QUANTITY_TO'];

						$ratio = $arItemsResult[$productId]['PRODUCT']['MEASURE_RATIO'];
						if ($ratio > $row['QUANTITY_TO_SORT'])
							continue;
						if ($ratio < $row['QUANTITY_FROM_SORT'])
						{
							$newRatio = $ratio * ((int)($row['QUANTITY_FROM_SORT']/$ratio));
							if ($newRatio < $row['QUANTITY_FROM_SORT'])
								$newRatio += $ratio;
							if ($newRatio > $row['QUANTITY_TO_SORT'])
								continue;
							$ratio = $newRatio;
						}

						if (
							!isset($arItemsResult[$productId]['PRICES'][$priceId])
							|| ($arItemsResult[$productId]['PRICES'][$priceId]['QUANTITY_FROM'] > $row['QUANTITY_FROM'])
						)
						{
							$arItemsResult[$productId]['PRODUCT']['DEFAULT_QUANTITY'] = $ratio;
							$arItemsResult[$productId]['PRICES'][$priceId] = array(
								'PRICE' => $row['PRICE'],
								'CURRENCY' => $row['CURRENCY'],
								'QUANTITY_FROM' => $row['QUANTITY_FROM'],
								'QUANTITY_TO' => $row['QUANTITY_TO']
							);
						}
					}
					unset($row, $iterator);
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

			if (!empty($skuIds))
			{
				foreach ($priceIds as $priceId)
				{
					$iterator = Catalog\PriceTable::getList([
						'select' => [
							'PRODUCT_ID',
							'PRICE',
							'CURRENCY',
							'QUANTITY_FROM',
							'QUANTITY_TO',
						],
						'filter' => [
							'@PRODUCT_ID' => $skuIds,
							'=CATALOG_GROUP_ID' => $priceId,
						]
					]);
					while ($row = $iterator->fetch())
					{
						$productId = (int)$row['PRODUCT_ID'];
						$row['QUANTITY_FROM_SORT'] = ($row['QUANTITY_FROM'] === null ? 0 : (int)$row['QUANTITY_FROM']);
						$row['QUANTITY_TO_SORT'] = ($row['QUANTITY_TO'] === null ? INF : (int)$row['QUANTITY_TO']);
						$row['QUANTITY_FROM'] = (int)$row['QUANTITY_FROM'];
						$row['QUANTITY_TO'] = (int)$row['QUANTITY_TO'];

						if (
							!isset($arItemsResult[$productId]['PRICES'][$priceId])
							|| ($arItemsResult[$productId]['PRICES'][$priceId]['QUANTITY_FROM'] > $row['QUANTITY_FROM'])
						)
						{
							$arItemsResult[$productId]['PRICES'][$priceId] = [
								'PRICE' => $row['PRICE'],
								'CURRENCY' => $row['CURRENCY'],
								'QUANTITY_FROM' => $row['QUANTITY_FROM'],
								'QUANTITY_TO' => $row['QUANTITY_TO'],
							];
						}
					}
					unset($row, $iterator);
				}
			}
		}

		return $arItemsResult;
	}

	protected function getSkuPrices(): array
	{
		$result = array();
		if ($this->offers)
		{
			$priceIds = $this->getVisiblePrices();
			foreach ($this->offers as $productId => $offers)
			{
				foreach (array_keys($offers) as $index)
				{
					foreach ($priceIds as $id)
					{
						if (!isset($this->offers[$productId][$index]['PRICES'][$id]))
							continue;
						if (!isset($result[$id]))
							$result[$id] = [];
						$result[$id][$index] = $this->offers[$productId][$index]['PRICES'][$id];
					}
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

	protected function getUserId(): int
	{
		global $USER;
		return (int)$USER->GetID();
	}

	protected function getCaller(): string
	{
		return $this->arParams['caller'];
	}

	protected function isMultipleSelect(): bool
	{
		return $this->arParams['multiple_select'];
	}

	protected function getLid()
	{
		return $this->arParams['LID'];
	}

	protected function getTableId(): string
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

	protected function getIblockId(): int
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
		return $this->iblockList[$id] ?? false;
	}

	protected function isAdvancedSearchAvailable(): bool
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

	protected function getActiveSectionLabel(): string
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

	protected function getFilterFields(): array
	{
		return [
			"filter_timestamp_from",
			"filter_timestamp_to",
			"filter_active",
			"filter_code",
			'filter_id_start',
			'filter_id_end',
			'filter_xml_id',
		];
	}

	protected function getFilterLabels(): array
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

	protected function getProps($flagAll = false): array
	{
		if ($this->arProps === null)
			$this->arProps = $this->getPropsList($this->getIblockId());

		return $flagAll ? $this->arProps : $this->filterProps($this->arProps);
	}

	protected function getSkuProps($flagAll = false): array
	{
		if ($this->arSkuProps === null)
		{
			$arCatalog = $this->getOffersCatalog();
			$this->arSkuProps = $arCatalog? $this->getPropsList($arCatalog["IBLOCK_ID"], $arCatalog['SKU_PROPERTY_ID']) : array();
		}
		return $flagAll ? $this->arSkuProps : $this->filterProps($this->arSkuProps);
	}

	protected function getPrices(): array
	{
		if ($this->arPrices === null)
		{
			$this->arPrices = Catalog\GroupTable::getTypeList();
		}

		return $this->arPrices;
	}

	/**
	 * @return void
	 */
	protected function loadHeaders()
	{
		if ($this->arHeaders !== null)
			return;

		$balanceTitle = Loc::getMessage($this->getStoreId() > 0 ? "SOPS_BALANCE" : "SOPS_BALANCE2");
		$this->arHeaders = [
			["id" => "ID", "content" => "ID", "sort" => "ID", "default" => true],
			["id" => "ACTIVE", "content" => Loc::getMessage("SOPS_ACTIVE"), "sort" => "ACTIVE", "default" => true],
			["id" => "DETAIL_PICTURE", "default" => true, "content" => Loc::getMessage("SPS_FIELD_DETAIL_PICTURE"), "align" => "center"],
			["id" => "NAME", "content" => Loc::getMessage("SPS_NAME"), "sort" => "NAME", "default" => true],
			["id" => "BALANCE", "content" => $balanceTitle, "default" => true, "align" => "right"],
			["id" => "CODE", "content" => Loc::getMessage("SPS_FIELD_CODE"), "sort" => "CODE"],
			["id" => "EXTERNAL_ID", "content" => Loc::getMessage("SPS_FIELD_XML_ID"), "sort" => "XML_ID"],
			["id" => "SORT", "content" => Loc::getMessage("SPS_FIELD_SORT"), "sort" => "SORT", "align" => "right"],
			["id" => "SHOW_COUNTER", "content" => Loc::getMessage("SPS_FIELD_SHOW_COUNTER"), "sort" => "SHOW_COUNTER", "align" => "right"],
			["id" => "SHOW_COUNTER_START", "content" => Loc::getMessage("SPS_FIELD_SHOW_COUNTER_START"), "sort" => "SHOW_COUNTER_START", "align" => "right"],
			["id" => "PREVIEW_PICTURE", "content" => Loc::getMessage("SPS_FIELD_PREVIEW_PICTURE"), "align" => "right"],
			["id" => "PREVIEW_TEXT", "content" => Loc::getMessage("SPS_FIELD_PREVIEW_TEXT")],
			["id" => "DETAIL_TEXT", "content" => Loc::getMessage("SPS_FIELD_DETAIL_TEXT")],
			["id" => "EXPAND", "content" => Loc::getMessage("SPS_EXPAND"), "sort" => "", "default" => true],
			["id" => "QUANTITY", "content" =>  Loc::getMessage("SPS_QUANTITY")],
			["id" => "ACTION", "content" =>  Loc::getMessage("SPS_FIELD_ACTION"),  "default" => true]
		];
		$this->dataFields = [
			'ID' => true,
			'ACTIVE' => true,
			'DETAIL_PICTURE' => true,
			'NAME' => true,
			'CODE' => true,
			'EXTERNAL_ID' => true,
			'SORT' => true,
			'SHOW_COUNTER' => true,
			'SHOW_COUNTER_START' => true,
			'PREVIEW_PICTURE' => true,
			'PREVIEW_TEXT' => true,
			'DETAIL_TEXT' => true
		];
		$arProps = $this->getProps(true);
		if (!empty($arProps))
		{
			foreach ($arProps as &$prop)
			{
				$this->arHeaders[] = [
					"id" => "PROPERTY_".$prop['ID'], "content" => $prop['NAME'],
					"align" => ($prop["PROPERTY_TYPE"] == 'N' ? "right" : "left"),
					"sort" => ($prop["MULTIPLE"] != 'Y' ? "PROPERTY_".$prop['ID'] : "")
				];
				$this->dataFields['PROPERTY_'.$prop['ID']] = true;
			}
			unset($prop);
		}
		$arProps = $this->getSkuProps(true);
		if (!empty($arProps))
		{
			foreach ($arProps as &$prop)
			{
				$this->arHeaders[] = [
					"id" => "PROPERTY_".$prop['ID'], "content" => $prop['NAME'].' ('.Loc::getMessage("SPS_OFFER").')',
					"align" => ($prop["PROPERTY_TYPE"] == 'N' ? "right" : "left"),
				];
				$this->dataFields['PROPERTY_'.$prop['ID']] = true;
			}
			unset($prop);
		}
		unset($arProps);
		$arPrices = $this->getPrices();
		if (!empty($arPrices))
		{
			foreach ($arPrices as &$price)
			{
				$this->arHeaders[] = [
					"id" => "PRICE".$price["ID"],
					"content" => (!empty($price["NAME_LANG"]) ? $price["NAME_LANG"] : $price["NAME"]),
					"default" => $price["BASE"] == 'Y'
				];
				$this->dataFields['PRICE'.$price['ID']] = true;
			}
			unset($price);
		}
		unset($arPrices);

		foreach (array_keys($this->arHeaders) as $index)
		{
			$this->arHeaders[$index]['default'] ??= false;
		}
	}

	/**
	 * @return array
	 */
	protected function getHeaders(): array
	{
		$this->loadHeaders();
		return $this->arHeaders;
	}

	/**
	 * @return void
	 */
	protected function loadVisibleColumns()
	{
		if ($this->visibleColumns !== null)
			return;
		$this->visibleColumns = [];
		$this->visibleFields = [];
		$this->visibleProperties = [];
		$this->visiblePrices = [];

		if ($this->isAdminSection())
		{
			$aOptions = CUserOptions::GetOption("list", $this->getTableId(), array());
			$aColsTmp = explode(",", $aOptions["columns"] ?? '');
		}
		else
		{
			$aColsTmp = $this->getGridOptions()->GetVisibleColumns();
		}

		$aCols = [];
		foreach ($aColsTmp as $col)
		{
			$col = trim($col);
			if ($col != '')
				$aCols[] = $col;
		}
		$headers = $this->getHeaders();
		$useDefault = empty($aCols);
		foreach ($headers as $param)
		{
			if (($useDefault && $param["default"]) || in_array($param["id"], $aCols))
			{
				$this->visibleColumns[] = $param["id"];

				if (mb_strpos($param["id"], 'PRICE') === 0)
				{
					$this->visiblePrices[] = (int)str_replace('PRICE', '', $param["id"]);
				}
				elseif (mb_strpos($param["id"], 'PROPERTY_') === 0)
				{
					$this->visibleProperties[] = (int)str_replace('PROPERTY_', '', $param["id"]);
				}
				elseif (isset($this->dataFields[$param["id"]]))
				{
					$this->visibleFields[] = $param["id"];
				}
			}
		}
		if (!in_array('ID', $this->visibleColumns))
		{
			$this->visibleColumns[] = 'ID';
			$this->visibleFields[] = 'ID';
			$this->visibleFields[] = 'IBLOCK_ID';
		}
		unset($param, $headers);
	}

	/**
	 * @return array
	 */
	protected function getVisibleColumns(): array
	{
		$this->loadVisibleColumns();
		return $this->visibleColumns;
	}

	/**
	 * @return array
	 */
	protected function getVisiblePrices(): array
	{
		$this->loadVisibleColumns();
		return $this->visiblePrices;
	}

	/**
	 * @return array
	 */
	protected function getVisibleProperties(): array
	{
		$this->loadVisibleColumns();
		return $this->visibleProperties;
	}

	/**
	 * @return array
	 */
	protected function getVisibleFields(): array
	{
		$this->loadVisibleColumns();
		return $this->visibleFields;
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
		{
			$arFilter['!=TYPE'] = Catalog\ProductTable::getStoreDocumentRestrictedProductTypes();
		}

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
					if (is_array($value) || mb_strlen($value))
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
					if (is_array($value) || mb_strlen($value))
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
			$arFilter['USE_SUBSTRING_QUERY'] = (isset($_REQUEST['USE_SUBSTRING_QUERY']) && $_REQUEST['USE_SUBSTRING_QUERY'] == 'Y' ? 'Y' : 'N');
			$arSearchedIds = $arSearchedSectionIds = array();

			if (preg_match('#^[0-9\s]+$#', $_REQUEST['QUERY']))
			{
				$barcode = preg_replace('#[^0-9]#', '', $_REQUEST['QUERY']);
				if (mb_strlen($barcode) > 8)
				{
					$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
					while ($res = $rsBarCode->Fetch())
					{
						$res2 = \CCatalogSku::GetProductInfo($res["PRODUCT_ID"]);
						$arSearchedIds[] = $res2 ? $res2['ID'] : $res['PRODUCT_ID'];
						$arFilter['USE_BARCODE'] = true;
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
					if (mb_strpos($ar['ITEM_ID'], 'S') === 0)
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

	protected function getSectionsTree($iblockId, $rootSectionId = 0, $activeSectionId = 0): array
	{
		$iblockId = (int)$iblockId;
		$rootSectionId = (int)$rootSectionId;
		$activeSectionId = (int)$activeSectionId;
		$this->activeSectionNavChain = array();
		if ($activeSectionId)
		{
			$sectionList = \CIBlockSection::GetNavChain(
				$iblockId,
				$activeSectionId,
				[
					'ID',
					'IBLOCK_ID',
					'NAME',
				],
				true
			);
			foreach ($sectionList as $arSection)
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

	protected function getIblockList(): array
	{
		if ($this->iblockList === null)
		{
			$this->iblockList = array();
			$ids = array();
			$filter = array();
			if ($this->getSubscription())
				$filter['=SUBSCRIPTION'] = 'Y';
			if ($this->arParams['iblockfix'] === 'Y')
			{
				$filter['=IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
			}

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

	protected function getParentSectionId(): int
	{
		if (!$this->getSectionId())
		{
			return -1;
		}
		$section = Iblock\SectionTable::getRow([
			'select' => [
				'ID',
				'IBLOCK_SECTION_ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->getIblockId(),
				'=ID' => (int)$this->getSectionId(),
			]
		]);
		if (empty($section))
		{
			return -1;
		}

		return (int)$section['IBLOCK_SECTION_ID'];
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
		$dbResultList = $this->getMixedList($this->getListSort(), $filter, false, ['ID', 'IBLOCK_ID']);

		$filter['QUERY'] ??= '';
		$filter['USE_SUBSTRING_QUERY'] ??= 'N';

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
			'ALLOW_SELECT_PARENT' => $this->arParams['allow_select_parent'],
			'MULTIPLE' => $this->isMultipleSelect(),
		);
	}

	protected function saveState()
	{
		if ($this->getIblockId())
		{
			\CUserOptions::SetOption("catalog", $this->getTableId(),
				array('IBLOCK_ID' =>$this->getIblockId(),
					'SECTION_ID' =>$this->getSectionId(),
					'QUERY' => $_REQUEST['QUERY'] ?? '',
					'USE_SUBSTRING_QUERY' => isset($_REQUEST['USE_SUBSTRING_QUERY']) && $_REQUEST['USE_SUBSTRING_QUERY'] == 'Y' ? 'Y' : 'N'
				),
				false, $this->getUserId());
		}
	}

	private function getPropsList($iblockId, $skuPropertyId = 0): array
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

	private function filterProps(&$props): array
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
	protected function getItemProperies(int $id): array
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

	/**
	 * Returns a filter by element properties and product fields. Internal.
	 *
	 * @param array $filter
	 * @return array
	 */
	private static function getElementInherentFilter(array $filter): array
	{
		$result = array();
		if (!empty($filter))
		{
			foreach($filter as $index => $value)
			{
				$op = CIBlock::MkOperationFilter($index);
				$newIndex = mb_strtoupper($op["FIELD"]);
				if (
					strncmp($newIndex, "PROPERTY_", 9) == 0
					|| \CProductQueryBuilder::isValidField($newIndex)
				)
				{
					$result[$index] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * @param array $filter
	 * @return bool
	 */
	private static function checkLoadSections(array $filter): bool
	{
		$result = true;
		if (!empty($filter))
		{
			$catalogIncluded = Loader::includeModule('catalog');
			foreach($filter as $index => $value)
			{
				$op = CIBlock::MkOperationFilter($index);
				$newIndex = mb_strtoupper($op["FIELD"]);
				if (
					strncmp($newIndex, "PROPERTY_", 9) == 0
					|| ($catalogIncluded && \CProductQueryBuilder::isRealFilterField($newIndex))
				)
				{
					$result = false;
					break;
				}
			}
		}
		return $result;
	}
}
