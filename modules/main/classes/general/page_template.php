<?php

class CPageTemplate
{
	public static function GetList($arSiteTemplates=array())
	{
		global $APPLICATION;

		$arDirs = array("templates/.default/page_templates");
		foreach($arSiteTemplates as $val)
			$arDirs[] = "templates/".$val."/page_templates";

		$arFiles = array();
		foreach($arDirs as $dir)
		{
			$path = getLocalPath($dir, BX_PERSONAL_ROOT);
			if($path === false)
				continue;
			$template_dir = $_SERVER["DOCUMENT_ROOT"].$path;
			if($handle = opendir($template_dir))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == ".." || !is_dir($template_dir."/".$file))
						continue;

					$template_file = $template_dir."/".$file."/template.php";
					if(!file_exists($template_file))
						continue;

					if($APPLICATION->GetFileAccessPermission($path."/".$file."/template.php") < "R")
						continue;

					$arFiles[$file] = $template_file;
				}
				closedir($handle);
			}
		}

		$res = array();
		foreach($arFiles as $file=>$template_file)
		{
			/** @var CPageTemplate $pageTemplate */
			$pageTemplate = false;
			include_once($template_file);

			if(!$pageTemplate || !is_callable(array($pageTemplate, 'GetDescription')))
				continue;

			$arRes = array(
				"name"=>$file,
				"description"=>"",
				"icon"=>"",
				"file"=>$file,
				"sort"=>150,
				"type"=>"",
			);

			$arDesc = $pageTemplate->GetDescription();

			if(is_array($arDesc["modules"]))
				foreach($arDesc["modules"] as $module)
					if(!IsModuleInstalled($module))
						continue 2;

			if(is_array($arDesc))
			{
				foreach($arDesc as $key=>$val)
					$arRes[$key] = $val;
			}

			$res[$file] = $arRes;
		}

		uasort($res, array('CPageTemplate', '_templ_sort'));
		return $res;
	}

	public static function GetDescription()
	{
		return array();
	}

	public static function _templ_sort($a, $b)
	{
		if($a["sort"] < $b["sort"])
			return -1;
		elseif($a["sort"] > $b["sort"])
			return 1;
		else
			return strcmp($a["name"], $b["name"]);
	}

	public static function GetTemplate($template, $arSiteTemplates=array())
	{
		global $APPLICATION;

		$arDirs = array("templates/.default/page_templates");
		foreach($arSiteTemplates as $val)
			$arDirs[] = "templates/".$val."/page_templates";

		$template = _normalizePath($template);

		$sFile = false;
		foreach($arDirs as $dir)
		{
			$path = getLocalPath($dir, BX_PERSONAL_ROOT);
			if($path === false)
				continue;

			$template_dir = $_SERVER["DOCUMENT_ROOT"].$path;
			$template_file = $template_dir."/".$template."/template.php";
			if(!file_exists($template_file))
				continue;

			if($APPLICATION->GetFileAccessPermission($path."/".$template."/template.php") < "R")
				continue;

			$sFile = $template_file;
		}
		if($sFile !== false)
		{
			$pageTemplate = false;
			include_once($sFile);

			if(is_object($pageTemplate))
				return $pageTemplate;
		}
		return false;
	}

	public static function IncludeLangFile($filepath)
	{
		$file = basename($filepath);
		$dir = dirname($filepath);

		$langSubst = LangSubst(LANGUAGE_ID);
		$fname = $dir."/lang/".$langSubst."/".$file;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $langSubst);
		if(LANGUAGE_ID <> "en" && LANGUAGE_ID <> "ru")
		{
			if(file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $dir."/lang/".LANGUAGE_ID."/".$file;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, LANGUAGE_ID);
		if(file_exists($fname))
		{
			__IncludeLang($fname, false, true);
		}
	}
}
