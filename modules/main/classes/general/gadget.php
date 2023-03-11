<?php

IncludeModuleLangFile(__FILE__);

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
		$arGadgets = [];

		$folders = [
			"/bitrix/gadgets",
			"/local/gadgets",
		];

		foreach ($folders as $folder)
		{
			// Find all namespaces of gadgets
			$arGdNS = static::getNamespaces($_SERVER["DOCUMENT_ROOT"] . $folder);

			// Find all gadgets
			foreach ($arGdNS as $NS)
			{
				$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$NS;
				if (is_dir($gdDir) && ($handle = opendir($gdDir)))
				{
					while (false !== ($file = readdir($handle)))
					{
						if ($file=="." || $file=="..")
							continue;
						$arGadgetParams = BXGadget::GetById($NS."/".$file, $bWithParameters, $arAllCurrentValues);
						if ($arGadgetParams)
							$arGadgets[$file] = $arGadgetParams;
						else
							unset($arGadgets[$file]);
					}
					closedir($handle);
				}
			}
		}

		uasort($arGadgets, ["BXGadget", "_sort"]);

		return $arGadgets;
	}

	protected static function getNamespaces($gdDir)
	{
		$arGdNS = ["bitrix"];
		if (is_dir($gdDir) && ($handle = opendir($gdDir)))
		{
			while (($item = readdir($handle)) !== false)
			{
				if (is_dir($gdDir . "/" . $item) && $item != "." && $item != ".." && $item != "bitrix")
				{
					$arGdNS[] = $item;
				}
			}
			closedir($handle);
		}
		return $arGdNS;
	}

	public static function _sort($ar1, $ar2)
	{
		return strcmp($ar1["NAME"], $ar2["NAME"]);
	}

	public static function GetById($id, $bWithParameters = false, $arAllCurrentValues = false)
	{
		$id = _normalizePath(mb_strtolower($id));

		$folders = [
			"/bitrix/gadgets",
			"/local/gadgets",
		];

		$namespace = '';
		if (($p = mb_strpos($id, "/")) > 0)
		{
			//specific namespace
			$namespace = mb_substr($id, 0, $p);
			$id = mb_substr($id, $p + 1);
		}

		// Find all gadgets
		$arGadget = false;
		foreach ($folders as $folder)
		{
			// Find all namespaces of gadgets
			if ($namespace != '')
			{
				$arGdNS = [$namespace];
			}
			else
			{
				$arGdNS = static::getNamespaces($_SERVER["DOCUMENT_ROOT"] . $folder);
			}

			foreach ($arGdNS as $NS)
			{
				$gdDir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$NS;
				$gdDirSiteRoot = $folder."/".$NS;
				if (is_dir($gdDir."/".$id))
				{
					$arDescription = [];

					CComponentUtil::__IncludeLang($gdDirSiteRoot."/".$id, "/.description.php");

					if (!file_exists($gdDir."/".$id."/.description.php"))
						continue;

					if (!@include($gdDir."/".$id."/.description.php"))
					{
						$arGadget = false;
						continue;
					}

					if (isset($arDescription["LANG_ONLY"]) && $arDescription["LANG_ONLY"]!=LANGUAGE_ID)
					{
						$arGadget = false;
						continue;
					}

					if ($bWithParameters)
					{
						$arCurrentValues = [];
						if (is_array($arAllCurrentValues))
						{
							foreach ($arAllCurrentValues as $k => $v)
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

						$arParameters = [];

						if (file_exists($gdDir."/".$id."/.parameters.php"))
						{
							include($gdDir."/".$id."/.parameters.php");
						}
						$arDescription["PARAMETERS"] = $arParameters["PARAMETERS"];
						$arDescription["USER_PARAMETERS"] = array(
							"TITLE_STD" => array(
								"NAME" => GetMessage("CMDESKTOP_UP_TITLE_STD"),
								"TYPE" => "STRING",
								"DEFAULT" => ""
							)
						);
						if (isset($arParameters["USER_PARAMETERS"]) && is_array($arParameters["USER_PARAMETERS"]))
						{
							$arDescription["USER_PARAMETERS"] = array_merge($arDescription["USER_PARAMETERS"], $arParameters["USER_PARAMETERS"]);
						}
					}
					$arDescription["PATH"] = $gdDir."/".$id;
					$arDescription["PATH_SITEROOT"] = $gdDirSiteRoot."/".$id;

					$arDescription["ID"] = mb_strtoupper($id);
					if ($arDescription["ICON"] && mb_substr($arDescription["ICON"], 0, 1) != "/")
						$arDescription["ICON"] = "/bitrix/gadgets/".$NS."/".$id."/".$arDescription["ICON"];

					unset($arDescription["NOPARAMS"]);

					$arGadget = $arDescription;
				}
			}
		}
		return $arGadget;
	}

	public static function SavePositions($arParams, $positions)
	{
		$allOptions = static::readSettings($arParams);

		$arUserOptions = ($arParams["MULTIPLE"] == "Y" ? $allOptions[$arParams["DESKTOP_PAGE"]] : $allOptions);

		$arNewUserOptions = ["GADGETS" => []];

		if (isset($arUserOptions["COLS"]))
		{
			$arNewUserOptions["COLS"] = $arUserOptions["COLS"];
		}
		if (isset($arUserOptions["arCOLUMN_WIDTH"]))
		{
			$arNewUserOptions["arCOLUMN_WIDTH"] = $arUserOptions["arCOLUMN_WIDTH"];
		}
		if (isset($arUserOptions["NAME"]))
		{
			$arNewUserOptions["NAME"] = $arUserOptions["NAME"];
		}

		foreach ($positions as $col => $items)
		{
			foreach ($items as $row => $gdId)
			{
				if(mb_substr($gdId, -2, 2) == "*H")
				{
					$gdId = mb_substr($gdId, 0, -2);
					$hidden = true;
				}
				else
				{
					$hidden = false;
				}

				$arNewUserOptions["GADGETS"][$gdId] = $arUserOptions["GADGETS"][$gdId] ?? [];
				$arNewUserOptions["GADGETS"][$gdId]["COLUMN"] = $col;
				$arNewUserOptions["GADGETS"][$gdId]["ROW"] = $row;
				$arNewUserOptions["GADGETS"][$gdId]["HIDE"] = ($hidden? "Y" : "N");
			}
		}

		if ($arParams["MULTIPLE"] == "Y")
		{
			$allOptions[$arParams["DESKTOP_PAGE"]] = $arNewUserOptions;
		}
		else
		{
			$allOptions = $arNewUserOptions;
		}

		static::writeSettings($allOptions, $arParams);
	}

	public static function writeSettings(array $options, array $arParams): void
	{
		$userId = ($arParams["DEFAULT_ID"] ? 0 : false);

		CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $options, false, $userId);
	}

	public static function readSettings(array $arParams): array
	{
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

		if (!$arUserOptions)
		{
			$desktopId = false;
			$page = $APPLICATION->GetCurPage();
			if (in_array($page, array(SITE_DIR."index.php", SITE_DIR, "/")))
			{
				$desktopId = "mainpage";
			}
			elseif (in_array($page, array(SITE_DIR."desktop.php", "/desktop.php")))
			{
				$desktopId = "dashboard";
			}

			if ($desktopId !== false)
			{
				$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$desktopId);
			}
		}

		if (!is_array($arUserOptions))
		{
			$arUserOptions = [];
		}

		return $arUserOptions;
	}

	public static function getGadgetSettings($id, $arParams)
	{
		$arUserOptions = static::readSettings($arParams);

		if ($arParams["MULTIPLE"] == "Y")
		{
			$arUserOptions = $arUserOptions[$arParams["DESKTOP_PAGE"]];
		}

		return $arUserOptions["GADGETS"][$id]["SETTINGS"] ?? [];
	}

	public static function getDesktopParams($arParams)
	{
		return [
			"DEFAULT_ID" => ($arParams["DEFAULT_ID"] ?: ''),
			"ID" => $arParams["ID"],
			"MULTIPLE" => $arParams["MULTIPLE"],
			"DESKTOP_PAGE" => (int)$arParams["DESKTOP_PAGE"],
		];
	}
}
