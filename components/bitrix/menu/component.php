<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
* Bitrix vars
*
* @var array $arResult
* @var array $arParams
* @var CMain $APPLICATION
* @var CUser $USER
* @var CBitrixMenuComponent $this
*/

global $CACHE_MANAGER;

//Menu depth level
if (isset($arParams["MAX_LEVEL"]) && 1 < intval($arParams["MAX_LEVEL"]) && intval($arParams["MAX_LEVEL"]) < 5)
	$arParams["MAX_LEVEL"] = intval($arParams["MAX_LEVEL"]);
else
	$arParams["MAX_LEVEL"] = 1;

//Root menu type
if (isset($arParams["ROOT_MENU_TYPE"]) && $arParams["ROOT_MENU_TYPE"] <> '')
	$arParams["ROOT_MENU_TYPE"] = htmlspecialcharsbx(trim($arParams["ROOT_MENU_TYPE"]));
else
	$arParams["ROOT_MENU_TYPE"] = "left";

//Child menu type
if (isset($arParams["CHILD_MENU_TYPE"]) && $arParams["CHILD_MENU_TYPE"] <> '')
	$arParams["CHILD_MENU_TYPE"] = htmlspecialcharsbx(trim($arParams["CHILD_MENU_TYPE"]));
else
	$arParams["CHILD_MENU_TYPE"] = "left";

if (isset($arParams["MENU_TYPES"]) && is_array($arParams["MENU_TYPES"]) && !empty($arParams["MENU_TYPES"]))
{
	$arParams["MENU_TYPES"] = array_map(function($type) {
		return is_string($type) && $type !== '' ? htmlspecialcharsbx(trim($type)) : 'left';
	}, $arParams["MENU_TYPES"]);

	$arParams["ROOT_MENU_TYPE"] = isset($arParams["MENU_TYPES"][0]) ? $arParams["MENU_TYPES"][0] : 'left';
	$arParams["CHILD_MENU_TYPE"] = isset($arParams["MENU_TYPES"][1]) ? $arParams["MENU_TYPES"][1] : 'left';
}
else
{
	$arParams["MENU_TYPES"] = [
		$arParams["ROOT_MENU_TYPE"],
		$arParams["CHILD_MENU_TYPE"],
	];
}

//Include menu_ext.php
$arParams["USE_EXT"] = (isset($arParams["USE_EXT"]) && $arParams["USE_EXT"] == "Y" ? true : false);

$arParams["DELAY"] = (isset($arParams["DELAY"]) && $arParams["DELAY"] == "Y" ? "Y" : "N");

//Allow multiple highlightning of current item in menu
$arParams["ALLOW_MULTI_SELECT"] = (($arParams["ALLOW_MULTI_SELECT"] ?? '') == "Y");

//Find current menu item in RecalcMenu(). Cach ID depends on this parameter too
$arParams["CACHE_SELECTED_ITEMS"] = ($arParams["CACHE_SELECTED_ITEMS"] <> "N" && $arParams["CACHE_SELECTED_ITEMS"] !== false);

$curDir = $APPLICATION->GetCurDir();

if($this->startResultCache(false, false, (($arParams["MENU_CACHE_USE_USERS"] ?? '') === "Y"? $this->getGenerationCachePath($USER->GetID()): false)))
{
	if(defined("BX_COMP_MANAGED_CACHE"))
		$CACHE_MANAGER->registerTag("bitrix:menu");
	//Read root menu
	$menu = new CMenu($arParams["ROOT_MENU_TYPE"]);
	$menu->Init($curDir, $arParams["USE_EXT"], $componentPath."/stub.php");

	$menu->RecalcMenu($arParams["ALLOW_MULTI_SELECT"], $arParams["CACHE_SELECTED_ITEMS"]);

	$arResult = array();

	//Read child menu recursive
	if ($arParams["MAX_LEVEL"] > 1)
	{
		$this->getChildMenuRecursive(
			$menu->arMenu,
			$arResult,
			$arParams["MENU_TYPES"],
			$arParams["USE_EXT"],
			$menu->template,
			$currentLevel = 1,
			$arParams["MAX_LEVEL"],
			$arParams["ALLOW_MULTI_SELECT"],
			$arParams["CACHE_SELECTED_ITEMS"],
			false
		);

		if($arParams["SHOW_LAST_LEVEL_BUTTONS"]!="Y")
		{
			$arResult["menuDir"] = $menu->MenuDir;
			$arResult["menuType"] = $menu->type;
		}
		else
		{
			$arResult["initMenuDir"] = $menu->MenuDir;
			$arResult["initMenuType"] = $menu->type;
		}
	}
	else
	{
		$arResult = $menu->arMenu;
		$arResult["menuDir"] = $menu->MenuDir;
		$arResult["menuType"] = $menu->type;
		for ($menuIndex = 0, $menuCount = count($menu->arMenu); $menuIndex < $menuCount; $menuIndex++)
		{
			//Menu from iblock (bitrix:menu.sections)
			if (is_array($arResult[$menuIndex]["PARAMS"]) && isset($arResult[$menuIndex]["PARAMS"]["FROM_IBLOCK"]))
			{
				$arResult[$menuIndex]["DEPTH_LEVEL"] = $arResult[$menuIndex]["PARAMS"]["DEPTH_LEVEL"];
				$arResult[$menuIndex]["IS_PARENT"] = $arResult[$menuIndex]["PARAMS"]["IS_PARENT"];
			}
			else
			{
				//Menu from files
				$arResult[$menuIndex]["DEPTH_LEVEL"] = 1;
				$arResult[$menuIndex]["IS_PARENT"] = false;
			}
		}
	}

	unset($menu->arMenu);
	$this->endResultCache();
}

$menuDir = $arResult["menuDir"];
unset($arResult["menuDir"]);
$menuType = $arResult["menuType"];
unset($arResult["menuType"]);

//We can't write a new data in $arResult
//Let's hack $arParams
$arParams["MENU_DIR"] = $menuDir;

//we have no selected items therefore we should find selection now
if($arParams["CACHE_SELECTED_ITEMS"] == false)
	$this->setSelectedItems($arParams["ALLOW_MULTI_SELECT"]);

//****************
//Icons
//***************

if($USER->IsAuthorized())
{
	$menuExists = ($menuDir <> '');
	$bFileman = $USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoOperation('fileman_edit_menu_elements');
	$bMenuAdd =	$bFileman && ($curDir != $menuDir || !$menuExists); // fm_create_new_file is checked later
	$bMenuEdit = $bFileman && $menuExists && $USER->CanDoFileOperation('fm_edit_existent_file', array(SITE_ID, $menuDir.".".$menuType.".menu.php"));
	$bMenuDelete = $bFileman && $curDir == $menuDir && $USER->CanDoFileOperation('fm_delete_file', array(SITE_ID, $menuDir.".".$menuType.".menu.php"));

	if($bMenuAdd || $bMenuEdit || $bMenuDelete)
	{
		$arMenuTypes = GetMenuTypes(SITE_ID);
		$bDefaultItem = ($curDir == "/" && $menuType == "top" || $curDir <> "/" && $menuType == "left");
		$buttonID = "menus";
	}

	if ($bMenuAdd)
	{
		$bMenuAdd = false;
		$currentAddDir = $curDir;

		while($currentAddDir <> '')
		{
			$currentAddDir = rtrim($currentAddDir, "/");

			if (is_dir($_SERVER["DOCUMENT_ROOT"].$currentAddDir) && $USER->CanDoFileOperation('fm_create_new_file', array(SITE_ID, $currentAddDir."/.".$menuType.".menu.php")))
			{
				$bMenuAdd = true;
				$menuDirAdd = $currentAddDir;
				break;
			}

			$position = bxstrrpos($currentAddDir, "/");
			if ($position === false)
				break;

			$currentAddDir = mb_substr($currentAddDir, 0, $position + 1);
		}
	}

	$arIcons = array();

	if($bMenuEdit)
	{
		$menu_edit_url = $APPLICATION->GetPopupLink(array(
			"URL"=> "/bitrix/admin/public_menu_edit.php?lang=".LANGUAGE_ID.
				"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
				"&path=".urlencode($menuDir)."&name=".$menuType
			)
		);

		//Icons
		$arIcons[] = array(
			"URL" => 'javascript:'.$menu_edit_url,
			"ICON" => "bx-context-toolbar-edit-icon",
			"TITLE" => GetMessage("MAIN_MENU_EDIT"),
			"DEFAULT" => true,
		);

		//panel
		$static_var_name = 'BX_TOPPANEL_MENU_EDIT_'.$menuType;

		if (!defined($static_var_name))
		{
			define($static_var_name, 1);

			$APPLICATION->AddPanelButton(array(
				"HREF" => ($bDefaultItem ? 'javascript:'.$menu_edit_url : ''),
				"ID" => $buttonID,
				"ICON" => "bx-panel-menu-icon",
				"ALT" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_ALT')
					.($bDefaultItem ? ' '.'"'.(isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType]:$menuType).'"' : ''),
				"TEXT" => GetMessage("MAIN_MENU_TOP_PANEL_BUTTON_TEXT"),
				"MAIN_SORT"	=> "300",
				"SORT" => 10,
				"RESORT_MENU" => true,
				"HINT" => array(
					"TITLE" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_TEXT'),
					"TEXT" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_HINT'),
				)
			), $bDefaultItem);

			$aMenuItem =  array(
				"TEXT" => GetMessage(
					'MAIN_MENU_TOP_PANEL_ITEM_TEXT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"TITLE" => GetMessage(
					'MAIN_MENU_TOP_PANEL_ITEM_ALT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"SORT" => "100",
				"ICON" => "menu-edit",
				"ACTION" => $menu_edit_url,
				"DEFAULT" => $bDefaultItem,
			);
			$APPLICATION->AddPanelButtonMenu($buttonID, $aMenuItem);
		}
	}

	if ($bMenuAdd)
	{
		$newMenuType = $menuType;
		if($arParams["SHOW_LAST_LEVEL_BUTTONS"]=="Y" && $arParams["CHILD_MENU_TYPE"]!=$newMenuType)
			$newMenuType = $arParams["CHILD_MENU_TYPE"];

		/** @noinspection PhpUndefinedVariableInspection */
		$menu_edit_url = $APPLICATION->GetPopupLink(array(
			"URL" => "/bitrix/admin/public_menu_edit.php?new=Y&lang=".LANGUAGE_ID.
				"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
				"&path=".urlencode($menuDirAdd)."&name=".$newMenuType
			)
		);

		//Icons
		$arIcons[] = array(
			"URL"		=> 'javascript:'.$menu_edit_url,
			"ICON"		=> "menu-add",
			"TITLE"		=> GetMessage('MAIN_MENU_ADD_NEW'),
			"DEFAULT"	=> !$bMenuEdit  ? true : false,
			"IN_PARAMS_MENU" => true
		);

		//panel
		$static_var_name = 'BX_TOPPANEL_MENU_ADD_'.$newMenuType;

		if (!defined($static_var_name))
		{
			define($static_var_name, 1);

			$APPLICATION->AddPanelButton(array(
				"HREF" => ($bDefaultItem ? 'javascript:'.$menu_edit_url : ''),
				"ID" => $buttonID,
				"ICON" => "bx-panel-menu-icon",
				"ALT" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_ALT')
					.($bDefaultItem ? ' '.'&quot;'.(isset($arMenuTypes[$newMenuType]) ? $arMenuTypes[$newMenuType]:$newMenuType).'&quot;' : ''),
				"TEXT" => GetMessage("MAIN_MENU_TOP_PANEL_BUTTON_TEXT"),
				"MAIN_SORT" => "300",
				"SORT" => 10,
				"RESORT_MENU" => true,
				"HINT" => array(
					"TITLE" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_TEXT'),
					"TEXT" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_HINT'),
				)
			), false);

			$aMenuItem =  array(
				"TEXT" => GetMessage(
					'MAIN_MENU_ADD_TOP_PANEL_ITEM_TEXT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$newMenuType]) ? $arMenuTypes[$newMenuType] : $newMenuType))
				),
				"TITLE" => GetMessage(
					'MAIN_MENU_ADD_TOP_PANEL_ITEM_ALT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$newMenuType]) ? $arMenuTypes[$newMenuType] : $newMenuType))
				),
				"SORT" => "200",
				"ICON" => "menu-add",
				"ACTION" => $menu_edit_url,
				"DEFAULT" => false,
			);

			if (!defined('BX_TOPPANEL_MENU_SEPARATOR_INCLUDED'))
			{
				$APPLICATION->AddPanelButtonMenu($buttonID, array('SEPARATOR' => "Y", "SORT" => "150"));
				define('BX_TOPPANEL_MENU_SEPARATOR_INCLUDED', 1);
			}

			$APPLICATION->AddPanelButtonMenu($buttonID, $aMenuItem);
		}
	}

	if ($bMenuDelete)
	{
		$menu_del_url = "if(confirm('".CUtil::JSEscape(GetMessage('menu_comp_del_conf', array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType)))).
			"')) {BX.showWait(); BX.ajax.get('/bitrix/admin/public_menu_edit.php?lang=".LANGUAGE_ID.
			"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
			"&path=".urlencode($menuDir)."&name=".$menuType."&action=delete&".bitrix_sessid_get()."')}";

		//Icons
		$arIcons[] = array(
			"URL" => "javascript:".$menu_del_url,
			"ICON" => "menu-delete",
			"TITLE" => GetMessage('menu_comp_del_menu'),
			"IN_PARAMS_MENU" => true
		);

		//panel
		$static_var_name = 'BX_TOPPANEL_MENU_DEL_'.$menuType;

		if (!defined($static_var_name))
		{
			define($static_var_name, 1);

			$APPLICATION->AddPanelButton(array(
				"HREF" => '',
				"ID" => $buttonID,
				"ICON" => "bx-panel-menu-icon",
				"TEXT" => GetMessage("MAIN_MENU_TOP_PANEL_BUTTON_TEXT"),
				"MAIN_SORT" => "300",
				"SORT" => 10,
				"RESORT_MENU"=>true,
				"HINT" => array(
					"TITLE" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_TEXT'),
					"TEXT" => GetMessage('MAIN_MENU_TOP_PANEL_BUTTON_HINT'),
				)
			));

			$aMenuItem =  array(
				"TEXT" => GetMessage(
					'MAIN_MENU_DEL_TOP_PANEL_ITEM_TEXT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"TITLE" => GetMessage(
					'MAIN_MENU_DEL_TOP_PANEL_ITEM_ALT',
					array('#MENU_TITLE#' => (isset($arMenuTypes[$menuType]) ? $arMenuTypes[$menuType] : $menuType))
				),
				"SORT" => "300",
				"ICON" => "menu-delete",
				"ACTION" => $menu_del_url,
			);

			if (!defined('BX_TOPPANEL_DEL_MENU_SEPARATOR_INCLUDED'))
			{
				$APPLICATION->AddPanelButtonMenu($buttonID, array('SEPARATOR' => "Y", "SORT" => "250"));
				define('BX_TOPPANEL_DEL_MENU_SEPARATOR_INCLUDED', 1);
			}
			$APPLICATION->AddPanelButtonMenu($buttonID, $aMenuItem);
		}
	}

	if($bMenuAdd || $bMenuEdit || $bMenuDelete)
		$this->AddIncludeAreaIcons($arIcons);
}

//****************
//Delayed menu
//***************

if (
	!empty($arParams["RETURN"])
	&& $arParams["RETURN"] == "Y"
)
{
	return $arResult;
}

if ($arParams["DELAY"] == "Y")
{
	$APPLICATION->AddBufferContent(array($this, "GetMenuString"), $arParams["ROOT_MENU_TYPE"]);

	if ($this->InitComponentTemplate())
	{
		$APPLICATION->AddHeadScript($this->__template->__folder."/script.js");
		$APPLICATION->SetAdditionalCSS($this->__template->__folder."/style.css");
	}
}
else
{
	$this->IncludeComponentTemplate();
}
