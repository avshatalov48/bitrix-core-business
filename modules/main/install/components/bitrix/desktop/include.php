<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

CComponentUtil::__IncludeLang("/bitrix/components/bitrix/desktop/", "/include.php");

function GDCSaveSettings($arParams, $POS)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	if ($arParams["DEFAULT_ID"])
	{
		$user_option_id = 0;
		$arUserOptionsDefault = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], false, $user_option_id);
	}
	else
	{
		$user_option_id = false;
		$arUserOptionsDefault = false;
	}

	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);

	if (!$arUserOptions && !$user_option_id)
	{
		$tmp_desktop_id = false;
		if (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."index.php", SITE_DIR, "/")))
			$tmp_desktop_id = "mainpage";
		elseif (in_array($APPLICATION->GetCurPage(), array(SITE_DIR."desktop.php", "/desktop.php")))
			$tmp_desktop_id = "dashboard";

		if ($tmp_desktop_id !== false)
			$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$tmp_desktop_id, false, false);
	}

	if ($arParams["MULTIPLE"] == "Y")
	{
		$arUserOptionsTmp = $arUserOptions;
		$arUserOptions = $arUserOptions[$arParams["DESKTOP_PAGE"]];
	}

	if(!is_array($arUserOptions))
		$arUserOptions = array("GADGETS"=>array());
	$arNewUserOptions = array("GADGETS"=>array());

	if (array_key_exists("COLS", $arUserOptions))
		$arNewUserOptions["COLS"] = $arUserOptions["COLS"];
	if (array_key_exists("arCOLUMN_WIDTH", $arUserOptions))
		$arNewUserOptions["arCOLUMN_WIDTH"] = $arUserOptions["arCOLUMN_WIDTH"];
	if (array_key_exists("NAME", $arUserOptions))
		$arNewUserOptions["NAME"] = $arUserOptions["NAME"];

	foreach($POS as $col=>$items)
	{
		foreach($items as $row=>$gdId)
		{
			if(mb_substr($gdId, -2, 2) == "*H")
			{
				$gdId = mb_substr($gdId, 0, -2);
				$bHided = true;
			}
			else
				$bHided = false;

			if(is_array($arUserOptions["GADGETS"][$gdId]))
				$arNewUserOptions["GADGETS"][$gdId] = $arUserOptions["GADGETS"][$gdId];
			else
				$arNewUserOptions["GADGETS"][$gdId] = array();

			$arNewUserOptions["GADGETS"][$gdId]["COLUMN"] = $col;
			$arNewUserOptions["GADGETS"][$gdId]["ROW"] = $row;
			$arNewUserOptions["GADGETS"][$gdId]["HIDE"] = ($bHided?"Y":"N");
		}
	}

	if ($arParams["MULTIPLE"] == "Y")
	{
		$arUserOptionsTmp[$arParams["DESKTOP_PAGE"]] = $arNewUserOptions;
		$arNewUserOptions = $arUserOptionsTmp;
	}

	CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arNewUserOptions, false, $user_option_id);
}

class BXGadget
{
	public static function GetGadgetContent(&$arGadget, $arParams)
	{
		global $APPLICATION, $USER;

		CComponentUtil::__IncludeLang($arGadget["PATH_SITEROOT"], "/index.php");

		$arGadgetParams = $arGadget["SETTINGS"];
		$id = $arGadget["ID"];

		ob_start();
		include($arGadget["PATH"]."/index.php");
		return ob_get_clean();
	}

	public static function GetList($bWithParameters = false, $arAllCurrentValues = false)
	{
		$arGadgets = array();

		$folders = array(
			"/bitrix/gadgets",
			"/local/gadgets",
		);

		foreach($folders as $folder)
		{
			// Find all namespaces of gadgets
			$arGdNS = array("bitrix");
			$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder;
			if(is_dir($gdDir) && ($handle = opendir($gdDir)))
			{
				while(false !== ($item = readdir($handle)))
					if(is_dir($gdDir."/".$item) && $item != "." && $item != ".." && $item != "bitrix")
						$arGdNS[] = $item;
				closedir($handle);
			}

			// Find all gadgets
			foreach($arGdNS as $NS)
			{
				$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$NS;
				if(is_dir($gdDir) && ($handle = opendir($gdDir)))
				{
					while (false !== ($file = readdir($handle)))
					{
						if($file=="." || $file=="..")
							continue;
						$arGadgetParams = BXGadget::GetById($NS."/".$file, $bWithParameters, $arAllCurrentValues);
						if($arGadgetParams)
							$arGadgets[$file] = $arGadgetParams;
						else
							unset($arGadgets[$file]);
					}
					closedir($handle);
				}
			}
		}

		uasort($arGadgets, array("BXGadget", "_sort"));

		return $arGadgets;
	}

	public static function _sort($ar1, $ar2)
	{
		return strcmp($ar1["NAME"], $ar2["NAME"]);
	}

	public static function GetById($id, $bWithParameters = false, $arAllCurrentValues = false)
	{
		$id = _normalizePath(mb_strtolower($id));

		$folders = array(
			"/bitrix/gadgets",
			"/local/gadgets",
		);

		if(($p = mb_strpos($id, "/"))>0)
		{
			//specific namespace
			$arGdNS = array(mb_substr($id, 0, $p));
			$id = mb_substr($id, $p + 1);
		}
		else
		{
			// Find all namespaces of gadgets
			$arGdNS = array("bitrix");
			foreach($folders as $folder)
			{
				$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder;
				if(is_dir($gdDir) && ($handle = opendir($gdDir)))
				{
					while(false !== ($item = readdir($handle)))
						if(is_dir($gdDir."/".$item) && $item != "." && $item != ".." && $item != "bitrix")
							$arGdNS[] = $item;
					closedir($handle);
				}
			}
		}

		// Find all gadgets
		$arGadget = false;
		foreach($folders as $folder)
		{
			foreach($arGdNS as $NS)
			{
				$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$NS;
				$gdDirSiteRoot = $folder."/".$NS;
				if(is_dir($gdDir."/".$id))
				{
					$arDescription = array();

					CComponentUtil::__IncludeLang($gdDirSiteRoot."/".$id, "/.description.php");

					if(!file_exists($gdDir."/".$id."/.description.php"))
						continue;

					if(!@include($gdDir."/".$id."/.description.php"))
					{
						$arGadget = false;
						continue;
					}

					if(isset($arDescription["LANG_ONLY"]) && $arDescription["LANG_ONLY"]!=LANGUAGE_ID)
					{
						$arGadget = false;
						continue;
					}

					if($bWithParameters)
					{
						$arCurrentValues = array();
						if(is_array($arAllCurrentValues))
						{
							foreach($arAllCurrentValues as $k=>$v)
							{
								$pref = "G_".mb_strtoupper($id)."_";
								if(mb_substr($k, 0, mb_strlen($pref)) == $pref)
									$arCurrentValues[mb_substr($k, mb_strlen($pref))] = $v;
								else
								{
									$pref = "GU_".mb_strtoupper($id)."_";
									if(mb_substr($k, 0, mb_strlen($pref)) == $pref)
										$arCurrentValues[mb_substr($k, mb_strlen($pref))] = $v;
								}
							}
						}

						CComponentUtil::__IncludeLang($gdDirSiteRoot."/".$id, "/.parameters.php");

						$arParameters = array();

						if(file_exists($gdDir."/".$id."/.parameters.php"))
							include($gdDir."/".$id."/.parameters.php");
						$arDescription["PARAMETERS"] = $arParameters["PARAMETERS"];
						$arDescription["USER_PARAMETERS"] = array(
							"TITLE_STD" => array(
								"NAME" => GetMessage("CMDESKTOP_UP_TITLE_STD"),
								"TYPE" => "STRING",
								"DEFAULT" => ""
							)
						);
						if (array_key_exists("USER_PARAMETERS", $arParameters) && is_array($arParameters["USER_PARAMETERS"]))
							$arDescription["USER_PARAMETERS"] = array_merge($arDescription["USER_PARAMETERS"], $arParameters["USER_PARAMETERS"]);
					}
					$arDescription["PATH"] = $gdDir."/".$id;
					$arDescription["PATH_SITEROOT"] = $gdDirSiteRoot."/".$id;

					$arDescription["ID"] = mb_strtoupper($id);
					if($arDescription["ICON"] && mb_substr($arDescription["ICON"], 0, 1) != "/")
						$arDescription["ICON"] = "/bitrix/gadgets/".$NS."/".$id."/".$arDescription["ICON"];

					unset($arDescription["NOPARAMS"]);

					$arGadget = $arDescription;
				}
			}
		}
		return $arGadget;
	}
}
