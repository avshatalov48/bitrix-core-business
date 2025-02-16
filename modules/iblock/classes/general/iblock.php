<?php

use Bitrix\Main;
use Bitrix\Main\File\Image;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

class CAllIBlock
{
	protected const TABLE_PREFIX_SINGLE_PROPERTY_VALUES = 'b_iblock_element_prop_s';
	protected const TABLE_PREFIX_MULTIPLE_PROPERTY_VALUES = 'b_iblock_element_prop_m';
	protected const TABLE_COMMON_PROPERTY_VALUES = 'b_iblock_element_property';

	public string $LAST_ERROR = '';
	protected static array $disabledCacheTag = [];
	protected static int $enableClearTagCache = 0;

	protected static ?bool $catalogIncluded = null;
	protected static ?bool $workflowIncluded = null;

	private static array $urlElementDataCache = [];

	private static array $urlSectionDataCache = [];

	private static array $productIblockDataCache = [];

	private static array $urlParentCache = [];

	public static function clearUrlDataCache(): void
	{
		self::$productIblockDataCache = [];
		self::$urlParentCache = [];
		self::$urlElementDataCache = [];
		self::$urlSectionDataCache = [];
	}

	public static function fillUrlElementDataCache(int $id): void
	{
		if ($id <= 0)
		{
			return;
		}
		if (isset(self::$urlElementDataCache[$id]))
		{
			return;
		}

		$element = Iblock\ElementTable::getRow([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'CODE',
				'XML_ID',
				'IBLOCK_SECTION_ID',
			],
			'filter' => [
				'=ID' => $id,
			]
		]);
		if ($element !== null)
		{
			$element['ID'] = (int)$element['ID'];
			$element['IBLOCK_ID'] = (int)$element['IBLOCK_ID'];
			$element['CODE'] = (string)$element['CODE'];
			$element['XML_ID'] = (string)$element['XML_ID'];
			$element['IBLOCK_SECTION_ID'] = (int)$element['IBLOCK_SECTION_ID'];
			$element['IBLOCK_SECTION_CODE'] = '';
			$sectionId = $element['IBLOCK_SECTION_ID'];
			if ($sectionId > 0)
			{
				if (!isset(self::$urlSectionDataCache[$sectionId]))
				{
					self::$urlSectionDataCache[$sectionId] = false;
					$section = Iblock\SectionTable::getRow([
						'select' => [
							'ID',
							'CODE',
						],
						'filter' => [
							'=ID' => $sectionId,
							'=IBLOCK_ID' => $element['IBLOCK_ID'],
						]
					]);
					if ($section !== null)
					{
						$section['ID'] = (int)$section['ID'];
						$section['CODE'] = (string)$section['CODE'];
						self::$urlSectionDataCache[$sectionId] = $section;
					}
				}
				if (!empty(self::$urlSectionDataCache[$sectionId]))
				{
					$element['IBLOCK_SECTION_CODE'] = self::$urlSectionDataCache[$sectionId]['CODE'];
				}
			}

			self::$urlElementDataCache[$id] = $element;
		}
		else
		{
			self::$urlElementDataCache[$id] = false;
		}
	}

	private static function getUrlElementData(int $id): ?array
	{
		if ($id <= 0)
		{
			return null;
		}

		if (!isset(self::$urlElementDataCache[$id]))
		{
			static::fillUrlElementDataCache($id);
		}

		return (
			!empty(self::$urlElementDataCache[$id])
				? self::$urlElementDataCache[$id]
				: null
		);
	}

	private static function getProductIblockData(int $iblockId): ?array
	{
		if ($iblockId <= 0)
		{
			return null;
		}
		if (self::$catalogIncluded === null)
		{
			self::$catalogIncluded = Loader::includeModule('catalog');
		}
		if (!self::$catalogIncluded)
		{
			return null;
		}

		if (!isset(self::$productIblockDataCache[$iblockId]))
		{
			$iblock = CCatalogSku::GetInfoByOfferIBlock($iblockId);
			if (is_array($iblock))
			{
				$productIblock = CIBlock::GetArrayByID($iblock['PRODUCT_IBLOCK_ID']);
				if (is_array($productIblock))
				{
					$iblock['PRODUCT_IBLOCK'] = [
						'ID' => (int)$productIblock['ID'],
						'IBLOCK_TYPE_ID' => $productIblock['IBLOCK_TYPE_ID'],
						'CODE' => (string)$productIblock['CODE'],
						'XML_ID' => (string)$productIblock['XML_ID'],
						'DETAIL_PAGE_URL' => (string)$productIblock['DETAIL_PAGE_URL'],
					];
				}
				else
				{
					$iblock = false;
				}
			}
			self::$productIblockDataCache[$iblockId] = $iblock;
		}

		return (
			!empty(self::$productIblockDataCache[$iblockId])
				? self::$productIblockDataCache[$iblockId]
				: null
		);
	}

	private static function getProductId(int $elementId, int $iblockId): ?int
	{
		if ($elementId <= 0 || $iblockId <= 0)
		{
			return null;
		}
		if (self::$catalogIncluded === null)
		{
			self::$catalogIncluded = Loader::includeModule('catalog');
		}
		if (!self::$catalogIncluded)
		{
			return null;
		}

		if (!isset(self::$urlParentCache[$elementId]))
		{
			$list = CCatalogSku::getProductList($elementId, $iblockId);
			self::$urlParentCache[$elementId] = $list[$elementId]['ID'] ?? false;
		}

		return (
			!empty(self::$urlParentCache[$elementId])
				? self::$urlParentCache[$elementId]
				: null
		);
	}

	public static function ShowPanel($IBLOCK_ID=0, $ELEMENT_ID=0, $SECTION_ID="", $type="news", $bGetIcons=false, $componentName="", $arLabels=array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CUser $USER */
		global $USER;

		if (($USER->IsAuthorized() || $APPLICATION->ShowPanel===true) && $APPLICATION->ShowPanel!==false)
		{
			if (CModule::IncludeModule("iblock") && $type <> '')
			{
				$arButtons = CIBlock::GetPanelButtons($IBLOCK_ID, $ELEMENT_ID, $SECTION_ID, array(
					"LABELS" => $arLabels,
				));

				$mode = $APPLICATION->GetPublicShowMode();

				if($bGetIcons)
				{
					return CIBlock::GetComponentMenu($mode, $arButtons);
				}
				else
				{
					CIBlock::AddPanelButtons($mode, $componentName, $arButtons);
				}
			}
		}
		return null;
	}

	public static function AddPanelButtons($mode, $componentName, $arButtons)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$arImages = array(
			"add_element" => (defined("PANEL_ADD_ELEMENT_BTN")) ? PANEL_ADD_ELEMENT_BTN : "/bitrix/images/iblock/icons/new_element.gif",
			"edit_element" => (defined("PANEL_EDIT_ELEMENT_BTN")) ? PANEL_EDIT_ELEMENT_BTN : "/bitrix/images/iblock/icons/edit_element.gif",
			"edit_iblock" => (defined("PANEL_EDIT_IBLOCK_BTN")) ? PANEL_EDIT_IBLOCK_BTN : "/bitrix/images/iblock/icons/edit_iblock.gif",
			"history_element" => (defined("PANEL_HISTORY_ELEMENT_BTN")) ? PANEL_HISTORY_ELEMENT_BTN : "/bitrix/images/iblock/icons/history.gif",
			"edit_section" => (defined("PANEL_EDIT_SECTION_BTN")) ? PANEL_EDIT_SECTION_BTN : "/bitrix/images/iblock/icons/edit_section.gif",
			"add_section" => (defined("PANEL_ADD_SECTION_BTN")) ? PANEL_ADD_SECTION_BTN : "/bitrix/images/iblock/icons/new_section.gif",
			"element_list" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_el.gif",
			"section_list" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_sec.gif",
		);

		$componentName = (string)$componentName;

		if (!empty($arButtons[$mode]) && is_array($arButtons[$mode]))
		{
			//Try to detect component via backtrace
			if ($componentName === '')
			{
				$arTrace = debug_backtrace();
				foreach($arTrace as $arCallInfo)
				{
					if (isset($arCallInfo["file"]))
					{
						$file = mb_strtolower(str_replace("\\", "/", $arCallInfo["file"]));
						if(preg_match("#.*/bitrix/components/(.+?)/(.+?)/#", $file, $match))
						{
							$componentName = $match[1].":".$match[2];
							break;
						}
					}
				}
			}
			if ($componentName !== '')
			{
				$arComponentDescription = CComponentUtil::GetComponentDescr($componentName);
				if(is_array($arComponentDescription) && mb_strlen($arComponentDescription["NAME"]))
					$componentName = $arComponentDescription["NAME"];
			}
			else
			{
				$componentName = Loc::getMessage("IBLOCK_PANEL_UNKNOWN_COMPONENT");
			}

			$arPanelButton = array(
				"SRC" => "/bitrix/images/iblock/icons/iblock.gif",
				"ALT" => $componentName,
				"TEXT" => $componentName,
				"MAIN_SORT" => 300,
				"SORT" => 30,
				"MENU" => array(),
				"MODE" => $mode,
			);

			foreach($arButtons[$mode] as $i=>$arSubButton)
			{
				if (isset($arImages[$i]))
					$arSubButton['IMAGE'] = $arImages[$i];

				if($arSubButton["DEFAULT"])
					$arPanelButton["HREF"] = $arSubButton["ACTION"];

				$arPanelButton["MENU"][] = $arSubButton;
			}

			if (!empty($arButtons["submenu"]) && is_array($arButtons["submenu"]))
			{
				$arSubMenu = array(
					"SRC" => "/bitrix/images/iblock/icons/iblock.gif",
					"ALT" => Loc::getMessage("IBLOCK_PANEL_CONTROL_PANEL_ALT"),
					"TEXT" => Loc::getMessage("IBLOCK_PANEL_CONTROL_PANEL"),
					"MENU" => array(),
					"MODE" => $mode,
				);

				foreach($arButtons["submenu"] as $i=>$arSubButton)
				{
					if (isset($arImages[$i]))
						$arSubButton['IMAGE'] = $arImages[$i];
					$arSubMenu["MENU"][] = $arSubButton;
				}

				$arPanelButton["MENU"][] = array("SEPARATOR" => "Y");
				$arPanelButton["MENU"][] = $arSubMenu;
			}
			$APPLICATION->AddPanelButton($arPanelButton);
		}

		if (!empty($arButtons["intranet"]) && is_array($arButtons["intranet"]) && CModule::IncludeModule("intranet"))
		{
			/** @global CIntranetToolbar $INTRANET_TOOLBAR */
			global $INTRANET_TOOLBAR;
			foreach($arButtons["intranet"] as $arButton)
				$INTRANET_TOOLBAR->AddButton($arButton);
		}
	}

	public static function GetComponentMenu($mode, $arButtons)
	{
		$arImages = array(
			"add_element" => "/bitrix/images/iblock/icons/new_element.gif",
			"edit_element" => "/bitrix/images/iblock/icons/edit_element.gif",
			"edit_iblock" => "/bitrix/images/iblock/icons/edit_iblock.gif",
			"history_element" => "/bitrix/images/iblock/icons/history.gif",
			"edit_section" => "/bitrix/images/iblock/icons/edit_section.gif",
			"add_section" => "/bitrix/images/iblock/icons/new_section.gif",
			"element_list" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_el.gif",
			"section_list" => "/bitrix/themes/.default/icons/iblock/mnu_iblock_sec.gif",
		);

		$arResult = array();
		foreach($arButtons[$mode] as $i=>$arButton)
		{
			if (!isset($arButton['SEPARATOR']))
			{
				$arButton['URL'] = $arButton['ACTION'] ?? null;
				unset($arButton['ACTION']);
				$arButton['IMAGE'] = $arImages[$i] ?? null;
			}
			$arResult[] = $arButton;
		}
		return $arResult;
	}

	public static function GetPanelButtons($IBLOCK_ID=0, $ELEMENT_ID=0, $SECTION_ID=0, $arOptions=array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$windowParams = array('width' => 700, 'height' => 400, 'resize' => false);

		$arButtons = array(
			"view" => array(),
			"edit" => array(),
			"configure" => array(),
			"submenu" => array(),
		);

		$bSectionButtons = !(isset($arOptions['SECTION_BUTTONS']) && $arOptions['SECTION_BUTTONS'] === false);
		$bSessID = !(isset($arOptions['SESSID']) && $arOptions['SESSID'] === false);

		$IBLOCK_ID = (int)$IBLOCK_ID;
		$ELEMENT_ID = (int)$ELEMENT_ID;
		$SECTION_ID = (int)$SECTION_ID;

		if(($ELEMENT_ID > 0) && (($IBLOCK_ID <= 0) || ($bSectionButtons && $SECTION_ID == 0)))
		{
			$rsIBlockElement = CIBlockElement::GetList(array(), array(
				"ID" => $ELEMENT_ID,
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
			if($arIBlockElement = $rsIBlockElement->Fetch())
			{
				$IBLOCK_ID = $arIBlockElement["IBLOCK_ID"];
				$SECTION_ID = $arIBlockElement["IBLOCK_SECTION_ID"];
			}
		}

		if($IBLOCK_ID <= 0)
			return $arButtons;

		$bCatalog = false;
		$useCatalogButtons = (($ELEMENT_ID <= 0 || isset($arOptions['SHOW_CATALOG_BUTTONS'])) && !empty($arOptions['USE_CATALOG_BUTTONS']) && is_array($arOptions['USE_CATALOG_BUTTONS']));
		$catalogButtons = array();
		if ($useCatalogButtons || (isset($arOptions["CATALOG"]) && $arOptions["CATALOG"] == true))
		{
			if (self::$catalogIncluded === null)
				self::$catalogIncluded = \Bitrix\Main\Loader::includeModule('catalog');
			$bCatalog = self::$catalogIncluded;
			if (!self::$catalogIncluded)
				$useCatalogButtons = false;
		}

		if ($useCatalogButtons)
		{
			if (isset($arOptions['USE_CATALOG_BUTTONS']['add_product']) && $arOptions['USE_CATALOG_BUTTONS']['add_product'] == true)
				$catalogButtons['add_product'] = true;
			if (isset($arOptions['USE_CATALOG_BUTTONS']['add_sku']) && $arOptions['USE_CATALOG_BUTTONS']['add_sku'] == true)
				$catalogButtons['add_sku'] = true;
			if (empty($catalogButtons))
				$useCatalogButtons = false;
		}

		$return_url = array(
			"add_element" => "",
			"edit_element" => "",
			"edit_iblock" => "",
			"history_element" => "",
			"edit_section" => "",
			"add_section" => "",
			"delete_section" => "",
			"delete_element" => "",
			"element_list" => "",
			"section_list" => "",
		);

		if(isset($arOptions['RETURN_URL']))
		{
			if(is_array($arOptions["RETURN_URL"]))
			{
				foreach($arOptions["RETURN_URL"] as $key => $url)
					if(!empty($url) && array_key_exists($key, $return_url))
						$return_url[$key] = $url;
			}
			elseif(!empty($arOptions["RETURN_URL"]))
			{
				foreach($return_url as $key => $url)
					$return_url[$key] = $arOptions["RETURN_URL"];
			}
		}

		$str = "";
		foreach($return_url as $key => $url)
		{
			if(empty($url))
			{
				if(empty($str))
				{
					$str = \Bitrix\Main\Context::getCurrent()->getServer()->getRequestUri();
					if(defined("BX_AJAX_PARAM_ID"))
						$str = CHTTP::urlDeleteParams($str, array(BX_AJAX_PARAM_ID));
				}

				$return_url[$key] = $str;
			}
		}

		$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
		if (self::$workflowIncluded === null)
			self::$workflowIncluded = \Bitrix\Main\Loader::includeModule('workflow');
		$bWorkflow = self::$workflowIncluded && ($arIBlock["WORKFLOW"] !== "N");
		$s = $bWorkflow? "&WF=Y": "";

		$arLabels = ($arOptions["LABELS"] ?? []);
		$labelList = [
			'ELEMENT_EDIT_TEXT' => 'ELEMENT_EDIT',
			'ELEMENT_EDIT_TITLE' => 'ELEMENT_EDIT',
			'ELEMENT_ADD_TEXT' => 'ELEMENT_ADD',
			'ELEMENT_ADD_TITLE' => 'ELEMENT_ADD',
			'ELEMENT_DELETE_TEXT' => 'ELEMENT_DELETE',
			'ELEMENT_DELETE_TITLE' => 'ELEMENT_DELETE',
			'SECTION_EDIT_TEXT' => 'SECTION_EDIT',
			'SECTION_EDIT_TITLE' => 'SECTION_EDIT',
			'SECTION_ADD_TEXT' => 'SECTION_ADD',
			'SECTION_ADD_TITLE' => 'SECTION_ADD',
			'SECTION_DELETE_TEXT' => 'SECTION_DELETE',
			'SECTION_DELETE_TITLE' => 'SECTION_DELETE',
			'ELEMENTS_NAME_TEXT' => 'ELEMENTS_NAME',
			'ELEMENTS_NAME_TITLE' => 'ELEMENTS_NAME',
			'SECTIONS_NAME_TEXT' => 'SECTIONS_NAME',
			'SECTIONS_NAME_TITLE' => 'SECTIONS_NAME',
		];
		foreach ($labelList as $phraseId => $iblockPhrase)
		{
			if (
				isset($arLabels[$phraseId])
				&& is_string($arLabels[$phraseId])
				&& $arLabels[$phraseId] !== ''
			)
			{
				continue;
			}
			$arLabels[$phraseId] = $arIBlock[$iblockPhrase];
		}

		if($ELEMENT_ID > 0 && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_edit"))
		{
			$url = "/bitrix/admin/".CIBlock::GetAdminElementEditLink($IBLOCK_ID, $ELEMENT_ID, array(
				"force_catalog" => $bCatalog,
				"filter_section" => $SECTION_ID,
				"bxpublic" => "Y",
				"from_module" => "iblock",
				"return_url" => $return_url["edit_element"],
			)).$s;

			$action = $APPLICATION->GetPopupLink(
				array(
					"URL" => $url,
					"PARAMS" => $windowParams,
				)
			);

			$arButton = array(
				"TEXT" => $arLabels["ELEMENT_EDIT_TEXT"],
				"TITLE" => $arLabels["ELEMENT_EDIT_TITLE"],
				"ACTION" => 'javascript:'.$action,
				"ACTION_URL" => $url,
				"ONCLICK" => $action,
				"DEFAULT" => $APPLICATION->GetPublicShowMode() !== 'configure',
				"ICON" => "bx-context-toolbar-edit-icon",
				"ID" => "bx-context-toolbar-edit-element"
			);
			$arButtons["edit"]["edit_element"] = $arButton;
			$arButtons["configure"]["edit_element"] = $arButton;

			$url = str_replace("&bxpublic=Y&from_module=iblock", "", $url);
			$arButton["ACTION"] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
			unset($arButton["ONCLICK"]);
			$arButtons["submenu"]["edit_element"] = $arButton;

			if($bWorkflow)
			{
				$url = "/bitrix/admin/iblock_history_list.php?type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&ELEMENT_ID=".$ELEMENT_ID."&filter_section=".$SECTION_ID."&return_url=".UrlEncode($return_url["history_element"]);
				$arButton = array(
					"TEXT" => Loc::getMessage("IBLOCK_PANEL_HISTORY_BUTTON"),
					"TITLE" => Loc::getMessage("IBLOCK_PANEL_HISTORY_BUTTON"),
					"ACTION" => "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
					"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
					"ID" => "bx-context-toolbar-history-element"
				);
				$arButtons["submenu"]["history_element"] = $arButton;
			}
		}

		if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_element_bind"))
		{
			$params = array(
				"force_catalog" => $bCatalog,
				"filter_section" => $SECTION_ID,
				"IBLOCK_SECTION_ID" => $SECTION_ID,
				"bxpublic" => "Y",
				"from_module" => "iblock",
				"return_url" => $return_url["add_element"],
			);

			if ($useCatalogButtons)
			{
				CCatalogAdminTools::setProductFormParams();
				CCatalogAdminTools::setCatalogPanelButtons($arButtons, $IBLOCK_ID, $catalogButtons, $params, $windowParams);
			}
			else
			{
				$url = "/bitrix/admin/".CIBlock::GetAdminElementEditLink($IBLOCK_ID, null, $params);
				$action = $APPLICATION->GetPopupLink(
					array(
						"URL" => $url,
						"PARAMS" => $windowParams,
					)
				);
				$arButton = array(
					"TEXT" => $arLabels["ELEMENT_ADD_TEXT"],
					"TITLE" => $arLabels["ELEMENT_ADD_TITLE"],
					"ACTION" => 'javascript:'.$action,
					"ACTION_URL" => $url,
					"ONCLICK" => $action,
					"ICON" => "bx-context-toolbar-create-icon",
					"ID" => "bx-context-toolbar-add-element",
				);
				$arButtons["edit"]["add_element"] = $arButton;
				$arButtons["configure"]["add_element"] = $arButton;
				$arButtons["intranet"][] = array(
					'TEXT' => $arButton["TEXT"],
					'TITLE' => $arButton["TITLE"],
					'ICON' => 'add',
					'ONCLICK' => $arButton["ACTION"],
					'SORT' => 1000,
				);

				$url = str_replace("&bxpublic=Y&from_module=iblock", "", $url);
				$arButton["ACTION"] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
				unset($arButton["ONCLICK"]);
				$arButtons["submenu"]["add_element"] = $arButton;
			}
		}

		if($ELEMENT_ID > 0 && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_delete"))
		{
			//Delete Element
			if(!empty($arButtons["edit"]))
				$arButtons["edit"][] = array("SEPARATOR" => "Y", "HREF" => "");
			if(!empty($arButtons["configure"]))
				$arButtons["configure"][] = array("SEPARATOR" => "Y", "HREF" => "");
			if(!empty($arButtons["submenu"]))
				$arButtons["submenu"][] = array("SEPARATOR" => "Y", "HREF" => "");

			$url = CIBlock::GetAdminElementListLink($IBLOCK_ID, array('action'=>'delete'));
			if($bSessID)
				$url .= '&'.bitrix_sessid_get();
			$url .= '&ID='.(preg_match('/^iblock_list_admin\.php/', $url)? "E": "").$ELEMENT_ID."&return_url=".UrlEncode($return_url["delete_element"]);
			$url = "/bitrix/admin/".$url;
			$arButton = array(
				"TEXT" => $arLabels["ELEMENT_DELETE_TEXT"],
				"TITLE" => $arLabels["ELEMENT_DELETE_TITLE"],
				"ACTION"=>"javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage("IBLOCK_PANEL_ELEMENT_DEL_CONF"))."'))jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ACTION_URL" => $url,
				"ONCLICK"=>"if(confirm('".CUtil::JSEscape(Loc::getMessage("IBLOCK_PANEL_ELEMENT_DEL_CONF"))."'))jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ICON" => "bx-context-toolbar-delete-icon",
				"ID" => "bx-context-toolbar-delete-element"
			);
			$arButtons["edit"]["delete_element"] = $arButton;
			$arButtons["configure"]["delete_element"] = $arButton;
			$arButtons["submenu"]["delete_element"] = $arButton;
		}

		if($ELEMENT_ID <= 0 && $bSectionButtons)
		{
			$rsIBTYPE = CIBlockType::GetByID($arIBlock["IBLOCK_TYPE_ID"]);
			if(($arIBTYPE = $rsIBTYPE->Fetch()) && ($arIBTYPE["SECTIONS"] == "Y"))
			{
				if($SECTION_ID > 0 && CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_edit"))
				{
					if(!empty($arButtons["edit"]))
						$arButtons["edit"][] = array("SEPARATOR" => "Y", "HREF" => "");
					if(!empty($arButtons["configure"]))
						$arButtons["configure"][] = array("SEPARATOR" => "Y", "HREF" => "");
					if(!empty($arButtons["submenu"]))
						$arButtons["submenu"][] = array("SEPARATOR" => "Y", "HREF" => "");

					$url = "/bitrix/admin/".CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $SECTION_ID, array(
						"force_catalog" => $bCatalog,
						"filter_section" => $SECTION_ID,
						"bxpublic" => "Y",
						"from_module" => "iblock",
						"return_url" => $return_url["edit_section"],
					));

					$action = $APPLICATION->GetPopupLink(
						array(
							"URL" => $url,
							"PARAMS" => $windowParams,
						)
					);

					$arButton = array(
						"TEXT" => $arLabels["SECTION_EDIT_TEXT"],
						"TITLE" => $arLabels["SECTION_EDIT_TITLE"],
						"ACTION" => 'javascript:'.$action,
						"ACTION_URL" => $url,
						"ICON" => "bx-context-toolbar-edit-icon",
						"ONCLICK" => $action,
						"DEFAULT" => $APPLICATION->GetPublicShowMode() !== 'configure',
						"ID" => "bx-context-toolbar-edit-section"
					);
					$arButtons["edit"]["edit_section"] = $arButton;
					$arButtons["configure"]["edit_section"] = $arButton;

					$url = str_replace("&bxpublic=Y&from_module=iblock", "", $url);
					$arButton["ACTION"] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
					unset($arButton["ONCLICK"]);
					$arButtons["submenu"]["edit_section"] = $arButton;
				}

				if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_section_bind"))
				{
					$url = "/bitrix/admin/".CIBlock::GetAdminSectionEditLink($IBLOCK_ID, null, array(
						"force_catalog" => $bCatalog,
						"IBLOCK_SECTION_ID" => $SECTION_ID,
						"filter_section" => $SECTION_ID,
						"bxpublic" => "Y",
						"from_module" => "iblock",
						"return_url" => $return_url["add_section"],
					));

					$action = $APPLICATION->GetPopupLink(
						array(
							"URL" => $url,
							"PARAMS" => $windowParams,
						)
					);

					$arButton = array(
						"TEXT" => $arLabels["SECTION_ADD_TEXT"],
						"TITLE" => $arLabels["SECTION_ADD_TITLE"],
						"ACTION" => 'javascript:'.$action,
						"ACTION_URL" => $url,
						"ICON" => "bx-context-toolbar-create-icon",
						"ID" => "bx-context-toolbar-add-section",
						"ONCLICK" => $action
					);

					$arButtons["edit"]["add_section"] = $arButton;
					$arButtons["configure"]["add_section"] = $arButton;

					$url = str_replace("&bxpublic=Y&from_module=iblock", "", $url);
					$arButton["ACTION"] = "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')";
					unset($arButton["ONCLICK"]);
					$arButtons["submenu"]["add_section"] = $arButton;
				}

				//Delete section
				if($SECTION_ID > 0 && CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_delete"))
				{
					$url = CIBlock::GetAdminSectionListLink($IBLOCK_ID, Array('action'=>'delete'));
					if($bSessID)
						$url .= '&'.bitrix_sessid_get();
					$url .= '&ID[]='.(preg_match('/^iblock_list_admin\.php/', $url)? "S": "").$SECTION_ID."&return_url=".UrlEncode($return_url["delete_section"]);
					$url = "/bitrix/admin/".$url;

					$arButton = array(
						"TEXT" => $arLabels["SECTION_DELETE_TEXT"],
						"TITLE" => $arLabels["SECTION_DELETE_TITLE"],
						"ACTION" => "javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage("IBLOCK_PANEL_SECTION_DEL_CONF"))."'))jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
						"ACTION_URL" => $url,
						"ONCLICK" => "if(confirm('".CUtil::JSEscape(Loc::getMessage("IBLOCK_PANEL_SECTION_DEL_CONF"))."'))jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
						"ICON" => "bx-context-toolbar-delete-icon",
						"ID" => "bx-context-toolbar-delete-section"
					);
					$arButtons["edit"]["delete_section"] = $arButton;
					$arButtons["configure"]["delete_section"] = $arButton;
					$arButtons["submenu"]["delete_section"] = $arButton;
				}
			}
		}

		if( ($IBLOCK_ID > 0) && CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display") )
		{
			if(!empty($arButtons["submenu"]))
				$arButtons["submenu"][] = array("SEPARATOR" => "Y", "HREF" => "");

			if($SECTION_ID > 0)
				$url = "/bitrix/admin/".CIBlock::GetAdminElementListLink($IBLOCK_ID , array('find_section_section'=>$SECTION_ID));
			else
				$url = "/bitrix/admin/".CIBlock::GetAdminElementListLink($IBLOCK_ID , array(
					'find_el_y'=>'Y', 'clear_filter'=>'Y', 'apply_filter'=>'Y'));

			$arButton = array(
				"TEXT" => (($arLabels["ELEMENTS_NAME_TEXT"] ?? '') <> ''? $arLabels["ELEMENTS_NAME_TEXT"] : $arIBlock["ELEMENTS_NAME"]),
				"TITLE" => (($arLabels["ELEMENTS_NAME_TITLE"] ?? '') <> ''? $arLabels["ELEMENTS_NAME_TITLE"] : $arIBlock["ELEMENTS_NAME"]),
				"ACTION" => "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ACTION_URL" => $url,
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ID" => "bx-context-toolbar-elements-list"
			);
			$arButtons["submenu"]["element_list"] = $arButton;

			$arButtons["intranet"]["element_list"] = array(
				'TEXT' => $arButton["TEXT"],
				'TITLE' => $arButton["TITLE"],
				'ICON' => 'settings',
				'ONCLICK' => $arButton["ACTION"],
				'SORT' => 1010,
			);

			$url = "/bitrix/admin/".CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$SECTION_ID));
			$arButton = array(
				"TEXT" => (($arLabels["SECTIONS_NAME_TEXT"] ?? '') <> ''? $arLabels["SECTIONS_NAME_TEXT"] : $arIBlock["SECTIONS_NAME"]),
				"TITLE" => (($arLabels["SECTIONS_NAME_TITLE"] ?? '') <> ''? $arLabels["SECTIONS_NAME_TITLE"] : $arIBlock["SECTIONS_NAME"]),
				"ACTION" => "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ACTION_URL" => $url,
				"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
				"ID" => "bx-context-toolbar-sections-list"
			);
			$arButtons["submenu"]["section_list"] = $arButton;

			if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit"))
			{
				$url = "/bitrix/admin/iblock_edit.php?type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&ID=".$IBLOCK_ID."&return_url=".UrlEncode($return_url["edit_iblock"]);
				$arButton = array(
					"TEXT" => Loc::getMessage("IBLOCK_PANEL_EDIT_IBLOCK_BUTTON", array("#IBLOCK_NAME#"=>$arIBlock["NAME"])),
					"TITLE" => Loc::getMessage("IBLOCK_PANEL_EDIT_IBLOCK_BUTTON", array("#IBLOCK_NAME#"=>$arIBlock["NAME"])),
					"ACTION" => "javascript:jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
					"ACTION_URL" => $url,
					"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($url)."')",
					"ID" => "bx-context-toolbar-edit-iblock"
				);
				$arButtons["submenu"]["edit_iblock"] = $arButton;
			}
		}

		return $arButtons;
	}

	/**
	 * @param int $iblock_id
	 * @return CDBResult
	 */
	public static function GetSite($iblock_id)
	{
		/** @global CDatabase $DB */
		global $DB;

		$strSql = "SELECT L.*, BS.* FROM b_iblock_site BS, b_lang L WHERE L.LID=BS.SITE_ID AND BS.IBLOCK_ID=".intval($iblock_id);
		return $DB->Query($strSql);
	}

	///////////////////////////////////////////////////////////////////
	// Block by ID
	///////////////////////////////////////////////////////////////////
	public static function GetByID($ID)
	{
		return CIBlock::GetList(Array(), Array("ID"=>$ID));
	}

	/**
	 * @param int $ID
	 * @param string $FIELD
	 * @return mixed
	 */
	public static function GetArrayByID($ID, $FIELD = "")
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if(CACHED_b_iblock === false)
		{
			$res = $DB->Query("
				SELECT b_iblock.*,".$DB->DateToCharFunction("TIMESTAMP_X")." TIMESTAMP_X
				from  b_iblock
				WHERE ID = ".$ID
			);
			$arResult = $res->Fetch();
			if($arResult)
			{
				$arMessages = CIBlock::GetMessages($ID);
				$arResult = array_merge($arResult, $arMessages);
				$arResult["FIELDS"] = CIBlock::GetFields($ID);
			}
		}
		else
		{
			global $CACHE_MANAGER;

			$bucket_size = intval(CACHED_b_iblock_bucket_size);
			if($bucket_size<=0) $bucket_size = 20;

			$bucket = intval($ID/$bucket_size);
			$cache_id = $bucket_size."iblock".$bucket;

			if($CACHE_MANAGER->Read(CACHED_b_iblock, $cache_id, "b_iblock"))
			{
				$arIBlocks = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arIBlocks = array();
				$res = $DB->Query("
					SELECT b_iblock.*,".$DB->DateToCharFunction("TIMESTAMP_X")." TIMESTAMP_X
					from  b_iblock
					WHERE ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)
				);
				while($arIBlock = $res->Fetch())
				{
					$arMessages = CIBlock::GetMessages($arIBlock["ID"]);
					$arIBlock = array_merge($arIBlock, $arMessages);
					$arIBlock["FIELDS"] = CIBlock::GetFields($arIBlock["ID"]);
					$arIBlocks[$arIBlock["ID"]] = $arIBlock;
				}

				$CACHE_MANAGER->Set($cache_id, $arIBlocks);
			}

			if(isset($arIBlocks[$ID]))
			{
				$arResult = $arIBlocks[$ID];

				if(!array_key_exists("ELEMENT_DELETE", $arResult))
				{
					$arMessages = CIBlock::GetMessages($ID);
					$arResult = array_merge($arResult, $arMessages);
					CIBlock::CleanCache($ID);
				}

				if (
					!array_key_exists("FIELDS", $arResult)
					|| !is_array($arResult["FIELDS"]["IBLOCK_SECTION"]["DEFAULT_VALUE"])
				)
				{
					$arResult["FIELDS"] = CIBlock::GetFields($ID);
					CIBlock::CleanCache($ID);
				}
			}
			else
			{
				$arResult = false;
			}
		}
		if (empty($arResult))
			return false;

		if ($FIELD)
		{
			if (array_key_exists($FIELD, $arResult))
				return $arResult[$FIELD];
			else
				return null;
		}
		else
		{
			return $arResult;
		}
	}

	public static function CleanCache($ID)
	{
		/** @global CCacheManager $CACHE_MANAGER */
		global $CACHE_MANAGER;

		$ID = intval($ID);
		if(CACHED_b_iblock !== false)
		{
			$bucket_size = intval(CACHED_b_iblock_bucket_size);
			if($bucket_size<=0) $bucket_size = 20;

			$bucket = intval($ID/$bucket_size);
			$cache_id = $bucket_size."iblock".$bucket;

			$CACHE_MANAGER->Clean($cache_id, "b_iblock");
		}
		Iblock\IblockTable::cleanCache();
		Iblock\IblockSiteTable::cleanCache();
	}

	///////////////////////////////////////////////////////////////////
	// New block
	///////////////////////////////////////////////////////////////////
	function Add($arFields)
	{
		/** @global CCacheManager $CACHE_MANAGER */
		global $CACHE_MANAGER;
		/** @global CDatabase $DB */
		global $DB;
		$SAVED_PICTURE = null;

		//Default Yes
		$arFields["ACTIVE"] = isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] === "N"? "N": "Y";
		$arFields["WORKFLOW"] = isset($arFields["WORKFLOW"]) && $arFields["WORKFLOW"] === "N"? "N": "Y";
		$arFields["INDEX_ELEMENT"] = isset($arFields["INDEX_ELEMENT"]) && $arFields["INDEX_ELEMENT"] === "N"? "N": "Y";
		//Default No
		$arFields["BIZPROC"] = isset($arFields["BIZPROC"]) && $arFields["BIZPROC"] === "Y"? "Y": "N";
		$arFields["INDEX_SECTION"] = isset($arFields["INDEX_SECTION"]) && $arFields["INDEX_SECTION"] === "Y"? "Y": "N";

		if(!isset($arFields["SECTION_CHOOSER"]))
			$arFields["SECTION_CHOOSER"] = "L";
		elseif($arFields["SECTION_CHOOSER"] !== "D" && $arFields["SECTION_CHOOSER"] !== "P")
			$arFields["SECTION_CHOOSER"] = "L";

		if(!isset($arFields["DESCRIPTION_TYPE"]) || $arFields["DESCRIPTION_TYPE"] !== "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		$arFields["VERSION"] = isset($arFields["VERSION"]) && intval($arFields["VERSION"]) === 2? "2": "1";

		if(isset($arFields["RIGHTS_MODE"]))
			$arFields["RIGHTS_MODE"] =  $arFields["RIGHTS_MODE"] === "E"? "E": "S";
		elseif(isset($arFields["RIGHTS"]))
			$arFields["RIGHTS_MODE"] = "E";
		else
			$arFields["RIGHTS_MODE"] = "S";

		if (array_key_exists("PICTURE", $arFields))
		{
			if(
				!is_array($arFields["PICTURE"])
				|| (
					($arFields["PICTURE"]["name"] ?? '') === ''
					&& ($arFields["PICTURE"]["del"] ?? '' ) === ''
				)
			)
			{
				unset($arFields["PICTURE"]);
			}
			else
			{
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
			}
		}

		if(array_key_exists("SITE_ID", $arFields))
		{
			$arFields["LID"] = $arFields["SITE_ID"];
			unset($arFields["SITE_ID"]);
		}

		if(array_key_exists("EXTERNAL_ID", $arFields))
		{
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];
			unset($arFields["EXTERNAL_ID"]);
		}

		if(array_key_exists("SECTION_PROPERTY", $arFields))
			$arFields["SECTION_PROPERTY"] = "Y";

		unset($arFields["ID"]);

		if(!$this->CheckFields($arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			$arLID = array();
			if(array_key_exists("LID", $arFields))
			{
				if(is_array($arFields["LID"]))
				{
					foreach($arFields["LID"] as $site_id)
						$arLID[$site_id] = $DB->ForSQL($site_id);
				}
				else
				{
					$arLID[$arFields["LID"]] = $DB->ForSQL($arFields["LID"]);
				}
			}

			if(empty($arLID))
				unset($arFields["LID"]);
			else
				$arFields["LID"] = end($arLID);

			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			$ID = $DB->Add("b_iblock", $arFields, array("DESCRIPTION"), "iblock");

			if(array_key_exists("PICTURE", $arFields))
			{
				$arFields["PICTURE"] = $SAVED_PICTURE;
			}

			$this->SetMessages($ID, $arFields);

			if(array_key_exists("FIELDS", $arFields) && is_array($arFields["FIELDS"]))
				$this->SetFields($ID, $arFields["FIELDS"]);

			if($arFields["RIGHTS_MODE"] === "E")
			{
				if(
					!array_key_exists("RIGHTS", $arFields)
					&& array_key_exists("GROUP_ID", $arFields)
					&& is_array($arFields["GROUP_ID"])
				)
				{
					$obIBlockRights = new CIBlockRights($ID);
					$obIBlockRights->SetRights($obIBlockRights->ConvertGroups($arFields["GROUP_ID"]));
				}
				elseif(
					array_key_exists("RIGHTS", $arFields)
					&& is_array($arFields["RIGHTS"])
				)
				{
					$obIBlockRights = new CIBlockRights($ID);
					$obIBlockRights->SetRights($arFields["RIGHTS"]);
				}
			}
			else
			{
				if(array_key_exists("GROUP_ID", $arFields) && is_array($arFields["GROUP_ID"]))
					$this->SetPermission($ID, $arFields["GROUP_ID"]);
			}

			if (array_key_exists("IPROPERTY_TEMPLATES", $arFields))
			{
				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($ID);
				$ipropTemplates->set($arFields["IPROPERTY_TEMPLATES"]);
			}

			if(!empty($arLID))
			{
				$DB->Query("
					DELETE FROM b_iblock_site WHERE IBLOCK_ID = ".$ID."
				");

				$DB->Query("
					INSERT INTO b_iblock_site(IBLOCK_ID, SITE_ID)
					SELECT ".$ID.", LID
					FROM b_lang
					WHERE LID IN ('".implode("', '", $arLID)."')
				");
			}

			if($arFields["VERSION"] == 2)
			{
				if($this->_Add($ID))
				{
					$Result = $ID;
					$arFields["ID"] = &$ID;
				}
				else
				{
					$this->LAST_ERROR = Loc::getMessage("IBLOCK_TABLE_CREATION_ERROR");
					$Result = false;
					$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
				}
			}
			else
			{
				$Result = $ID;
				$arFields["ID"] = &$ID;
			}

			CDiskQuota::recalculateDb();

			$this->CleanCache($ID);
		}

		$arFields["RESULT"] = &$Result;

		foreach(GetModuleEvents("iblock", "OnAfterIBlockAdd", true)  as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if(defined("BX_COMP_MANAGED_CACHE") && self::isEnabledClearTagCache())
			$CACHE_MANAGER->ClearByTag("iblock_id_new");

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Update
	///////////////////////////////////////////////////////////////////
	function Update($ID, $arFields)
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}
		$SAVED_PICTURE = null;

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		if (array_key_exists("PICTURE", $arFields))
		{
			if (
				!is_array($arFields["PICTURE"])
				|| (
					($arFields["PICTURE"]["name"] ?? '') === ''
					&& ($arFields["PICTURE"]["del"] ?? '') === ''
				)
			)
			{
				unset($arFields["PICTURE"]);
			}
			else
			{
				$pic_res = $DB->Query("SELECT PICTURE FROM b_iblock WHERE ID=".$ID);
				if($pic_res = $pic_res->Fetch())
				{
					$arFields["PICTURE"]["old_file"] = $pic_res["PICTURE"];
				}
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
			}
		}

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(is_set($arFields, "WORKFLOW") && $arFields["WORKFLOW"]!="N")
			$arFields["WORKFLOW"]="Y";

		if(is_set($arFields, "BIZPROC") && $arFields["BIZPROC"]!="Y")
			$arFields["BIZPROC"]="N";

		if(is_set($arFields, "SECTION_CHOOSER") && $arFields["SECTION_CHOOSER"]!="D" && $arFields["SECTION_CHOOSER"]!="P")
			$arFields["SECTION_CHOOSER"]="L";

		if(is_set($arFields, "INDEX_SECTION") && $arFields["INDEX_SECTION"]!="Y")
			$arFields["INDEX_SECTION"]="N";

		if(is_set($arFields, "INDEX_ELEMENT") && $arFields["INDEX_ELEMENT"]!="Y")
			$arFields["INDEX_ELEMENT"]="N";

		if(is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"]!="html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if(is_set($arFields, "SITE_ID"))
			$arFields["LID"] = $arFields["SITE_ID"];

		if(is_set($arFields, "SECTION_PROPERTY"))
			$arFields["SECTION_PROPERTY"] = "Y";

		if(is_set($arFields, "PROPERTY_INDEX") && $arFields["PROPERTY_INDEX"]!="I" && $arFields["PROPERTY_INDEX"]!="Y")
			$arFields["SECTION_PROPERTY"] = "N";

		$RIGHTS_MODE = CIBlock::GetArrayByID($ID, "RIGHTS_MODE");

		if(!$this->CheckFields($arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			$arLID = array();
			$str_LID = "";
			if(is_set($arFields, "LID"))
			{
				if(is_array($arFields["LID"]))
					$arLID = $arFields["LID"];
				else
					$arLID[] = $arFields["LID"];

				$arFields["LID"] = false;
				$str_LID = "''";
				foreach($arLID as $v)
				{
					$arFields["LID"] = $v;
					$str_LID .= ", '".$DB->ForSql($v)."'";
				}
			}

			unset($arFields["ID"]);
			unset($arFields["VERSION"]);

			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			$strUpdate = $DB->PrepareUpdate("b_iblock", $arFields, "iblock");

			if(array_key_exists("PICTURE", $arFields))
				$arFields["PICTURE"] = $SAVED_PICTURE;

			$arBinds=Array();
			if(is_set($arFields, "DESCRIPTION"))
				$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];

			if($strUpdate <> '')
			{
				$strSql = "UPDATE b_iblock SET ".$strUpdate." WHERE ID=".$ID;
				$DB->QueryBind($strSql, $arBinds);
			}

			$this->SetMessages($ID, $arFields);
			if(isset($arFields["FIELDS"]) && is_array($arFields["FIELDS"]))
				$this->SetFields($ID, $arFields["FIELDS"]);

			if(array_key_exists("RIGHTS_MODE", $arFields))
			{
				if($arFields["RIGHTS_MODE"] === "E" && $RIGHTS_MODE !== "E")
				{
					CIBlock::SetPermission($ID, array());
				}
				elseif($arFields["RIGHTS_MODE"] !== "E" && $RIGHTS_MODE === "E")
				{
					$obIBlockRights = new CIBlockRights($ID);
					$obIBlockRights->DeleteAllRights();
				}

				if($arFields["RIGHTS_MODE"] === "E")
					$RIGHTS_MODE = "E";
			}

			if($RIGHTS_MODE === "E")
			{
				if(
					!array_key_exists("RIGHTS", $arFields)
					&& array_key_exists("GROUP_ID", $arFields)
					&& is_array($arFields["GROUP_ID"])
				)
				{
					$obIBlockRights = new CIBlockRights($ID);
					$obIBlockRights->SetRights($obIBlockRights->ConvertGroups($arFields["GROUP_ID"]));
				}
				elseif(
					array_key_exists("RIGHTS", $arFields)
					&& is_array($arFields["RIGHTS"])
				)
				{
					$obIBlockRights = new CIBlockRights($ID);
					$obIBlockRights->SetRights($arFields["RIGHTS"]);
				}
			}
			else
			{
				if(array_key_exists("GROUP_ID", $arFields) && is_array($arFields["GROUP_ID"]))
					CIBlock::SetPermission($ID, $arFields["GROUP_ID"]);
			}

			if (array_key_exists("IPROPERTY_TEMPLATES", $arFields))
			{
				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($ID);
				$ipropTemplates->set($arFields["IPROPERTY_TEMPLATES"]);
			}

			if(!empty($arLID))
			{
				$strSql = "DELETE FROM b_iblock_site WHERE IBLOCK_ID=".$ID;
				$DB->Query($strSql);

				$strSql =
					"INSERT INTO b_iblock_site(IBLOCK_ID, SITE_ID) ".
					"SELECT ".$ID.", LID ".
					"FROM b_lang ".
					"WHERE LID IN (".$str_LID.") ";
				$DB->Query($strSql);
			}

			if(CModule::IncludeModule("search"))
			{
				$dbAfter = $DB->Query("SELECT ACTIVE FROM b_iblock WHERE ID=".$ID);
				$arAfter = $dbAfter->Fetch();
				if($arAfter["ACTIVE"] != "Y")
					CSearch::DeleteIndex("iblock", false, false, $ID);
			}

			CDiskQuota::recalculateDb();

			$Result = true;
		}

		$this->CleanCache($ID);

		$arFields["ID"] = $ID;
		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		self::clearIblockTagCache($ID);

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Function deletes iblock by ID
	///////////////////////////////////////////////////////////////////
	public static function Delete($ID)
	{
		/** @global CDatabase $DB */
		global $DB;
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}

		$APPLICATION->ResetException();
		foreach(GetModuleEvents("iblock", "OnBeforeIBlockDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID)) === false)
			{
				$err = Loc::getMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				$ex = $APPLICATION->GetException();
				if(is_object($ex))
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}
		}

		foreach (GetModuleEvents("iblock", "OnIBlockDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		$iblockSections = CIBlockSection::GetList(Array(), Array(
			"IBLOCK_ID" => $ID,
			"DEPTH_LEVEL" => 1,
			"CHECK_PERMISSIONS" => "N",
		), false, Array("ID"));
		while($iblockSection = $iblockSections->Fetch())
		{
			if(!CIBlockSection::Delete($iblockSection["ID"], false))
				return false;
		}

		$iblockElements = CIBlockElement::GetList(Array(), Array(
			"IBLOCK_ID" => $ID,
			"SHOW_NEW" => "Y",
			"CHECK_PERMISSIONS" => "N",
		), false, false, array("IBLOCK_ID", "ID"));
		while($iblockElement = $iblockElements->Fetch())
		{
			if(!CIBlockElement::Delete($iblockElement["ID"]))
				return false;
		}

		$props = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID" => $ID,
				"CHECK_PERMISSIONS" =>"N",
		));
		while($property = $props->Fetch())
		{
			if(!CIBlockProperty::Delete($property["ID"]))
				return false;
		}

		CFile::Delete(self::GetArrayByID($ID , "PICTURE"));

		$seq = new CIBlockSequence($ID);
		$seq->Drop(true);

		$obIBlockRights = new CIBlockRights($ID);
		$obIBlockRights->DeleteAllRights();

		$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($ID);
		$ipropTemplates->delete();

		CIBlockSectionPropertyLink::DeleteByIBlock($ID);

		$DB->Query("delete from b_iblock_offers_tmp where PRODUCT_IBLOCK_ID=".$ID);
		$DB->Query("delete from b_iblock_offers_tmp where OFFERS_IBLOCK_ID=".$ID);

		if(!$DB->Query("DELETE FROM b_iblock_messages WHERE IBLOCK_ID = ".$ID))
			return false;

		if(!$DB->Query("DELETE FROM b_iblock_fields WHERE IBLOCK_ID = ".$ID))
			return false;

		$USER_FIELD_MANAGER->OnEntityDelete("IBLOCK_".$ID."_SECTION");

		if(!$DB->Query("DELETE FROM b_iblock_group WHERE IBLOCK_ID=".$ID))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock_rss WHERE IBLOCK_ID=".$ID))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock_site WHERE IBLOCK_ID=".$ID))
			return false;
		if(!$DB->Query("DELETE FROM b_iblock WHERE ID=".$ID))
			return false;

		$DB->DDL("DROP TABLE IF EXISTS b_iblock_element_prop_s".$ID, true);
		$DB->DDL("DROP TABLE IF EXISTS b_iblock_element_prop_m".$ID, true);
		$DB->DDL("DROP SEQUENCE IF EXISTS sq_b_iblock_element_prop_m".$ID, true);

		CIBlock::CleanCache($ID);

		foreach(GetModuleEvents("iblock", "OnAfterIBlockDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		self::clearIblockTagCache($ID);

		CDiskQuota::recalculateDb();

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Check function called from Add and Update
	///////////////////////////////////////////////////////////////////
	function CheckFields(&$arFields, $ID=false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$this->LAST_ERROR = "";

		if ($ID !== false)
		{
			$ID = (int)$ID;
			if ($ID <= 0)
			{
				return false;
			}
		}

		$NAME = $arFields["NAME"] ?? "";
		if(
			($ID===false || array_key_exists("NAME", $arFields))
			&& (string)$NAME === ''
		)
			$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_NAME")."<br>";

		if($ID===false && !is_set($arFields, "IBLOCK_TYPE_ID"))
			$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_BLOCK_TYPE")."<br>";

		if($ID===false)
		{
			//For new record take default values
			$WORKFLOW = array_key_exists("WORKFLOW", $arFields)? $arFields["WORKFLOW"]: "Y";
			$BIZPROC  = array_key_exists("BIZPROC",  $arFields)? $arFields["BIZPROC"]:  "N";
		}
		else
		{
			//For existing one read old values
			$arIBlock = CIBlock::GetArrayByID($ID);
			if (!is_array($arIBlock))
			{
				$this->LAST_ERROR .= Loc::getMessage('IBLOCK_ERR_IBLOCK_IS_ABSENT') . '<br>';
			}
			$WORKFLOW = array_key_exists("WORKFLOW", $arFields)? $arFields["WORKFLOW"]: $arIBlock["WORKFLOW"];
			$BIZPROC  = array_key_exists("BIZPROC",  $arFields)? $arFields["BIZPROC"]:  $arIBlock["BIZPROC"];
			if($BIZPROC != "Y") $BIZPROC = "N";//This is cache compatibility issue
		}

		if($WORKFLOW == "Y" && $BIZPROC == "Y")
			$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_WORKFLOW_AND_BIZPROC")."<br>";

		if(is_set($arFields, "IBLOCK_TYPE_ID"))
		{
			$r = CIBlockType::GetByID($arFields["IBLOCK_TYPE_ID"]);
			if(!$r->Fetch())
				$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_BLOCK_TYPE_ID")."<br>";
		}

		if(
			isset($arFields["PICTURE"])
			&& is_array($arFields["PICTURE"])
			&& array_key_exists("bucket", $arFields["PICTURE"])
			&& is_object($arFields["PICTURE"]["bucket"])
		)
		{
			//This is trusted image from xml import
		}
		elseif(
			isset($arFields["PICTURE"])
			&& is_array($arFields["PICTURE"])
			&& isset($arFields["PICTURE"]["name"])
		)
		{
			$error = CFile::CheckImageFile($arFields["PICTURE"]);
			if ($error <> '')
				$this->LAST_ERROR .= $error."<br>";
		}

		if(
			($ID===false && !is_set($arFields, "LID")) ||
			(is_set($arFields, "LID")
			&& (
				(is_array($arFields["LID"]) && count($arFields["LID"])<=0)
				||
				(!is_array($arFields["LID"]) && $arFields["LID"] == '')
				)
			)
		)
		{
			$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_SITE_ID_NA")."<br>";
		}
		elseif(is_set($arFields, "LID"))
		{
			if(!is_array($arFields["LID"]))
				$arFields["LID"] = Array($arFields["LID"]);

			foreach($arFields["LID"] as $v)
			{
				$r = CSite::GetByID($v);
				if(!$r->Fetch())
					$this->LAST_ERROR .= "'".$v."' - ".Loc::getMessage("IBLOCK_BAD_SITE_ID")."<br>";
			}
		}

		if (is_set($arFields, "API_CODE"))
		{
			if ($arFields['API_CODE'] == '')
			{
				$arFields['API_CODE'] = false;
			}
			else
			{
				if (!preg_match('/^[a-z][a-z0-9]{0,49}$/i', $arFields['API_CODE']))
				{
					$this->LAST_ERROR .= Loc::getMessage("IBLOCK_FIELD_API_CODE_FORMAT_ERROR").'<br>';
				}
				else
				{
					// check for uniqueness
					$count = Iblock\IblockTable::getCount(Main\ORM\Query\Query::filter()
						->where('API_CODE', $arFields['API_CODE'])
						->whereNot('ID', $ID)
					);

					if ($count > 0)
					{
						$this->LAST_ERROR .= Loc::getMessage("IBLOCK_FIELD_API_CODE_UNIQUE_ERROR").'<br>';
					}
				}
			}
		}

		if (is_set($arFields, "REST_ON"))
		{
			if ($arFields['REST_ON'] !== 'Y')
			{
				$arFields['REST_ON'] = 'N';
			}
			else
			{
				if (!$arFields['API_CODE'])
				{
					$this->LAST_ERROR .= Loc::getMessage("IBLOCK_BAD_REST_ON_WO_API_CODE").'<br>';
				}
			}
		}

		unset($arFields['TIMESTAMP_X']);
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();
		unset($helper, $connection);

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockAdd", true);
		else
		{
			$arFields["ID"] = $ID;
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockUpdate", true);
		}

		foreach($db_events as  $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		/****************************** QUOTA ******************************/
		if(empty($this->LAST_ERROR) && (COption::GetOptionInt("main", "disk_space") > 0))
		{
			$quota = new CDiskQuota();
			if(!$quota->checkDiskQuota($arFields))
				$this->LAST_ERROR = $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/

		if($this->LAST_ERROR <> '')
			return false;

		return true;
	}

	public static function SetPermission($IBLOCK_ID, $arGROUP_ID)
	{
		/** @global CDatabase $DB */
		global $DB;
		$IBLOCK_ID = (int)$IBLOCK_ID;
		static $letters = array(
			'E' => true,
			'R' => true,
			'S' => true,
			'T' => true,
			'U' => true,
			'W' => true,
			'X' => true
		);

		$arToDelete = array();
		$arToInsert = array();

		if(is_array($arGROUP_ID))
		{
			foreach($arGROUP_ID as $group_id => $perm)
			{
				$group_id = (int)$group_id;
				if ($group_id > 0 && isset($letters[$perm]))
				{
					$arToInsert[$group_id] = $perm;
				}
			}
		}

		$rs = $DB->Query("
			SELECT GROUP_ID, PERMISSION
			FROM b_iblock_group
			WHERE IBLOCK_ID = ".$IBLOCK_ID."
		");
		while($ar = $rs->Fetch())
		{
			$group_id = (int)$ar["GROUP_ID"];

			if(isset($arToInsert[$group_id]) && $arToInsert[$group_id] === $ar["PERMISSION"])
			{
				unset($arToInsert[$group_id]); //This already in DB
			}
			else
			{
				$arToDelete[] = $group_id;
			}
		}

		if(!empty($arToDelete))
		{
			$DB->Query("
				DELETE FROM b_iblock_group
				WHERE IBLOCK_ID = ".$IBLOCK_ID."
				AND GROUP_ID in (".implode(", ", $arToDelete).")
			"); //And this should be deleted
		}

		if(!empty($arToInsert))
		{
			foreach($arToInsert as $group_id => $perm)
			{
				$DB->Query("
					INSERT INTO b_iblock_group(IBLOCK_ID, GROUP_ID, PERMISSION)
					SELECT ".$IBLOCK_ID.", ID, '".$perm."'
					FROM b_group
					WHERE ID = ".$group_id."
				");
			}
		}

		if(!empty($arToDelete) || !empty($arToInsert))
		{
			if(CModule::IncludeModule("search"))
			{
				$arGroups = CIBlock::GetGroupPermissions($IBLOCK_ID);
				if(isset($arGroups[2]))
					CSearch::ChangePermission("iblock", array(2), false, false, $IBLOCK_ID);
				else
					CSearch::ChangePermission("iblock", $arGroups, false, false, $IBLOCK_ID);
			}
		}
	}

	public static function SetMessages($ID, $arFields)
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = intval($ID);
		if($ID > 0)
		{
			$arMessages = array(
				"ELEMENT_NAME",
				"ELEMENTS_NAME",
				"ELEMENT_ADD",
				"ELEMENT_EDIT",
				"ELEMENT_DELETE",
				"SECTION_NAME",
				"SECTIONS_NAME",
				"SECTION_ADD",
				"SECTION_EDIT",
				"SECTION_DELETE",
			);
			$arUpdate = array();
			foreach($arMessages as $MESSAGE_ID)
			{
				if(array_key_exists($MESSAGE_ID, $arFields))
					$arUpdate[] = $MESSAGE_ID;
			}
			if(count($arUpdate) > 0)
			{
				$res = $DB->Query("
					DELETE FROM b_iblock_messages
					WHERE IBLOCK_ID = ".$ID."
					AND MESSAGE_ID in ('".implode("', '", $arUpdate)."')
				");
				if($res)
				{
					foreach($arUpdate as $MESSAGE_ID)
					{
						$MESSAGE_TEXT = trim($arFields[$MESSAGE_ID]);
						if($MESSAGE_TEXT <> '')
							$DB->Add("b_iblock_messages", array(
								"ID" => 1, //FAKE field for not use sequence
								"IBLOCK_ID" => $ID,
								"MESSAGE_ID" => $MESSAGE_ID,
								"MESSAGE_TEXT" => $MESSAGE_TEXT,
							));
					}
				}
			}
		}
	}

	public static function GetMessages($ID, $type="")
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = intval($ID);
		$arMessages = array(
			"ELEMENT_NAME" => Loc::getMessage("IBLOCK_MESS_ELEMENT_NAME"),
			"ELEMENTS_NAME" => "",
			"ELEMENT_ADD" => Loc::getMessage("IBLOCK_MESS_ELEMENT_ADD"),
			"ELEMENT_EDIT" => Loc::getMessage("IBLOCK_MESS_ELEMENT_EDIT"),
			"ELEMENT_DELETE" => Loc::getMessage("IBLOCK_MESS_ELEMENT_DELETE"),
			"SECTION_NAME" => Loc::getMessage("IBLOCK_MESS_SECTION_NAME"),
			"SECTIONS_NAME" => "",
			"SECTION_ADD" => Loc::getMessage("IBLOCK_MESS_SECTION_ADD"),
			"SECTION_EDIT" => Loc::getMessage("IBLOCK_MESS_SECTION_EDIT"),
			"SECTION_DELETE" => Loc::getMessage("IBLOCK_MESS_SECTION_DELETE"),
		);
		$res = $DB->Query("
			SELECT
				B.IBLOCK_TYPE_ID
				,M.IBLOCK_ID
				,M.MESSAGE_ID
				,M.MESSAGE_TEXT
			FROM
				b_iblock B
				LEFT JOIN b_iblock_messages M ON B.ID = M.IBLOCK_ID
			WHERE
				B.ID = ".$ID."
		");

		while($ar = $res->Fetch())
		{
			$type = $ar["IBLOCK_TYPE_ID"];
			if($ar["MESSAGE_ID"])
				$arMessages[$ar["MESSAGE_ID"]] = $ar["MESSAGE_TEXT"];
		}
		if(($arMessages["ELEMENTS_NAME"] == '') || ($arMessages["SECTIONS_NAME"] == ''))
		{
			if($type)
			{
				$arType = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
				if($arType)
				{
					if($arMessages["ELEMENTS_NAME"] == '')
						$arMessages["ELEMENTS_NAME"] = $arType["ELEMENT_NAME"];
					if($arMessages["SECTIONS_NAME"] == '')
						$arMessages["SECTIONS_NAME"] = $arType["SECTION_NAME"];
				}
			}
		}
		if($arMessages["ELEMENTS_NAME"] == '')
			$arMessages["ELEMENTS_NAME"] = Loc::getMessage("IBLOCK_MESS_ELEMENTS_NAME");
		if($arMessages["SECTIONS_NAME"] == '')
			$arMessages["SECTIONS_NAME"] = Loc::getMessage("IBLOCK_MESS_SECTIONS_NAME");
		return $arMessages;
	}

	public static function GetFieldsDefaults()
	{
/*************
REQ
+	IBLOCK_SECTION_ID 	int(11),
	ACTIVE 			char(1) 	not null 	default 'Y',
+	ACTIVE_FROM 		datetime,
+	ACTIVE_TO 		datetime,
	SORT 			int(11) 	not null 	default '500',
	NAME 			varchar(255)	not null,
+	PREVIEW_PICTURE 	int(18),
+	PREVIEW_TEXT 		text,
	PREVIEW_TEXT_TYPE	varchar(4) 	not null 	default 'text',
+	DETAIL_PICTURE 		int(18),
+	DETAIL_TEXT 		longtext,
	DETAIL_TEXT_TYPE 	varchar(4) 	not null 	default 'text',
+	XML_ID 			varchar(255),
+	CODE 			varchar(255),
+	TAGS 			varchar(255),
**************/
		static $res = false;
		if (!$res)
		{
			$defaultValues = static::getFieldsDefaultValues();

			$res = [
				'IBLOCK_SECTION' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_SECTIONS'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['IBLOCK_SECTION']),
					'VISIBLE' => 'Y',
				],
				'ACTIVE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_ACTIVE'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['ACTIVE'],
					'VISIBLE' => 'Y',
				],
				'ACTIVE_FROM' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_ACTIVE_PERIOD_FROM'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['ACTIVE_FROM'],
					'VISIBLE' => 'Y',
				],
				'ACTIVE_TO' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_ACTIVE_PERIOD_TO'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['ACTIVE_TO'],
					'VISIBLE' => 'Y',
				],
				'SORT' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_SORT'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['SORT'],
					'VISIBLE' => 'Y',
				],
				'NAME' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_NAME'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['NAME'],
					'VISIBLE' => 'Y',
				],
				'PREVIEW_PICTURE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_PREVIEW_PICTURE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['PREVIEW_PICTURE']),
					'VISIBLE' => 'Y',
				],
				'PREVIEW_TEXT_TYPE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_PREVIEW_TEXT_TYPE'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['PREVIEW_TEXT_TYPE'],
					'VISIBLE' => 'Y',
				],
				'PREVIEW_TEXT' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_PREVIEW_TEXT'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['PREVIEW_TEXT'],
					'VISIBLE' => 'Y',
				],
				'DETAIL_PICTURE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_DETAIL_PICTURE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['DETAIL_PICTURE']),
					'VISIBLE' => 'Y',
				],
				'DETAIL_TEXT_TYPE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_DETAIL_TEXT_TYPE'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['DETAIL_TEXT_TYPE'],
					'VISIBLE' => 'Y',
				],
				'DETAIL_TEXT' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_DETAIL_TEXT'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['DETAIL_TEXT'],
					'VISIBLE' => 'Y',
				],
				'XML_ID' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_XML_ID'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['XML_ID'],
					'VISIBLE' => 'Y',
				],
				'CODE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_CODE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['CODE']),
					'VISIBLE' => 'Y',
				],
				'TAGS' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_TAGS'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['TAGS'],
					'VISIBLE' => 'Y',
				],
				'SECTION_NAME' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_NAME'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['SECTION_NAME'],
					'VISIBLE' => 'Y',
				],
				'SECTION_PICTURE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_PREVIEW_PICTURE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['SECTION_PICTURE']),
					'VISIBLE' => 'Y',
				],
				'SECTION_DESCRIPTION_TYPE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_SECTION_DESCRIPTION_TYPE'),
					'IS_REQUIRED' => 'Y',
					'DEFAULT_VALUE' => $defaultValues['SECTION_DESCRIPTION_TYPE'],
					'VISIBLE' => 'Y',
				],
				'SECTION_DESCRIPTION' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_SECTION_DESCRIPTION'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['SECTION_DESCRIPTION'],
					'VISIBLE' => 'Y',
				],
				'SECTION_DETAIL_PICTURE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_DETAIL_PICTURE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['SECTION_DETAIL_PICTURE']),
					'VISIBLE' => 'Y',
				],
				'SECTION_XML_ID' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_XML_ID'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['SECTION_XML_ID'],
					'VISIBLE' => 'Y',
				],
				'SECTION_CODE' => [
					'NAME' => Loc::getMessage('IBLOCK_FIELD_CODE'),
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => serialize($defaultValues['SECTION_CODE']),
					'VISIBLE' => 'Y',
				],
				'LOG_SECTION_ADD' => [
					'NAME' => 'LOG_SECTION_ADD',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_SECTION_ADD'],
					'VISIBLE' => 'Y',
				],
				'LOG_SECTION_EDIT' => [
					'NAME' => 'LOG_SECTION_EDIT',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_SECTION_EDIT'],
					'VISIBLE' => 'Y',
				],
				'LOG_SECTION_DELETE' => [
					'NAME' => 'LOG_SECTION_DELETE',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_SECTION_DELETE'],
					'VISIBLE' => 'Y',
				],
				'LOG_ELEMENT_ADD' => [
					'NAME' => 'LOG_ELEMENT_ADD',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_ELEMENT_ADD'],
					'VISIBLE' => 'Y',
				],
				'LOG_ELEMENT_EDIT' => [
					'NAME' => 'LOG_ELEMENT_EDIT',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_ELEMENT_EDIT'],
					'VISIBLE' => 'Y',
				],
				'LOG_ELEMENT_DELETE' => [
					'NAME' => 'LOG_ELEMENT_DELETE',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['LOG_ELEMENT_DELETE'],
					'VISIBLE' => 'Y',
				],
				'XML_IMPORT_START_TIME' => [
					'NAME' => 'XML_IMPORT_START_TIME',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['XML_IMPORT_START_TIME'],
					'VISIBLE' => 'N',
				],
				'DETAIL_TEXT_TYPE_ALLOW_CHANGE' => [
					'NAME' => 'DETAIL_TEXT_TYPE_ALLOW_CHANGE',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['DETAIL_TEXT_TYPE_ALLOW_CHANGE'],
					'VISIBLE' => 'N',
				],
				'PREVIEW_TEXT_TYPE_ALLOW_CHANGE' => [
					'NAME' => 'PREVIEW_TEXT_TYPE_ALLOW_CHANGE',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['PREVIEW_TEXT_TYPE_ALLOW_CHANGE'],
					'VISIBLE' => 'N',
				],
				'SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE' => [
					'NAME' => 'SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE',
					'IS_REQUIRED' => false,
					'DEFAULT_VALUE' => $defaultValues['SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE'],
					'VISIBLE' => 'N',
				],
			];
		}

		return $res;
	}

	/**
	 * @param string $fieldName
	 * @return array|null
	 */
	public static function getFieldDefaultSettings(string $fieldName): ?array
	{
		if ($fieldName === '')
		{
			return null;
		}

		$fields = static::GetFieldsDefaults();

		return ($fields[$fieldName] ?? null);
	}

	public static function SetFields($ID, $arFields)
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = (int)$ID;
		if ($ID > 0)
		{
			$fields = CIBlock::GetFieldsDefaults();
			$defaultValues = static::getFieldsDefaultValues();

			foreach (array_keys($arFields) as $fieldId)
			{
				if (!is_array($arFields[$fieldId]))
				{
					unset($arFields[$fieldId]);
				}
				if (isset($fields[$fieldId]['IS_REQUIRED']))
				{
					$arFields[$fieldId]['IS_REQUIRED'] ??= $fields[$fieldId]['IS_REQUIRED'];
				}
				if (isset($defaultValues[$fieldId]))
				{
					if (is_array($defaultValues[$fieldId]))
					{
						$value = $arFields[$fieldId]['DEFAULT_VALUE'] ?? [];
						if (!is_array($value))
						{
							$value = [];
						}
						$value = array_intersect_key($value, $defaultValues[$fieldId]);
						$value = array_merge($defaultValues[$fieldId], $value);
						$arFields[$fieldId]['DEFAULT_VALUE'] = $value;
					}
					elseif ($defaultValues[$fieldId] === false)
					{
						$arFields[$fieldId]['DEFAULT_VALUE'] ??= $defaultValues[$fieldId];
					}
				}
			}

			if (isset($arFields['PREVIEW_PICTURE']))
			{
				$arFields['PREVIEW_PICTURE']['DEFAULT_VALUE'] = serialize(static::preparePreviewPictureFieldSettings(
					$arFields['PREVIEW_PICTURE']['DEFAULT_VALUE']
				));
			}

			if (isset($arFields['DETAIL_PICTURE']))
			{
				$arFields['DETAIL_PICTURE']['DEFAULT_VALUE'] = serialize(static::prepareDetailPictureFieldSettings(
					$arFields['DETAIL_PICTURE']['DEFAULT_VALUE']
				));
			}

			if (isset($arFields['CODE']))
			{
				$arFields['CODE']['DEFAULT_VALUE'] = serialize(static::prepareCodeFieldSettings(
					$arFields['CODE']['DEFAULT_VALUE']
				));
			}

			if (isset($arFields['SECTION_PICTURE']))
			{
				$arFields['SECTION_PICTURE']['DEFAULT_VALUE'] = serialize(static::preparePreviewPictureFieldSettings(
					$arFields['SECTION_PICTURE']['DEFAULT_VALUE']
				));
			}

			if (isset($arFields['SECTION_DETAIL_PICTURE']))
			{
				$arFields['SECTION_DETAIL_PICTURE']['DEFAULT_VALUE'] = serialize(static::prepareDetailPictureFieldSettings(
					$arFields['SECTION_DETAIL_PICTURE']['DEFAULT_VALUE']
				));
			}
			if (isset($arFields['SECTION_CODE']))
			{
				$arFields['SECTION_CODE']['DEFAULT_VALUE'] = serialize(static::prepareCodeFieldSettings(
					$arFields['SECTION_CODE']['DEFAULT_VALUE']
				));
			}
			if (isset($arFields['SORT']))
			{
				$arFields['SORT']['DEFAULT_VALUE'] = (int)($arFields['SORT']['DEFAULT_VALUE'] ?? 500);
			}
			if (isset($arFields['IBLOCK_SECTION']))
			{
				$arFields['IBLOCK_SECTION']['DEFAULT_VALUE'] = serialize([
					'KEEP_IBLOCK_SECTION_ID' =>
						$arFields['IBLOCK_SECTION']['DEFAULT_VALUE']['KEEP_IBLOCK_SECTION_ID'] === 'Y'
							? 'Y'
							: 'N'
					,
				]);
			}

			$res = $DB->Query("
				SELECT * FROM b_iblock_fields
				WHERE IBLOCK_ID = " . $ID . "
			");
			while ($ar = $res->Fetch())
			{
				$arUpdate = [];
				$fieldId = $ar['FIELD_ID'];
				if (isset($arFields[$fieldId]) && isset($fields[$fieldId]))
				{
					if ($fields[$fieldId]["IS_REQUIRED"] === false)
					{
						$IS_REQUIRED = ($arFields[$fieldId]["IS_REQUIRED"] ?? 'N');
					}
					else
					{
						$IS_REQUIRED = $fields[$fieldId]["IS_REQUIRED"];
					}
					$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
					if ($ar["IS_REQUIRED"] !== $IS_REQUIRED)
					{
						$arUpdate['IS_REQUIRED'] = $IS_REQUIRED;
					}
					if (
						isset($arFields[$fieldId]["DEFAULT_VALUE"])
						&& $ar["DEFAULT_VALUE"] !== $arFields[$fieldId]["DEFAULT_VALUE"]
					)
					{
						$arUpdate['DEFAULT_VALUE'] = $arFields[$fieldId]["DEFAULT_VALUE"];
					}
					unset($fields[$fieldId]);
				}
				elseif (isset($fields[$fieldId]))
				{
					$IS_REQUIRED = $fields[$fieldId]["IS_REQUIRED"];
					$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
					if ($ar["IS_REQUIRED"] !== $IS_REQUIRED)
					{
						$arUpdate = [
							"IS_REQUIRED" => $IS_REQUIRED,
							"DEFAULT_VALUE" => "",
						];
					}
					unset($fields[$fieldId]);
				}
				else
				{
					$DB->Query("DELETE FROM b_iblock_fields WHERE IBLOCK_ID = ".$ID." AND FIELD_ID = '".$DB->ForSQL($fieldId)."'");
				}

				if (!empty($arUpdate))
				{
					$strUpdate = $DB->PrepareUpdate("b_iblock_fields", $arUpdate);
					if ($strUpdate != "")
					{
						$strSql = "UPDATE b_iblock_fields SET " . $strUpdate
							. " WHERE IBLOCK_ID = " . $ID
							. " AND FIELD_ID = '" . $DB->ForSQL($fieldId) . "'";
						$arBinds = [];
						if (isset($arUpdate["DEFAULT_VALUE"]))
						{
							$arBinds["DEFAULT_VALUE"] = $arUpdate["DEFAULT_VALUE"];
						}
						$DB->QueryBind($strSql, $arBinds);
					}
				}
			}
			foreach($fields as $FIELD_ID => $arDefaults)
			{
				if(array_key_exists($FIELD_ID, $arFields))
				{
					if($arDefaults["IS_REQUIRED"] === false)
						$IS_REQUIRED = $arFields[$FIELD_ID]["IS_REQUIRED"];
					else
						$IS_REQUIRED = $arDefaults["IS_REQUIRED"];
					$DEFAULT_VALUE = $arFields[$FIELD_ID]["DEFAULT_VALUE"];
				}
				else
				{
					$IS_REQUIRED = $arDefaults["IS_REQUIRED"];
					$DEFAULT_VALUE = false;
				}
				$IS_REQUIRED = ($IS_REQUIRED === "Y"? "Y": "N");
				$arAdd = [
					"IBLOCK_ID" => $ID,
					"FIELD_ID" => $FIELD_ID,
					"IS_REQUIRED" => $IS_REQUIRED,
					"DEFAULT_VALUE" => $DEFAULT_VALUE,
				];
				$arInsert = $DB->PrepareInsert("b_iblock_fields", $arAdd);
				$DB->Query("INSERT INTO b_iblock_fields (".$arInsert[0].") VALUES (".$arInsert[1].")");
			}

			CIBlock::CleanCache($ID);
		}
	}

	public static function GetFields($ID)
	{
		/** @global CDatabase $DB */
		global $DB;
		$ID = (int)$ID;
		$fields = static::GetFieldsDefaults();
		$defaultValues = static::getFieldsDefaultValues();

		$res = $DB->Query("
			SELECT
				F.*
			FROM
				b_iblock B
				LEFT JOIN b_iblock_fields F ON B.ID = F.IBLOCK_ID
			WHERE
				B.ID = ".$ID."
		");
		while ($ar = $res->Fetch())
		{
			$fieldId = $ar['FIELD_ID'];
			if (isset($fields[$fieldId]))
			{
				if ($fields[$fieldId]['IS_REQUIRED'] === false)
				{
					$fields[$fieldId]['IS_REQUIRED'] = $ar['IS_REQUIRED'] === 'Y' ? 'Y' : 'N';
				}
				$fields[$fieldId]['DEFAULT_VALUE'] = $ar['DEFAULT_VALUE'];
			}
		}
		unset($res);

		foreach ($fields as $FIELD_ID => $default)
		{
			if ($default['IS_REQUIRED'] === false)
			{
				$fields[$FIELD_ID]['IS_REQUIRED'] = 'N';
			}

			if (isset($defaultValues[$FIELD_ID]) && is_array($defaultValues[$FIELD_ID]))
			{
				$a = &$fields[$FIELD_ID]['DEFAULT_VALUE'];

				if (is_string($a) && $a !== '')
				{
					if (CheckSerializedData($a))
					{
						$a = unserialize($a, ['allowed_classes' => false]);
					}
				}
				if (!is_array($a))
				{
					$a = [];
				}
				$a = array_merge($defaultValues[$FIELD_ID], $a);

				if (array_key_exists('TRANS_LEN', $a))
				{
					$trans_len = (int)$a['TRANS_LEN'];
					if ($trans_len > 255)
					{
						$trans_len = 255;
					}
					elseif ($trans_len < 1)
					{
						$trans_len = 100;
					}
					$a['TRANS_LEN'] = $trans_len;
				}
			}
		}

		return $fields;
	}

	public static function GetProperties($ID, $arOrder = array(), $arFilter = array())
	{
		$props = new CIBlockProperty();
		$arFilter["IBLOCK_ID"] = $ID;
		return $props->GetList($arOrder, $arFilter);
	}

	public static function GetGroupPermissions($ID)
	{
		/** @global CDatabase $DB */
		global $DB;
		$arRes = array();
		$ID = (int)$ID;
		if ($ID <= 0)
			return $arRes;

		$dbres = $DB->Query("
			SELECT GROUP_ID, PERMISSION
			FROM b_iblock_group
			WHERE IBLOCK_ID = ".$ID."
		");
		while($res = $dbres->Fetch())
			$arRes[$res["GROUP_ID"]] = $res["PERMISSION"];
		unset($res);
		unset($dbres);

		return $arRes;
	}

	public static function GetPermission($IBLOCK_ID, $FOR_USER_ID = false)
	{
		/** @global CDatabase $DB */
		global $DB;
		/** @global CUser $USER */
		global $USER;
		static $CACHE = array();
		$USER_ID = is_object($USER)? intval($USER->GetID()): 0;

		if($FOR_USER_ID > 0 && $FOR_USER_ID != $USER_ID)
		{
			$arGroups = CUser::GetUserGroup($FOR_USER_ID);
			if(
				in_array(1, $arGroups)
				&& COption::GetOptionString("main", "controller_member", "N") != "Y"
				&& COption::GetOptionString("main", "~controller_limited_admin", "N") != "Y"
			)
				return "X";
			$USER_GROUPS = implode(",", $arGroups);
		}
		elseif(is_object($USER))
		{
			if($USER->IsAdmin())
				return "X";
			$USER_GROUPS = $USER->GetGroups();
		}
		else
		{
			$USER_GROUPS = "2";
		}

		$IBLOCK_ID = intval($IBLOCK_ID);
		$CACHE_KEY = $IBLOCK_ID."|".$USER_GROUPS;

		if(!array_key_exists($CACHE_KEY, $CACHE))
		{
			//Deny by default
			$CACHE[$CACHE_KEY] = "D";
			//Now check database
			$strSql = "
				SELECT MAX(IBG.PERMISSION) as P
				FROM b_iblock_group IBG
				WHERE IBG.IBLOCK_ID=".$IBLOCK_ID."
				AND IBG.GROUP_ID IN (".$USER_GROUPS.")
			";
			$res = $DB->Query($strSql);
			if($r = $res->Fetch())
			{
				if($r['P'] <> '')
				{
					//Overwrite default value
					$CACHE[$CACHE_KEY] = $r["P"];
				}
			}
		}

		return $CACHE[$CACHE_KEY];
	}

	public static function OnBeforeLangDelete($lang)
	{
		/** @global CDatabase $DB */
		global $DB;
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$r = $DB->Query("
			SELECT IBLOCK_ID
			FROM b_iblock_site
			WHERE SITE_ID='".$DB->ForSQL($lang, 2)."'
			ORDER BY IBLOCK_ID
		");
		$arIBlocks = array();
		while($a = $r->Fetch())
			$arIBlocks[] = $a["IBLOCK_ID"];
		if(count($arIBlocks) > 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("IBLOCK_SITE_LINKS_EXISTS", array("#ID_LIST#" => implode(", ", $arIBlocks))));
			return false;
		}
		else
		{
			return true;
		}
	}

	public static function OnLangDelete($lang)
	{
		return true;
	}

	public static function OnGroupDelete($group_id)
	{
		/** @global CDatabase $DB */
		global $DB;

		return $DB->Query("DELETE FROM b_iblock_group WHERE GROUP_ID=".intval($group_id), true);
	}

	public static function MkOperationFilter($key): array
	{
		static $operations = [
			'!><' => 'NB', //not between
			'!='  => 'NI', //not Identical
			'!%'  => 'NS', //not substring
			'><'  => 'B',  //between
			'>='  => 'GE', //greater or equal
			'<='  => 'LE', //less or equal
			'='   => 'I', //Identical
			'%'   => 'S', //substring
			'?'   => '?', //logical
			'>'   => 'G', //greater
			'<'   => 'L', //less
			'!'   => 'N', // not field LIKE val
			'*'   => 'FT', // partial full text match
			'*='  => 'FTI', // identical full text match
			'*%'  => 'FTL', // partial full text match based on LIKE
		];

		$key = (string)$key;
		$result = [
			'FIELD' => $key,
			'OPERATION' => 'E',
			'PREFIX' => '',
		];
		if ($key === '')
		{
			return $result; // zero key
		}

		for ($i = 3; $i > 0; $i--)
		{
			$op = mb_substr($key, 0, $i);
			if ($op && isset($operations[$op]))
			{
				$result['FIELD'] = mb_substr($key, $i);
				$result['OPERATION'] = $operations[$op];
				$result['PREFIX'] = $op;
				break;
			}
		}

		return $result; // field LIKE val
	}

	public static function FilterCreate($field_name, $values, $type, $cOperationType=false, $bSkipEmpty = true)
	{
		return CIBlock::FilterCreateEx($field_name, $values, $type, $bFullJoin, $cOperationType, $bSkipEmpty);
	}

	public static function ForLIKE($str)
	{
		/** @global CDatabase $DB */
		global $DB;

		return str_replace("%", "\\%", str_replace("_", "\\_", $DB->ForSQL($str)));
	}

	public static function FilterCreateEx($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		/** @global CDatabase $DB */
		global $DB;

		if(!is_array($vals))
			$vals=Array($vals);

		if(count($vals)<1)
			return "";

		if(is_bool($cOperationType))
		{
			if($cOperationType===true)
				$cOperationType = "N";
			else
				$cOperationType = "E";
		}

		if($cOperationType=="E") // most req operation
			$strOperation = "=";
		elseif($cOperationType=="G")
			$strOperation = ">";
		elseif($cOperationType=="GE")
			$strOperation = ">=";
		elseif($cOperationType=="LE")
			$strOperation = "<=";
		elseif($cOperationType=="L")
			$strOperation = "<";
		elseif($cOperationType=='B')
			$strOperation = array('BETWEEN', 'AND');
		elseif($cOperationType=='NB')
			$strOperation = array('BETWEEN', 'AND');
		else
			$strOperation = "=";

		if($cOperationType=='B' || $cOperationType=='NB')
		{
			if(count($vals)==2 && !is_array($vals[0]))
				$vals = array($vals);
		}

		$bNegative = mb_substr($cOperationType, 0, 1) == "N";
		$bFullJoin = false;
		$bWasLeftJoin = false;

		$arIn = Array(); //This will gather equality number conditions
		$bWasNull = false;
		$res = Array();
		foreach($vals as $val)
		{
			if(
				!$bSkipEmpty
				|| (is_array($strOperation) && is_array($val))
				|| (is_bool($val) && $val===false)
				|| (string)$val <> ''
			)
			{
				switch ($type)
				{
				case "string_equal":
					if($cOperationType=="?")
					{
						if((string)$val <> '')
							$res[] = GetFilterQuery($fname, $val, "N");
					}
					elseif($cOperationType=="S" || $cOperationType=="NS")
						$res[] = ($cOperationType=="NS"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'%".CIBlock::ForLIKE($val)."%'").")";
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
						$res[] = ($cOperationType=="NB"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." ".$strOperation[0]." '".CIBlock::_Upper($DB->ForSql($val[0]))."' ".$strOperation[1]." '".CIBlock::_Upper($DB->ForSql($val[1]))."')";
					else
					{
						if((string)$val == '')
							$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
						else
							$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname).$strOperation.CIBlock::_Upper("'".$DB->ForSql($val)."'").")";
					}
					break;
				case "string":
					if($cOperationType=="?")
					{
						if((string)$val <> '')
						{
							$sr = GetFilterQuery($fname, $val, "Y", array(), ($fname=="BE.SEARCHABLE_CONTENT" || $fname=="BE.DETAIL_TEXT" ? "Y" : "N"));
							if($sr != "0")
								$res[] = $sr;
						}
					}
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
					{
						$res[] = ($cOperationType=="NB"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." ".$strOperation[0]." '".CIBlock::_Upper($DB->ForSql($val[0]))."' ".$strOperation[1]." '".CIBlock::_Upper($DB->ForSql($val[1]))."')";
					}
					elseif($cOperationType=="S" || $cOperationType=="NS")
						$res[] = ($cOperationType=="NS"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'%".CIBlock::ForLIKE($val)."%'").")";
					elseif($cOperationType=="FTL")
					{
						$sqlWhere = new CSQLWhere();
						$condition = $sqlWhere->matchLike($fname, $val);
						if ($condition != '')
							$res[] = $condition;
					}
					else
					{
						if((string)$val == '')
							$res[] = ($bNegative? "NOT": "")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
						else
							if($strOperation=="=" && $cOperationType!="I" && $cOperationType!="NI")
								$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'".$DB->ForSqlLike($val)."'")." ESCAPE '\\'" : $fname." LIKE '".$DB->ForSqlLike($val)."'").")";
							else
								$res[] = ($bNegative? " ".$fname." IS NULL OR NOT ": "")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." ".$strOperation." ".CIBlock::_Upper("'".$DB->ForSql($val)."'")." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").")";
					}
					break;
				case "date":
					if(!is_array($val) && $val == '')
					{
						$res[] = ($cOperationType == "N" ? "NOT" : "") . "(" . $fname . " IS NULL)";
					}
					elseif (($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
					{
						if (
							static::isCorrectFullFormatDate($DB->ForSql($val[0])) &&
							static::isCorrectFullFormatDate($DB->ForSql($val[1]))
						)
						{
							$res[] = ($cOperationType == 'NB' ? ' ' . $fname . ' IS NULL OR NOT ' : '')
								. '('
								. $fname
								. ' '
								. $strOperation[0]
								. ' '
								. $DB->CharToDateFunction($DB->ForSql($val[0]), "FULL")
								. ' '
								. $strOperation[1]
								. ' '
								. $DB->CharToDateFunction($DB->ForSql($val[1]), "FULL")
								. ')';
						}
					}
					else
					{
						if (static::isCorrectFullFormatDate($DB->ForSql($val)))
						{
							$res[] = ($bNegative ? " " . $fname . " IS NULL OR NOT " : "")
								. "("
								. $fname
								. " "
								. $strOperation
								. " "
								. $DB->CharToDateFunction($DB->ForSql($val), "FULL")
								. ")";
						}
					}
					break;
				case "number":
					if($val === '' || $val === null || $val === false)
					{
						$res[] = $fname." IS ".($bNegative? "NOT NULL": " NULL");
						$bWasNull = true;
					}
					elseif($cOperationType=="B" || $cOperationType=="NB")
					{
						if(is_array($val))
						{
							if(count($val)==2)
							{
								$res[] =
									($cOperationType == 'NB' ? ' ' . $fname . ' IS NULL OR NOT ' : '')
									. '(' . $fname . ' ' . $strOperation[0]
									. ' ' . self::getNumberValueForSql($val[0])
									. ' ' . $strOperation[1]
									. ' ' . self::getNumberValueForSql($val[1])
									. ')'
								;
							}
							else
							{
								$res[] =
									($cOperationType == 'NB' ? ' ' . $fname . ' IS NULL OR NOT ' : '')
									. '(' . $fname . ' = ' . self::getNumberValueForSql(array_pop($val[0])) . ')'
								;
							}
						}
						else
						{
							$res[] =
								($cOperationType=='NB' ? ' ' . $fname . ' IS NULL OR NOT ' : '')
								. '(' . $fname . ' = ' . self::getNumberValueForSql($val) . ')'
							;
						}
					}
					elseif($bNegative)
					{
						$parsedValue = self::getNumberValueForSql($val);
						$res[] = " ".$fname." IS NULL OR NOT (".$fname." ".$strOperation." ".$parsedValue.")";
						if($strOperation == '=')
						{
							$arIn[] = $parsedValue;
						}
						unset($parsedValue);
					}
					else
					{
						$parsedValue = self::getNumberValueForSql($val);
						$res[] = "(".$fname." ".$strOperation." ".$parsedValue.")";
						if($strOperation == '=')
						{
							$arIn[] = $parsedValue;
						}
					}
					break;
				case "number_above":
					if($val === '' || $val === null || $val === false)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "fulltext":
					if($cOperationType=="FT" || $cOperationType=="FTI")
					{
						$sqlWhere = new CSQLWhere();
						$condition = $sqlWhere->match($fname, $val, $cOperationType=="FT");
						if ($condition != '')
							$res[] = $condition;
					}
					elseif($cOperationType=="FTL")
					{
						$sqlWhere = new CSQLWhere();
						$condition = $sqlWhere->matchLike($fname, $val);
						if ($condition != '')
							$res[] = $condition;
					}
					elseif($cOperationType=="?")
					{
						if((string)$val <> '')
						{
							$sr = GetFilterQuery($fname, $val, "Y", array(), ($fname=="BE.SEARCHABLE_CONTENT" || $fname=="BE.DETAIL_TEXT" ? "Y" : "N"));
							if($sr != "0")
								$res[] = $sr;
						}
					}
					elseif(($cOperationType=="B" || $cOperationType=="NB") && is_array($val) && count($val)==2)
					{
						$res[] = ($cOperationType=="NB"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." ".$strOperation[0]." '".CIBlock::_Upper($DB->ForSql($val[0]))."' ".$strOperation[1]." '".CIBlock::_Upper($DB->ForSql($val[1]))."')";
					}
					elseif($cOperationType=="S" || $cOperationType=="NS")
						$res[] = ($cOperationType=="NS"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'%".CIBlock::ForLIKE($val)."%'").")";
					else
					{
						if((string)$val == '')
							$res[] = ($bNegative? "NOT": "")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
						else
							if($strOperation=="=" && $cOperationType!="I" && $cOperationType!="NI")
								$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'".$DB->ForSqlLike($val)."'")." ESCAPE '\\'" : $fname." LIKE '".$DB->ForSqlLike($val)."'").")";
							else
								$res[] = ($bNegative? " ".$fname." IS NULL OR NOT ": "")."(".($DB->type=="ORACLE"?CIBlock::_Upper($fname)." ".$strOperation." ".CIBlock::_Upper("'".$DB->ForSql($val)."'")." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").")";
					}
					break;
				}

				if((is_array($val) || (string)$val <> '') && !$bNegative)
					$bFullJoin = true;
				else
					$bWasLeftJoin = true;
			}
		}

		$strResult = "";

		$cntIn = count($arIn);
		if(
			!$bWasNull
			&& $cntIn > 1
			&& (
				$cntIn < 2000
				|| $DB->type == "MYSQL"
			)
		)
		{
			if($bNegative)
				$res = array($fname." IS NULL OR NOT (".$fname." IN (".implode(", ", $arIn)."))");
			else
				$res = array($fname." IN (".implode(", ", $arIn).")");
		}

		foreach($res as $i=>$val)
		{
			if($i>0)
				$strResult .= ($bNegative? " AND ": " OR ");
			$strResult .= "(".$val.")";
		}

		if($strResult!="")
			$strResult = "(".$strResult.")";

		if($bFullJoin && $bWasLeftJoin && !$bNegative)
			$bFullJoin = false;

		return $strResult;
	}

	/**
	 * Returns sql-safe value for numbers (include infinity and NaN).
	 *
	 * @param mixed $value
	 * @return string|float
	 */
	private static function getNumberValueForSql(mixed $value): string|float
	{
		$value = (float)$value;
		if (is_nan($value) || is_infinite($value))
		{
			return "'" . $value . "'";
		}

		return $value;
	}

	public static function isCorrectFullFormatDate($value): bool
	{
		$result = true;

		// get user time
		if ($value instanceof Main\Type\DateTime && !$value->isUserTimeEnabled())
		{
			$value = clone $value;
			$value->toUserTime();
		}

		// format
		if (($context = Main\Context::getCurrent()) && ($culture = $context->getCulture()) !== null)
		{
			$format = $culture->getFormatDatetime();
		}
		else
		{
			$format = CLang::GetDateFormat('FULL');
		}

		$formatDate = CDatabase::FormatDate($value, $format, "YYYY-MM-DD HH:MI:SS");

		if ($formatDate === false || $formatDate === '')
		{
			$result = false;
		}

		return $result;
	}

	public static function _MergeIBArrays($iblock_id, $iblock_code = false, $iblock_id2 = false, $iblock_code2 = false)
	{
		if(!is_array($iblock_id))
		{
			if(is_numeric($iblock_id) || $iblock_id <> '')
				$iblock_id = Array($iblock_id);
			elseif(is_array($iblock_id2))
				$iblock_id = $iblock_id2;
			elseif(is_numeric($iblock_id2) || $iblock_id2 <> '')
				$iblock_id = Array($iblock_id2);
		}

		if(!is_array($iblock_code))
		{
			if(is_numeric($iblock_code) || $iblock_code <> '')
				$iblock_code = Array($iblock_code);
			elseif(is_array($iblock_code2))
				$iblock_code = $iblock_code2;
			elseif(is_numeric($iblock_code2) || $iblock_code2 <> '')
				$iblock_code = Array($iblock_code2);
		}

		if(is_array($iblock_code) && is_array($iblock_id))
			return array_merge($iblock_code, $iblock_id);

		if(is_array($iblock_code))
			return $iblock_code;

		if(is_array($iblock_id))
			return $iblock_id;

		return array();
	}

	public static function OnSearchGetURL($arFields)
	{
		/** @global CDatabase $DB */
		global $DB;
		static $arIBlockCache = array();

		if($arFields["MODULE_ID"] !== "iblock" || mb_substr($arFields["URL"], 0, 1) !== "=")
			return $arFields["URL"];

		$IBLOCK_ID = intval($arFields["PARAM2"]);

		if(!array_key_exists($IBLOCK_ID, $arIBlockCache))
		{
			$res = $DB->Query("
				SELECT
					DETAIL_PAGE_URL,
					SECTION_PAGE_URL,
					CODE as IBLOCK_CODE,
					XML_ID as IBLOCK_EXTERNAL_ID,
					IBLOCK_TYPE_ID
				FROM
					b_iblock
				WHERE ID = ".$IBLOCK_ID."
			");
			$arIBlockCache[$IBLOCK_ID] = $res->Fetch();
		}

		if(!is_array($arIBlockCache[$IBLOCK_ID]))
			return "";

		$arFields["URL"] = LTrim($arFields["URL"], " =");
		parse_str($arFields["URL"], $arr);
		$arr = $arIBlockCache[$IBLOCK_ID] + $arr;
		$arr["LANG_DIR"] = $arFields["DIR"];

		if(mb_substr($arFields["ITEM_ID"], 0, 1) !== 'S')
			return CIBlock::ReplaceDetailUrl($arIBlockCache[$IBLOCK_ID]["DETAIL_PAGE_URL"], $arr, false, "E");
		else
			return CIBlock::ReplaceDetailUrl($arIBlockCache[$IBLOCK_ID]["SECTION_PAGE_URL"], $arr, false, "S");
	}

	public static function ReplaceSectionUrl($url, $arr, $server_name = false, $arrType = false)
	{
		$url = str_replace(
			[
				'#ID#',
				'#CODE#',
			],
			[
				'#SECTION_ID#',
				'#SECTION_CODE#',
			],
			(string)$url
		);

		return CIBlock::ReplaceDetailUrl($url, $arr, $server_name, $arrType);
	}

	/**
	 * @deprecated
	 * @see CIBlock::getProductUrlValue()
	 *
	 * @param $OF_ELEMENT_ID
	 * @param $OF_IBLOCK_ID
	 * @param $server_name
	 * @param mixed $arrType
	 * @return string
	 */
	public static function _GetProductUrl($OF_ELEMENT_ID, $OF_IBLOCK_ID, $server_name = false, $arrType = false)
	{
		return self::getProductUrlValue(
			[
				'ID' => $OF_ELEMENT_ID,
				'IBLOCK_ID' => $OF_IBLOCK_ID,
			],
			(bool)$server_name
		);
	}

	protected static function getProductUrlValue(array $element, bool $serverName): string
	{
		$result = '';

		$id = (int)($element['ID'] ?? 0);
		$iblockId = (int)($element['IBLOCK_ID'] ?? 0);

		if ($iblockId <= 0 || $id <= 0)
		{
			return $result;
		}

		if (self::$catalogIncluded === null)
		{
			self::$catalogIncluded = Loader::includeModule('catalog');
		}
		if (!self::$catalogIncluded)
		{
			return $result;
		}

		$iblock = static::getProductIblockData($iblockId);
		if ($iblock === null)
		{
			return $result;
		}

		$parentId = (int)($element['PROPERTY_' . $iblock['SKU_PROPERTY_ID'] . '_VALUE'] ?? self::getProductId($id, $iblockId));
		if ($parentId <= 0)
		{
			return $result;
		}

		$parent = self::getUrlElementData($parentId);
		if ($parent === null)
		{
			return $result;
		}

		return CIBlock::ReplaceDetailUrl(
			$iblock['PRODUCT_IBLOCK']['DETAIL_PAGE_URL'],
			[
				'LANG_DIR' => (string)($element['LANG_DIR'] ?? ''),
				'ID' => $parent['ID'],
				'ELEMENT_ID' => $parent['ID'],
				'CODE' => $parent['CODE'],
				'ELEMENT_CODE' => $parent['CODE'],
				'EXTERNAL_ID' => $parent['XML_ID'],
				'IBLOCK_TYPE_ID' => $iblock['PRODUCT_IBLOCK']['IBLOCK_TYPE_ID'],
				'IBLOCK_ID' => $parent['IBLOCK_ID'],
				'IBLOCK_CODE' => $iblock['PRODUCT_IBLOCK']['CODE'],
				'IBLOCK_EXTERNAL_ID' => $iblock['PRODUCT_IBLOCK']['XML_ID'],
				'IBLOCK_SECTION_ID' => $parent['IBLOCK_SECTION_ID'],
				'SECTION_CODE' => $parent['IBLOCK_SECTION_CODE'],
			],
			$serverName,
			'E'
		);
	}

	public static function ReplaceDetailUrl($url, $arr, $server_name = false, $arrType = false)
	{
		$url = (string)$url;

		if ($server_name)
		{
			$url = str_replace('#LANG#', (string)($arr['LANG_DIR'] ?? ''), $url);
			if (
				(defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				|| !defined('BX_STARTED')
			)
			{
				static $cache = array();
				if (isset($arr['LID']))
				{
					if (!isset($cache[$arr['LID']]))
					{
						$db_lang = CLang::GetByID($arr['LID']);
						$arLang = $db_lang->Fetch();
						if (!empty($arLang))
						{
							$arLang['DIR'] = (string)$arLang['DIR'];
							$arLang['SERVER_NAME'] = (string)$arLang['SERVER_NAME'];
						}
						$cache[$arr['LID']] = $arLang;
					}
					$arLang = $cache[$arr['LID']];
					if (!empty($arLang))
					{
						$url = str_replace(
							[
								'#SITE_DIR#',
								'#SERVER_NAME#',
							],
							[
								$arLang['DIR'],
								$arLang['SERVER_NAME'],
							],
							$url
						);
					}
				}
			}
			else
			{
				$url = str_replace(
					[
						'#SITE_DIR#',
						'#SERVER_NAME#',
					],
					[
						SITE_DIR,
						SITE_SERVER_NAME,
					],
					$url
				);
			}
		}

		$id = (int)($arr['ID'] ?? 0);
		$preparedId = $id > 0 ? $id : '';

		static $arSearch = [
			/*Thees come from GetNext*/
			'#SITE_DIR#',
			'#ID#',
			'#CODE#',
			'#EXTERNAL_ID#',
			'#IBLOCK_TYPE_ID#',
			'#IBLOCK_ID#',
			'#IBLOCK_CODE#',
			'#IBLOCK_EXTERNAL_ID#',
			/*And thees was born during components 2 development*/
			'#ELEMENT_ID#',
			'#ELEMENT_CODE#',
			'#SECTION_ID#',
			'#SECTION_CODE#',
			'#SECTION_CODE_PATH#',
		];
		$iblockId = (int)($arr['IBLOCK_ID'] ?? 0);
		$preparedCode = rawurlencode(
			(string)($arr['~CODE'] ?? ($arr['CODE'] ?? ''))
		);
		$iblockSectionId = (int)($arr['IBLOCK_SECTION_ID'] ?? 0);
		$arReplace = [
			(string)($arr['LANG_DIR'] ?? ''),
			$preparedId,
			$preparedCode,
			rawurlencode(
				(string)($arr['~EXTERNAL_ID'] ?? ($arr['EXTERNAL_ID'] ?? ''))
			),
			rawurlencode(
				(string)($arr['~IBLOCK_TYPE_ID'] ?? ($arr['IBLOCK_TYPE_ID'] ?? ''))
			),
			($iblockId > 0 ? $iblockId : ''),
			rawurlencode(
				(string)($arr['~IBLOCK_CODE'] ?? ($arr['IBLOCK_CODE'] ?? ''))
			),
			rawurlencode(
				(string)($arr['~IBLOCK_EXTERNAL_ID'] ?? ($arr['IBLOCK_EXTERNAL_ID'] ?? ''))
			),
		];

		if ($arrType === "E")
		{
			if (strpos($url, '#PRODUCT_URL#') !== false)
			{
				$url = str_replace(
					'#PRODUCT_URL#',
					self::getProductUrlValue($arr, $server_name),
					$url
				);
			}

			$arReplace[] = $preparedId;
			$arReplace[] = rawurlencode(
				(string)($arr['~CODE'] ?? ($arr['CODE'] ?? ''))
			);

			#Deal with symbol codes
			$SECTION_CODE = '';
			$SECTION_CODE_PATH = '';
			if ($iblockSectionId > 0)
			{
				if (strpos($url, '#SECTION_CODE#') !== false)
				{
					$SECTION_CODE = CIBlockSection::getSectionCode($iblockSectionId);
				}

				if (strpos($url, '#SECTION_CODE_PATH#') !== false)
				{
					$SECTION_CODE_PATH = CIBlockSection::getSectionCodePath($iblockSectionId);
				}
			}

			$arReplace[] = $iblockSectionId > 0 ? $iblockSectionId: '';
			$arReplace[] = $SECTION_CODE;
			$arReplace[] = $SECTION_CODE_PATH;
		}
		elseif ($arrType === "S")
		{
			$SECTION_CODE_PATH = '';
			if (
				$id > 0
				&& strpos($url, '#SECTION_CODE_PATH#') !== false
			)
			{
				$SECTION_CODE_PATH = CIBlockSection::getSectionCodePath($id);
			}
			$arReplace[] = '';
			$arReplace[] = '';
			$arReplace[] = $preparedId;
			$arReplace[] = $preparedCode;
			$arReplace[] = $SECTION_CODE_PATH;
		}
		else
		{
			$elementId = (int)($arr['ELEMENT_ID'] ?? 0);
			$preparedElementId = $elementId > 0 ? $elementId : '';
			$arReplace[] = $preparedElementId;
			$arReplace[] = rawurlencode((string)($arr['~ELEMENT_CODE'] ?? ($arr['ELEMENT_CODE'] ?? '')));
			$arReplace[] = $iblockSectionId > 0 ? $iblockSectionId : '';
			$arReplace[] = rawurlencode((string)($arr['~SECTION_CODE'] ?? ($arr['SECTION_CODE'] ?? '')));
			$arReplace[] = '';
		}

		$url = str_replace($arSearch, $arReplace, $url);

		return preg_replace("'(?<!:)/+'s", "/", $url);
	}

	public static function OnSearchReindex($NS=Array(), $oCallback=NULL, $callback_method="")
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;
		/** $global CDatabase $DB */
		global $DB;

		$strNSJoin1 = "";
		$strNSFilter1 = "";
		$strNSFilter2 = "";
		$strNSFilter3 = "";
		$arResult = Array();
		if($NS["MODULE"]=="iblock" && $NS["ID"] <> '')
		{
			$arrTmp = explode(".", $NS["ID"]);
			$strNSFilter1 = " AND B.ID>=".intval($arrTmp[0])." ";
			if(mb_substr($arrTmp[1], 0, 1) != 'S')
			{
				$strNSFilter2 = " AND BE.ID>".intval($arrTmp[1])." ";
			}
			else
			{
				$strNSFilter2 = false;
				$strNSFilter3 = " AND BS.ID>".intval(mb_substr($arrTmp[1], 1))." ";
			}
		}
		if($NS["SITE_ID"]!="")
		{
			$strNSJoin1 .= " INNER JOIN b_iblock_site BS ON BS.IBLOCK_ID=B.ID ";
			$strNSFilter1 .= " AND BS.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."' ";
		}
		$strSql = "
			SELECT B.ID, B.IBLOCK_TYPE_ID, B.INDEX_ELEMENT, B.INDEX_SECTION, B.RIGHTS_MODE,
				B.IBLOCK_TYPE_ID, B.CODE as IBLOCK_CODE, B.XML_ID as IBLOCK_EXTERNAL_ID,
				B.SOCNET_GROUP_ID
			FROM b_iblock B
			".$strNSJoin1."
			WHERE B.ACTIVE = 'Y'
				AND (B.INDEX_ELEMENT='Y' OR B.INDEX_SECTION='Y')
				".$strNSFilter1."
			ORDER BY B.ID
		";

		$dbrIBlock = $DB->Query($strSql);
		while($arIBlock = $dbrIBlock->Fetch())
		{
			$IBLOCK_ID = $arIBlock["ID"];

			$arGroups = Array();

			$strSql =
				"SELECT GROUP_ID ".
				"FROM b_iblock_group ".
				"WHERE IBLOCK_ID= ".$IBLOCK_ID." ".
				"	AND PERMISSION>='R' ".
				"	AND GROUP_ID>1 ".
				"ORDER BY GROUP_ID";

			$dbrIBlockGroup = $DB->Query($strSql);
			while($arIBlockGroup = $dbrIBlockGroup->Fetch())
			{
				$arGroups[] = $arIBlockGroup["GROUP_ID"];
				if($arIBlockGroup["GROUP_ID"]==2) break;
			}

			$arSITE = Array();
			$strSql =
				"SELECT SITE_ID ".
				"FROM b_iblock_site ".
				"WHERE IBLOCK_ID= ".$IBLOCK_ID;

			$dbrIBlockSite = $DB->Query($strSql);
			while($arIBlockSite = $dbrIBlockSite->Fetch())
				$arSITE[] = $arIBlockSite["SITE_ID"];

			if($arIBlock["INDEX_ELEMENT"]=='Y' && ($strNSFilter2 !== false))
			{
				$strSql =
					"SELECT BE.ID, BE.NAME, BE.TAGS, ".
					"	".$DB->DateToCharFunction("BE.ACTIVE_FROM")." as DATE_FROM, ".
					"	".$DB->DateToCharFunction("BE.ACTIVE_TO")." as DATE_TO, ".
					"	".$DB->DateToCharFunction("BE.TIMESTAMP_X")." as LAST_MODIFIED, ".
					"	BE.PREVIEW_TEXT_TYPE, BE.PREVIEW_TEXT, ".
					"	BE.DETAIL_TEXT_TYPE, BE.DETAIL_TEXT, ".
					"	BE.XML_ID as EXTERNAL_ID, BE.CODE, ".
					"	BE.IBLOCK_SECTION_ID ".
					"FROM b_iblock_element BE ".
					"WHERE BE.IBLOCK_ID=".$IBLOCK_ID." ".
					"	AND BE.ACTIVE='Y' ".
					CIBlockElement::WF_GetSqlLimit("BE.", "N").
					$strNSFilter2.
					"ORDER BY BE.ID ";

				//For MySQL, we have to solve client out of memory
				//problem by limiting the query
				if($DB->type=="MYSQL")
				{
					$limit = 1000;
					$strSql .= " LIMIT ".$limit;
				}
				else
				{
					$limit = false;
				}

				$dbrIBlockElement = $DB->Query($strSql);
				while($arIBlockElement = $dbrIBlockElement->Fetch())
				{
					$DETAIL_URL =
							"=ID=".urlencode($arIBlockElement["ID"]).
							"&EXTERNAL_ID=".urlencode($arIBlockElement["EXTERNAL_ID"]).
							"&CODE=".urlencode($arIBlockElement["CODE"]).
							"&IBLOCK_SECTION_ID=".urlencode($arIBlockElement["IBLOCK_SECTION_ID"]).
							"&IBLOCK_TYPE_ID=".urlencode($arIBlock["IBLOCK_TYPE_ID"]).
							"&IBLOCK_ID=".urlencode($IBLOCK_ID).
							"&IBLOCK_CODE=".urlencode($arIBlock["IBLOCK_CODE"]).
							"&IBLOCK_EXTERNAL_ID=".urlencode($arIBlock["IBLOCK_EXTERNAL_ID"]);

					$BODY =
						($arIBlockElement["PREVIEW_TEXT_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockElement["PREVIEW_TEXT"]) :
							$arIBlockElement["PREVIEW_TEXT"]
						)."\r\n".
						($arIBlockElement["DETAIL_TEXT_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockElement["DETAIL_TEXT"]) :
							$arIBlockElement["DETAIL_TEXT"]
						);

					$dbrProperties = CIBlockElement::GetProperty($IBLOCK_ID, $arIBlockElement["ID"], "sort", "asc", array("ACTIVE"=>"Y", "SEARCHABLE"=>"Y"));
					while($arProperties = $dbrProperties->Fetch())
					{
						$BODY .= "\r\n";

						if($arProperties["USER_TYPE"] <> '')
							$UserType = CIBlockProperty::GetUserType($arProperties["USER_TYPE"]);
						else
							$UserType = array();

						if(array_key_exists("GetSearchContent", $UserType))
						{
							$BODY .= CSearch::KillTags(
								call_user_func_array($UserType["GetSearchContent"],
									array(
										$arProperties,
										array("VALUE" => $arProperties["VALUE"]),
										array(),
									)
								)
							);
						}
						elseif(array_key_exists("GetPublicViewHTML", $UserType))
						{
							$BODY .= CSearch::KillTags(
								call_user_func_array($UserType["GetPublicViewHTML"],
									array(
										$arProperties,
										array("VALUE" => $arProperties["VALUE"]),
										array(),
									)
								)
							);
						}
						elseif($arProperties["PROPERTY_TYPE"]=='L')
						{
							$BODY .= $arProperties["VALUE_ENUM"];
						}
						elseif($arProperties["PROPERTY_TYPE"]=='F')
						{
							$arFile = CIBlockElement::__GetFileContent($arProperties["VALUE"]);
							if(is_array($arFile))
							{
								$BODY .= $arFile["CONTENT"];
								$arIBlockElement["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
							}
						}
						else
						{
							$BODY .= $arProperties["VALUE"];
						}
					}

					if($arIBlock["RIGHTS_MODE"] !== "E")
						$arPermissions = $arGroups;
					else
					{
						$obElementRights = new CIBlockElementRights($IBLOCK_ID, $arIBlockElement["ID"]);
						$arPermissions = $obElementRights->GetGroups(array("element_read"));
					}

					$Result = array(
						"ID" => $arIBlockElement["ID"],
						"LAST_MODIFIED" => ($arIBlockElement["DATE_FROM"] <> ''? $arIBlockElement["DATE_FROM"]: $arIBlockElement["LAST_MODIFIED"]),
						"TITLE" => $arIBlockElement["NAME"],
						"BODY" => $BODY,
						"TAGS" => $arIBlockElement["TAGS"],
						"SITE_ID" => $arSITE,
						"PARAM1" => $arIBlock["IBLOCK_TYPE_ID"],
						"PARAM2" => $IBLOCK_ID,
						"DATE_FROM" => ($arIBlockElement["DATE_FROM"] <> ''? $arIBlockElement["DATE_FROM"] : false),
						"DATE_TO" => ($arIBlockElement["DATE_TO"] <> ''? $arIBlockElement["DATE_TO"] : false),
						"PERMISSIONS" => $arPermissions,
						"URL" => $DETAIL_URL
					);

					if ($arIBlock["SOCNET_GROUP_ID"] > 0)
						$Result["PARAMS"] = array(
							"socnet_group" => $arIBlock["SOCNET_GROUP_ID"],
						);

					if($oCallback)
					{
						$res = call_user_func(array($oCallback, $callback_method), $Result);
						if(!$res)
							return $IBLOCK_ID.".".$arIBlockElement["ID"];
					}
					else
					{
						$arResult[] = $Result;
					}

					if($limit !== false)
					{
						$limit--;
						if($limit <= 0)
							return $IBLOCK_ID.".".$arIBlockElement["ID"];
					}
				}
			}

			if($arIBlock["INDEX_SECTION"]=='Y')
			{
				$strSql =
					"SELECT BS.ID, BS.NAME, ".
					"	".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as LAST_MODIFIED, ".
					"	BS.DESCRIPTION_TYPE, BS.DESCRIPTION, BS.XML_ID as EXTERNAL_ID, BS.CODE, ".
					"	BS.IBLOCK_ID ".
					"FROM b_iblock_section BS ".
					"WHERE BS.IBLOCK_ID=".$IBLOCK_ID." ".
					"	AND BS.GLOBAL_ACTIVE='Y' ".
					$strNSFilter3.
					"ORDER BY BS.ID ";

				$dbrIBlockSection = $DB->Query($strSql);
				while($arIBlockSection = $dbrIBlockSection->Fetch())
				{
					$DETAIL_URL =
							"=ID=".$arIBlockSection["ID"].
							"&EXTERNAL_ID=".$arIBlockSection["EXTERNAL_ID"].
							"&CODE=".$arIBlockSection["CODE"].
							"&IBLOCK_TYPE_ID=".$arIBlock["IBLOCK_TYPE_ID"].
							"&IBLOCK_ID=".$arIBlockSection["IBLOCK_ID"].
							"&IBLOCK_CODE=".$arIBlock["IBLOCK_CODE"].
							"&IBLOCK_EXTERNAL_ID=".$arIBlock["IBLOCK_EXTERNAL_ID"];
					$BODY =
						($arIBlockSection["DESCRIPTION_TYPE"]=="html" ?
							CSearch::KillTags($arIBlockSection["DESCRIPTION"])
						:
							$arIBlockSection["DESCRIPTION"]
						);
					$BODY .= $USER_FIELD_MANAGER->OnSearchIndex("IBLOCK_".$arIBlockSection["IBLOCK_ID"]."_SECTION", $arIBlockSection["ID"]);

					if($arIBlock["RIGHTS_MODE"] !== "E")
						$arPermissions = $arGroups;
					else
					{
						$obSectionRights = new CIBlockSectionRights($IBLOCK_ID, $arIBlockSection["ID"]);
						$arPermissions = $obSectionRights->GetGroups(array("section_read"));
					}

					$Result = Array(
						"ID" => "S".$arIBlockSection["ID"],
						"LAST_MODIFIED" => $arIBlockSection["LAST_MODIFIED"],
						"TITLE" => $arIBlockSection["NAME"],
						"BODY" => $BODY,
						"SITE_ID" => $arSITE,
						"PARAM1" => $arIBlock["IBLOCK_TYPE_ID"],
						"PARAM2" => $IBLOCK_ID,
						"PERMISSIONS" => $arPermissions,
						"URL" => $DETAIL_URL,
						);

					if ($arIBlock["SOCNET_GROUP_ID"] > 0)
						$Result["PARAMS"] = array(
							"socnet_group" => $arIBlock["SOCNET_GROUP_ID"],
						);

					if($oCallback)
					{
						$res = call_user_func(array($oCallback, $callback_method), $Result);
						if(!$res)
							return $IBLOCK_ID.".S".$arIBlockSection["ID"];
					}
					else
					{
						$arResult[] = $Result;
					}
				}
			}
			$strNSFilter2="";
			$strNSFilter3="";
		}

		if($oCallback)
			return false;

		return $arResult;
	}

	public static function GetElementCount($iblock_id)
	{
		/** @global CDatabase $DB */
		global $DB;

		$res = $DB->Query("
			SELECT COUNT('x') as C
			FROM b_iblock_element BE
			WHERE BE.IBLOCK_ID=".intval($iblock_id)."
			AND (
				(BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL)
				OR BE.WF_NEW='Y'
			)
		");
		$ar = $res->Fetch();
		unset($res);

		return (int)($ar["C"] ?? 0);
	}

	public static function ResizePicture($arFile, $arResize)
	{
		if($arFile["tmp_name"] == '')
			return $arFile;

		if(array_key_exists("error", $arFile) && $arFile["error"] !== 0)
			return Loc::getMessage("IBLOCK_BAD_FILE_ERROR");

		$file = $arFile["tmp_name"];

		if(!file_exists($file) && !is_file($file))
			return Loc::getMessage("IBLOCK_BAD_FILE_NOT_FOUND");

		$width = (int)$arResize["WIDTH"];
		$height = (int)$arResize["HEIGHT"];

		if($width <= 0 && $height <= 0)
			return $arFile;

		$image = new Image($file);
		$imageInfo = $image->getInfo(false);
		if (empty($imageInfo))
		{
			return Loc::getMessage("IBLOCK_BAD_FILE_NOT_PICTURE");
		}
		$orig = [
			0 => $imageInfo->getWidth(),
			1 => $imageInfo->getHeight(),
			2 => $imageInfo->getFormat(),
			3 => $imageInfo->getAttributes(),
			"mime" => $imageInfo->getMime(),
		];

		$width_orig = $orig[0];
		$height_orig = $orig[1];

		$orientation = 0;
		$exifData = [];
		$image_type = $orig[2];
		if($image_type == Image::FORMAT_JPEG)
		{
			$exifData = $image->getExifData();
			if (isset($exifData['Orientation']))
			{
				$orientation = $exifData['Orientation'];
				if ($orientation >= 5 && $orientation <= 8)
				{
					$width_orig = $orig[1];
					$height_orig = $orig[0];
				}
			}
		}

		if(($width > 0 && $orig[0] > $width) || ($height > 0 && $orig[1] > $height))
		{
			if($arFile["COPY_FILE"] == "Y")
			{
				$new_file = CTempFile::GetFileName(basename($file));
				CheckDirPath($new_file);
				$arFile["copy"] = true;

				if(copy($file, $new_file))
					$file = $new_file;
				else
					return Loc::getMessage("IBLOCK_BAD_FILE_NOT_FOUND");
			}

			if($width <= 0)
				$width = $width_orig;

			if($height <= 0)
				$height = $height_orig;

			$height_new = $height_orig;
			if($width_orig > $width)
				$height_new = $width * $height_orig  / $width_orig;

			if($height_new > $height)
				$width = $height * $width_orig / $height_orig;
			else
				$height = $height_new;

			$image_type = $orig[2];
			if ($image_type == Image::FORMAT_JPEG)
			{
				$image = imagecreatefromjpeg($file);
				if ($image === false)
				{
					ini_set('gd.jpeg_ignore_warning', 1);
					$image = imagecreatefromjpeg($file);
				}

				if ($orientation > 1)
				{
					if ($orientation == 7 || $orientation == 8)
						$image = imagerotate($image, 90, null);
					elseif ($orientation == 3 || $orientation == 4)
						$image = imagerotate($image, 180, null);
					elseif ($orientation == 5 || $orientation == 6)
						$image = imagerotate($image, 270, null);

					if (
						$orientation == 2 || $orientation == 7
						|| $orientation == 4 || $orientation == 5
					)
					{
						$engine = new Image\Gd();
						$engine->setResource($image);
						$engine->flipHorizontal();
					}
				}
			}
			elseif ($image_type == Image::FORMAT_GIF)
			{
				$image = imagecreatefromgif($file);
			}
			elseif ($image_type == Image::FORMAT_PNG)
			{
				$image = imagecreatefrompng($file);
			}
			elseif ($image_type == Image::FORMAT_WEBP)
			{
				$image = imagecreatefromwebp($file);
			}
			else
			{
				return Loc::getMessage("IBLOCK_ERR_BAD_FILE_UNSUPPORTED");
			}

			$image_p = imagecreatetruecolor($width, $height);
			if($image_type == Image::FORMAT_JPEG)
			{
				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

				if($arResize["COMPRESSION"] > 0)
					imagejpeg($image_p, $file, $arResize["COMPRESSION"]);
				else
					imagejpeg($image_p, $file);
			}
			elseif($image_type == Image::FORMAT_GIF && function_exists("imagegif"))
			{
				imagetruecolortopalette($image_p, true, imagecolorstotal($image));
				imagepalettecopy($image_p, $image);

				//Save transparency for GIFs
				$transparentColor = imagecolortransparent($image);
				if($transparentColor >= 0 && $transparentColor < imagecolorstotal($image))
				{
					$transparentColor = imagecolortransparent($image_p, $transparentColor);
					imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentColor);
				}

				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagegif($image_p, $file);
			}
			else
			{
				//Save transparency for PNG
				$transparentColor = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
				imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentColor);
				$transparentColor = imagecolortransparent($image_p, $transparentColor);

				imagealphablending($image_p, false);
				if($arResize["METHOD"] === "resample")
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				else
					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

				imagesavealpha($image_p, true);
				imagepng($image_p, $file);
			}

			imagedestroy($image);
			imagedestroy($image_p);

			$arFile["size"] = filesize($file);
			$arFile["tmp_name"] = $file;
			return $arFile;
		}
		else
		{
			return $arFile;
		}
	}

	public static function FilterPicture($filePath, $arFilter)
	{
		if (!file_exists($filePath))
		{
			return false;
		}
		if (
			!isset($arFilter['name'])
			|| ($arFilter['name'] !== 'sharpen' && $arFilter['name'] !== 'watermark')
		)
		{
			return false;
		}

		$image = new Image($filePath);
		$imageInfo = $image->getInfo();
		if (empty($imageInfo))
		{
			return false;
		}
		if (!$image->load())
		{
			return false;
		}

		$orientation = 0;
		$exifData = $image->getExifData();
		if (isset($exifData['Orientation']))
		{
			$orientation = $exifData['Orientation'];
		}
		$image->autoRotate($orientation);
		switch ($arFilter['name'])
		{
			case 'sharpen':
				$image->filter(Image\Mask::createSharpen($arFilter['precision']));
				break;
			case 'watermark':
				if ($arFilter['type'] === 'text' && mb_strlen($arFilter['text']) > 1 && $arFilter['coefficient'] > 0)
				{
					$arFilter['text_width'] = ($imageInfo->getWidth() - 5) * $arFilter['coefficient'] / 100;
				}
				$watermark = Image\Watermark::createFromArray($arFilter);
				$image->drawWatermark($watermark);
				break;
		}
		$image->save(self::getDefaultJpegQuality());
		$image->clear();

		return true;
	}

	public static function NumberFormat($num)
	{
		if ($num <> '')
		{
			$res = preg_replace("#\\.([0-9]*?)(0+)\$#", ".\\1", $num);
			return rtrim($res, ".");
		}
		else
		{
			return "";
		}
	}

	public static function _Order($by, $order, $default_order, $nullable = true)
	{
		static $arOrder = array(
			"nulls,asc"  => array(true,  "asc" ),
			"asc,nulls"  => array(false, "asc" ),
			"nulls,desc" => array(true,  "desc"),
			"desc,nulls" => array(false, "desc"),
			"asc"        => array(true,  "asc" ),
			"desc"       => array(false, "desc"),
		);
		if (!is_string($order))
		{
			$order = 'desc,nulls';
		}
		$order = mb_strtolower(trim($order));
		$default_order = mb_strtolower(trim($default_order));
		if (isset($arOrder[$order]))
			$o = $arOrder[$order];
		elseif(isset($arOrder[$default_order]))
			$o = $arOrder[$default_order];
		else
			$o = $arOrder["desc,nulls"];

		//There is no need to "reverse" nulls order when
		//column can not contain nulls
		if(!$nullable)
		{
			$o[0] = ($o[1] == "asc");
		}

		return $o;
	}

	public static function GetAdminIBlockEditLink($IBLOCK_ID, $arParams = array(), $strAdd = "")
	{
		if (
			(
				defined("CATALOG_PRODUCT")
				|| (isset($arParams["force_catalog"]) && $arParams["force_catalog"])
				|| array_key_exists('catalog', $arParams)
			)
			&& !array_key_exists("menu", $arParams)
		)
		{
			$url = "cat_catalog_edit.php";
			$param = "IBLOCK_ID";
		}
		else
		{
			$url = "iblock_edit.php";
			$param = "ID";
		}

		$url.= "?".$param."=".intval($IBLOCK_ID);
		$url.= "&type=".urlencode(CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"));
		$url.= "&admin=Y";
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);

		if ($arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param int|null $SECTION_ID
	 * @param array $arParams
	 * @param string $strAdd
	 * @return string
	 */
	public static function GetAdminSectionEditLink($IBLOCK_ID, $SECTION_ID, $arParams = array(), $strAdd = "")
	{
		if (
			(
				defined("CATALOG_PRODUCT")
				|| (isset($arParams["force_catalog"]) && $arParams["force_catalog"])
				|| array_key_exists('catalog', $arParams)
			)
			&& !array_key_exists("menu", $arParams)
		)
			$url = "cat_section_edit.php";
		else
			$url = "iblock_section_edit.php";

		$url.= "?IBLOCK_ID=".intval($IBLOCK_ID);
		$url.= "&type=".urlencode(CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"));
		if($SECTION_ID !== null)
			$url.= "&ID=".intval($SECTION_ID);
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);

		if (isset($arParams["replace_script_name"]) && $arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param int|null $ELEMENT_ID
	 * @param array $arParams
	 * @param string $strAdd
	 * @return string
	 */
	public static function GetAdminElementEditLink($IBLOCK_ID, $ELEMENT_ID, $arParams = array(), $strAdd = "")
	{
		if (
			(
				defined("CATALOG_PRODUCT")
				|| (isset($arParams["force_catalog"]) && $arParams["force_catalog"])
			)
			&& !array_key_exists("menu", $arParams)
		)
			$url = "cat_product_edit.php";
		else
			$url = "iblock_element_edit.php";

		$url.= "?IBLOCK_ID=".intval($IBLOCK_ID);
		$url.= "&type=".urlencode(CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"));
		if($ELEMENT_ID !== null)
			$url.= "&ID=".intval($ELEMENT_ID);
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);

		if (isset($arParams["replace_script_name"]) && $arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	public static function GetAdminSubElementEditLink($IBLOCK_ID, $ELEMENT_ID, $SUBELEMENT_ID, $arParams = array(), $strAdd = '', $absoluteUrl = false)
	{
		$absoluteUrl = ($absoluteUrl === true);
		// it\s temporary hack
		if (defined('SELF_FOLDER_URL'))
		{
			$url = '/bitrix/tools/iblock/iblock_subelement_edit.php';
		}
		else
		{
			$url = ($absoluteUrl ? '/bitrix/admin/' : '') . 'iblock_subelement_edit.php';
		}
		$url .= '?IBLOCK_ID='.(int)$IBLOCK_ID.'&type='.urlencode(CIBlock::GetArrayByID($IBLOCK_ID, 'IBLOCK_TYPE_ID'));
		$url .= '&PRODUCT_ID='.(int)$ELEMENT_ID.'&ID='.(int)$SUBELEMENT_ID.'&lang='.LANGUAGE_ID;

		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= '&'.urlencode($name).'='.urlencode($value);

		if (isset($arParams["replace_script_name"]) && $arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param array $arParams
	 * @param string $strAdd
	 * @return string
	 */
	public static function GetAdminElementListLink($IBLOCK_ID, $arParams = array(), $strAdd = "")
	{
		$url = self::GetAdminElementListScriptName($IBLOCK_ID, $arParams);
		$url.= "?IBLOCK_ID=".intval($IBLOCK_ID);
		$url.= "&type=".urlencode(CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"));
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);

		if (isset($arParams["replace_script_name"]) && $arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param array $arParams
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function GetAdminElementListScriptName($IBLOCK_ID, $arParams = array())
	{
		if (!isset($arParams["skip_public"]))
		{
			if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1 || self::isPublicSidePanel())
			{
				return "menu_catalog_" . $IBLOCK_ID . "/";
			}
		}

		if (defined("CATALOG_PRODUCT") && !array_key_exists("menu", $arParams))
		{
			if (CIBlock::GetAdminListMode($IBLOCK_ID) == Iblock\IblockTable::LIST_MODE_COMBINED)
				$url = "cat_product_list.php";
			else
				$url = "cat_product_admin.php";
		}
		else
		{
			if (CIBlock::GetAdminListMode($IBLOCK_ID) == Iblock\IblockTable::LIST_MODE_COMBINED)
				$url = "iblock_list_admin.php";
			else
				$url = "iblock_element_admin.php";
		}

		return $url;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param array $arParams
	 * @param string $strAdd
	 * @return string
	 */
	public static function GetAdminSectionListLink($IBLOCK_ID, $arParams = array(), $strAdd = "")
	{
		$url = self::GetAdminSectionListScriptName($IBLOCK_ID, $arParams);
		$url.= "?IBLOCK_ID=".intval($IBLOCK_ID);
		$url.= "&type=".urlencode(CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"));
		$url.= "&lang=".urlencode(LANGUAGE_ID);
		foreach ($arParams as $name => $value)
			if (isset($value))
				$url.= "&".urlencode($name)."=".urlencode($value);

		if (isset($arParams["replace_script_name"]) && $arParams["replace_script_name"])
		{
			$url = self::replaceScriptName($url);
		}

		$url = str_replace("&skip_public=1", "", $url);

		return $url.$strAdd;
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Iblock\Url\AdminPage\IblockBuilder
	 *
	 * @param int $IBLOCK_ID
	 * @param array $arParams
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function GetAdminSectionListScriptName($IBLOCK_ID, $arParams = array())
	{
		if (!isset($arParams["skip_public"]))
		{
			if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1 || self::isPublicSidePanel())
			{
				return "menu_catalog_category_".$IBLOCK_ID."/";
			}
		}

		if ((defined("CATALOG_PRODUCT") || array_key_exists('catalog', $arParams)) && !array_key_exists("menu", $arParams))
		{
			if (CIBlock::GetAdminListMode($IBLOCK_ID) == Iblock\IblockTable::LIST_MODE_COMBINED)
				$url = "cat_product_list.php";
			else
				$url = "cat_section_admin.php";
		}
		else
		{
			if (CIBlock::GetAdminListMode($IBLOCK_ID) == Iblock\IblockTable::LIST_MODE_COMBINED)
				$url = "iblock_list_admin.php";
			else
				$url = "iblock_section_admin.php";
		}

		return $url;
	}

	private static function isPublicSidePanel()
	{
		$iframe = $_REQUEST["IFRAME"] ?? null;
		$iframeType = $_REQUEST["IFRAME_TYPE"] ?? null;
		$publicSidePanel = $_REQUEST["publicSidePanel"] ?? null;

		return
			$iframe === "Y"
			&& ($publicSidePanel === "Y" || $iframeType === "PUBLIC_FRAME")
		;
	}

	private static function replaceScriptName($url)
	{
		if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1 || self::isPublicSidePanel())
		{
			$url = str_replace(".php", "/", $url);
		}

		return str_replace("&replace_script_name=1", "", $url);
	}

	/**
	 * @param int $IBLOCK_ID
	 * @return string
	 */
	public static function GetAdminListMode($IBLOCK_ID): string
	{
		$list_mode = (string)CIBlock::GetArrayByID($IBLOCK_ID, "LIST_MODE");

		if (
			$list_mode == Iblock\IblockTable::LIST_MODE_SEPARATE
			|| $list_mode == Iblock\IblockTable::LIST_MODE_COMBINED
		)
		{
			return $list_mode;
		}
		else
		{
			return
				Main\Config\Option::get('iblock', 'combined_list_mode') === 'Y'
					? Iblock\IblockTable::LIST_MODE_COMBINED
					: Iblock\IblockTable::LIST_MODE_SEPARATE
			;
		}
	}

	public static function CheckForIndexes($IBLOCK_ID)
	{
		global $DB;
		$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);

		$ar = $arIBlock["FIELDS"]["CODE"]["DEFAULT_VALUE"];
		if (
			is_array($ar)
			&& $ar["UNIQUE"] == "Y"
			&& !$DB->IndexExists("b_iblock_element", array("IBLOCK_ID", "CODE"))
		)
			$DB->DDL("create index ix_iblock_element_code on b_iblock_element (IBLOCK_ID, CODE)");

		$ar = $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"];
		if (
			is_array($ar)
			&& $ar["UNIQUE"] == "Y"
			&& !$DB->IndexExists("b_iblock_section", array("IBLOCK_ID", "CODE"))
		)
			$DB->DDL("create index ix_iblock_section_code on b_iblock_section (IBLOCK_ID, CODE)");
	}

	public static function GetAuditTypes()
	{
		return array(
			"IBLOCK_SECTION_ADD" => "[IBLOCK_SECTION_ADD] ".Loc::getMessage("IBLOCK_SECTION_ADD"),
			"IBLOCK_SECTION_EDIT" => "[IBLOCK_SECTION_EDIT] ".Loc::getMessage("IBLOCK_SECTION_EDIT"),
			"IBLOCK_SECTION_DELETE" => "[IBLOCK_SECTION_DELETE] ".Loc::getMessage("IBLOCK_SECTION_DELETE"),
			"IBLOCK_ELEMENT_ADD" => "[IBLOCK_ELEMENT_ADD] ".Loc::getMessage("IBLOCK_ELEMENT_ADD"),
			"IBLOCK_ELEMENT_EDIT" => "[IBLOCK_ELEMENT_EDIT] ".Loc::getMessage("IBLOCK_ELEMENT_EDIT"),
			"IBLOCK_ELEMENT_DELETE" => "[IBLOCK_ELEMENT_DELETE] ".Loc::getMessage("IBLOCK_ELEMENT_DELETE"),
			"IBLOCK_ADD" => "[IBLOCK_ADD] ".Loc::getMessage("IBLOCK_ADD"),
			"IBLOCK_EDIT" => "[IBLOCK_EDIT] ".Loc::getMessage("IBLOCK_EDIT"),
			"IBLOCK_DELETE" => "[IBLOCK_DELETE] ".Loc::getMessage("IBLOCK_DELETE"),
		);
	}

	public static function roundDB($value)
	{
		$len = 18;
		$dec = 4;
		$eps = 1.00 / pow(10, $len + 4);
		$rounded = round((float)$value + $eps, $len);
		if (is_nan($rounded) || is_infinite($rounded))
			$rounded = 0;

		$result = sprintf("%01.".$dec."f", $rounded);
		if (mb_strlen($result) > ($len - $dec))
			$result = trim(mb_substr($result, 0, $len - $dec), ".");

		return $result;
	}

	public static function _transaction_lock($IBLOCK_ID)
	{
		/** @global CDatabase $DB */
		global $DB;

		$DB->Query("UPDATE b_iblock set TMP_ID = '".md5(mt_rand())."' WHERE ID = ".$IBLOCK_ID);
	}

	public static function isShortDate($strDate)
	{
		$arDate = ParseDateTime($strDate, FORMAT_DATETIME);
		unset($arDate["DD"]);
		unset($arDate["MMMM"]);
		unset($arDate["MM"]);
		unset($arDate["M"]);
		unset($arDate["YYYY"]);
		return array_sum($arDate) == 0;
	}

	public static function _Upper($str)
	{
		return $str;
	}

	function _Add($ID)
	{
		return false;
	}

	public static function _NotEmpty($column)
	{
		return "";
	}

	public static function makeFilePropArray($data, $del = false, $description = null, $options = array())
	{
		if (is_array($data) && array_key_exists("VALUE", $data))
		{
			$data["VALUE"] = self::makeFileArray($data["VALUE"], $del, $description, $options);
		}
		else
		{
			$data = array(
				"VALUE" => self::makeFileArray($data, $del, $description, $options),
			);
		}

		if (array_key_exists("description", $data["VALUE"] ?? []))
		{
			$data["DESCRIPTION"] = $data["VALUE"]["description"];
		}

		return $data;
	}

	public static function makeFileArray($data, $del = false, $description = null, $options = array())
	{
		$emptyFile = array(
			"name" => null,
			"type" => null,
			"tmp_name" => null,
			"error" => 4,
			"size" => 0,
		);

		if ($del)
		{
			$result = $emptyFile;
			$result["del"] = "Y";
		}
		elseif (is_null($data))
		{
			$result = $emptyFile;
		}
		elseif (is_numeric($data))
		{
			$result = self::makeFileArrayFromId($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		elseif (is_string($data))
		{
			$result = self::makeFileArrayFromPath($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		elseif (is_array($data))
		{
			$result = self::makeFileArrayFromArray($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		else
		{
			$result = $emptyFile;
		}

		return $result;
	}

	private static function makeFileArrayFromId($file_id, $description = null, $options = array())
	{
		$result = false;

		if (isset($options["allow_file_id"]) && $options["allow_file_id"] === true)
		{
			$result = CFile::MakeFileArray($file_id);
		}

		if (!is_null($description))
		{
			$result = ($result === false ? array(
				"name" => null,
				"type" => null,
				"tmp_name" => null,
				"error" => 4,
				"size" => 0,
			) : $result);
			$result["description"] = $description;
		}
		return $result;
	}

	private static function makeFileArrayFromPath($file_path, $description = null, $options = array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$result = false;

		if (preg_match("/^https?:\\/\\//", $file_path))
		{
			$result = CFile::MakeFileArray($file_path);
		}
		else
		{
			$io = CBXVirtualIo::GetInstance();
			$normPath = $io->CombinePath("/", $file_path);
			$absPath = $io->CombinePath($_SERVER["DOCUMENT_ROOT"], $normPath);
			if ($io->ValidatePathString($absPath) && $io->FileExists($absPath))
			{
				$physicalName = $io->GetPhysicalName($absPath);
				$uploadDir = $io->GetPhysicalName(preg_replace("#[\\\\\\/]+#", "/", $_SERVER['DOCUMENT_ROOT'].'/'.(COption::GetOptionString('main', 'upload_dir', 'upload')).'/'));
				if (mb_strpos($physicalName, $uploadDir) === 0)
				{
					$result = CFile::MakeFileArray($physicalName);
				}
				else
				{
					$perm = $APPLICATION->GetFileAccessPermission($normPath);
					if ($perm >= "W")
					{
						$result = CFile::MakeFileArray($physicalName);
					}
				}
			}
		}

		if (is_array($result))
		{
			if (!is_null($description))
				$result["description"] = $description;
		}

		return $result;
	}

	private static function makeFileArrayFromArray($file_array, $description = null, $options = array())
	{
		$result = false;

		if (is_uploaded_file($file_array["tmp_name"]))
		{
			$result = $file_array;
			if (!is_null($description))
				$result["description"] = $description;
		}
		elseif (
			$file_array["tmp_name"] <> ''
			&& mb_strpos($file_array["tmp_name"], CTempFile::GetAbsoluteRoot()) === 0
		)
		{
			$io = CBXVirtualIo::GetInstance();
			$absPath = $io->CombinePath("/", $file_array["tmp_name"]);
			$tmpPath = CTempFile::GetAbsoluteRoot()."/";
			if (mb_strpos($absPath, $tmpPath) === 0 || (($absPath = ltrim($absPath, "/")) && mb_strpos($absPath, $tmpPath) === 0))
			{
				$result = $file_array;
				$result["tmp_name"] = $absPath;
				$result['error'] = (int)($result['error'] ?? 0);
				if (!is_null($description))
					$result["description"] = $description;
			}
		}
		elseif ($file_array["tmp_name"] <> '')
		{
			$io = CBXVirtualIo::GetInstance();
			$normPath = $io->CombinePath("/", $file_array["tmp_name"]);
			$absPath = $io->CombinePath(CTempFile::GetAbsoluteRoot(), $normPath);
			$tmpPath = CTempFile::GetAbsoluteRoot()."/";
			if (mb_strpos($absPath, $tmpPath) === 0 && $io->FileExists($absPath) ||
				($absPath = $io->CombinePath($_SERVER["DOCUMENT_ROOT"], $normPath)) && mb_strpos($absPath, $tmpPath) === 0)
			{
				$result = $file_array;
				$result["tmp_name"] = $absPath;
				$result['error'] = (int)($result['error'] ?? 0);
				if (!is_null($description))
					$result["description"] = $description;
			}
		}
		else
		{
			$emptyFile = array(
				"name" => null,
				"type" => null,
				"tmp_name" => null,
				"error" => 4,
				"size" => 0,
			);
			if ($file_array == $emptyFile)
			{
				$result = $emptyFile;
				if (!is_null($description))
					$result["description"] = $description;
			}
		}

		return $result;
	}

	public static function disableTagCache($iblock_id)
	{
		$iblock_id = (int)$iblock_id;
		if ($iblock_id > 0)
			self::$disabledCacheTag[$iblock_id] = $iblock_id;
	}

	public static function enableTagCache($iblock_id)
	{
		$iblock_id = (int)$iblock_id;
		if (isset(self::$disabledCacheTag[$iblock_id]))
			unset(self::$disabledCacheTag[$iblock_id]);
	}

	public static function clearIblockTagCache($iblock_id)
	{
		global $CACHE_MANAGER;
		$iblock_id = (int)$iblock_id;
		if (defined("BX_COMP_MANAGED_CACHE") && $iblock_id > 0 && self::isEnabledClearTagCache())
			$CACHE_MANAGER->ClearByTag('iblock_id_'.$iblock_id);
	}

	public static function registerWithTagCache($iblock_id)
	{
		global $CACHE_MANAGER;
		$iblock_id = (int)$iblock_id;
		if ($iblock_id > 0 && !isset(self::$disabledCacheTag[$iblock_id]))
			$CACHE_MANAGER->RegisterTag("iblock_id_".$iblock_id);
	}

	public static function enableClearTagCache()
	{
		self::$enableClearTagCache++;
	}

	public static function disableClearTagCache()
	{
		self::$enableClearTagCache--;
	}

	public static function isEnabledClearTagCache(): bool
	{
		return (self::$enableClearTagCache >= 0);
	}

	public static function getDefaultJpegQuality(): int
	{
		$jpgQuality = (int)Main\Config\Option::get('main', 'image_resize_quality', '95');
		if ($jpgQuality <= 0 || $jpgQuality > 100)
		{
			$jpgQuality = 95;
		}

		return $jpgQuality;
	}

	public static function checkActivityDatesAgent($iblockId, $previousTime): string
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
		{
			return '';
		}
		$currentTime = time();
		$result = '\CIBlock::checkActivityDatesAgent('.$iblockId.', '.$currentTime.');';
		$previousTime = (int)$previousTime;
		if ($previousTime <= 0)
		{
			return $result;
		}

		$start = Main\Type\DateTime::createFromTimestamp($previousTime);
		$finish = Main\Type\DateTime::createFromTimestamp($currentTime);

		$iterator = Iblock\ElementTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
				'=WF_STATUS_ID' => 1,
				'=WF_PARENT_ELEMENT_ID' => null,
				array(
					'LOGIC' => 'OR',
					array(
						'>ACTIVE_FROM' => $start,
						'<=ACTIVE_FROM' => $finish
					),
					array(
						'>ACTIVE_TO' => $start,
						'<=ACTIVE_TO' => $finish
					)
				)
			),
			'limit' => 1
		));
		unset($finish);
		unset($start);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
		{
			static::clearIblockTagCache($iblockId);
		}
		unset($row);

		return $result;
	}

	/**
	 * Returns default rights for apply to iblock (admin access and public reading).
	 *
	 * @return array
	 */
	public static function getDefaultRights(): array
	{
		return array(
			1 => \CIBlockRights::FULL_ACCESS,
			2 => \CIBlockRights::PUBLIC_READ
		);
	}

	public static function isUniqueElementCode(int $id): bool
	{
		if ($id <= 0)
		{
			return false;
		}

		$iblock = static::GetFields($id);

		return (
			isset($iblock['CODE']['DEFAULT_VALUE']['UNIQUE'])
			&& $iblock['CODE']['DEFAULT_VALUE']['UNIQUE'] === 'Y'
		);
	}

	public static function isUniqueSectionCode(int $id): bool
	{
		if ($id <= 0)
		{
			return false;
		}

		$iblock = static::GetFields($id);

		return (
			isset($iblock['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE'])
			&& $iblock['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE'] === 'Y'
		);
	}

	protected static function getFieldsDefaultValues(): array
	{
		$jpgQuality = static::getDefaultJpegQuality();

		return [
			'IBLOCK_SECTION' => [
				'KEEP_IBLOCK_SECTION_ID' => 'N',
			],
			'ACTIVE' => 'Y',
			'ACTIVE_FROM' => '',
			'ACTIVE_TO' => '',
			'SORT' => 500,
			'NAME' => '',
			'PREVIEW_PICTURE' => [
				'FROM_DETAIL' => 'N',
				'UPDATE_WITH_DETAIL' => 'N',
				'DELETE_WITH_DETAIL' => 'N',
				'SCALE' => 'N',
				'WIDTH' => '',
				'HEIGHT' => '',
				'IGNORE_ERRORS' => 'N',
				'METHOD' => 'resample',
				'COMPRESSION' => $jpgQuality,
				'USE_WATERMARK_TEXT' => 'N',
				'WATERMARK_TEXT' => '',
				'WATERMARK_TEXT_FONT' => '',
				'WATERMARK_TEXT_COLOR' => '',
				'WATERMARK_TEXT_SIZE' => '',
				'WATERMARK_TEXT_POSITION' => '',
				'USE_WATERMARK_FILE' => 'N',
				'WATERMARK_FILE' => '',
				'WATERMARK_FILE_ALPHA' => '',
				'WATERMARK_FILE_POSITION' => '',
				'WATERMARK_FILE_ORDER' => '', // unused
			],
			'PREVIEW_TEXT_TYPE' => 'text',
			'PREVIEW_TEXT' => '',
			'DETAIL_PICTURE' => array(
				'SCALE' => 'N',
				'WIDTH' => '',
				'HEIGHT' => '',
				'IGNORE_ERRORS' => 'N',
				'METHOD' => 'resample',
				'COMPRESSION' => $jpgQuality,
				'USE_WATERMARK_TEXT' => 'N',
				'WATERMARK_TEXT' => '',
				'WATERMARK_TEXT_FONT' => '',
				'WATERMARK_TEXT_COLOR' => '',
				'WATERMARK_TEXT_SIZE' => '',
				'WATERMARK_TEXT_POSITION' => '',
				'USE_WATERMARK_FILE' => 'N',
				'WATERMARK_FILE' => '',
				'WATERMARK_FILE_ALPHA' => '',
				'WATERMARK_FILE_POSITION' => '',
				'WATERMARK_FILE_ORDER' => '', // unused
			),
			'DETAIL_TEXT_TYPE' => 'text',
			'DETAIL_TEXT' => '',
			'XML_ID' => '',
			'CODE' => [
				'UNIQUE' => 'N',
				'TRANSLITERATION' => 'N',
				'TRANS_LEN' => 100,
				'TRANS_CASE' => 'L',
				'TRANS_SPACE' => '-',
				'TRANS_OTHER' => '-',
				'TRANS_EAT' => 'Y',
				'USE_GOOGLE' => 'N',
			],
			'TAGS' => '',
			'SECTION_NAME' => '',
			'SECTION_PICTURE' => [
				'FROM_DETAIL' => 'N',
				'UPDATE_WITH_DETAIL' => 'N',
				'DELETE_WITH_DETAIL' => 'N',
				'SCALE' => 'N',
				'WIDTH' => '',
				'HEIGHT' => '',
				'IGNORE_ERRORS' => 'N',
				'METHOD' => 'resample',
				'COMPRESSION' => $jpgQuality,
				'USE_WATERMARK_TEXT' => 'N',
				'WATERMARK_TEXT' => '',
				'WATERMARK_TEXT_FONT' => '',
				'WATERMARK_TEXT_COLOR' => '',
				'WATERMARK_TEXT_SIZE' => '',
				'WATERMARK_TEXT_POSITION' => '',
				'USE_WATERMARK_FILE' => 'N',
				'WATERMARK_FILE' => '',
				'WATERMARK_FILE_ALPHA' => '',
				'WATERMARK_FILE_POSITION' => '',
				'WATERMARK_FILE_ORDER' => '', // unused
			],
			'SECTION_DESCRIPTION_TYPE' => 'text',
			'SECTION_DESCRIPTION' => '',
			'SECTION_DETAIL_PICTURE' => [
				'SCALE' => 'N',
				'WIDTH' => '',
				'HEIGHT' => '',
				'IGNORE_ERRORS' => 'N',
				'METHOD' => 'resample',
				'COMPRESSION' => $jpgQuality,
				'USE_WATERMARK_TEXT' => 'N',
				'WATERMARK_TEXT' => '',
				'WATERMARK_TEXT_FONT' => '',
				'WATERMARK_TEXT_COLOR' => '',
				'WATERMARK_TEXT_SIZE' => '',
				'WATERMARK_TEXT_POSITION' => '',
				'USE_WATERMARK_FILE' => 'N',
				'WATERMARK_FILE' => '',
				'WATERMARK_FILE_ALPHA' => '',
				'WATERMARK_FILE_POSITION' => '',
				'WATERMARK_FILE_ORDER' => '',
			],
			'SECTION_XML_ID' => '',
			'SECTION_CODE' => [
				'UNIQUE' => 'N',
				'TRANSLITERATION' => 'N',
				'TRANS_LEN' => 100,
				'TRANS_CASE' => 'L',
				'TRANS_SPACE' => '-',
				'TRANS_OTHER' => '-',
				'TRANS_EAT' => 'Y',
				'USE_GOOGLE' => 'N',
			],
			'LOG_SECTION_ADD' => false,
			'LOG_SECTION_EDIT' => false,
			'LOG_SECTION_DELETE' => false,
			'LOG_ELEMENT_ADD' => false,
			'LOG_ELEMENT_EDIT' => false,
			'LOG_ELEMENT_DELETE' => false,
			'XML_IMPORT_START_TIME' => false,
			'DETAIL_TEXT_TYPE_ALLOW_CHANGE' => 'Y',
			'PREVIEW_TEXT_TYPE_ALLOW_CHANGE' => 'Y',
			'SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE' => 'Y',
		];
	}

	protected static function prepareDetailPictureFieldSettings(array $settings): array
	{
		$compression = (int)$settings['COMPRESSION'];
		if ($compression > 100)
		{
			$compression = 100;
		}
		elseif ($compression <= 0)
		{
			$compression = '';
		}

		return [
			'SCALE' => $settings['SCALE'] === 'Y'? 'Y': 'N',
			'WIDTH' => (int)$settings['WIDTH'] ?: '',
			'HEIGHT' => (int)$settings['HEIGHT'] ?: '',
			'IGNORE_ERRORS' => $settings['IGNORE_ERRORS'] === 'Y'? 'Y': 'N',
			'METHOD' => $settings['METHOD'] === 'resample'? 'resample': '',
			'COMPRESSION' => $compression,
			'USE_WATERMARK_TEXT' => $settings['USE_WATERMARK_TEXT'] === 'Y'? 'Y': 'N',
			'WATERMARK_TEXT' => $settings['WATERMARK_TEXT'],
			'WATERMARK_TEXT_FONT' => $settings['WATERMARK_TEXT_FONT'],
			'WATERMARK_TEXT_COLOR' => $settings['WATERMARK_TEXT_COLOR'],
			'WATERMARK_TEXT_SIZE' => (int)$settings['WATERMARK_TEXT_SIZE'] ?: '',
			'WATERMARK_TEXT_POSITION' => $settings['WATERMARK_TEXT_POSITION'],
			'USE_WATERMARK_FILE' => $settings['USE_WATERMARK_FILE'] === 'Y'? 'Y': 'N',
			'WATERMARK_FILE' => $settings['WATERMARK_FILE'],
			'WATERMARK_FILE_ALPHA' => (int)$settings['WATERMARK_FILE_ALPHA'] ?: '',
			'WATERMARK_FILE_POSITION' => $settings['WATERMARK_FILE_POSITION'],
			'WATERMARK_FILE_ORDER' => $settings['WATERMARK_FILE_ORDER'], // unused
		];
	}

	protected static function preparePreviewPictureFieldSettings(array $settings): array
	{
		$result = static::prepareDetailPictureFieldSettings($settings);

		$result['FROM_DETAIL'] = $settings['FROM_DETAIL'] === 'Y'? 'Y': 'N';
		$result['DELETE_WITH_DETAIL'] = $settings['DELETE_WITH_DETAIL'] === 'Y'? 'Y': 'N';
		$result['UPDATE_WITH_DETAIL'] = $settings['UPDATE_WITH_DETAIL'] === 'Y'? 'Y': 'N';

		return $result;
	}

	protected static function prepareCodeFieldSettings(array $settings): array
	{
		$maxLength = (int)$settings['TRANS_LEN'];
		if ($maxLength > 255)
		{
			$maxLength = 255;
		}
		elseif($maxLength < 1)
		{
			$maxLength = 100;
		}
		$transCase = (string)$settings['TRANS_CASE'];
		if ($transCase !== 'U' && $transCase !== '')
		{
			$transCase = 'L';
		}

		return [
			'UNIQUE' => $settings['UNIQUE'] === 'Y'? 'Y': 'N',
			'TRANSLITERATION' => $settings['TRANSLITERATION'] === 'Y'? 'Y': 'N',
			'TRANS_LEN' =>  $maxLength,
			'TRANS_CASE' => $transCase,
			'TRANS_SPACE' => substr($settings['TRANS_SPACE'], 0, 1),
			'TRANS_OTHER' => substr($settings['TRANS_OTHER'], 0, 1),
			'TRANS_EAT' => $settings['TRANS_EAT'] === 'N'? 'N': 'Y',
			'USE_GOOGLE' => $settings['USE_GOOGLE'] === 'Y'? 'Y': 'N',
		];
	}

	public static function getSinglePropertyValuesTableName(int $iblockId): string
	{
		return self::TABLE_PREFIX_SINGLE_PROPERTY_VALUES . $iblockId;
	}

	public static function getMultiplePropertyValuesTableName(int $iblockId): string
	{
		return self::TABLE_PREFIX_MULTIPLE_PROPERTY_VALUES . $iblockId;
	}

	public static function getCommonPropertyValuesTableName(): string
	{
		return self::TABLE_COMMON_PROPERTY_VALUES;
	}

	public function getLastError(): string
	{
		return $this->LAST_ERROR;
	}
}
