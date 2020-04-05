<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBitrixMenuComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams["CACHE_TYPE"] = $arParams["MENU_CACHE_TYPE"];
		$arParams["CACHE_TIME"] = $arParams["MENU_CACHE_TIME"];
		return $arParams;
	}

	public function getCacheID($additionalCacheID = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CUser $USER */
		global $USER;

		$strCacheID = "";
		if($this->arParams["MENU_CACHE_TIME"])
		{
			if($this->arParams["CACHE_SELECTED_ITEMS"])
				$strCacheID = $APPLICATION->GetCurPage();
			else
				$strCacheID = "";
			$strCacheID .=
				":".$this->arParams["USE_EXT"].
				":".$this->arParams["MAX_LEVEL"].
				":".$this->arParams["ROOT_MENU_TYPE"].
				":".$this->arParams["CHILD_MENU_TYPE"].
				":".LANGUAGE_ID.
				":".SITE_ID.
				""
			;

			if($this->arParams["MENU_CACHE_USE_GROUPS"] === "Y")
				$strCacheID .= ":".$USER->GetGroups();

			if($this->arParams["MENU_CACHE_USE_USERS"] === "Y")
				$strCacheID .= ":".$USER->GetID();

			if(is_array($this->arParams["MENU_CACHE_GET_VARS"]))
			{
				foreach($this->arParams["MENU_CACHE_GET_VARS"] as $name)
				{
					$name = trim($name);
					if($name != "" && array_key_exists($name, $_GET))
						$strCacheID .= ":".$name."=".$_GET[$name];
				}
			}

			$strCacheID = md5($strCacheID);
		}

		return $strCacheID;
	}

	public function getGenerationCachePath($id)
	{
		$hash = md5($id);
		$path = $this->getRelativePath()."/".substr($hash,-5,2)."/".substr($hash,-3);
		return $path;
	}

	public function getChildMenuRecursive(&$arMenu, &$arResult, $menuType, $use_ext, $menuTemplate, $currentLevel, $maxLevel, $bMultiSelect, $bCheckSelected, $parentItem)
	{
		if ($currentLevel > $maxLevel)
			return;

		for ($menuIndex = 0, $menuCount = count($arMenu); $menuIndex < $menuCount; $menuIndex++)
		{
			//Menu from iblock (bitrix:menu.sections)
			$arMenu[$menuIndex]["CHAIN"] = (is_array($parentItem) && !empty($parentItem["CHAIN"]) ? $parentItem["CHAIN"] : array());
			$arMenu[$menuIndex]["CHAIN"][] = $arMenu[$menuIndex]["TEXT"];

			if (is_array($arMenu[$menuIndex]["PARAMS"]) && isset($arMenu[$menuIndex]["PARAMS"]["FROM_IBLOCK"]))
			{
				$iblockSectionLevel = intval($arMenu[$menuIndex]["PARAMS"]["DEPTH_LEVEL"]);
				if ($currentLevel > 1)
					$iblockSectionLevel = $iblockSectionLevel + $currentLevel - 1;

				$arResult[] = $arMenu[$menuIndex] + Array("DEPTH_LEVEL" => $iblockSectionLevel, "IS_PARENT" => $arMenu[$menuIndex]["PARAMS"]["IS_PARENT"]);
				continue;
			}

			//Menu from files
			$subMenuExists = false;
			if ($currentLevel < $maxLevel)
			{
				//directory link only
				$bDir = false;
				if(!preg_match("'^(([a-z]+://)|mailto:|javascript:)'i", $arMenu[$menuIndex]["LINK"]))
				{
					if(substr($arMenu[$menuIndex]["LINK"], -1) == "/")
						$bDir = true;
				}
				if($bDir)
				{
					$menu = new CMenu($menuType);
					$menu->disableDebug();
					$success = $menu->Init($arMenu[$menuIndex]["LINK"], $use_ext, $menuTemplate, $onlyCurrentDir = true);
					$subMenuExists = ($success && count($menu->arMenu) > 0);

					if ($subMenuExists)
					{
						$menu->RecalcMenu($bMultiSelect, $bCheckSelected);

						$arResult[] = $arMenu[$menuIndex] + Array("DEPTH_LEVEL" => $currentLevel, "IS_PARENT" => (count($menu->arMenu) > 0));

						if($arMenu[$menuIndex]["SELECTED"])
						{
							$arResult["menuType"] = $menuType;
							$arResult["menuDir"] = $arMenu[$menuIndex]["LINK"];
						}

						if(count($menu->arMenu) > 0)
							$this->GetChildMenuRecursive($menu->arMenu, $arResult, $menuType, $use_ext, $menuTemplate, $currentLevel+1, $maxLevel, $bMultiSelect, $bCheckSelected, $arMenu[$menuIndex]);
					}
				}
			}

			if(!$subMenuExists)
				$arResult[] = $arMenu[$menuIndex] + Array("DEPTH_LEVEL" => $currentLevel, "IS_PARENT" => false);
		}
	}

	public function getMenuString($type = "left")
	{
		/** @var CMenuCustom*/
		global $BX_MENU_CUSTOM;
		global $APPLICATION;

		$sReturn = "";
		if ($APPLICATION->buffer_manual)
		{
			$arMenuCustom = $BX_MENU_CUSTOM->GetItems($type);
			if (is_array($arMenuCustom))
				$this->arResult = array_merge($this->arResult, $arMenuCustom);

			ob_start();
			$this->IncludeComponentTemplate();
			$sReturn = ob_get_contents();
			ob_end_clean();
		}
		return $sReturn;
	}

	public function setSelectedItems($bMultiSelect = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$cur_page = $APPLICATION->GetCurPage(true);
		$cur_page_no_index = $APPLICATION->GetCurPage(false);
		$cur_selected = -1;
		$cur_selected_len = -1;

		foreach($this->arResult as $iMenuItem => $MenuItem)
		{
			$LINK = $MenuItem['LINK'];
			$ADDITIONAL_LINKS = $MenuItem['ADDITIONAL_LINKS'];
			$SELECTED = false;

			$all_links = array();
			if(is_array($ADDITIONAL_LINKS))
			{
				foreach($ADDITIONAL_LINKS as $link)
				{
					$tested_link = trim($link);
					if(strlen($tested_link)>0)
						$all_links[] = $tested_link;
				}
			}
			$all_links[] = $LINK;

			if($MenuItem['PERMISSION'] != 'Z')
			{
				foreach($all_links as $tested_link)
				{
					if($tested_link == '')
						continue;

					$SELECTED = CMenu::IsItemSelected($tested_link, $cur_page, $cur_page_no_index);
					if($SELECTED)
					{
						$this->arResult[$iMenuItem]['SELECTED'] = true;
						break;
					}
				}
			}

			if($SELECTED && !$bMultiSelect)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$new_len = strlen($tested_link);
				if($new_len > $cur_selected_len)
				{
					if($cur_selected !== -1)
						$this->arResult[$cur_selected]['SELECTED'] = false;

					$cur_selected = $iMenuItem;
					$cur_selected_len = $new_len;
				}
				elseif($tested_link !== SITE_DIR)
				{
					$this->arResult[$iMenuItem]['SELECTED'] = false;
				}
			}
		}
	}
}
