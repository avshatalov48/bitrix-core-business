<?php

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Iblock;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;
use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Catalog;
use Bitrix\Catalog\Access;

/**
 * @deprecated Use CCatalogAdminTools
 * @see CCatalogAdminTools
 */
class CCatalogAdminToolsAll
{
}

class CCatalogAdminTools extends CCatalogAdminToolsAll
{
	public const TAB_PRODUCT = 'F';
	public const TAB_CATALOG = 'P';
	public const TAB_SKU = 'O';
	public const TAB_SET = 'S';
	public const TAB_GROUP = 'G';
	public const TAB_SERVICE = 'B';

	protected const TAB_KEY = 'PRODUCT_TYPE';

	protected const DELETE_SET = 'setdel';
	protected const DELETE_GROUP = 'groupdel';

	protected static string $strMainPrefix = '';
	protected static array $arErrors = [];
	protected static array $arCheckResult = [];

	public static function getTabList(bool $boolFull = false): array
	{
		if ($boolFull)
		{
			return [
				self::TAB_PRODUCT => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_PRODUCT'),
				self::TAB_CATALOG => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_CATALOG'),
				self::TAB_SKU => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SKU'),
				self::TAB_SET => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SET'),
				self::TAB_GROUP => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_GROUP'),
				self::TAB_SERVICE => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SERVICE'),
			];
		}

		return [
			self::TAB_PRODUCT,
			self::TAB_CATALOG,
			self::TAB_SKU,
			self::TAB_SET,
			self::TAB_GROUP,
			self::TAB_SERVICE,
		];
	}

	public static function getTabDescriptions(): array
	{
		return [
			self::TAB_PRODUCT => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_PRODUCT'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_TITLE_PRODUCT'),
			],
			self::TAB_CATALOG => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_CATALOG'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_CATALOG'),
			],
			self::TAB_SKU => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SKU'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SKU'),
			],
			self::TAB_SET => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SET'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SET'),
			],
			self::TAB_GROUP => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_GROUP'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_GROUP'),
			],
			self::TAB_SERVICE => [
				'NAME' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SERVICE'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_TAB_SERVICE'),
			],
		];
	}

	public static function getCurrentTabFromRequest(): ?string
	{
		$tabList = array_fill_keys(self::getTabList(), true);
		if (!Catalog\Config\Feature::isProductSetsEnabled())
		{
			unset($tabList[self::TAB_SET]);
			unset($tabList[self::TAB_GROUP]);
		}

		$request = Context::getCurrent()->getRequest();
		$result = $request->get(self::$strMainPrefix . self::TAB_KEY);
		if (!is_string($result))
		{
			return null;
		}
		if (!isset($tabList[$result]))
		{
			return null;
		}

		return $result;
	}

	/**
	 * Menu for iblock element list when user not has access.
	 *
	 * @return array
	 */
	public static function getIblockElementMenuLocked(): array
	{
		return [
			[
				'ID' => 'create_new_product_button_access_denied', // used in BX.Catalog.IblockProductList
				'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_EXT'),
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_EXT'),
				'ONCLICK' => 'javascript:;',
			],
		];
	}

	public static function getIBlockElementMenu(
		$intIBlockID,
		&$arCatalog,
		$arParams,
		BaseBuilder $urlBuilder = null,
		$gridId = ''
	)
	{
		$arResult = false;
		$intIBlockID = (int)$intIBlockID;
		if ($intIBlockID <= 0)
			return false;

		if (empty($arCatalog))
			$arCatalog = CCatalogSku::GetInfoByIBlock($intIBlockID);
		if (empty($arCatalog))
			return false;

		if (empty($arParams) || !is_array($arParams))
			return false;

		if ($urlBuilder === null)
		{
			$urlBuilder = BuilderManager::getInstance()->getBuilder(BaseBuilder::TYPE_AUTODETECT);
		}
		if ($urlBuilder === null)
		{
			return false;
		}

		$urlBuilder->setIblockId($intIBlockID);
		$urlBuilder->setUrlParams([]);

		$productCardEnabled = false;
		$builderId = $urlBuilder->getId();
		$publicShop = !(
			$builderId === Iblock\Url\AdminPage\IblockBuilder::TYPE_ID
			|| $builderId === Catalog\Url\AdminPage\CatalogBuilder::TYPE_ID
		);
		if ($publicShop)
		{
			// TODO: need fix this hack
			if ($builderId === 'CRM')
			{
				if (Loader::includeModule('crm'))
				{
					$productCardEnabled = \Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isFullCatalogEnabled();
				}
			}
			else
			{
				$productCardEnabled = Catalog\Config\State::isProductCardSliderEnabled();
			}
		}

		$arItems = array();

		$sectionId = $arParams['find_section_section'] ?? null;
		if ($sectionId !== null)
		{
			$sectionId = (int)$sectionId;
			if ($sectionId <= 0)
			{
				$sectionId = null;
			}
		}
		$productLimits = Catalog\Config\State::getExceedingProductLimit($intIBlockID, $sectionId);
		if (!empty($productLimits))
		{
			if (!empty($productLimits['HELP_MESSAGE']))
			{
				$arItems[] = [
					'ICON' => 'btn_lock',
					'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_EXT_2'),
					$productLimits['HELP_MESSAGE']['TYPE'] => $productLimits['HELP_MESSAGE']['LINK'],
				];
			}
		}
		else
		{
			// TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
			$publicFlag = $productCardEnabled
				|| $builderId === Catalog\Url\InventoryBuilder::TYPE_ID // hack for inventory documents
			;

			$serviceItem = null;
			if (
				$arCatalog['SUBSCRIPTION'] === 'N'
				&& (
					$arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_CATALOG
					|| $arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_FULL
					)
			)
			{
				if (Catalog\Config\Feature::isCatalogServicesEnabled())
				{
					$serviceItem = [
						'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SERVICE'),
						'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
					];
					if ($productCardEnabled)
					{
						$detailUrlParams = array_merge(
							$arParams,
							[
								'productTypeId' => Catalog\ProductTable::TYPE_SERVICE,
							]
						);
					}
					else
					{
						$detailUrlParams = self::getParamsWithTab($arParams, self::TAB_SERVICE);
					}
					$serviceItem['LINK'] = $urlBuilder->getElementDetailUrl(
						0,
						$detailUrlParams
					);
					unset($detailUrlParams);
				}
				else
				{
					$helpLink = Catalog\Config\Feature::getCatalogServicesHelpLink();
					if (!empty($helpLink))
					{
						$serviceItem = [
							'ICON' => 'btn_lock',
							'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SERVICE'),
							$helpLink['TYPE'] => $helpLink['LINK'],
						];
					}
					unset($helpLink);
				}
			}

			if ($arCatalog['CATALOG'] == 'Y')
			{
				if ($productCardEnabled)
				{
					$additionalParams = [];
					if ($arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_CATALOG)
					{
						$additionalParams = [
							'productTypeId' => Catalog\ProductTable::TYPE_PRODUCT,
						];
					}
					elseif ($arCatalog['CATALOG_TYPE'] === CCatalogSku::TYPE_FULL)
					{
						$additionalParams = [
							'productTypeId' => Catalog\ProductTable::TYPE_SKU,
						];
					}
					$detailUrlParams = array_merge(
						$arParams,
						$additionalParams
					);
				}
				else
				{
					$detailUrlParams = self::getParamsWithTab($arParams, self::TAB_CATALOG);
				}
				if (!isset($arParams['from']) || $arParams['from'] !== 'iblock_section_admin')
				{
					$arItems[] = array(
						'ICON' => 'btn_new',
						'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_EXT_2'),
						'ID' => 'create_new_product_button_' . $gridId,
						'LINK' => $urlBuilder->getElementDetailUrl(
							0,
							$detailUrlParams
						),
						'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
					);

					$arItems[] = array(
						'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PRODUCT'),
						'LINK' => $urlBuilder->getElementDetailUrl(
							0,
							self::getParamsWithTab($arParams, self::TAB_CATALOG)
						),
						'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
					);
				}
				else
				{
					$arItems[] = array(
						'ICON' => 'btn_new',
						'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PRODUCT'),
						'ID' => 'create_new_product_button_' . $gridId,
						'LINK' => $urlBuilder->getElementDetailUrl(
							0,
							$detailUrlParams
						),
						'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
					);
				}

				if (!$productCardEnabled)
				{
					if (CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
					{
						$arItems[] = array(
							'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SKU_2'),
							'LINK' => $urlBuilder->getElementDetailUrl(
								0,
								self::getParamsWithTab($arParams, self::TAB_SKU)
							),
							'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
						);
					}
					if ($serviceItem !== null)
					{
						$arItems[] = $serviceItem;
					}
					if (Catalog\Config\Feature::isProductSetsEnabled())
					{
						if (CCatalogSku::TYPE_OFFERS != $arCatalog['CATALOG_TYPE'])
						{
							$arItems[] = array(
								'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SET'),
								'LINK' => $urlBuilder->getElementDetailUrl(
									0,
									self::getParamsWithTab($arParams, self::TAB_SET)
								),
								'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
							);
						}
						$arItems[] = array(
							'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_GROUP'),
							'LINK' => $urlBuilder->getElementDetailUrl(
								0,
								self::getParamsWithTab($arParams, self::TAB_GROUP)
							),
							'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
						);
					}
					else
					{
						$helpLink = Catalog\Config\Feature::getProductSetsHelpLink();
						if (!empty($helpLink))
						{
							if (CCatalogSku::TYPE_OFFERS != $arCatalog['CATALOG_TYPE'])
							{
								$arItems[] = [
									'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SET'),
									$helpLink['TYPE'] => $helpLink['LINK'],
									'ICON' => 'btn_lock',
								];
							}
							$arItems[] = [
								'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_GROUP'),
								$helpLink['TYPE'] => $helpLink['LINK'],
								'ICON' => 'btn_lock',
							];
						}
					}
				}
				else
				{
					if ($serviceItem !== null)
					{
						$arItems[] = $serviceItem;
					}
				}
			}
			else
			{
				$arItems[] = array(
					'ICON' => 'btn_new',
					'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SKU'),
					'ID' => 'create_new_product_button_' . $gridId,
					'LINK' => $urlBuilder->getElementDetailUrl(
						0,
						self::getParamsWithTab($arParams, self::TAB_SKU)
					),
					'PUBLIC' => $publicFlag, // TODO: remove this hack after refactoring \CAdminUiList::AddAdminContextMenu
				);
			}

			if (
				$publicShop
				&& $arCatalog['CATALOG'] === 'Y'
				&& (
					CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE']
					|| CCatalogSku::TYPE_CATALOG == $arCatalog['CATALOG_TYPE']
				)
				&& \Bitrix\Main\Loader::includeModule('crm')
			)
			{
				if (\Bitrix\Crm\Order\Import\Instagram::isAvailable()
					&& Access\AccessController::getCurrent()->check(Access\ActionDictionary::ACTION_CATALOG_IMPORT_EXECUTION)
				)
				{
					$arItems[] = [
						'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_INSTAGRAM_IMPORT_2'),
						'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_INSTAGRAM_IMPORT_TITLE'),
						'LINK' => \Bitrix\Main\Config\Option::get('crm', 'path_to_order_import_instagram'),
						'PUBLIC' => true,
						'SHOW_TITLE' => true,
					];
				}
			}
		}
		unset($productLimits);

		if (!empty($arItems))
		{
			$arResult = $arItems;
		}

		return $arResult;
	}

	private static function getParamsWithTab(array $params, string $tab): array
	{
		$params[self::$strMainPrefix . self::TAB_KEY] = $tab;

		return $params;
	}

	private static function getDeleteAction(array $fields, string $action): array
	{
		$fields[$action] = 'Y';

		return $fields;
	}

	public static function getIBlockElementContentMenu(
		$intIBlockID,
		$intID,
		&$arCatalog,
		$arParams,
		BaseBuilder $urlBuilder = null
	)
	{
		$arResult = false;

		$intIBlockID = (int)$intIBlockID;
		$intID = (int)$intID;
		if ($intIBlockID <= 0 || $intID <= 0)
			return false;
		if (empty($arCatalog))
			$arCatalog = CCatalogSku::GetInfoByIBlock($intIBlockID);
		if (empty($arCatalog))
			return false;
		if ($arCatalog['CATALOG'] != 'Y')
			return false;

		if (empty($arParams) || !is_array($arParams))
			$arParams = array();

		if ($urlBuilder === null)
		{
			$urlBuilder = BuilderManager::getInstance()->getBuilder(BaseBuilder::TYPE_AUTODETECT);
		}
		if ($urlBuilder === null)
		{
			return false;
		}
		$urlBuilder->setIblockId($intIBlockID);
		$urlBuilder->setUrlParams([]);

		$allowedProductTypes = static::getIblockProductTypeList($intIBlockID, true);

		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();

		$intProductID = CIBlockElement::GetRealElement($intID);

		$currentTab = self::getCurrentTabFromRequest();

		$productType = self::getProductTypeForNewProduct($arCatalog);
		$boolExistSet = false;
		$boolExistGroup = false;
		$existInSet = false;
		$product = Catalog\ProductTable::getRow([
			'select' => [
				'ID',
				'TYPE',
				'BUNDLE',
			],
			'filter' => [
				'=ID' => $intProductID,
			],
		]);
		if ($product !== null)
		{
			$productType = (int)$product['TYPE'];
			$boolExistSet = $productType === Catalog\ProductTable::TYPE_SET;
			$boolExistGroup = $product['BUNDLE'] === Catalog\ProductTable::STATUS_YES;
		}
		if (!$boolExistSet)
		{
			$existInSet = CCatalogProductSet::isProductInSet($intProductID, CCatalogProductSet::TYPE_SET);
		}

		$arItems = array();

		if (!$existInSet)
		{
			if (
				isset($allowedProductTypes[Catalog\ProductTable::TYPE_PRODUCT])
			)
			{
				$row = [
					'ICON' => '',
					'TEXT' => $allowedProductTypes[Catalog\ProductTable::TYPE_PRODUCT],
				];
				if (
					$productType === Catalog\ProductTable::TYPE_SET
					&& $currentTab === null
				)
				{
					$row['ACTION'] = "if(confirm('"
						. CUtil::JSEscape(Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SET_DELETE_CONFIRM'))
						. "'))"
						. "window.location='"
						. $urlBuilder->getElementDetailUrl(
							$intID,
							self::getParamsWithTab(
								self::getDeleteAction($arParams, self::DELETE_SET),
								self::TAB_CATALOG
							),
							'&' . bitrix_sessid_get()
						)
						. "';"
					;
				}
				elseif (
					$productType === Catalog\ProductTable::TYPE_SKU
					&& $currentTab === null
				)
				{
					$row['TITLE'] = Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_CATALOG_FROM_SKU');
					$row['SHOW_TITLE'] = true;
					$row['DISABLED'] = true;
				}
				elseif (
					(
						$productType === Catalog\ProductTable::TYPE_PRODUCT
						&& $currentTab === null
					)
					|| $currentTab === self::TAB_CATALOG
				)
				{
					$row['CHECKED'] = true;
				}
				else
				{
					$row['LINK'] = $urlBuilder->getElementDetailUrl(
						$intID,
						self::getParamsWithTab($arParams, self::TAB_CATALOG)
					);
				}
				$arItems[] = $row;
				unset($row);
			}

			if (
				isset($allowedProductTypes[Catalog\ProductTable::TYPE_SKU])
				|| isset($allowedProductTypes[Catalog\ProductTable::TYPE_EMPTY_SKU])
			)
			{
				if ($productType === Catalog\ProductTable::TYPE_EMPTY_SKU)
				{
					$row = [
						'ICON' => '',
						'TEXT' => $allowedProductTypes[Catalog\ProductTable::TYPE_EMPTY_SKU],
					];
					if (
						$currentTab === null
						|| $currentTab === self::TAB_SKU
					)
					{
						$row['CHECKED'] = true;
					}
					else
					{
						$row['LINK'] = $urlBuilder->getElementDetailUrl(
							$intID,
							self::getParamsWithTab($arParams, self::TAB_SKU)
						);
					}
				}
				else
				{
					$row = [
						'ICON' => '',
						'TEXT' => $allowedProductTypes[Catalog\ProductTable::TYPE_SKU],
					];
					if (
						(
							$productType === Catalog\ProductTable::TYPE_PRODUCT
							&& $currentTab === null
						)
						|| $currentTab === self::TAB_CATALOG
						|| $currentTab === self::TAB_SET
					)
					{
						$row['LINK'] = $urlBuilder->getElementDetailUrl(
							$intID,
							self::getParamsWithTab($arParams, self::TAB_SKU)
						);
					}
					elseif (
						$productType === Catalog\ProductTable::TYPE_SET
						&& $currentTab === null
					)
					{
						$row['ACTION'] = "if(confirm('"
							. CUtil::JSEscape(Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SET_DELETE_CONFIRM'))
							. "'))"
							. "window.location='"
							. $urlBuilder->getElementDetailUrl(
								$intID,
								self::getParamsWithTab(
									self::getDeleteAction($arParams, self::DELETE_SET),
									self::TAB_SKU
								),
								'&' . bitrix_sessid_get()
							)
							. "';"
						;
					}
					elseif (
						$productType === Catalog\ProductTable::TYPE_SKU
						|| $currentTab === self::TAB_SKU
					)
					{
						$row['CHECKED'] = true;
					}
				}
				$arItems[] = $row;
				unset($row);
			}

			if (isset($allowedProductTypes[Catalog\ProductTable::TYPE_SERVICE]))
			{
				$row = [
					'ICON' => '',
					'TEXT' => $allowedProductTypes[Catalog\ProductTable::TYPE_SERVICE],
				];
				if (
					(
						$productType === Catalog\ProductTable::TYPE_PRODUCT
						&& $currentTab === null
					)
					|| $currentTab === self::TAB_CATALOG
				)
				{
					$row['LINK'] = $urlBuilder->getElementDetailUrl(
						$intID,
						self::getParamsWithTab($arParams, self::TAB_SERVICE)
					);
				}
				if (
					$productType === Catalog\ProductTable::TYPE_SET
					&& $currentTab === null
				)
				{
					$row['ACTION'] = "if(confirm('"
						. CUtil::JSEscape(Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SET_DELETE_CONFIRM'))
						. "'))"
						. "window.location='"
						. $urlBuilder->getElementDetailUrl(
							$intID,
							self::getParamsWithTab(
								self::getDeleteAction($arParams, self::DELETE_SET),
								self::TAB_SERVICE
							),
							'&' . bitrix_sessid_get()
						)
						. "';"
					;
				}
				elseif (
					$productType === Catalog\ProductTable::TYPE_SKU
					&& $currentTab === null
				)
				{
					$row['TITLE'] = Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SERVICE_FROM_SKU');
					$row['SHOW_TITLE'] = true;
					$row['DISABLED'] = true;
				}
				elseif (
					(
						$productType === Catalog\ProductTable::TYPE_SERVICE
						&& $currentTab === null
					)
					|| $currentTab === self::TAB_SERVICE
				)
				{
					$row['CHECKED'] = true;
				}
				else
				{
					$row['LINK'] = $urlBuilder->getElementDetailUrl(
						$intID,
						self::getParamsWithTab($arParams, self::TAB_SERVICE)
					);
				}
				$arItems[] = $row;
				unset($row);
			}

			if (
				isset($allowedProductTypes[Catalog\ProductTable::TYPE_SET])
			)
			{
				$row = [
					'ICON' => '',
					'TEXT' => $allowedProductTypes[Catalog\ProductTable::TYPE_SET],
				];
				if (
					(
						$productType === Catalog\ProductTable::TYPE_PRODUCT
						&& $currentTab === null
					)
					|| $currentTab === self::TAB_CATALOG
				)
				{
					$row['LINK'] = $urlBuilder->getElementDetailUrl(
						$intID,
						self::getParamsWithTab($arParams, self::TAB_SET)
					);
				}
				elseif (
					$productType === Catalog\ProductTable::TYPE_SKU
					&& $currentTab === null
				)
				{
					$row['TITLE'] = Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SET_FROM_SKU');
					$row['SHOW_TITLE'] = true;
					$row['DISABLED'] = true;
				}
				elseif (
					$productType === Catalog\ProductTable::TYPE_SET
					|| $currentTab === self::TAB_SET
				)
				{
					$row['CHECKED'] = true;
				}
				else
				{
					$row['LINK'] = $urlBuilder->getElementDetailUrl(
						$intID,
						self::getParamsWithTab($arParams, self::TAB_SET)
					);
				}
				$arItems[] = $row;
				unset($row);
			}
		}

		if (!$boolFeatureSet && CCatalogSku::TYPE_FULL !== $arCatalog['CATALOG_TYPE'])
		{
			$arItems = [];
		}
		//group
		if ($boolFeatureSet && $currentTab !== self::TAB_GROUP)
		{
			if (
				$productType !== Catalog\ProductTable::TYPE_EMPTY_SKU
				&& $productType !== Catalog\ProductTable::TYPE_FREE_OFFER
			)
			{
				if (!empty($arItems))
				{
					$arItems[] = ['SEPARATOR' => 'Y'];
				}

				if ($currentTab === null)
				{
					if (!$boolExistGroup)
					{
						$arItems[] = [
							'ICON' => '',
							'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_GROUP_ADD'),
							'LINK' => $urlBuilder->getElementDetailUrl(
								$intID,
								self::getParamsWithTab($arParams, self::TAB_GROUP)
							),
						];
					}
					else
					{
						$arItems[] = [
							'ICON' => 'delete',
							'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_GROUP_DELETE'),
							'ACTION' => "if(confirm('"
								. CUtil::JSEscape(Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_GROUP_DELETE_CONFIRM'))
								. "'))window.location='"
								. $urlBuilder->getElementDetailUrl(
									$intID,
									self::getDeleteAction($arParams, self::DELETE_GROUP),
									'&' . bitrix_sessid_get()
								)
								. "';",
						];
					}
				}
				else
				{
					if ($boolExistGroup)
					{
						$arItems[] = [
							'ICON' => 'delete',
							'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_GROUP_DELETE'),
							'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_DELETE_GROUP_LOCKED'),
							'SHOW_TITLE' => true,
							'DISABLED' => true,
						];
					}
				}
			}
		}

		if (!empty($arItems))
		{
			$arResult = [
				'TEXT' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SELECTOR'),
				'TITLE' => Loc::getMessage('BT_CAT_SET_PRODUCT_TYPE_SELECTOR_TITLE'),
				'MENU' => $arItems
			];
		}

		return $arResult;
	}

	public static function getShowTabs($intIBlockID, $intID, &$arCatalog)
	{
		$intIBlockID = (int)$intIBlockID;
		if ($intIBlockID <= 0)
			return false;

		if (empty($arCatalog))
			$arCatalog = CCatalogSku::GetInfoByIBlock($intIBlockID);
		if (empty($arCatalog))
			return false;

		$arResult = array_fill_keys(self::getTabList(), false);
		$currentTab = self::getCurrentTabFromRequest();

		if ($intID > 0)
		{
			$intProductID = CIBlockElement::GetRealElement($intID);
			$productType = 0;
			$haveBundle = false;
			$product = Catalog\ProductTable::getRow([
				'select' => [
					'ID',
					'TYPE',
					'BUNDLE',
				],
				'filter' => [
					'=ID' => $intProductID,
				],
			]);
			if ($product !== null)
			{
				$productType = (int)$product['TYPE'];
				$haveBundle = $product['BUNDLE'] === 'Y';
			}
			$arResult[self::TAB_CATALOG] = (
				CCatalogSku::TYPE_CATALOG == $arCatalog['CATALOG_TYPE']
				|| CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE']
				|| CCatalogSku::TYPE_OFFERS == $arCatalog['CATALOG_TYPE']
			);
			if ($productType === Catalog\ProductTable::TYPE_EMPTY_SKU)
			{
				$arResult[self::TAB_CATALOG] = false;
			}
			$arResult[self::TAB_SKU] = (
				CCatalogSku::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE']
				|| CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE']
			);
			if (CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
			{
				if (
					$productType === Catalog\ProductTable::TYPE_SKU
					|| $currentTab === self::TAB_SKU
				)
				{
					if (Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') !== 'Y')
					{
						$arResult[self::TAB_CATALOG] = false;
					}
				}
				else
				{
					if ($productType !== Catalog\ProductTable::TYPE_EMPTY_SKU)
					{
						$arResult[self::TAB_SKU] = false;
					}
				}
			}
			if (CCatalogSku::TYPE_PRODUCT != $arCatalog['CATALOG_TYPE'])
			{
				if (Catalog\Config\Feature::isProductSetsEnabled())
				{
					if (CCatalogSku::TYPE_OFFERS != $arCatalog['CATALOG_TYPE'])
					{
						$arResult[self::TAB_SET] = (
							$productType === Catalog\ProductTable::TYPE_SET
							|| $currentTab === self::TAB_SET
						);
					}
					$arResult[self::TAB_GROUP] = (
						$haveBundle
						|| $currentTab === self::TAB_GROUP
					);
					if ($arResult[self::TAB_SET])
					{
						$arResult[self::TAB_CATALOG] = true;
						$arResult[self::TAB_SKU] = false;
					}
				}
			}
		}
		else
		{
			if ($currentTab !== null)
			{
				if (CCatalogSku::TYPE_OFFERS == $arCatalog['CATALOG_TYPE'])
				{
					if (
						$currentTab === self::TAB_SET
						|| $currentTab === self::TAB_SKU
						|| $currentTab === self::TAB_SERVICE
					)
					{
						$currentTab = null;
					}
				}
			}
			if ($currentTab !== null)
			{
				$arResult[$currentTab] = true;
				if ($currentTab === self::TAB_GROUP || $currentTab === self::TAB_SET)
				{
					$arResult[self::TAB_CATALOG] = true;
				}
				if (
					$currentTab === self::TAB_SKU
					&& $arCatalog['CATALOG'] === 'Y'
					&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y'
				)
				{
					$arResult[self::TAB_CATALOG] = true;
				}
				if ($currentTab === self::TAB_SERVICE)
				{
					$arResult[$currentTab] = false;
					$arResult[self::TAB_CATALOG] = true;
				}
			}
			else
			{
				$arResult[self::TAB_CATALOG] = (
					CCatalogSku::TYPE_CATALOG == $arCatalog['CATALOG_TYPE']
					|| CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE']
					|| CCatalogSku::TYPE_OFFERS == $arCatalog['CATALOG_TYPE']
				);
				$arResult[self::TAB_SKU] = (
					CCatalogSku::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE']
				);
			}
		}
		if (!$arResult[self::TAB_CATALOG] && $arResult[self::TAB_SKU])
		{
			$fieldsList = Catalog\Product\SystemField::getFieldNamesByRestrictions([
				'TYPE' => Catalog\ProductTable::TYPE_SKU,
				'IBLOCK_ID' => $intIBlockID,
			]);
			if (!empty($fieldsList))
			{
				$arResult[self::TAB_PRODUCT] = true;
			}
		}

		return $arResult;
	}

	public static function getProductTypeForNewProduct(array $catalog): ?int
	{
		$currentTab = self::getCurrentTabFromRequest();
		switch ($catalog['CATALOG_TYPE'])
		{
			case CCatalogSku::TYPE_CATALOG:
				switch ($currentTab)
				{
					case self::TAB_SET:
						$result = Catalog\ProductTable::TYPE_SET;
						break;
					case self::TAB_SERVICE:
						$result = Catalog\ProductTable::TYPE_SERVICE;
						break;
					default:
						$result = Catalog\ProductTable::TYPE_PRODUCT;
						break;
				}
				break;
			case CCatalogSku::TYPE_FULL:
				switch ($currentTab)
				{
					case self::TAB_SET:
						$result = Catalog\ProductTable::TYPE_SET;
						break;
					case self::TAB_SERVICE:
						$result = Catalog\ProductTable::TYPE_SERVICE;
						break;
					case self::TAB_SKU:
						$result = Catalog\ProductTable::TYPE_SKU;
						break;
					default:
						$result = Catalog\ProductTable::TYPE_PRODUCT;
						break;
				}
				break;
			case CCatalogSku::TYPE_PRODUCT:
				$result = Catalog\ProductTable::TYPE_SKU;
				break;
			case CCatalogSku::TYPE_OFFERS:
				$result = Catalog\ProductTable::TYPE_OFFER;
				break;
			default:
				$result = null;
				break;
		}

		return $result;
	}

	public static function getProductTypeByTab(?string $tab): ?int
	{
		switch ($tab)
		{
			case self::TAB_CATALOG:
				$result = Catalog\ProductTable::TYPE_PRODUCT;
				break;
			case self::TAB_SKU:
				$result = Catalog\ProductTable::TYPE_SKU;
				break;
			case self::TAB_SET:
				$result = Catalog\ProductTable::TYPE_SET;
				break;
			case self::TAB_SERVICE:
				$result = Catalog\ProductTable::TYPE_SERVICE;
				break;
			default:
				$result = null;
				break;
		}

		return $result;
	}

	public static function getFormProductTypeName(int $id): ?string
	{
		$result = null;

		$id = CIBlockElement::GetRealElement($id);
		if ($id > 0)
		{
			$row = Catalog\ProductTable::getRow([
				'select' => [
					'ID',
					'TYPE',
				],
				'filter' => [
					'=ID' => $id,
				]
			]);
			if ($row !== null)
			{
				$result = (int)$row['TYPE'];
			}
		}
		$tabType = self::getProductTypeByTab(self::getCurrentTabFromRequest());
		if ($tabType !== null)
		{
			$result = $tabType;
		}
		if ($result === Catalog\ProductTable::TYPE_PRODUCT)
		{
			$result = null;
		}
		if ($result !== null)
		{
			$typeList = Catalog\ProductTable::getProductTypes(true);
			$result = $typeList[$result] ?? null;
		}

		return $result;
	}

	public static function getFormParams($params = array())
	{
		if (!is_array($params))
		{
			$params = [];
		}
		static::addTabParams($params);

		return $params;
	}

	public static function showFormParams()
	{
		$params = self::getFormParams();
		if (!empty($params))
		{
			foreach ($params as $key => $value)
			{
				?><input type="hidden" name="<? echo htmlspecialcharsbx($key); ?>" value="<? echo htmlspecialcharsbx($value); ?>"><?
			}
			unset($key, $value);
		}
		unset($params);
	}

	public static function setCatalogPanelButtons(&$buttons, $iblock, $catalogButtons, $params, $windowParams)
	{
		global $APPLICATION;

		$iblock = (int)$iblock;
		if ($iblock <= 0)
			return;
		if (empty($params) || !is_array($params))
			return;
		if (empty($windowParams) || !is_array($windowParams))
			$windowParams = array('width' => 700, 'height' => 400, 'resize' => false);

		if (isset($catalogButtons['add_product']))
		{
			$params[self::$strMainPrefix.self::TAB_KEY] = self::TAB_CATALOG;
			$url = '/bitrix/admin/'.CIBlock::GetAdminElementEditLink($iblock, null, $params);
			$action = $APPLICATION->GetPopupLink(
				array(
					"URL" => $url,
					"PARAMS" => $windowParams,
				)
			);
			$productButton = array(
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_EXT'),
				'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_PROD_TITLE'),
				'ACTION' => 'javascript:'.$action,
				'ACTION_URL' => $url,
				'ONCLICK' => $action,
				'ICON' => 'bx-context-toolbar-create-icon',
				'ID' => 'bx-context-toolbar-add-element',
			);

			$buttons['edit']['add_element'] = $productButton;
			$buttons['configure']['add_element'] = $productButton;
			$buttons['intranet'][] = array(
				'TEXT' => $productButton['TEXT'],
				'TITLE' => $productButton['TITLE'],
				'ICON'	=> 'add',
				'ONCLICK' => $productButton['ACTION'],
				'SORT' => 1000,
			);

			$url = str_replace('&bxpublic=Y&from_module=iblock', '', $url);
			$productButton['ACTION'] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
			unset($productButton['ONCLICK']);
			$buttons['submenu']['add_element'] = $productButton;
			unset($productButton);
		}

		if (isset($catalogButtons['add_sku']))
		{
			$params[self::$strMainPrefix.self::TAB_KEY] = self::TAB_SKU;
			$url = '/bitrix/admin/'.CIBlock::GetAdminElementEditLink($iblock, null, $params);
			$action = $APPLICATION->GetPopupLink(
				array(
					"URL" => $url,
					"PARAMS" => $windowParams,
				)
			);
			$skuButton = array(
				'TITLE' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SKU'),
				'TEXT' => Loc::getMessage('BT_CAT_ADM_TOOLS_ADD_SKU_TITLE'),
				'ACTION' => 'javascript:'.$action,
				'ACTION_URL' => $url,
				'ONCLICK' => $action,
				'ICON' => 'bx-context-toolbar-create-icon',
				'ID' => 'bx-context-toolbar-add-sku',
			);

			$buttons['edit']['add_sku'] = $skuButton;
			$buttons['configure']['add_sku'] = $skuButton;
			$buttons['intranet'][] = array(
				'TEXT' => $skuButton['TEXT'],
				'TITLE' => $skuButton['TITLE'],
				'ICON'	=> 'add',
				'ONCLICK' => $skuButton['ACTION'],
				'SORT' => 1010,
			);

			$url = str_replace('&bxpublic=Y&from_module=iblock', '', $url);
			$skuButton['ACTION'] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
			unset($skuButton['ONCLICK']);
			$buttons['submenu']['add_sku'] = $skuButton;
			unset($skuButton);
		}
	}

	public static function setMainPrefix($strPrefix): void
	{
		self::$strMainPrefix = (string)$strPrefix;
	}

	public static function getMainPrefix(): string
	{
		return self::$strMainPrefix;
	}

	public static function setProductFormParams()
	{
		self::setMainPrefix('');
	}

	public static function setSkuFormParams()
	{
		self::setMainPrefix('SUB');
	}

	public static function getErrors(): array
	{
		return self::$arErrors;
	}

	public static function changeTabs($intIBlockID, $intID, &$arCatalog)
	{
		$intIBlockID = (int)$intIBlockID;
		$intID = (int)$intID;
		if ($intIBlockID <= 0 || $intID <= 0)
			return false;

		if (empty($arCatalog))
			$arCatalog = CCatalogSku::GetInfoByIBlock($intIBlockID);
		if (empty($arCatalog))
			return false;
		if ($arCatalog['CATALOG'] != 'Y')
			return false;

		$intProductID = CIBlockElement::GetRealElement($intID);

		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();

		$result = false;
		if ($boolFeatureSet)
		{
			$request = Context::getCurrent()->getRequest();
			if ($request->get(self::DELETE_GROUP) === 'Y')
			{
				$result = CCatalogProductSet::deleteAllSetsByProduct($intProductID, CCatalogProductSet::TYPE_GROUP);
			}
			elseif ($request->get(self::DELETE_SET) === 'Y')
			{
				$result = CCatalogProductSet::deleteAllSetsByProduct($intProductID, CCatalogProductSet::TYPE_SET);
			}
			unset($request);
		}

		return $result;
	}

	public static function addTabParams(&$arParams)
	{
		if (!is_array($arParams))
		{
			return;
		}
		$currentTab = self::getCurrentTabFromRequest();
		if ($currentTab !== null)
		{
			$arParams = self::getParamsWithTab($arParams, $currentTab);
		}
	}

	/**
	 * @deprecated
	 *
	 * @return void
	 */
	public static function clearTabParams()
	{
		if (array_key_exists(self::$strMainPrefix.self::TAB_KEY, $_REQUEST))
			unset($_REQUEST[self::$strMainPrefix.self::TAB_KEY]);
		if (array_key_exists(self::$strMainPrefix.self::TAB_KEY, $_POST))
			unset($_POST[self::$strMainPrefix.self::TAB_KEY]);
	}

	/**
	 * @param int $iblockId
	 * @param bool $withDescr
	 * @return array|mixed
	 */
	public static function getIblockProductTypeList($iblockId, $withDescr = false)
	{
		//TODO: change this method with \Bitrix\Catalog\Model\Product::getProductTypes
		$result = [];
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
		{
			return $result;
		}
		$withDescr = ($withDescr === true);

		$iblockData = CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($iblockData))
		{
			return $result;
		}

		$data = [
			CCatalogSku::TYPE_CATALOG => [
				Catalog\ProductTable::TYPE_PRODUCT,
			],
			CCatalogSku::TYPE_PRODUCT => [
				Catalog\ProductTable::TYPE_SKU,
				Catalog\ProductTable::TYPE_EMPTY_SKU,
			],
			CCatalogSku::TYPE_FULL => [
				Catalog\ProductTable::TYPE_PRODUCT,
				Catalog\ProductTable::TYPE_SKU,
				Catalog\ProductTable::TYPE_EMPTY_SKU,
			],
			CCatalogSku::TYPE_OFFERS => [
				Catalog\ProductTable::TYPE_OFFER,
				Catalog\ProductTable::TYPE_FREE_OFFER,
			]
		];
		if (Catalog\Config\Feature::isProductSetsEnabled())
		{
			$data[CCatalogSku::TYPE_CATALOG][] = Catalog\ProductTable::TYPE_SET;
			$data[CCatalogSku::TYPE_FULL][] = Catalog\ProductTable::TYPE_SET;
		}
		if (Catalog\Config\Feature::isCatalogServicesEnabled())
		{
			$data[CCatalogSku::TYPE_CATALOG][] = Catalog\ProductTable::TYPE_SERVICE;
			$data[CCatalogSku::TYPE_FULL][] = Catalog\ProductTable::TYPE_SERVICE;
		}
		if (!isset($data[$iblockData['CATALOG_TYPE']]))
		{
			return $result;
		}

		$result = $data[$iblockData['CATALOG_TYPE']];
		if ($withDescr)
		{
			$productList = Catalog\ProductTable::getProductTypes(true);
			$extResult = [];
			foreach ($result as $type)
			{
				$extResult[$type] = $productList[$type];
			}
			unset($type);
			$result = $extResult;
			unset($extResult, $productList);
		}

		return $result;
	}

	/**
	 * @deprecated
	 *
	 * @param bool $withDescr
	 * @return array
	 */
	public static function getProductTypeList($withDescr = false)
	{
		$withDescr = ($withDescr === true);

		$result = array(
			Catalog\ProductTable::TYPE_PRODUCT,
		);
		$result[] = Catalog\ProductTable::TYPE_SKU;
		$result[] = Catalog\ProductTable::TYPE_EMPTY_SKU;
		if (Catalog\Config\Feature::isProductSetsEnabled())
			$result[] = Catalog\ProductTable::TYPE_SET;
		$result[] = Catalog\ProductTable::TYPE_OFFER;
		$result[] = Catalog\ProductTable::TYPE_FREE_OFFER;

		if ($withDescr)
		{
			$productList = Catalog\ProductTable::getProductTypes(true);
			$extResult = array();
			foreach ($result as $type)
				$extResult[$type] = $productList[$type];
			unset($type);
			$result = $extResult;
			unset($extResult, $productList);
		}

		return $result;
	}

	public static function getSystemProductFieldsHtml(array $product, array $config): string
	{
		$config['SYSTEM_UF_FIELDS'] = 'Y';
		$result = self::getProductUserFields($product, $config);

		return ($result === null ? '' : $result[0]);
	}

	public static function getAllProductFieldsHtml(array $product, array $config): array
	{
		$config['SYSTEM_UF_FIELDS'] = 'Y';
		$config['CUSTOM_UF_FIELDS'] = 'Y';
		$result = self::getProductUserFields($product, $config);

		return ($result === null
			? [
				0 => '',
				1 => '',
			]
			: $result
		);
	}

	public static function saveSystemProductFields(array $product): bool
	{
		if (!isset($product['ID']) || !isset($product['IBLOCK_ID']))
		{
			return true;
		}
		$product['IBLOCK_ID'] = (int)$product['IBLOCK_ID'];
		if ($product['IBLOCK_ID'] <= 0)
		{
			return true;
		}
		$product['PRODUCT_ID'] = (int)($product['PRODUCT_ID'] ?? CIBlockElement::GetRealElement($product['ID']));

		$iterator = Catalog\Model\Product::getList([
			'select' => [
				'ID',
				'TYPE',
			],
			'filter' => [
				'=ID' => $product['PRODUCT_ID'],
			],
		]);
		$row = $iterator->fetch();
		if (empty($row))
		{
			return true;
		}

		$systemFields = Catalog\Product\SystemField::getFieldNamesByRestrictions([
			'TYPE' => (int)$row['TYPE'],
			'IBLOCK_ID' => $product['IBLOCK_ID'],
		]);
		if (empty($systemFields))
		{
			return true;
		}

		$fields = [];

		$userFieldManager = Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();
		$userFieldManager->EditFormAddFields(Catalog\ProductTable::getUfId(), $fields);
		unset($userFieldManager);

		if (empty($fields))
		{
			return true;
		}

		$fields = array_intersect_key($fields, array_fill_keys($systemFields, true));
		if (empty($fields))
		{
			return true;
		}

		$result = Catalog\Model\Product::update(
			$product['PRODUCT_ID'],
			[
				'fields' => $fields,
				'external_fields' => [
					'IBLOCK_ID' => $product['IBLOCK_ID'],
				],
			]
		);

		if ($result->isSuccess())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function getClearedGridFields(array $options = []): array
	{
		$result = array_fill_keys(Catalog\ProductTable::getProductTypes(false), []);

		$useCatalogTab = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
		$useNewCard = (bool)($options['USE_NEW_CARD'] ?? false);

		$baseClearSkuFields = [
			'CATALOG_QUANTITY',
			'CATALOG_QUANTITY_RESERVED',
			'CATALOG_QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'CATALOG_PURCHASING_PRICE',
			'CATALOG_PURCHASING_CURRENCY',
			'CATALOG_MEASURE',
			'CATALOG_VAT_INCLUDED',
			'VAT_ID',
			'CATALOG_WEIGHT',
			'CATALOG_WIDTH',
			'CATALOG_LENGTH',
			'CATALOG_HEIGHT',
			'CATALOG_BAR_CODE',
		];
		if (!$useNewCard && !$useCatalogTab)
		{
			$result[Catalog\ProductTable::TYPE_SKU] = $baseClearSkuFields;
		}
		if (!$useCatalogTab)
		{
			$result[Catalog\ProductTable::TYPE_EMPTY_SKU] = $baseClearSkuFields;
		}
		$result[Catalog\ProductTable::TYPE_SET] = [
			'CATALOG_QUANTITY_RESERVED',
			'CATALOG_BAR_CODE',
		];

		$result[Catalog\ProductTable::TYPE_SERVICE] = [
			'CATALOG_QUANTITY',
			'CATALOG_QUANTITY_RESERVED',
			'CATALOG_QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'CATALOG_WEIGHT',
			'CATALOG_WIDTH',
			'CATALOG_LENGTH',
			'CATALOG_HEIGHT',
			'CATALOG_BAR_CODE',
		];

		return $result;
	}

	public static function getLockedGridFields(array $options = []): array
	{
		$result = array_fill_keys(Catalog\ProductTable::getProductTypes(false), []);
		$useInventoryManagment = Catalog\Config\State::isUsedInventoryManagement();
		$showCatalog = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
		$useNewCard = (bool)($options['USE_NEW_CARD'] ?? false);

		$result[Catalog\ProductTable::TYPE_PRODUCT] = [
			'CATALOG_QUANTITY' => $useInventoryManagment ? false: [],
			'CATALOG_QUANTITY_RESERVED' => $useInventoryManagment ? false : [],
			'CATALOG_AVAILABLE' => false,
			'CATALOG_QUANTITY_TRACE' => $useInventoryManagment ? false : [],
			'CAN_BUY_ZERO' => $useInventoryManagment ? false : [],
			'CATALOG_PURCHASING_PRICE' => $useInventoryManagment ? false : [],
			'CATALOG_MEASURE_RATIO' => [],
			'CATALOG_MEASURE' => [],
			'CATALOG_VAT_INCLUDED' => [],
			'VAT_ID' => [],
			'CATALOG_WEIGHT' => [],
			'CATALOG_WIDTH' => [],
			'CATALOG_LENGTH' => [],
			'CATALOG_HEIGHT' => [],
			'CATALOG_BAR_CODE' => false,
		];

		$result[Catalog\ProductTable::TYPE_SET] = [
			'CATALOG_QUANTITY' => false,
			'CATALOG_QUANTITY_RESERVED' => false,
			'CATALOG_AVAILABLE' => false,
			'CATALOG_QUANTITY_TRACE' => false,
			'CAN_BUY_ZERO' => false,
			'CATALOG_PURCHASING_PRICE' => [],
			'CATALOG_MEASURE_RATIO' => false,
			'CATALOG_MEASURE' => false,
			'CATALOG_VAT_INCLUDED' => [],
			'VAT_ID' => [],
			'CATALOG_WEIGHT' => false,
			'CATALOG_WIDTH' => [],
			'CATALOG_LENGTH' => [],
			'CATALOG_HEIGHT' => [],
			'CATALOG_BAR_CODE' => false,
		];

		$baseLockedSkuFields = [
			'CATALOG_QUANTITY' => $showCatalog && !$useInventoryManagment ? [] : false,
			'CATALOG_QUANTITY_RESERVED' => $showCatalog && !$useInventoryManagment ? [] : false,
			'CATALOG_AVAILABLE' => false,
			'CATALOG_QUANTITY_TRACE' => $showCatalog && !$useInventoryManagment ? [] : false,
			'CAN_BUY_ZERO' => $showCatalog && !$useInventoryManagment ? [] : false,
			'CATALOG_PURCHASING_PRICE' => $showCatalog ? [] : false,
			'CATALOG_MEASURE_RATIO' => $showCatalog && !$useInventoryManagment ? [] : false,
			'CATALOG_MEASURE' => $showCatalog ? [] : false,
			'CATALOG_VAT_INCLUDED' => $showCatalog ? [] : false,
			'VAT_ID' => $showCatalog ? [] : false,
			'CATALOG_WEIGHT' => $showCatalog ? [] : false,
			'CATALOG_WIDTH' => $showCatalog ? [] : false,
			'CATALOG_LENGTH' => $showCatalog ? [] : false,
			'CATALOG_HEIGHT' => $showCatalog ? [] : false,
			'CATALOG_BAR_CODE' => false,
		];
		if (!$showCatalog)
		{
			$result[Catalog\ProductTable::TYPE_EMPTY_SKU] = $baseLockedSkuFields;
		}

		$skuUnlock = $showCatalog || $useNewCard;

		$result[Catalog\ProductTable::TYPE_SKU] = [
			'CATALOG_QUANTITY' => $skuUnlock && !$useInventoryManagment ? [] : false,
			'CATALOG_QUANTITY_RESERVED' => $skuUnlock && !$useInventoryManagment ? [] : false,
			'CATALOG_AVAILABLE' => false,
			'CATALOG_QUANTITY_TRACE' => $skuUnlock && !$useInventoryManagment ? [] : false,
			'CAN_BUY_ZERO' => $skuUnlock && !$useInventoryManagment ? [] : false,
			'CATALOG_PURCHASING_PRICE' => $skuUnlock ? [] : false,
			'CATALOG_MEASURE_RATIO' => $skuUnlock && !$useInventoryManagment ? [] : false,
			'CATALOG_MEASURE' => $skuUnlock ? [] : false,
			'CATALOG_VAT_INCLUDED' => $skuUnlock ? [] : false,
			'VAT_ID' => $skuUnlock ? [] : false,
			'CATALOG_WEIGHT' => $skuUnlock ? [] : false,
			'CATALOG_WIDTH' => $skuUnlock ? [] : false,
			'CATALOG_LENGTH' => $skuUnlock ? [] : false,
			'CATALOG_HEIGHT' => $skuUnlock ? [] : false,
			'CATALOG_BAR_CODE' => false,
		];

		$result[Catalog\ProductTable::TYPE_EMPTY_SKU] = $result[Catalog\ProductTable::TYPE_SKU];

		$result[Catalog\ProductTable::TYPE_OFFER] = $result[Catalog\ProductTable::TYPE_PRODUCT];
		$result[Catalog\ProductTable::TYPE_FREE_OFFER] = $result[Catalog\ProductTable::TYPE_PRODUCT];

		$result[Catalog\ProductTable::TYPE_SERVICE] = [
			'CATALOG_QUANTITY' => false,
			'CATALOG_QUANTITY_RESERVED' => false,
			'CATALOG_AVAILABLE' => [],
			'CATALOG_QUANTITY_TRACE' => false,
			'CAN_BUY_ZERO' => false,
			'CATALOG_PURCHASING_PRICE' => [],
			'CATALOG_MEASURE_RATIO' => false,
			'CATALOG_MEASURE' => [],
			'CATALOG_VAT_INCLUDED' => [],
			'VAT_ID' => [],
			'CATALOG_WEIGHT' => false,
			'CATALOG_WIDTH' => false,
			'CATALOG_LENGTH' => false,
			'CATALOG_HEIGHT' => false,
			'CATALOG_BAR_CODE' => false,
		];

		return $result;
	}

	private static function getProductUserFields(array $product, array $config): ?array
	{
		if (!isset($product['ID']) || !isset($product['IBLOCK_ID']) || !isset($product['TYPE']))
		{
			return null;
		}
		$product['IBLOCK_ID'] = (int)$product['IBLOCK_ID'];
		if ($product['IBLOCK_ID'] <= 0)
		{
			return null;
		}
		$product['PRODUCT_ID'] = (int)($product['PRODUCT_ID'] ?? CIBlockElement::GetRealElement($product['ID']));
		$product['TYPE'] = (int)$product['TYPE'];

		$result = [
			0 => '',
			1 => '',
		];

		$userFieldManager = Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();
		$productUserFields = $userFieldManager->GetUserFields(
			Catalog\ProductTable::getUfId(),
			$product['PRODUCT_ID'],
			LANGUAGE_ID
		);
		if (empty($productUserFields))
		{
			return $result;
		}

		$config['ALLOW_EDIT'] = $config['ALLOW_EDIT'] ?? true;

		foreach (array_keys($productUserFields) as $fieldName)
		{
			$productUserFields[$fieldName]['VALUE_ID'] = $product['PRODUCT_ID'];
			$productUserFields[$fieldName]['EDIT_FORM_LABEL'] = $productUserFields[$fieldName]['EDIT_FORM_LABEL']
				??
				$productUserFields[$fieldName]['FIELD_NAME']
			;
			if (!$config['ALLOW_EDIT'])
			{
				$productUserFields[$fieldName]['EDIT_IN_LIST'] = 'N';
			}
		}
		unset($fieldName);

		$config['FROM_FORM'] = $config['FROM_FORM'] ?? false;
		$showSystemFields = ($config['SYSTEM_UF_FIELDS'] ?? 'N') === 'Y';
		$showCustomFields = ($config['CUSTOM_UF_FIELDS'] ?? 'N') === 'Y';

		if (!$showSystemFields && !$showCustomFields)
		{
			return $result;
		}

		$request = Main\Context::getCurrent()->getRequest();

		if ($showSystemFields)
		{
			$html = Catalog\Product\SystemField::renderAdminEditForm(
				$product,
				$config
			);
			if ($html !== null)
			{
				$result[0] = $html;
			}
		}

		$allSystemFields = Catalog\Product\SystemField::getFieldNamesByRestrictions([]);
		if (!empty($allSystemFields))
		{
			$productUserFields = array_diff_key(
				$productUserFields,
				array_fill_keys($allSystemFields, true)
			);
		}
		unset($allSystemFields);

		if (
			$showCustomFields
			&& !empty($productUserFields)
		)
		{
			foreach ($productUserFields as $fieldName => $row)
			{
				$result[1] .= $userFieldManager->GetEditFormHTML(
					$config['FROM_FORM'],
					$request->getPost($fieldName) ?? '',
					$row
				);
			}
		}

		return $result;
	}

	/**
	 * Returns true, if enable inventory managment and current user not have full store access.
	 *
	 * @return bool
	 */
	public static function needSummaryStoreAmountByPermissions(): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return false;
		}

		$allowedStores = Access\AccessController::getCurrent()->getPermissionValue(
			Access\ActionDictionary::ACTION_STORE_VIEW
		);
		if (
			is_array($allowedStores)
			&& in_array(Access\Permission\PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true)
		)
		{
			return false;
		}

		return true;
	}

	public static function allowedShowQuantityFields(): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return true;
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return true;
		}
		$allowedStores = Access\AccessController::getCurrent()->getPermissionValue(
			Access\ActionDictionary::ACTION_STORE_VIEW
		);
		if (!empty($allowedStores))
		{
			return true;
		}

		return false;
	}

	public static function getSummaryStoreAmountByPermissions(array $productIds): array
	{
		if (!Loader::includeModule('crm'))
		{
			return [];
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($productIds, true);
		if (empty($productIds))
		{
			return [];
		}

		$accessController = Access\AccessController::getCurrent();
		$allowedStores = $accessController->getPermissionValue(
			Access\ActionDictionary::ACTION_STORE_VIEW
		);
		if (empty($allowedStores))
		{
			return [];
		}
		$permissionFilter = $accessController->getEntityFilter(
			Access\ActionDictionary::ACTION_STORE_VIEW,
			Catalog\StoreProductTable::class
		);
		unset($accessController);

		$result = array_fill_keys(
			$productIds,
			[
				'QUANTITY' => 0,
				'QUANTITY_RESERVED' => 0,
			]
		);

		foreach (array_chunk($productIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$iterator = Catalog\StoreProductTable::getList([
				'select' => [
					'PRODUCT_ID',
					'SUM_QUANTITY',
					'SUM_QUANTITY_RESERVED',
				],
				'filter' => array_merge(
					[
						'@PRODUCT_ID' => $pageIds,
						'=STORE.ACTIVE' => 'Y',
					],
					$permissionFilter
				),
				'group' => [
					'PRODUCT_ID',
				],
				'runtime' => [
					new ORM\Fields\ExpressionField('SUM_QUANTITY', 'SUM(%s)', ['AMOUNT']),
					new ORM\Fields\ExpressionField('SUM_QUANTITY_RESERVED', 'SUM(%s)', ['QUANTITY_RESERVED']),
				],
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['PRODUCT_ID'];
				$result[$id] = [
					'QUANTITY' => (float)$row['SUM_QUANTITY'],
					'QUANTITY_RESERVED' => (float)$row['SUM_QUANTITY_RESERVED'],
				];
			}
			unset($row, $iterator);
		}
		unset($pageIds);

		return $result;
	}
}

class CCatalogAdminProductSetEdit
{
	const NEW_ITEM_COUNT = 3;
	const PREFIX_SET = 'PRODUCT_SET';
	const PREFIX_GROUP = 'PRODUCT_GROUP';

	protected static $strMainPrefix = '';
	protected static $intTypeID = 0;
	protected static $arErrors = array();
	protected static $arSrcValues = array();
	protected static $arCheckValues = array();

	public static function setMainPrefix($strPrefix)
	{
		self::$strMainPrefix = (string)$strPrefix;
	}

	public static function getMainPrefix()
	{
		return self::$strMainPrefix;
	}

	public static function setTypeID($intTypeID)
	{
		$intTypeID = (int)$intTypeID;
		if (CCatalogProductSet::TYPE_SET != $intTypeID && CCatalogProductSet::TYPE_GROUP != $intTypeID)
			return;
		self::$intTypeID = $intTypeID;
	}

	public static function getTypeID()
	{
		return self::$intTypeID;
	}

	public static function setProductFormParams($arParams)
	{
		if (empty($arParams) || !is_array($arParams))
			return;
		if (!isset($arParams['TYPE']))
			return;
		$intTypeID = (int)$arParams['TYPE'];
		if (CCatalogProductSet::TYPE_SET != $intTypeID && CCatalogProductSet::TYPE_GROUP != $intTypeID)
			return;
		self::$intTypeID = $intTypeID;
		$strPrefix = (CCatalogProductSet::TYPE_SET == $intTypeID ? self::PREFIX_SET : self::PREFIX_GROUP);

		self::setMainPrefix($strPrefix);
	}

	public static function setSkuFormParams($arParams)
	{
		if (empty($arParams) || !is_array($arParams))
			return;
		if (!isset($arParams['TYPE']))
			return;
		$intTypeID = (int)$arParams['TYPE'];
		if (CCatalogProductSet::TYPE_SET != $intTypeID && CCatalogProductSet::TYPE_GROUP != $intTypeID)
			return;
		self::$intTypeID = $intTypeID;
		$strPrefix = 'SUB'.(CCatalogProductSet::TYPE_SET == $intTypeID ? self::PREFIX_SET : self::PREFIX_GROUP);

		self::setMainPrefix($strPrefix);
	}

	public static function getEmptySet($intProductID)
	{
		$arResult = false;
		if (CCatalogProductSet::TYPE_SET == self::$intTypeID || CCatalogProductSet::TYPE_GROUP == self::$intTypeID)
		{
			$arResult = array(
				'n0' => array(
					'ITEM_ID' => $intProductID,
					'ACTIVE' => 'Y',
					'SORT' => '100',
					'ITEMS' => self::getEmptyItem(0),
					'NEW_ITEM_COUNT' => self::NEW_ITEM_COUNT
				)
			);
		}
		return $arResult;
	}

	public static function getEmptyItem($arParams)
	{
		$arResult = array();
		if (CCatalogProductSet::TYPE_SET != self::$intTypeID && CCatalogProductSet::TYPE_GROUP != self::$intTypeID)
			return $arResult;
		if (!is_array($arParams))
			$arParams = array('nStart' => $arParams);
		if (!isset($arParams['nStart']))
			$arParams['nStart'] = 0;
		$arParams['nStart'] = (int)$arParams['nStart'];
		switch(self::$intTypeID)
		{
			case CCatalogProductSet::TYPE_SET:
				for ($i = $arParams['nStart']; $i < ($arParams['nStart'] + self::NEW_ITEM_COUNT); $i++)
				{
					$arResult['n'.$i] = array(
						'ITEM_ID' => '',
						'QUANTITY' => '',
						'DISCOUNT_PERCENT' => '',
						'SORT' => 100,
						'NEW_ITEM' => true,
						'EMPTY_ITEM' => true,
						'ITEM_NAME' => '',
					);
				}
				break;
			case CCatalogProductSet::TYPE_GROUP:
				for ($i = $arParams['nStart']; $i < ($arParams['nStart'] + self::NEW_ITEM_COUNT); $i++)
				{
					$arResult['n'.$i] = array(
						'ITEM_ID' => '',
						'QUANTITY' => '',
						'SORT' => 100,
						'NEW_ITEM' => true,
						'EMPTY_ITEM' => true,
						'ITEM_NAME' => '',
					);
				}
				break;
			default:
				break;
		}
		return $arResult;
	}

	public static function getFormValues(&$arSets)
	{
		if (CCatalogProductSet::TYPE_SET != self::$intTypeID && CCatalogProductSet::TYPE_GROUP != self::$intTypeID)
			return;
		if (empty($arSets) || !is_array($arSets))
			return;

		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();
		if (!$boolFeatureSet)
			return;

		if (!isset(self::$arSrcValues[self::$strMainPrefix]) || empty(self::$arSrcValues[self::$strMainPrefix]))
			return;

		foreach (self::$arSrcValues[self::$strMainPrefix] as $setKey => $setData)
		{
			if (empty($setData['ITEMS']))
			{
				if (array_key_exists($setKey, $arSets))
					unset($arSets[$setKey]);
				continue;
			}
			$newSetData = $setData;
			unset($newSetData['ITEMS']);
			$newItemCount = 0;
			$setItems = array();

			foreach ($setData['ITEMS'] as $itemKey => $item)
			{
				if (empty($item['ITEM_ID']) || trim($item['ITEM_ID'] == ''))
					continue;
				$itemKey = (int)$itemKey;
				if ($itemKey > 0)
				{
					$setItems[$itemKey] = $item;
				}
				else
				{
					$setItems['n'.$newItemCount] = $item;
					$newItemCount++;
				}
			}
			unset($itemKey, $item);

			$newSetData['ITEMS'] = $setItems;
			$newSetData['NEW_ITEM_COUNT'] = $newItemCount;

			if (isset($arSets[$setKey]))
			{
				$arSets[$setKey] = array_merge($newSetData, $arSets[$setKey]);
				$arSets[$setKey]['ITEMS'] = $newSetData['ITEMS'];
				$arSets[$setKey]['NEW_ITEM_COUNT'] = $newSetData['NEW_ITEM_COUNT'];
			}
			else
			{
				$arSets[$setKey] = $newSetData;
			}
			unset($newSetData, $newItemCount, $setItems);
		}
		unset($setKey, $setData);
	}

	public static function addEmptyValues(&$arSets)
	{
		if (empty($arSets) || !is_array($arSets))
			return;

		foreach ($arSets as $setKey => $setData)
		{
			$start = $setData['NEW_ITEM_COUNT'] ?? 0;
			foreach (self::getEmptyItem($start) as $rowKey => $row)
				$arSets[$setKey]['ITEMS'][$rowKey] = $row;
			$arSets[$setKey]['NEW_ITEM_COUNT'] = $start + self::NEW_ITEM_COUNT;
			unset($rowKey, $row, $start);
		}
		unset($setKey, $setData);
	}

	public static function getItemsInfo(&$arSets)
	{
		$itemList = array();
		$itemIds = array();
		if (empty($arSets) || !is_array($arSets))
			return;
		foreach ($arSets as $key => $arOneSet)
		{
			foreach ($arOneSet['ITEMS'] as $keyItem => $arItem)
			{
				if ('' == $arItem['ITEM_ID'])
					continue;
				$intItemID = (int)$arItem['ITEM_ID'];
				if (0 >= $intItemID)
					continue;
				if (!isset($itemList[$intItemID]))
				{
					$itemList[$intItemID] = array();
					$itemIds[] = $intItemID;
				}
				$itemList[$intItemID][] = &$arSets[$key]['ITEMS'][$keyItem];
			}
		}
		if (!empty($itemList))
		{
			$productIterator = Iblock\ElementTable::getList(array(
				'select' => array('ID', 'NAME'),
				'filter' => array('@ID' => $itemIds)
			));
			while ($product = $productIterator->fetch())
			{
				$product['ID'] = (int)$product['ID'];
				if (!isset($itemList[$product['ID']]))
					continue;
				foreach ($itemList[$product['ID']] as &$setItem)
					$setItem['ITEM_NAME'] = $product['NAME'];
				unset($setItem);
			}
			unset($product, $productIterator);
			$productRatio = Catalog\ProductTable::getCurrentRatioWithMeasure($itemIds);
			if (!empty($productRatio))
			{
				foreach ($productRatio as $productId => $productData)
				{
					if (!isset($itemList[$productId]))
						continue;
					foreach ($itemList[$productId] as &$setItem)
					{
						$setItem['RATIO'] = $productData['RATIO'];
						$setItem['MEASURE'] = $productData['MEASURE'];
					}
					unset($setItem);
				}
				unset($productId, $productData);
			}
			unset($productRatio);
		}
		unset($itemIds, $itemList);
	}

	public static function clearOwnerSet(&$arSets)
	{
		if (empty($arSets) || !is_array($arSets))
			return;
		$index = 0;
		$result = array();
		foreach ($arSets as $oneSet)
		{
			$itemIndex = 0;
			$items = array();
			foreach ($oneSet['ITEMS'] as $oneItem)
			{
				$items['n'.$itemIndex] = $oneItem;
				$itemIndex++;
			}
			$result['n'.$index] = array(
				'ITEM_ID' => '',
				'ACTIVE' => $oneSet['ACTIVE'],
				'SORT' => $oneSet['SORT'],
				'ITEMS' => $items,
				'NEW_ITEM_COUNT' => $itemIndex
			);
			$index++;
		}
		unset($oneSet);
		$arSets = $result;
	}

	public static function showEditForm($arSets)
	{
		if (CCatalogProductSet::TYPE_SET != self::$intTypeID && CCatalogProductSet::TYPE_GROUP != self::$intTypeID)
			return;
		if (empty($arSets) || !is_array($arSets))
			return;

		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();
		if (!$boolFeatureSet)
			return;

		Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/tbl_edit.js');

		self::getItemsInfo($arSets);

		foreach ($arSets as $key => $arOneSet)
		{
			$blockName = self::$strMainPrefix.'_'.$arOneSet['ITEM_ID'];
			$blockName .= '_'.Main\Security\Random::getString(8, true);

			$strNamePrefix = self::$strMainPrefix.'['.$key.']';
			$strIDPrefix = $blockName.'_'.$key;
			?><table id="<? echo $strIDPrefix; ?>_TBL" class="internal" style="margin: 0 auto;">
			<tr class="heading">
			<td class="align-left"><? echo Loc::getMessage('BT_CAT_SET_ITEM_NAME'); ?></td>
			<td class="align-right"><? echo Loc::getMessage('BT_CAT_SET_ITEM_QUANTITY'); ?></td>
			<td class="align-left">&nbsp;</td><?
			if (CCatalogProductSet::TYPE_SET == self::$intTypeID)
			{
				?><td class="align-right"><? echo Loc::getMessage('BT_CAT_SET_ITEM_DISCOUNT_PERCENT_EXT'); ?></td><?
			}
			?>
			<td class="align-right"><? echo Loc::getMessage('BT_CAT_SET_ITEM_SORT'); ?></td><?
			if (0 < (int)$key)
			{
				?><td><? echo (
				CCatalogProductSet::TYPE_SET == self::$intTypeID
				? Loc::getMessage('BT_CAT_SET_ITEM_DEL_FROM_SET')
				: Loc::getMessage('BT_CAT_SET_ITEM_DEL_FROM_GROUP')); ?></td><?
			}
			?></tr><?
			Main\Type\Collection::sortByColumn(
				$arOneSet['ITEMS'],
				array(
					'NEW_ITEM' => SORT_ASC,
					'EMPTY_ITEM' => SORT_ASC,
					'SORT' => array(SORT_NUMERIC, SORT_ASC),
					'ITEM_ID' => array(SORT_NUMERIC, SORT_ASC)
				),
				array(
					'NEW_ITEM' => function($value)
					{
						return !is_null($value);
					},
					'EMPTY_ITEM' => function($value)
					{
						return !is_null($value);
					}
				),
				null,
				true
			);
			foreach ($arOneSet['ITEMS'] as $keyItem => $arOneItem)
			{
				$arItemParams = array(
					'SET_KEY' => $key,
					'KEY' => $keyItem,
					'NAME_PREFIX' => $strNamePrefix.'[ITEMS]['.$keyItem.']',
					'ID_PREFIX' => $strIDPrefix.'_ITEMS_'.$keyItem
				);
				self::showItemRow($arOneItem, $arItemParams);
			}
			?>
			</table>
			<div style="margin: 10px auto; text-align: center;">
			<input class="adm-btn-big" type="button" id="<? echo $strIDPrefix; ?>_ITEMS_ADD" value="<? echo Loc::getMessage('BT_CAT_SET_ITEM_ADD'); ?>" title="<? echo Loc::getMessage('BT_CAT_SET_ITEM_ADD_TITLE'); ?>">
			</div>
			<input type="hidden" id="<? echo $strIDPrefix; ?>_ITEMS_CNT" value="<? echo (int)$arOneSet['NEW_ITEM_COUNT']; ?>"><?
			$arNewParams = array(
				'SET_KEY' => $key,
				'KEY' => 'tmp_xxx',
				'NAME_PREFIX' => $strNamePrefix.'[ITEMS][ntmp_xxx]',
				'ID_PREFIX' => $strIDPrefix.'_ITEMS_ntmp_xxx'
			);

			$arCellInfo = self::getJSRow($arNewParams);

			$arJSParams = array(
				'PREFIX' => $strIDPrefix.'_ITEMS_',
				'PREFIX_NAME' => $strNamePrefix.'[ITEMS]',
				'TABLE_PROP_ID' => $strIDPrefix.'_TBL',
				'PROP_COUNT_ID' => $strIDPrefix.'_ITEMS_CNT',
				'BTN_ID' => $strIDPrefix.'_ITEMS_ADD',
				'CELLS' => $arCellInfo['CELLS'],
				'CELL_PARAMS' => $arCellInfo['CELL_PARAMS'],
				// TODO: remove this dirty hack after disable old product card in public shop
				'SEARCH_PAGE' => (defined('SELF_FOLDER_URL') ? '/shop/settings/' : '/bitrix/admin/').'cat_product_search_dialog.php',
			);
			?>
<script type="text/javascript">
if (!window.ob<?=$blockName; ?>)
{
	window.ob<?=$blockName; ?> = new JCCatTblEditExt(<? echo CUtil::PhpToJSObject($arJSParams); ?>);
}
</script>
			<?
			unset($blockName);
			break;
		}
	}

	public static function showItemRow($arRow, $arParams)
	{
		if (CCatalogProductSet::TYPE_SET != self::$intTypeID && CCatalogProductSet::TYPE_GROUP != self::$intTypeID)
			return;
		$strNamePrefix = $arParams['NAME_PREFIX'];
		$strIDPrefix = $arParams['ID_PREFIX'];
		$strKey = $arParams['KEY'];
		?><tr>
		<td class="align-left">
			<input name="<? echo $strNamePrefix; ?>[ITEM_ID]" id="<? echo $strIDPrefix; ?>_ITEM_ID" value="<? echo htmlspecialcharsbx($arRow['ITEM_ID']); ?>" size="5" type="text">
			<input type="button" value="..." id="<? echo $strIDPrefix; ?>_BTN" data-row-id="<? echo $strIDPrefix; ?>">
			&nbsp;<span id="<? echo $strIDPrefix; ?>_ITEM_ID_link"><? echo htmlspecialcharsEx($arRow['ITEM_NAME']); ?></span>
		</td>
		<td class="align-right">
			<input type="text" size="5" name="<? echo $strNamePrefix; ?>[QUANTITY]" id="<? echo $strIDPrefix; ?>_QUANTITY" value="<? echo htmlspecialcharsbx($arRow['QUANTITY']) ?>">
		</td>
		<td class="align-left"><?
		$measure = '';
		if (isset($arRow['RATIO']) && isset($arRow['MEASURE']))
		{
			$measure = ' * '.$arRow['RATIO'].' '.$arRow['MEASURE']['SYMBOL_RUS'];
		}
		?><span id="<? echo $strIDPrefix; ?>_MEASURE"><? echo $measure; ?></span></td><?
		if (CCatalogProductSet::TYPE_SET == self::$intTypeID)
		{
		?><td class="align-right">
			<input type="text" size="3" name="<? echo $strNamePrefix; ?>[DISCOUNT_PERCENT]" id="<? echo $strIDPrefix; ?>_DISCOUNT_PERCENT" value="<? echo htmlspecialcharsbx($arRow['DISCOUNT_PERCENT']) ?>">
		</td><?
		}
		?>
		<td class="align-right">
			<input type="text" size="3" name="<? echo $strNamePrefix; ?>[SORT]" id="<? echo $strIDPrefix; ?>_SORT" value="<? echo htmlspecialcharsbx($arRow['SORT']) ?>">
		</td>
		<?
		if (0 < (int)$arParams['SET_KEY'])
		{
		?><td>
			<input type="hidden" name="<? echo $strNamePrefix; ?>[DEL]" id="<? echo $strIDPrefix; ?>_DEL_N" value="N">
			<?
			if (0 < (int)$strKey)
			{
				?><input type="checkbox" name="<? echo $strNamePrefix; ?>[DEL]" id="<? echo $strIDPrefix; ?>_DEL" value="Y"><?
			}
			else
			{
				?>&nbsp;<?
			}
		?></td><?
		}
		?>
		</tr><?
	}

	protected static function getJSRow($arParams)
	{
		if (CCatalogProductSet::TYPE_SET != self::$intTypeID && CCatalogProductSet::TYPE_GROUP != self::$intTypeID)
			return '';
		$strNamePrefix = $arParams['NAME_PREFIX'];
		$strIDPrefix = $arParams['ID_PREFIX'];
		$strKey = $arParams['KEY'];

		$arCells = array();
		$arCellParams = array();
		$arCells[] = '<input name="'.$strNamePrefix.'[ITEM_ID]" id="'.$strIDPrefix.'_ITEM_ID" value="" size="5" type="text">'.
			' <input type="button" value="..." id="'.$strIDPrefix.'_BTN" data-row-id="'.$strIDPrefix.'">'.
			'&nbsp;<span id="'.$strIDPrefix.'_ITEM_ID_link"></span>';
		$arCellParams[] = array(
			'attrs' => array(
				'className' => 'align-left'
			)
		);
		$arCells[] = '<input type="text" size="5" name="'.$strNamePrefix.'[QUANTITY]" id="'.$strIDPrefix.'_QUANTITY" value="">';
		$arCellParams[] = array(
			'attrs' => array(
				'className' => 'align-right'
			)
		);
		$arCells[] = '<span id="'.$strIDPrefix.'_MEASURE"></span>';
		$arCellParams[] = array(
			'attrs' => array(
				'className' => 'align-left'
			)
		);
		if (CCatalogProductSet::TYPE_SET == self::$intTypeID)
		{
			$arCells[] = '<input type="text" size="3" name="'.$strNamePrefix.'[DISCOUNT_PERCENT]" id="'.$strIDPrefix.'_DISCOUNT_PERCENT" value="">';
			$arCellParams[] = array(
				'attrs' => array(
					'className' => 'align-right'
				)
			);
		}
		$arCells[] = '<input type="text" size="3" name="'.$strNamePrefix.'[SORT]" id="'.$strIDPrefix.'_SORT" value="100">';
		$arCellParams[] = array(
				'attrs' => array(
					'className' => 'align-right'
				)
			);
		if (0 < (int)$arParams['SET_KEY'])
		{
			$arCells[] = '<input type="hidden" name="'.$strNamePrefix.'[DEL]" id="'.$strIDPrefix.'_DEL_N" value="N">'.
			(0 < (int)$strKey
				? '<input type="checkbox" name="'.$strNamePrefix.'[DEL]" id="'.$strIDPrefix.'_DEL" value="Y">'
				: '&nbsp;'
			);
			$arCellParams[] = '';
		}

		return array(
			'CELLS' => $arCells,
			'CELL_PARAMS' => $arCellParams
		);
	}

	public static function checkFormValues($arItem)
	{
		self::$arErrors = array();

		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();
		if (!$boolFeatureSet)
			return true;

		self::$arSrcValues[self::$strMainPrefix] = array();
		self::$arCheckValues[self::$strMainPrefix] = array();

		if (isset($_POST[self::$strMainPrefix]) && is_array($_POST[self::$strMainPrefix]))
		{
			CCatalogProductSet::disableShowErrors();
			self::$arSrcValues[self::$strMainPrefix] = $_POST[self::$strMainPrefix];

			foreach (self::$arSrcValues[self::$strMainPrefix] as $key => $arOneSet)
			{
				$boolNew = (0 >= (int)$key);
				$arSaveSet = array(
					'TYPE' => self::$intTypeID,
					'ITEM_ID' => $arItem['PRODUCT_ID'],
					'ACTIVE' => 'Y',
					'ITEMS' => array()
				);

				$removeSet = true;
				if (CCatalogProductSet::TYPE_SET == self::$intTypeID)
				{
					foreach ($arOneSet['ITEMS'] as $keyItem => $arOneItem)
					{
						if ('Y' == $arOneItem['DEL'])
							continue;
						$itemId = (isset($arOneItem['ITEM_ID']) ? (int)$arOneItem['ITEM_ID'] : 0);
						if ($itemId <= 0)
							continue;
						$removeSet = false;
						$arOneItem['DISCOUNT_PERCENT'] = trim($arOneItem['DISCOUNT_PERCENT']);
						$arSaveItem = array(
							'ITEM_ID' => $itemId,
							'QUANTITY' => $arOneItem['QUANTITY'],
							'DISCOUNT_PERCENT' => ('' == $arOneItem['DISCOUNT_PERCENT'] ? false : $arOneItem['DISCOUNT_PERCENT']),
							'SORT' => $arOneItem['SORT']
						);
						if ((int)$keyItem <= 0)
							self::$arSrcValues[self::$strMainPrefix][$key]['ITEMS'][$keyItem]['NEW_ITEM'] = true;
						unset($itemId);
						$arSaveSet['ITEMS'][] = $arSaveItem;
					}
					unset($keyItem, $arOneItem);
				}
				else
				{
					foreach ($arOneSet['ITEMS'] as $keyItem => $arOneItem)
					{
						if ('Y' == $arOneItem['DEL'])
							continue;
						$itemId = (isset($arOneItem['ITEM_ID']) ? (int)$arOneItem['ITEM_ID'] : 0);
						if ($itemId <= 0)
							continue;
						$removeSet = false;
						$arSaveItem = array(
							'ITEM_ID' => $itemId,
							'QUANTITY' => $arOneItem['QUANTITY'],
							'SORT' => $arOneItem['SORT']
						);
						if ($arSaveItem['QUANTITY'] == '')
							$arSaveItem['QUANTITY'] = 1;
						if ((int)$keyItem <= 0)
							self::$arSrcValues[self::$strMainPrefix][$key]['ITEMS'][$keyItem]['NEW_ITEM'] = true;
						unset($itemId);
						$arSaveSet['ITEMS'][] = $arSaveItem;
					}
					unset($keyItem, $arOneItem);
				}
				if ($removeSet)
				{
					$boolCheck = true;
					$arSaveSet['DEL'] = 'Y';
				}
				else
				{
					$arTestSet = $arSaveSet;
					$boolCheck = (
						$boolNew
						? CCatalogProductSet::checkFields('TEST', $arTestSet, 0)
						: CCatalogProductSet::checkFields('UPDATE', $arTestSet, $key)
					);
					unset($arTestSet);
				}
				unset($removeSet);
				if (!$boolCheck)
				{
					$ex = new CAdminException(CCatalogProductSet::getErrors());
					self::$arErrors[$key] = $ex->GetString();
				}
				else
				{
					self::$arCheckValues[self::$strMainPrefix][$key] = $arSaveSet;
				}
				break;
			}

			CCatalogProductSet::enableShowErrors();
			return (empty(self::$arErrors));
		}
		return true;
	}

	public static function saveFormValues($arItem)
	{
		$boolFeatureSet = Catalog\Config\Feature::isProductSetsEnabled();
		if (!$boolFeatureSet)
			return;

		if (0 >= $arItem['PRODUCT_ID'])
			return;

		if (!empty(self::$arCheckValues[self::$strMainPrefix]))
		{
			foreach (self::$arCheckValues[self::$strMainPrefix] as $key => $arSaveSet)
			{

				if (0 >= $arSaveSet['ITEM_ID'])
					$arSaveSet['ITEM_ID'] = $arItem['PRODUCT_ID'];
				$boolNew = (0 >= (int)$key);
				if ($boolNew)
				{
					if (!isset($arSaveSet['DEL']) || $arSaveSet['DEL'] != 'Y')
						CCatalogProductSet::add($arSaveSet);
				}
				else
				{
					if (isset($arSaveSet['DEL']) && $arSaveSet['DEL'] == 'Y')
						CCatalogProductSet::delete($key);
					else
						CCatalogProductSet::update($key, $arSaveSet);
				}
				unset($boolNew);
			}
			unset($key, $arSaveSet);
		}
	}

	public static function getErrors()
	{
		return self::$arErrors;
	}
}
