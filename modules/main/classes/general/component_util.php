<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */

IncludeModuleLangFile(__FILE__);

class CComponentUtil
{
	public static function __IncludeLang($filePath, $fileName, $lang = false)
	{
		if ($lang === false)
			$lang = LANGUAGE_ID;

		if ($lang != "en" && $lang != "ru")
		{
			$subst_lang = LangSubst($lang);
			$fname = $_SERVER["DOCUMENT_ROOT"].$filePath."/lang/".$subst_lang."/".$fileName;
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $_SERVER["DOCUMENT_ROOT"].$filePath."/lang/".$lang."/".$fileName;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
		if (file_exists($fname))
		{
			__IncludeLang($fname, false, true);
		}
	}

	public static function PrepareVariables(&$arData)
	{
		unset($arData["NEW_COMPONENT_TEMPLATE"]);

		if ($arData["SEF_MODE"] == "Y")
		{
			unset($arData["VARIABLE_ALIASES"]);
			unset($arData["SEF_URL_TEMPLATES"]);

			foreach ($arData as $dataKey => $dataValue)
			{
				if (str_starts_with($dataKey, "SEF_URL_TEMPLATES_"))
				{
					$len = strlen("SEF_URL_TEMPLATES_");
					$arData["SEF_URL_TEMPLATES"][substr($dataKey, $len)] = $dataValue;
					unset($arData[$dataKey]);

					if (preg_match_all("'(\\?|&)(.+?)=#([^#]+?)#'is", $dataValue, $arMatches, PREG_SET_ORDER))
					{
						foreach ($arMatches as $arMatch)
							$arData["VARIABLE_ALIASES"][substr($dataKey, $len)][$arMatch[3]] = $arMatch[2];
					}
				}
				elseif (str_starts_with($dataKey, "VARIABLE_ALIASES_"))
				{
					unset($arData[$dataKey]);
				}
			}
		}
		else
		{
			unset($arData["VARIABLE_ALIASES"]);
			unset($arData["SEF_URL_TEMPLATES"]);

			foreach ($arData as $dataKey => $dataValue)
			{
				if (str_starts_with($dataKey, "SEF_URL_TEMPLATES_"))
				{
					unset($arData[$dataKey]);
				}
				elseif (str_starts_with($dataKey, "VARIABLE_ALIASES_"))
				{
					$arData["VARIABLE_ALIASES"][substr($dataKey, strlen("VARIABLE_ALIASES_"))] = $dataValue;
					unset($arData[$dataKey]);
				}
			}
		}
	}

	public static function __ShowError($errorMessage)
	{
		if ($errorMessage <> '')
			echo "<font color=\"#FF0000\">".$errorMessage."</font>";
	}

	public static function __BuildTree($arPath, &$arTree, &$arComponent, $level = 1)
	{
		$arBXTopComponentCatalogLevel = array("content", "service", "communication", "e-store", "utility");
		$arBXTopComponentCatalogLevelSort = array(600, 700, 800, 900, 1000);

		if (!isset($arTree["#"]) || !is_array($arTree["#"]))
			$arTree["#"] = array();

		if (!array_key_exists($arPath["ID"], $arTree["#"]))
		{
			$arTree["#"][$arPath["ID"]] = array();
			$arTree["#"][$arPath["ID"]]["@"] = array();
			$arTree["#"][$arPath["ID"]]["@"]["NAME"] = "";
			$arTree["#"][$arPath["ID"]]["@"]["SORT"] = intval($arPath["SORT"] ?? 0);
			if ($level == 1 && in_array($arPath["ID"], $arBXTopComponentCatalogLevel))
			{
				$arTree["#"][$arPath["ID"]]["@"]["NAME"] = GetMessage("VRT_COMP_CAT_".mb_strtoupper($arPath["ID"]));
				$arTree["#"][$arPath["ID"]]["@"]["SORT"] = intval($arBXTopComponentCatalogLevelSort[array_search($arPath["ID"], $arBXTopComponentCatalogLevel)]);
			}
			if ($arTree["#"][$arPath["ID"]]["@"]["NAME"] == '')
				$arTree["#"][$arPath["ID"]]["@"]["NAME"] = $arPath["NAME"] ?? '';
			if ($arTree["#"][$arPath["ID"]]["@"]["SORT"] <= 0)
				$arTree["#"][$arPath["ID"]]["@"]["SORT"] = 100;
		}

		if (array_key_exists("CHILD", $arPath))
		{
			CComponentUtil::__BuildTree($arPath["CHILD"], $arTree["#"][$arPath["ID"]], $arComponent, $level + 1);
		}
		else
		{
			if (!isset($arTree["#"][$arPath["ID"]]["*"]) || !is_array($arTree["#"][$arPath["ID"]]["*"]))
				$arTree["#"][$arPath["ID"]]["*"] = array();

			$arTree["#"][$arPath["ID"]]["*"][$arComponent["NAME"]] = $arComponent;
		}
	}

	public static function isComponent($componentPath)
	{
		$bDirectoryExists = file_exists($_SERVER["DOCUMENT_ROOT"].$componentPath)
			&& is_dir($_SERVER["DOCUMENT_ROOT"].$componentPath);
		if(!$bDirectoryExists)
			return false;

		$bComponentExists = file_exists($_SERVER["DOCUMENT_ROOT"].$componentPath."/component.php")
			&& is_file($_SERVER["DOCUMENT_ROOT"].$componentPath."/component.php");
		if($bComponentExists)
			return true;

		$bClassExists = file_exists($_SERVER["DOCUMENT_ROOT"].$componentPath."/class.php")
			&& is_file($_SERVER["DOCUMENT_ROOT"].$componentPath."/class.php");
		if($bClassExists)
			return true;

		return false;
	}

	public static function __GetComponentsTree($filterNamespace = false, $arNameFilter = false, $arFilter = false)
	{
		$arTree = array();
		$io = CBXVirtualIo::GetInstance();
		$folders = array(
			"/local/components",
			"/bitrix/components",
		);

		foreach($folders as $componentFolder)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$componentFolder))
			{
				if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$componentFolder))
				{
					while (($file = readdir($handle)) !== false)
					{
						if ($file == "." || $file == "..")
							continue;

						if (is_dir($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file))
						{
							if (CComponentUtil::isComponent($componentFolder."/".$file))
							{
								// It's component
								if ($filterNamespace !== false && $filterNamespace <> '')
									continue;
								if ($arNameFilter !== false && !CComponentUtil::CheckComponentName($file, $arNameFilter))
									continue;

								if (file_exists($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file."/.description.php"))
								{
									CComponentUtil::__IncludeLang($componentFolder."/".$file, ".description.php");

									$arComponentDescription = array();
									include($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file."/.description.php");

									if (isset($arFilter["TYPE"]) && $arFilter["TYPE"] != $arComponentDescription["TYPE"])
										continue;

									if (array_key_exists("PATH", $arComponentDescription) && array_key_exists("ID", $arComponentDescription["PATH"]))
									{
										$arComponent = array();
										$arComponent["NAME"] = $file;
										$arComponent["TYPE"] = (array_key_exists("TYPE", $arComponentDescription) ? $arComponentDescription["TYPE"] : "");
										$arComponent["NAMESPACE"] = "";
										$arComponent["TITLE"] = trim($arComponentDescription["NAME"]);
										$arComponent["DESCRIPTION"] = $arComponentDescription["DESCRIPTION"];

										if (array_key_exists("ICON", $arComponentDescription))
										{
											$arComponentDescription["ICON"] = ltrim($arComponentDescription["ICON"], "/");
											if($arComponentDescription["ICON"] != "" && $io->FileExists($io->RelativeToAbsolutePath($componentFolder."/".$file."/".$arComponentDescription["ICON"])))
												$arComponent["ICON"] = $componentFolder."/".$file."/".$arComponentDescription["ICON"];
											else
												$arComponent["ICON"] = "/bitrix/images/fileman/htmledit2/component.gif";
										}
										if (array_key_exists("COMPLEX", $arComponentDescription) && $arComponentDescription["COMPLEX"] == "Y")
											$arComponent["COMPLEX"] = "Y";
										else
											$arComponent["COMPLEX"] = "N";
										$arComponent["SORT"] = intval($arComponentDescription["SORT"]);
										if ($arComponent["SORT"] <= 0)
											$arComponent["SORT"] = 100;

										$arComponent["SCREENSHOT"] = array();
										if (array_key_exists("SCREENSHOT", $arComponentDescription))
										{
											if (!is_array($arComponentDescription["SCREENSHOT"]))
												$arComponentDescription["SCREENSHOT"] = array($arComponentDescription["SCREENSHOT"]);

											for ($i = 0, $cnt = count($arComponentDescription["SCREENSHOT"]); $i < $cnt; $i++)
												$arComponent["SCREENSHOT"][] = $componentFolder."/".$file.$arComponentDescription["SCREENSHOT"][$i];
										}

										CComponentUtil::__BuildTree($arComponentDescription["PATH"], $arTree, $arComponent);
									}
								}
							}
							else
							{
								// It's not a component
								if ($filterNamespace !== false && ($filterNamespace == '' || $filterNamespace != $file))
									continue;

								if ($handle1 = opendir($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file))
								{
									while (($file1 = readdir($handle1)) !== false)
									{
										if ($file1 == "." || $file1 == "..")
											continue;

										if (is_dir($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file."/".$file1))
										{
											if (CComponentUtil::isComponent($componentFolder."/".$file."/".$file1))
											{
												if ($arNameFilter !== false && !CComponentUtil::CheckComponentName($file1, $arNameFilter))
													continue;
												// It's component
												if (file_exists($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file."/".$file1."/.description.php"))
												{
													CComponentUtil::__IncludeLang($componentFolder."/".$file."/".$file1, ".description.php");

													$arComponentDescription = array();
													include($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file."/".$file1."/.description.php");

													if (isset($arFilter["TYPE"]) && (!isset($arComponentDescription["TYPE"]) || $arFilter["TYPE"] != $arComponentDescription["TYPE"]))
														continue;

													if (array_key_exists("PATH", $arComponentDescription) && array_key_exists("ID", $arComponentDescription["PATH"]))
													{
														$arComponent = array();
														$arComponent["NAME"] = $file.":".$file1;
														$arComponent["TYPE"] = (array_key_exists("TYPE", $arComponentDescription) ? $arComponentDescription["TYPE"] : "");
														$arComponent["NAMESPACE"] = $file;
														$arComponent["TITLE"] = trim($arComponentDescription["NAME"]);
														$arComponent["DESCRIPTION"] = $arComponentDescription["DESCRIPTION"];
														if (array_key_exists("ICON", $arComponentDescription))
														{
															$arComponentDescription["ICON"] = ltrim($arComponentDescription["ICON"], "/");
															if($arComponentDescription["ICON"] != "" && $io->FileExists($io->RelativeToAbsolutePath($componentFolder."/".$file."/".$file1."/".$arComponentDescription["ICON"])))
																$arComponent["ICON"] = $componentFolder."/".$file."/".$file1."/".$arComponentDescription["ICON"];
															else
																$arComponent["ICON"] = "/bitrix/images/fileman/htmledit2/component.gif";
														}
														if (array_key_exists("COMPLEX", $arComponentDescription) && $arComponentDescription["COMPLEX"] == "Y")
															$arComponent["COMPLEX"] = "Y";
														else
															$arComponent["COMPLEX"] = "N";
														$arComponent["SORT"] = intval($arComponentDescription["SORT"] ?? 0);
														if ($arComponent["SORT"] <= 0)
															$arComponent["SORT"] = 100;

														$arComponent["SCREENSHOT"] = array();
														if (array_key_exists("SCREENSHOT", $arComponentDescription))
														{
															if (!is_array($arComponentDescription["SCREENSHOT"]))
																$arComponentDescription["SCREENSHOT"] = array($arComponentDescription["SCREENSHOT"]);

															for ($i = 0, $cnt = count($arComponentDescription["SCREENSHOT"]); $i < $cnt; $i++)
																$arComponent["SCREENSHOT"][] = $componentFolder."/".$file."/".$file1.$arComponentDescription["SCREENSHOT"][$i];
														}

														CComponentUtil::__BuildTree($arComponentDescription["PATH"], $arTree, $arComponent);
													}
												}
											}
										}
									}
									closedir($handle1);
								}
							}
						}
					}
					closedir($handle);
				}
			}
		}

		return $arTree;
	}

	public static function __TreeFolderCompare($a, $b)
	{
		if ($a["@"]["SORT"] < $b["@"]["SORT"] || $a["@"]["SORT"] == $b["@"]["SORT"] && mb_strtolower($a["@"]["NAME"]) < mb_strtolower($b["@"]["NAME"]))
			return -1;
		elseif ($a["@"]["SORT"] > $b["@"]["SORT"] || $a["@"]["SORT"] == $b["@"]["SORT"] && mb_strtolower($a["@"]["NAME"]) > mb_strtolower($b["@"]["NAME"]))
			return 1;
		else
			return 0;
	}

	public static function __TreeItemCompare($a, $b)
	{
		if ($a["COMPLEX"] == "Y" && $b["COMPLEX"] == "Y" || $a["COMPLEX"] != "Y" && $b["COMPLEX"] != "Y")
		{
			if ($a["SORT"] < $b["SORT"] || $a["SORT"] == $b["SORT"] && mb_strtolower($a["TITLE"]) < mb_strtolower($b["TITLE"]))
				return -1;
			elseif ($a["SORT"] > $b["SORT"] || $a["SORT"] == $b["SORT"] && mb_strtolower($a["TITLE"]) > mb_strtolower($b["TITLE"]))
				return 1;
			else
				return 0;
		}
		else
		{
			if ($a["COMPLEX"] == "Y")
				return -1;
			if ($b["COMPLEX"] == "Y")
				return 1;
		}
		return 0;
	}

	public static function __SortComponentsTree(&$arTree)
	{
		uasort($arTree, array("CComponentUtil", "__TreeFolderCompare"));
		foreach ($arTree as $key => $value)
		{
			if (array_key_exists("#", $arTree[$key]))
				CComponentUtil::__SortComponentsTree($arTree[$key]["#"]);
			if (array_key_exists("*", $arTree[$key]))
				uasort($arTree[$key]["*"], array("CComponentUtil", "__TreeItemCompare"));
		}
	}

	public static function GetComponentsTree($filterNamespace = false, $arNameFilter = false, $arFilter = false)
	{
		$arTree = CComponentUtil::__GetComponentsTree($filterNamespace, $arNameFilter, $arFilter);

		CComponentUtil::__SortComponentsTree($arTree["#"]);

		return $arTree;
	}

	public static function GetNamespaceList()
	{
		$arNamespaces = array();
		$folders = array(
			"/local/components",
			"/bitrix/components",
		);

		foreach($folders as $componentFolder)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$componentFolder))
			{
				if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$componentFolder))
				{
					while (($file = readdir($handle)) !== false)
					{
						if ($file == "." || $file == "..")
							continue;

						if (
							is_dir($_SERVER["DOCUMENT_ROOT"].$componentFolder."/".$file)
							&& !CComponentUtil::isComponent($componentFolder."/".$file)
						)
						{
							$arNamespaces[] = $file;
						}
					}
					closedir($handle);
				}
			}
		}

		return array_unique($arNamespaces);
	}

	public static function GetComponentDescr($componentName)
	{
		$componentName = trim($componentName);

		static $cache = array();

		if($componentName == '')
		{
			$arComponentDescription = false;
		}
		else
		{
			if(array_key_exists($componentName, $cache))
				return $cache[$componentName];

			$path2Comp = CComponentEngine::MakeComponentPath($componentName);
			if($path2Comp == '')
			{
				$arComponentDescription = false;
			}
			else
			{
				$componentPath = getLocalPath("components".$path2Comp);
				if(CComponentUtil::isComponent($componentPath))
				{
					$arComponentDescription = array();
					if(file_exists($_SERVER["DOCUMENT_ROOT"].$componentPath."/.description.php"))
					{
						CComponentUtil::__IncludeLang($componentPath, ".description.php");
						include($_SERVER["DOCUMENT_ROOT"].$componentPath."/.description.php");
					}
				}
				else
				{
					$arComponentDescription = false;
				}
			}
		}

		$cache[$componentName] = $arComponentDescription;
		return $arComponentDescription;
	}

	public static function __GroupParamsCompare($a, $b)
	{
		if ($a["SORT"] < $b["SORT"])
			return -1;
		elseif ($a["SORT"] > $b["SORT"])
			return 1;
		else
			return 0;
	}

	/**
	 * @param string $componentName
	 * @param array $arCurrentValues Don't change the name! It's used in the .parameters.php file.
	 * @param array $templateProperties
	 * @return array|bool
	 */
	public static function GetComponentProps($componentName, $arCurrentValues = array(), $templateProperties = array())
	{
		$arComponentParameters = array();
		$componentName = trim($componentName);
		if ($componentName == '')
			return false;

		$path2Comp = CComponentEngine::MakeComponentPath($componentName);
		if ($path2Comp == '')
			return false;

		$componentPath = getLocalPath("components".$path2Comp);
		if(!CComponentUtil::isComponent($componentPath))
		{
			return false;
		}

		if (file_exists($_SERVER["DOCUMENT_ROOT"].$componentPath."/.parameters.php"))
		{
			CComponentUtil::__IncludeLang($componentPath, ".parameters.php");

			include($_SERVER["DOCUMENT_ROOT"].$componentPath."/.parameters.php");
		}

		if ($templateProperties && is_array($templateProperties))
		{
			if(is_array($arComponentParameters["PARAMETERS"]))
				$arComponentParameters["PARAMETERS"] = array_merge ($arComponentParameters["PARAMETERS"], $templateProperties);
			else
				$arComponentParameters["PARAMETERS"] = $templateProperties;
		}

		if (!array_key_exists("PARAMETERS", $arComponentParameters) || !is_array($arComponentParameters["PARAMETERS"]))
		{
			$arComponentParameters["PARAMETERS"] = array();
		}

		if (!array_key_exists("GROUPS", $arComponentParameters) || !is_array($arComponentParameters["GROUPS"]))
			$arComponentParameters["GROUPS"] = array();

		$arParamKeys = array_keys($arComponentParameters["GROUPS"]);
		for ($i = 0, $cnt = count($arParamKeys); $i < $cnt; $i++)
		{
			if (!IsSet($arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"]))
				$arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"] = 1000+$i;
			$arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"] = intval($arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"]);
			if ($arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"] <= 0)
				$arComponentParameters["GROUPS"][$arParamKeys[$i]]["SORT"] = 1000+$i;
		}

		$arVariableAliasesSettings = null;
		$arParamKeys = array_keys($arComponentParameters["PARAMETERS"]);
		for ($i = 0, $cnt = count($arParamKeys); $i < $cnt; $i++)
		{
			if ($arParamKeys[$i] == "SET_TITLE")
			{
				$arComponentParameters["GROUPS"]["ADDITIONAL_SETTINGS"] = array(
					"NAME" => GetMessage("COMP_GROUP_ADDITIONAL_SETTINGS"),
					"SORT" => 700
				);

				$arComponentParameters["PARAMETERS"]["SET_TITLE"] = array(
					"PARENT" => "ADDITIONAL_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_SET_TITLE"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "Y",
					"ADDITIONAL_VALUES" => "N",
					"REFRESH" => "Y"
				);
			}
			elseif ($arParamKeys[$i] == "CACHE_TIME")
			{
				$arComponentParameters["GROUPS"]["CACHE_SETTINGS"] = array(
					"NAME" => GetMessage("COMP_GROUP_CACHE_SETTINGS"),
					"SORT" => 600
				);

				$arSavedParams = $arComponentParameters["PARAMETERS"];
				$arComponentParameters["PARAMETERS"] = array();
				foreach ($arSavedParams as $keyTmp => $valueTmp)
				{
					if ($keyTmp == "CACHE_TIME")
					{
						$arComponentParameters["PARAMETERS"]["CACHE_TYPE"] = array(
							"PARENT" => "CACHE_SETTINGS",
							"NAME" => GetMessage("COMP_PROP_CACHE_TYPE"),
							"TYPE" => "LIST",
							"VALUES" => array("A" => GetMessage("COMP_PROP_CACHE_TYPE_AUTO")." ".GetMessage("COMP_PARAM_CACHE_MAN"), "Y" => GetMessage("COMP_PROP_CACHE_TYPE_YES"), "N" => GetMessage("COMP_PROP_CACHE_TYPE_NO")),
							"DEFAULT" => "A",
							"ADDITIONAL_VALUES" => "N"
						);
						$arComponentParameters["PARAMETERS"]["CACHE_TIME"] = array(
							"PARENT" => "CACHE_SETTINGS",
							"NAME" => GetMessage("COMP_PROP_CACHE_TIME"),
							"TYPE" => "STRING",
							"MULTIPLE" => "N",
							"DEFAULT" => intval($arSavedParams["CACHE_TIME"]["DEFAULT"]),
							"COLS" => 5
						);
						$arComponentParameters["PARAMETERS"]["CACHE_NOTES"] = array(
							"PARENT" => "CACHE_SETTINGS",
							"TYPE" => "CUSTOM",
							"JS_FILE" => "/bitrix/js/main/comp_props.js",
							"JS_EVENT" => "BxShowComponentNotes",
							"JS_DATA" => GetMessage("COMP_PROP_CACHE_NOTE", array(
								"#LANG#" => LANGUAGE_ID,
								"#AUTO_MODE#" => (COption::GetOptionString("main", "component_cache_on", "Y") == "Y"? GetMessage("COMP_PARAM_CACHE_AUTO_ON"):GetMessage("COMP_PARAM_CACHE_AUTO_OFF")),
								"#MANAGED_MODE#" =>(defined("BX_COMP_MANAGED_CACHE")? GetMessage("COMP_PARAM_CACHE_MANAGED_ON"):GetMessage("COMP_PARAM_CACHE_MANAGED_OFF")),
							)),
						);
					}
					else
					{
						$arComponentParameters["PARAMETERS"][$keyTmp] = $valueTmp;
					}
				}
			}
			elseif ($arParamKeys[$i] == "SEF_MODE" && isset($arComponentParameters["PARAMETERS"]["SEF_RULE"]))
			{
				$arComponentParameters["GROUPS"]["SEF_MODE"] = array(
					"NAME" => GetMessage("COMP_GROUP_SEF_MODE"),
					"SORT" => 500
				);
				$arComponentParameters["PARAMETERS"]["SEF_MODE"] = array(
					"PARENT" => "SEF_MODE",
					"NAME" => GetMessage("COMP_PROP_SEF_MODE"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
				);
				$arComponentParameters["PARAMETERS"]["SEF_RULE"]["PARENT"] = "SEF_MODE";
			}
			elseif ($arParamKeys[$i] == "SEF_RULE")
			{
				$arComponentParameters["PARAMETERS"]["SEF_RULE"]["TYPE"] = "TEMPLATES";
				$arComponentParameters["PARAMETERS"]["SEF_RULE"]["NAME"] = GetMessage("COMP_PARAM_SEF_RULE");
				if (isset($arCurrentValues["SEF_MODE"]) && $arCurrentValues["SEF_MODE"] === "Y")
				{
					if (is_array($arComponentParameters["PARAMETERS"]["SEF_RULE"]["VALUES"]))
					{
						foreach ($arComponentParameters["PARAMETERS"]["SEF_RULE"]["VALUES"] as $sefRuleValue)
						{
							if (
								is_array($sefRuleValue)
								&& isset($sefRuleValue["PARAMETER_LINK"])
								&& isset($arComponentParameters["PARAMETERS"][$sefRuleValue["PARAMETER_LINK"]])
							)
							{
								$arComponentParameters["PARAMETERS"][$sefRuleValue["PARAMETER_LINK"]]["PARENT"] = "SEF_MODE";
							}
						}
					}
				}
			}
			elseif ($arParamKeys[$i] == "SEF_MODE")
			{
				$arComponentParameters["GROUPS"]["SEF_MODE"] = array(
					"NAME" => GetMessage("COMP_GROUP_SEF_MODE"),
					"SORT" => 500
				);

				$arSEFModeSettings = $arComponentParameters["PARAMETERS"]["SEF_MODE"];

				$arComponentParameters["PARAMETERS"]["SEF_MODE"] = array(
					"PARENT" => "SEF_MODE",
					"NAME" => GetMessage("COMP_PROP_SEF_MODE"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
				);
				$arComponentParameters["PARAMETERS"]["SEF_FOLDER"] = array(
					"PARENT" => "SEF_MODE",
					"NAME" => GetMessage("COMP_PROP_SEF_FOLDER"),
					"TYPE" => "STRING",
					"MULTIPLE" => "N",
					"DEFAULT" => "",
					"COLS" => 30
				);

				if (is_array($arSEFModeSettings) && !empty($arSEFModeSettings))
				{
					if (!isset($arVariableAliasesSettings))
						$arVariableAliasesSettings = $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"];

					foreach ($arSEFModeSettings as $templateKey => $arTemplateValue)
					{
						$arComponentParameters["PARAMETERS"]["SEF_URL_TEMPLATES_".$templateKey] = array(
							"PARENT" => "SEF_MODE",
							"NAME" => $arTemplateValue["NAME"],
							"TYPE" => "STRING",
							"MULTIPLE" => "N",
							"DEFAULT" => $arTemplateValue["DEFAULT"],
							"HIDDEN" => $arTemplateValue["HIDDEN"] ?? '',
							"COLS" => 50,
							"VARIABLES" => array(),
						);

						if (is_array($arVariableAliasesSettings) && !empty($arVariableAliasesSettings))
						{
							foreach ($arTemplateValue["VARIABLES"] as $variable)
							{
								if (!empty($arVariableAliasesSettings[$variable]["TEMPLATE"]))
								{
									$arComponentParameters["PARAMETERS"]["SEF_URL_TEMPLATES_".$templateKey]["TYPE"] = "TEMPLATES";
									$arComponentParameters["PARAMETERS"]["SEF_URL_TEMPLATES_".$templateKey]["VALUES"][$variable] = array(
										"TEXT" => $arVariableAliasesSettings[$variable]["NAME"],
										"TEMPLATE" => $arVariableAliasesSettings[$variable]["TEMPLATE"],
									);
								}
								$arComponentParameters["PARAMETERS"]["SEF_URL_TEMPLATES_".$templateKey]["VARIABLES"]["#".$variable."#"] = $arVariableAliasesSettings[$variable]["NAME"] ?? '';
							}
						}
					}
				}
			}
			elseif ($arParamKeys[$i] == "VARIABLE_ALIASES")
			{
				$arComponentParameters["GROUPS"]["SEF_MODE"] = array(
					"NAME" => GetMessage("COMP_GROUP_SEF_MODE"),
					"SORT" => 500
				);

				$arVariableAliasesSettings = $arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"];

				unset($arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]);

				foreach ($arVariableAliasesSettings as $aliaseKey => $arAliaseValue)
				{
					$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES_".$aliaseKey] = array(
						"PARENT" => "SEF_MODE",
						"NAME" => $arAliaseValue["NAME"],
						"TYPE" => "STRING",
						"MULTIPLE" => "N",
						"DEFAULT" => $aliaseKey,
						"COLS" => 20,
					);
				}
			}
			elseif (IsSet($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"]) && $arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] <> '')
			{
				if ($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] == "URL_TEMPLATES")
				{
					$arComponentParameters["GROUPS"]["URL_TEMPLATES"] = array(
						"NAME" => GetMessage("COMP_GROUP_URL_TEMPLATES"),
						"SORT" => 400
					);
				}
				elseif ($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] == "VISUAL")
				{
					$arComponentParameters["GROUPS"]["VISUAL"] = array(
						"NAME" => GetMessage("COMP_GROUP_VISUAL"),
						"SORT" => 300
					);
				}
				elseif ($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] == "DATA_SOURCE")
				{
					$arComponentParameters["GROUPS"]["DATA_SOURCE"] = array(
						"NAME" => GetMessage("COMP_GROUP_DATA_SOURCE"),
						"SORT" => 200
					);
				}
				elseif ($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] == "BASE")
				{
					$arComponentParameters["GROUPS"]["BASE"] = array(
						"NAME" => GetMessage("COMP_GROUP_BASE"),
						"SORT" => 100
					);
				}
				elseif ($arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] == "ADDITIONAL_SETTINGS")
				{
					$arComponentParameters["GROUPS"]["ADDITIONAL_SETTINGS"] = array(
						"NAME" => GetMessage("COMP_GROUP_ADDITIONAL_SETTINGS"),
						"SORT" => 700
					);
				}
			}
			elseif ($arParamKeys[$i] == "AJAX_MODE")
			{
				$arComponentParameters["GROUPS"]["AJAX_SETTINGS"] = array(
					"NAME" => GetMessage("COMP_GROUP_AJAX_SETTINGS"),
					"SORT" => 550
				);

				$arComponentParameters["PARAMETERS"]["AJAX_MODE"] = array(
					"PARENT" => "AJAX_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_AJAX_MODE"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"ADDITIONAL_VALUES" => "N"
				);

				// $arComponentParameters["PARAMETERS"]["AJAX_OPTION_SHADOW"] = array(
					// "PARENT" => "AJAX_SETTINGS",
					// "NAME" => GetMessage("COMP_PROP_AJAX_OPTIONS_SHADOW"),
					// "TYPE" => "CHECKBOX",
					// "MULTIPLE" => "N",
					// "DEFAULT" => "Y",
					// "ADDITIONAL_VALUES" => "N"
				// );

				$arComponentParameters["PARAMETERS"]["AJAX_OPTION_JUMP"] = array(
					"PARENT" => "AJAX_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_AJAX_OPTIONS_JUMP"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "N",
					"ADDITIONAL_VALUES" => "N"
				);

				$arComponentParameters["PARAMETERS"]["AJAX_OPTION_STYLE"] = array(
					"PARENT" => "AJAX_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_AJAX_OPTIONS_STYLE"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "Y",
					"ADDITIONAL_VALUES" => "N"
				);

				$arComponentParameters["PARAMETERS"]["AJAX_OPTION_HISTORY"] = array(
					"PARENT" => "AJAX_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_AJAX_OPTIONS_HISTORY"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "N",
					"ADDITIONAL_VALUES" => "N"
				);

				$arComponentParameters["PARAMETERS"]["AJAX_OPTION_ADDITIONAL"] = array(
					"PARENT" => "AJAX_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_AJAX_OPTIONS_ADDITIONAL"),
					"TYPE" => "STRING",
					"HIDDEN" => "Y",
					"MULTIPLE" => "N",
					"DEFAULT" => "",
					"ADDITIONAL_VALUES" => "N"
				);
			}
			elseif ($arParamKeys[$i] == "USER_CONSENT")
			{
				$arComponentParameters["GROUPS"]["USER_CONSENT"] = array(
					"NAME" => GetMessage("COMP_GROUP_USER_CONSENT"),
					"SORT" => 350
				);

				$arComponentParameters["PARAMETERS"]["USER_CONSENT"] = array(
					"PARENT" => "USER_CONSENT",
					"NAME" => GetMessage("COMP_PROP_USER_CONSENT_USE"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"ADDITIONAL_VALUES" => "N"
				);

				$arComponentParameters["PARAMETERS"]["USER_CONSENT_ID"] = array(
					"PARENT" => "USER_CONSENT",
					"NAME" => GetMessage("COMP_PROP_USER_CONSENT_ID"),
					"TYPE" => "LIST",
					"VALUES" => array(GetMessage("COMP_PROP_USER_CONSENT_ID_DEF")) + \Bitrix\Main\UserConsent\Agreement::getActiveList(),
					"MULTIPLE" => "N",
					"DEFAULT" => "",
				);

				$arComponentParameters["PARAMETERS"]["USER_CONSENT_IS_CHECKED"] = array(
					"PARENT" => "USER_CONSENT",
					"NAME" => GetMessage("COMP_PROP_USER_CONSENT_IS_CHECKED"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "Y",
					"ADDITIONAL_VALUES" => "N"
				);

				$arComponentParameters["PARAMETERS"]["USER_CONSENT_IS_LOADED"] = array(
					"PARENT" => "USER_CONSENT",
					"NAME" => GetMessage("COMP_PROP_USER_CONSENT_IS_LOADED"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"ADDITIONAL_VALUES" => "N"
				);
			}
			else
			{
				$parent = $arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] ?? null;
				if (!isset($parent) || !isset($arComponentParameters["GROUPS"][$parent]))
				{
					$arComponentParameters["PARAMETERS"][$arParamKeys[$i]]["PARENT"] = "ADDITIONAL_SETTINGS";
					if (!isset($arComponentParameters["GROUPS"]["ADDITIONAL_SETTINGS"]))
					{
						$arComponentParameters["GROUPS"]["ADDITIONAL_SETTINGS"] = array(
							"NAME" => GetMessage("COMP_GROUP_ADDITIONAL_SETTINGS"),
							"SORT" => 700
						);
					}
				}
			}
		}

		if (\Bitrix\Main\Composite\Helper::isOn())
		{
			$arComponentParameters["GROUPS"]["COMPOSITE_SETTINGS"] = array(
				"NAME" => GetMessage("COMP_GROUP_COMPOSITE_SETTINGS"),
				"SORT" => 800
			);

			$arComponentParameters["PARAMETERS"]["COMPOSITE_FRAME_MODE"] = array(
				"PARENT" => "COMPOSITE_SETTINGS",
				"NAME" => GetMessage("COMP_PROP_COMPOSITE_FRAME_MODE"),
				"TYPE" => "LIST",
				"VALUES" => array(
					"A" => GetMessage("COMP_PROP_COMPOSITE_FRAME_MODE_AUTO"),
					"Y" => GetMessage("COMP_PROP_COMPOSITE_FRAME_MODE_PRO"),
					"N" => GetMessage("COMP_PROP_COMPOSITE_FRAME_MODE_CONTRA")
				),
				"DEFAULT" => "A",
				"REFRESH" => "Y",
				"ADDITIONAL_VALUES" => "N"
			);

			if (
				!isset($arCurrentValues["COMPOSITE_FRAME_MODE"]) ||
				in_array($arCurrentValues["COMPOSITE_FRAME_MODE"], array("A", "Y")))
			{
				$arComponentParameters["PARAMETERS"]["COMPOSITE_FRAME_TYPE"] = array(
					"PARENT" => "COMPOSITE_SETTINGS",
					"NAME" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE"),
					"TYPE" => "LIST",
					"VALUES" => array(
						"AUTO" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE_AUTO"),
						"STATIC" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE_STATIC"),
						"DYNAMIC_WITH_STUB" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE_DYNAMIC_WITH_STUB"),
						"DYNAMIC_WITHOUT_STUB" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE_DYNAMIC_WITHOUT_STUB"),
						"DYNAMIC_WITH_STUB_LOADING" => GetMessage("COMP_PROP_COMPOSITE_FRAME_TYPE_DYNAMIC_WITH_STUB_LOADING")
					),
					"DEFAULT" => "A",
					"ADDITIONAL_VALUES" => "N"
				);
			}

		}

		if(
			(CPageOption::GetOptionString("main","tips_creation","no")=="allowed")
			&& (str_contains($componentPath, "/forum"))
		)
		{
			//Create directories
			$help_lang_path = $_SERVER["DOCUMENT_ROOT"].$componentPath."/lang";
			if(!file_exists($help_lang_path))
				mkdir($help_lang_path);
			$help_lang_path .= "/ru";
			if(!file_exists($help_lang_path))
				mkdir($help_lang_path);
			$help_lang_path .= "/help";
			if(!file_exists($help_lang_path))
				mkdir($help_lang_path);
			if(is_dir($help_lang_path))
			{
				//Create files if none exists
				$lang_filename = $help_lang_path."/.tooltips.php";
				if(!file_exists($lang_filename))
				{
					$handle=fopen($lang_filename, "w");
					fwrite($handle, "<?\n?>");
					fclose($handle);
				}
				$handle=fopen($lang_filename, "r");
				$lang_contents = fread($handle, filesize($lang_filename));
				fclose($handle);
				$lang_file_modified = false;
				//Bug fix
				if(str_contains($lang_contents, "\$MESS['"))
				{
					$lang_contents = str_replace("\$MESS['", "\$MESS ['", $lang_contents);
					$lang_file_modified = true;
				}
				//Check out parameters
				foreach($arComponentParameters["PARAMETERS"] as $strName=>$arParameter)
				{
					if(!str_contains($lang_contents, "\$MESS ['${strName}_TIP'] = "))
					{
						$lang_contents = str_replace("?>", "\$MESS ['${strName}_TIP'] = \"".str_replace("\$", "\\\$", str_replace('"','\\"',$arParameter["NAME"]))."\";\n?>", $lang_contents);
						$lang_file_modified = true;
					}
				}
				//Save the result of the work
				if($lang_file_modified)
				{
					$handle=fopen($lang_filename, "w");
					fwrite($handle, $lang_contents);
					fclose($handle);
				}
			}
			reset($arComponentParameters["PARAMETERS"]);
		}
		uasort($arComponentParameters["GROUPS"], array("CComponentUtil", "__GroupParamsCompare"));


		return $arComponentParameters;
	}

	/**
	 * @param string $componentName
	 * @param string $templateName
	 * @param string $siteTemplate
	 * @param array $arCurrentValues Don't change the name! It's used in the .parameters.php file.
	 * @return array
	 */
	public static function GetTemplateProps($componentName, $templateName, $siteTemplate = "", $arCurrentValues = array())
	{
		$arTemplateParameters = array();

		$componentName = trim($componentName);
		if ($componentName == '')
			return $arTemplateParameters;

		if ($templateName == '')
			$templateName = ".default";

		if(preg_match("#[^a-z0-9_.-]#i", $templateName))
			return $arTemplateParameters;

		$path2Comp = CComponentEngine::MakeComponentPath($componentName);
		if ($path2Comp == '')
			return $arTemplateParameters;

		$componentPath = getLocalPath("components".$path2Comp);

		if (!CComponentUtil::isComponent($componentPath))
		{
			return $arTemplateParameters;
		}

		if ($siteTemplate <> "")
		{
			$siteTemplate = _normalizePath($siteTemplate);
		}

		$folders = array();
		if ($siteTemplate <> "")
		{
			$folders[] = "/local/templates/".$siteTemplate."/components".$path2Comp."/".$templateName;
		}
		$folders[] = "/local/templates/.default/components".$path2Comp."/".$templateName;
		$folders[] = "/local/components".$path2Comp."/templates/".$templateName;

		if ($siteTemplate <> "")
		{
			$folders[] = BX_PERSONAL_ROOT."/templates/".$siteTemplate."/components".$path2Comp."/".$templateName;
		}
		$folders[] = BX_PERSONAL_ROOT."/templates/.default/components".$path2Comp."/".$templateName;
		$folders[] = "/bitrix/components".$path2Comp."/templates/".$templateName;

		foreach($folders as $templateFolder)
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$templateFolder))
			{
				if (file_exists($_SERVER["DOCUMENT_ROOT"].$templateFolder."/.parameters.php"))
				{
					CComponentUtil::__IncludeLang($templateFolder, ".parameters.php");
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/.parameters.php");
				}
				return $arTemplateParameters;
			}
		}

		return $arTemplateParameters;
	}

	public static function GetTemplatesList($componentName, $currentTemplate = false)
	{
		$arTemplatesList = array();

		$componentName = trim($componentName);
		if ($componentName == '')
			return $arTemplatesList;

		$path2Comp = CComponentEngine::MakeComponentPath($componentName);
		if ($path2Comp == '')
			return $arTemplatesList;

		$componentPath = getLocalPath("components".$path2Comp);

		if (!CComponentUtil::isComponent($componentPath))
		{
			return $arTemplatesList;
		}

		$templateFolders = array();
		$arExists = array();
		$folders = array(
			"/local/templates",
			BX_PERSONAL_ROOT."/templates",
		);

		foreach($folders as $folder)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$folder))
			{
				if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$folder))
				{
					while (($file = readdir($handle)) !== false)
					{
						if ($file == "." || $file == "..")
							continue;

						if ($currentTemplate !== false && $currentTemplate != $file || $file == ".default")
							continue;

						if (file_exists($_SERVER["DOCUMENT_ROOT"].$folder."/".$file."/components".$path2Comp))
						{
							$templateFolders[] = array(
								"path" => $folder."/".$file."/components".$path2Comp,
								"template" => $file,
							);
						}
					}
					closedir($handle);

					if (file_exists($_SERVER["DOCUMENT_ROOT"].$folder."/.default/components".$path2Comp))
					{
						$templateFolders[] = array(
							"path" => $folder."/.default/components".$path2Comp,
							"template" => ".default",
						);
					}
				}
			}
		}

		$templateFolders[] = array(
			"path" => $componentPath."/templates",
			"template" => "",
		);

		foreach($templateFolders as $templateFolder)
		{
			$templateFolderPath = $templateFolder["path"];
			if ($handle1 = @opendir($_SERVER["DOCUMENT_ROOT"].$templateFolderPath))
			{
				while (($file1 = readdir($handle1)) !== false)
				{
					if ($file1 == "." || $file1 == "..")
						continue;

					if (in_array($file1, $arExists))
						continue;

					$arTemplate = array(
						"NAME" => $file1,
						"TEMPLATE" => $templateFolder["template"],
					);

					if (file_exists($_SERVER["DOCUMENT_ROOT"].$templateFolderPath."/".$file1."/.description.php"))
					{
						CComponentUtil::__IncludeLang($templateFolderPath."/".$file1, ".description.php");

						$arTemplateDescription = array();
						include($_SERVER["DOCUMENT_ROOT"].$templateFolderPath."/".$file1."/.description.php");

						$arTemplate["TITLE"] = $arTemplateDescription["NAME"];
						$arTemplate["DESCRIPTION"] = $arTemplateDescription["DESCRIPTION"];
					}

					$arTemplatesList[] = $arTemplate;
					$arExists[] = $arTemplate["NAME"];
				}
				@closedir($handle1);
			}
		}

		return $arTemplatesList;
	}

	public static function CopyComponent($componentName, $newNamespace, $newName = false, $bRewrite = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$componentName = trim($componentName);
		if ($componentName == '')
		{
			$APPLICATION->ThrowException(GetMessage("comp_util_err1"), "EMPTY_COMPONENT_NAME");
			return false;
		}

		$path2Comp = CComponentEngine::MakeComponentPath($componentName);
		if ($path2Comp == '')
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $componentName, GetMessage("comp_util_err2")), "ERROR_NOT_COMPONENT");
			return false;
		}

		$componentPath = getLocalPath("components".$path2Comp);

		if (!CComponentUtil::isComponent($componentPath))
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $componentName, GetMessage("comp_util_err2")), "ERROR_NOT_COMPONENT");
			return false;
		}

		$newNamespace = trim($newNamespace);
		if ($newNamespace <> '')
		{
			if (preg_match("#[^a-z0-9_.-]#i", $newNamespace))
			{
				$APPLICATION->ThrowException(str_replace("#NAME#", $newNamespace, GetMessage("comp_util_err3")), "ERROR_NEW_NAMESPACE");
				return false;
			}
		}

		if ($newName == '')
			$newName = false;

		if ($newName !== false)
		{
			if (!preg_match("#^([a-z0-9_-]+\\.)*([a-z0-9_-]+)$#i", $newName))
			{
				$APPLICATION->ThrowException(str_replace("#NAME#", $newName, GetMessage("comp_util_err4")), "ERROR_NEW_NAME");
				return false;
			}
		}

		$namespace = "";
		$name = $componentName;
		if (($pos = mb_strpos($componentName, ":")) !== false)
		{
			$namespace = mb_substr($componentName, 0, $pos);
			$name = mb_substr($componentName, $pos + 1);
		}

		if ($namespace == $newNamespace
			&& ($newName === false || $name == $newName))
		{
			$APPLICATION->ThrowException(GetMessage("comp_util_err5"), "ERROR_DUPL1");
			return false;
		}

		if ($newName !== false)
			$componentNameNew = $newNamespace.":".$newName;
		else
			$componentNameNew = $newNamespace.":".$name;

		$path2CompNew = CComponentEngine::MakeComponentPath($componentNameNew);
		if ($path2CompNew == '')
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $componentNameNew, GetMessage("comp_util_err2")), "ERROR_NOT_COMPONENT");
			return false;
		}

		$componentPathNew = getLocalPath("components".$path2CompNew);

		if (file_exists($_SERVER["DOCUMENT_ROOT"].$componentPathNew))
		{
			if (!$bRewrite)
			{
				$APPLICATION->ThrowException(str_replace("#NAME#", $componentNameNew, GetMessage("comp_util_err6")), "ERROR_EXISTS");
				return false;
			}
			else
			{
				DeleteDirFilesEx($componentPathNew);
			}
		}

		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$componentPathNew);

		CopyDirFiles($_SERVER["DOCUMENT_ROOT"].$componentPath, $_SERVER["DOCUMENT_ROOT"].$componentPathNew, true, true, false);

		return null;
	}

	public static function CopyTemplate($componentName, $templateName, $siteTemplate, $newSiteTemplate, $newName = false, $bRewrite = false)
	{
		global $APPLICATION;

		$componentName = trim($componentName);
		if ($componentName == '')
		{
			$APPLICATION->ThrowException(GetMessage("comp_util_err1"), "EMPTY_COMPONENT_NAME");
			return false;
		}

		$path2Comp = CComponentEngine::MakeComponentPath($componentName);
		if ($path2Comp == '')
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $componentName, GetMessage("comp_util_err2")), "ERROR_NOT_COMPONENT");
			return false;
		}

		$componentPath = getLocalPath("components".$path2Comp);

		if (!CComponentUtil::isComponent($componentPath))
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $componentName, GetMessage("comp_util_err2")), "ERROR_NOT_COMPONENT");
			return false;
		}

		if ($templateName == '')
			$templateName = ".default";

		if (preg_match("#[^a-z0-9_.-]#i", $templateName))
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $templateName, GetMessage("comp_util_err7")), "ERROR_BAD_TEMPLATE_NAME");
			return false;
		}

		if ($siteTemplate == '')
			$siteTemplate = false;

		if ($siteTemplate != false)
		{
			$siteTemplateDir = getLocalPath("templates/".$siteTemplate, BX_PERSONAL_ROOT);
			if ($siteTemplateDir === false || !is_dir($_SERVER["DOCUMENT_ROOT"].$siteTemplateDir))
			{
				$APPLICATION->ThrowException(str_replace("#NAME#", $siteTemplate, GetMessage("comp_util_err8")), "ERROR_NO_SITE_TEMPL");
				return false;
			}
		}

		if ($siteTemplate != false)
			$path = getLocalPath("templates/".$siteTemplate."/components".$path2Comp."/".$templateName, BX_PERSONAL_ROOT);
		else
			$path = getLocalPath("components".$path2Comp."/templates/".$templateName);

		if ($path === false || !file_exists($_SERVER["DOCUMENT_ROOT"].$path))
		{
			$APPLICATION->ThrowException(str_replace("#C_NAME#", $componentName, str_replace("#T_NAME#", $templateName, GetMessage("comp_util_err9"))), "ERROR_NO_TEMPL");
			return false;
		}

		if ($newSiteTemplate == '')
		{
			$APPLICATION->ThrowException(GetMessage("comp_util_err10"), "ERROR_EMPTY_SITE_TEMPL");
			return false;
		}

		$newSiteTemplateDir = getLocalPath("templates/".$newSiteTemplate, BX_PERSONAL_ROOT);
		if ($newSiteTemplateDir === false || !is_dir($_SERVER["DOCUMENT_ROOT"].$newSiteTemplateDir))
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $newSiteTemplate, GetMessage("comp_util_err8")), "ERROR_NO_SITE_TEMPL");
			return false;
		}

		if ($siteTemplate !== false
			&& $siteTemplate == $newSiteTemplate
			&& ($newName === false || $templateName == $newName))
		{
			$APPLICATION->ThrowException(GetMessage("comp_util_err11"), "ERROR_DUPL1");
			return false;
		}

		if ($newName !== false)
			$templateNameNew = $newName;
		else
			$templateNameNew = $templateName;

		if (preg_match("#[^a-z0-9_.-]#i", $templateNameNew))
		{
			$APPLICATION->ThrowException(str_replace("#NAME#", $templateNameNew, GetMessage("comp_util_err7")), "ERROR_BAD_TEMPLATE_NAME");
			return false;
		}

		$pathNew = $newSiteTemplateDir."/components".$path2Comp."/".$templateNameNew;

		if (file_exists($_SERVER["DOCUMENT_ROOT"].$pathNew))
		{
			if (!$bRewrite)
			{
				$APPLICATION->ThrowException(str_replace("#NAME#", $templateNameNew, GetMessage("comp_util_err12")), "ERROR_EXISTS");
				return false;
			}
			else
			{
				DeleteDirFilesEx($pathNew);
			}
		}

		CopyDirFiles($_SERVER["DOCUMENT_ROOT"].$path, $_SERVER["DOCUMENT_ROOT"].$pathNew, true, true, false);

		return true;
	}

	public static function CheckComponentName($name, $arFilter)
	{
		foreach ($arFilter as $pattern)
			if (preg_match($pattern, $name))
				return true;
		return false;
	}

	public static function GetDefaultNameTemplates()
	{
		return array(
			'#LAST_NAME# #NAME#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_JOHN'),
			'#LAST_NAME# #NAME# #SECOND_NAME#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_JOHN_LLOYD'),
			'#LAST_NAME#, #NAME# #SECOND_NAME#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_COMMA_JOHN_LLOYD'),
			'#NAME# #SECOND_NAME# #LAST_NAME#' => GetMessage('COMP_NAME_TEMPLATE_JOHN_LLOYD_SMITH'),
			'#NAME_SHORT# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('COMP_NAME_TEMPLATE_J_L_SMITH'),
			'#NAME_SHORT# #LAST_NAME#' => GetMessage('COMP_NAME_TEMPLATE_J_SMITH'),
			'#LAST_NAME# #NAME_SHORT#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_J'),
			'#LAST_NAME# #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_J_L'),
			'#LAST_NAME#, #NAME_SHORT#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_COMMA_J'),
			'#LAST_NAME#, #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('COMP_NAME_TEMPLATE_SMITH_COMMA_J_L'),
			'#NAME# #LAST_NAME#' => GetMessage('COMP_NAME_TEMPLATE_JOHN_SMITH'),
			'#NAME# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('COMP_NAME_TEMPLATE_JOHN_L_SMITH'),
			'' => GetMessage('COMP_PARAM_NAME_FORMAT_SITE')
		);
	}

	public static function GetDateFormatField($name="", $parent="", $no_year = false)
	{
		$timestamp = mktime(0,0,0,2,6,2010);
		return array(
			"PARENT" => $parent,
			"NAME" => $name,
			"TYPE" => "LIST",
			"VALUES" => $no_year ?
				array(
					"d-m" => FormatDate("d-m", $timestamp),//"22-02",
					"m-d" => FormatDate("m-d", $timestamp),//"02-22",
					"d.m" => FormatDate("d.m", $timestamp),//"22.02",
					"d.M" => FormatDate("d.M", $timestamp),//"22.Feb",
					"m.d" => FormatDate("m.d", $timestamp),//"02.22",
					"j M" => FormatDate("j M", $timestamp),//"22 Feb",
					"M j" => FormatDate("M j", $timestamp),//"Feb 22",
					"j F" => FormatDate("j F", $timestamp),//"22 February",
					"f j" => FormatDate("f j", $timestamp),//"February 22"
					CComponentUtil::GetDateFormatDefault($no_year) => GetMessage('COMP_PARAM_DATE_FORMAT_SITE')
				):
				array(
					"d-m-Y" => FormatDate("d-m-Y", $timestamp),//"22-02-2007",
					"m-d-Y" => FormatDate("m-d-Y", $timestamp),//"02-22-2007",
					"Y-m-d" => FormatDate("Y-m-d", $timestamp),//"2007-02-22",
					"d.m.Y" => FormatDate("d.m.Y", $timestamp),//"22.02.2007",
					"d.M.Y" => FormatDate("d.M.Y", $timestamp),//"22.Feb.2007",
					"m.d.Y" => FormatDate("m.d.Y", $timestamp),//"02.22.2007",
					"j M Y" => FormatDate("j M Y", $timestamp),//"22 Feb 2007",
					"M j, Y" => FormatDate("M j, Y", $timestamp),//"Feb 22, 2007",
					"j F Y" => FormatDate("j F Y", $timestamp),//"22 February 2007",
					"f j, Y" => FormatDate("f j, Y", $timestamp),//"February 22",
					"SHORT" => GetMessage('COMP_PARAM_DATE_FORMAT_SITE')
				),
			"DEFAULT" => CComponentUtil::GetDateFormatDefault($no_year),
			"ADDITIONAL_VALUES" => "Y",
		);
	}

	public static function GetDateFormatDefault($no_year = false)
	{
		global $DB;

		return $DB->DateFormatToPHP($no_year ? preg_replace('/[\-\.\/]*[Y]{2,4}[\-\.\/]*/', '', CSite::GetDateFormat('SHORT')) : CSite::GetDateFormat("SHORT"));
	}

	public static function GetDateTimeFormatField($name="", $parent="")
	{
		$timestamp = mktime(16,10,45,2,6,2010);
		return array(
			"PARENT" => $parent,
			"NAME" => $name,
			"TYPE" => "LIST",
			"VALUES" => array(
				"d-m-Y H:i:s" => FormatDate("d-m-Y H:i:s", $timestamp),//"22-02-2007 7:30",
				"m-d-Y H:i:s" => FormatDate("m-d-Y H:i:s", $timestamp),//"02-22-2007 7:30",
				"Y-m-d H:i:s" => FormatDate("Y-m-d H:i:s", $timestamp),//"2007-02-22 7:30",
				"d.m.Y H:i:s" => FormatDate("d.m.Y H:i:s", $timestamp),//"22.02.2007 7:30",
				"m.d.Y H:i:s" => FormatDate("m.d.Y H:i:s", $timestamp),//"02.22.2007 7:30",
				"j M Y H:i:s" => FormatDate("j M Y H:i:s", $timestamp),//"22 Feb 2007 7:30",
				"M j, Y H:i:s" => FormatDate("M j, Y H:i:s", $timestamp),//"Feb 22, 2007 7:30",
				"j F Y H:i:s" => FormatDate("j F Y H:i:s", $timestamp),//"22 February 2007 7:30",
				"f j, Y H:i:s" => FormatDate("f j, Y H:i:s", $timestamp),//"February 22, 2007",
				"d.m.y g:i:s A" => FormatDate("d.m.y g:i:s A", $timestamp),//"22.02.07 1:30 PM",
				"d.M.y g:i:s a" => FormatDate("d.M.y g:i:s a", $timestamp),//"22.Feb.07 1:30 pm",
				"d.M.Y g:i:s a" => FormatDate("d.M.Y g:i:s a", $timestamp),//"22.Feb.2007 1:30 pm",
				"d.m.y G:i" => FormatDate("d.m.y G:i", $timestamp),//"22.02.07 7:30",
				"j F Y G:i" => FormatDate("j F Y G:i", $timestamp),//"ZHL cool RUS",
				"j F Y g:i a" => FormatDate("j F Y g:i a", $timestamp),//"ZHL cool Burzh",
				"FULL" => GetMessage('COMP_PARAM_DATETIME_FORMAT_SITE')
			),
			"DEFAULT" => CComponentUtil::GetDateTimeFormatDefault(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}

	public static function GetDateTimeFormatDefault()
	{
		global $DB;

		return $DB->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	}

	public static function GetDateTimeFormatted($timestamp, $dateTimeFormat = false, $offset = 0, $hideToday = false)
	{
		if (is_array($timestamp))
		{
			$params = $timestamp;
			$timestamp = ($params['TIMESTAMP'] ?? false);
			$offset = (isset($params['TZ_OFFSET']) ? intval($params['TZ_OFFSET']) : 0);
			$hideToday = ($params['HIDE_TODAY'] ?? false);
		}

		if (empty($timestamp))
		{
			return '';
		}

		$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
		$timeFormat = $culture->getShortTimeFormat();
		$dateTimeFormat = $culture->getLongDateFormat().' '.$timeFormat;
		$dateTimeFormatWOYear = $culture->getDayMonthFormat().' '.$timeFormat;

		$arFormat = array(
			"tomorrow" => "tomorrow, ".$timeFormat,
			"today" => ($hideToday ? $timeFormat : "today, ".$timeFormat),
			"yesterday" => "yesterday, ".$timeFormat,
			"" => (
				date("Y", $timestamp) == date("Y")
					? $dateTimeFormatWOYear
					: $dateTimeFormat
			)
		);

		return FormatDate($arFormat, $timestamp, (time() + $offset));
	}
}
