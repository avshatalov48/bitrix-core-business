<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

IncludeModuleLangFile(__FILE__);

function GetTemplateContent($filename, $lang=LANG, $arTemplates=array())
{
	global $APPLICATION;

	$filename = _normalizePath($filename);

	$arDirs = array();
	foreach($arTemplates as $val)
		$arDirs[] = "templates/".$val."/page_templates";
	$arDirs[] = "templates/.default/page_templates";
	$arDirs[] = "php_interface/".$lang."templates";
	$arDirs[] = "php_interface/templates";

	foreach($arDirs as $dir)
	{
		$path = getLocalPath($dir."/".$filename, BX_PERSONAL_ROOT);
		if($path !== false && is_file($_SERVER["DOCUMENT_ROOT"].$path))
			return $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].$path);
	}

	return false;
}

function GetFileTemplates($lang = LANG, $arTemplates = array())
{
	global $APPLICATION;

	$arDirs = array(
		"php_interface/".$lang."/templates",
		"templates/.default/page_templates",
		"php_interface/templates",
	);
	foreach($arTemplates as $val)
		$arDirs[] = "templates/".$val."/page_templates";

	$res = array();
	foreach($arDirs as $dir)
	{
		$templDir = getLocalPath($dir, BX_PERSONAL_ROOT);
		if($templDir === false)
			continue;
		$dirPath = $_SERVER["DOCUMENT_ROOT"].$templDir;
		if(file_exists($dirPath))
		{
			$sDescFile = $dirPath."/.content.php";
			$TEMPLATE = array();
			if(file_exists($sDescFile))
				include($sDescFile);

			if($handle = @opendir($dirPath))
			{
				while(($file = readdir($handle)) !== false)
				{
					if(is_dir($dirPath."/".$file))
						continue;
					if(mb_substr($file, 0, 1) == ".")
						continue;

					$path = $templDir."/".$file;
					if($APPLICATION->GetFileAccessPermission($path) < "R")
						continue;

					$restmp = array(
						"name" => mb_substr($file, 0, bxstrrpos($file, ".")),
						"file" => $file,
						"sort" => 150,
						"path" => $path,
					);

					if(array_key_exists($file, $TEMPLATE))
					{
						if(array_key_exists("name", $TEMPLATE[$file]))
							$restmp["name"] = $TEMPLATE[$file]["name"];
						if(array_key_exists("sort", $TEMPLATE[$file]))
							$restmp["sort"] = $TEMPLATE[$file]["sort"];
					}

					$res[$file] = $restmp;
				}
				closedir($handle);
			}
		}
	}
	sortByColumn($res, "sort");

	return array_values($res);
}

function ParsePath($path, $bLast=false, $url=false, $param="", $bLogical = false)
{
	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	if($url===false)
		$url = BX_ROOT."/admin/fileman_admin.php";

	$arSite = array();
	if($site!==false && $site <> '')
	{
		$res = CSite::GetByID($site);
		if(!($arSite = $res->Fetch()))
			$site=false;
	}

	$addUrl = ($bLogical?"logical=Y":"");

	$arDirPath = explode("/", $path);
	$full_path = "";
	$prev_path = "";
	$arPath = array();
	if($bLast || $path <> '' || $site <> '')
	{
		$html_path = '<a href="'.$url.'?lang='.LANG.'&'.$addUrl.'">'.GetMessage("MAIN_ROOT_FOLDER").'</a>/';
	}
	else
	{
		$html_path = GetMessage("MAIN_ROOT_FOLDER")."/";
	}

	if($site!==false)
	{
		if($bLast || $path <> '')
		{
			$html_path .= '<a href="'.$url.'?lang='.LANG.'&'.$addUrl.'&amp;site='.$site.'">'.$arSite["NAME"].'</a>/';
		}
		else
		{
			$html_path .= $arSite["NAME"]."/";
		}
	}

	$io = CBXVirtualIo::GetInstance();
	$pathLast = count($arDirPath)-1;
	$last = "";
	foreach($arDirPath as $i => $pathPart)
	{
		if($pathPart == '')
			continue;

		$prev_path = $full_path;
		$full_path .= "/".$pathPart;
		$last = $pathPart;

		$sSectionName = $pathPart;
		if($bLogical && $io->DirectoryExists($DOC_ROOT.$full_path))
		{
			if(!$io->FileExists($DOC_ROOT.$full_path."/.section.php"))
				continue;

			include($io->GetPhysicalName($DOC_ROOT.$full_path."/.section.php"));
			if($sSectionName == '')
				$sSectionName = GetMessage("admin_tools_no_name");
		}

		if($i==$pathLast && (!$bLast || !$io->DirectoryExists($DOC_ROOT.$full_path)))
		{
			$html_path .= $sSectionName;
			$arPath[] = array(
				"LINK" => "",
				"TITLE" => $sSectionName
			);
		}
		else
		{
			$html_path .= "<a href=\"".$url."?lang=".LANG.'&'.$addUrl."&path=".UrlEncode($full_path).($site?"&site=".$site : "").($param<>""? "&".$param:"")."\">".$sSectionName."</a>/";
			if(!$arSite || !$bLogical || rtrim($arSite["DIR"], "/") != rtrim($full_path, "/"))
			{
				$arPath[] = array(
					"LINK" => $url."?lang=".LANG."&".$addUrl."&path=".UrlEncode($full_path).($site?"&site=".$site : "").($param<>""? "&".$param:""),
					"TITLE" => $sSectionName
				);
			}
		}
	}

	return array(
		"PREV" => $prev_path,
		"FULL" => $full_path,
		"HTML" => $html_path,
		"LAST" => $last,
		"AR_PATH" => $arPath,
	);
}

function CompareFiles($f1, $f2, $sort=array())
{
	$by = key($sort);
	$order = $sort[$by];
	if(mb_strtolower($order) == "desc")
	{
		if($by=="size")	return intval($f1["SIZE"])<intval($f2["SIZE"]);
		if($by=="timestamp") return intval($f1["TIMESTAMP"])<intval($f2["TIMESTAMP"]);
		return $f1["NAME"]<$f2["NAME"];
	}
	else
	{
		if($by=="size")	return intval($f1["SIZE"])>intval($f2["SIZE"]);
		if($by=="timestamp") return intval($f1["TIMESTAMP"])>intval($f2["TIMESTAMP"]);
		return $f1["NAME"]>$f2["NAME"];
	}
}

function GetDirList($path, &$arDirs, &$arFiles, $arFilter=array(), $sort=array(), $type="DF", $bLogical=false,$task_mode=false)
{
	global $USER, $APPLICATION;

	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$arDirs=array();
	$arFiles=array();

	$exts = mb_strtolower($arFilter["EXTENSIONS"] ?? '');
	$arexts=explode(",", $exts);
	if(isset($arFilter["TYPE"]))
		$type = mb_strtoupper($arFilter["TYPE"]);

	$io = CBXVirtualIo::GetInstance();
	$path = $io->CombinePath("/", $path);
	$abs_path = $io->CombinePath($DOC_ROOT, $path);

	if(!$io->DirectoryExists($abs_path))
		return false;

	$date_format = CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL"));
	$tzOffset = CTimeZone::GetOffset();

	$dir = $io->GetDirectory($abs_path);
	$arChildren = $dir->GetChildren();
	$arExtension = array("php"=>1, "html"=>1, "php3"=>1, "php4"=>1, "php5"=>1, "php6"=>1, "phtml"=>1, "htm"=>1);
	foreach ($arChildren as $child)
	{
		$arFile = array();

		if(($type=="F" || $type=="") && $child->IsDirectory())
			continue;
		if(($type=="D" || $type=="") && !$child->IsDirectory())
			continue;

		$file = $child->GetName();

		if($bLogical)
		{
			if($child->IsDirectory())
			{
				$sSectionName = "";
				$fsn = $io->CombinePath($abs_path, $file, ".section.php");
				if(!$io->FileExists($fsn))
					continue;

				include($io->GetPhysicalName($fsn));
				$arFile["LOGIC_NAME"] = $sSectionName;
			}
			else
			{
				$ext = CFileMan::GetFileTypeEx($file);
				if(!isset($arExtension[$ext]))
					continue;

				if($file=='.section.php')
					continue;

				if(!preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|php6|phtml)$/', $file, $regs))
				{
					$f = $io->GetFile($abs_path."/".$file);
					$filesrc = $f->GetContents();

					$title = PHPParser::getPageTitle($filesrc);
					if($title===false)
						continue;
					$arFile["LOGIC_NAME"] = $title;
				}
			}
		}

		$arFile["PATH"] = $abs_path."/".$file;
		$arFile["ABS_PATH"] = $path."/".$file;
		$arFile["NAME"] = $file;

		$arPerm = $APPLICATION->GetFileAccessPermission(array($site, $path."/".$file), $USER->GetUserGroupArray(),$task_mode);
		if ($task_mode)
		{
			$arFile["PERMISSION"] = $arPerm[0];
			if (!empty($arPerm[1]))
				$arFile["PERMISSION_EX"] = $arPerm[1];
		}
		else
			$arFile["PERMISSION"] = $arPerm;

		$arFile["TIMESTAMP"] = $child->GetModificationTime() + $tzOffset;
		$arFile["DATE"] = date($date_format, $arFile["TIMESTAMP"]);

		if (isset($arFilter["TIMESTAMP_1"]) && strtotime($arFile["DATE"]) < strtotime($arFilter["TIMESTAMP_1"]))
			continue;
		if (isset($arFilter["TIMESTAMP_2"]) && strtotime($arFile["DATE"]) > strtotime($arFilter["TIMESTAMP_2"]))
			continue;

		if(is_set($arFilter, "MIN_PERMISSION") && $arFile["PERMISSION"]<$arFilter["MIN_PERMISSION"] && !$task_mode)
			continue;

		if(!$child->IsDirectory() && $arFile["PERMISSION"]<="R" && !$task_mode)
			continue;

		if ($bLogical)
		{
			if(!empty($arFilter["NAME"]) && mb_strpos($arFile["LOGIC_NAME"], $arFilter["NAME"]) === false)
				continue;
		}
		else
		{
			if(!empty($arFilter["NAME"]) && mb_strpos($arFile["NAME"], $arFilter["NAME"]) === false)
				continue;
		}

		//if(strlen($arFilter["NAME"])>0 && strpos($arFile["NAME"], $arFilter["NAME"])===false)
		//	continue;

		if(mb_substr($arFile["ABS_PATH"], 0, mb_strlen(BX_ROOT."/modules")) == BX_ROOT."/modules" && !$USER->CanDoOperation('edit_php') && !$task_mode)
			continue;

		if ($arFile["PERMISSION"]=="U" && !$task_mode)
		{
			$ftype = GetFileType($arFile["NAME"]);
			if ($ftype!="SOURCE" && $ftype!="IMAGE" && $ftype!="UNKNOWN") continue;
			if (mb_substr($arFile["NAME"], 0, 1) == ".") continue;
		}

		if($child->IsDirectory())
		{
			$arFile["SIZE"] = 0;
			$arFile["TYPE"] = "D";
			$arDirs[]=$arFile;
		}
		else
		{
			if($exts!="")
				if(!in_array(mb_strtolower(mb_substr($file, bxstrrpos($file, ".") + 1)), $arexts))
					continue;

			$arFile["TYPE"] = "F";
			$arFile["SIZE"] = $child->GetFileSize();
			$arFiles[]=$arFile;
		}
	}

	if(is_array($sort) && !empty($sort))
	{
		$by = key($sort);
		$order = mb_strtolower($sort[$by]);
		$by = mb_strtolower($by);
		if($order!="desc")
			$order="asc";
		if($by!="size" && $by!="timestamp" && $by!="name_nat")
			$by="name";

		usort($arDirs, array("FilesCmp", "cmp_".$by."_".$order));
		usort($arFiles, array("FilesCmp", "cmp_".$by."_".$order));
	}

	return null;
}

function SetPrologTitle($prolog, $title)
{
	if(preg_match('/
		(\$APPLICATION->SetTitle\()
		(
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"                           # match double quoted string
			|
			\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'                       # match single quoted string
		)
		(\);)
		/ix', $prolog, $regs)
	)
	{
		$prolog = str_replace($regs[0], $regs[1]."\"".EscapePHPString($title)."\");", $prolog);
	}
	else
	{
		$p = mb_strpos($prolog, "prolog_before");
		if($p===false)
			$p = mb_strpos($prolog, "prolog.php");
		if($p===false)
			$p = mb_strpos($prolog, "header.php");

		if($p===false)
		{
			if($title == '')
				$prolog = preg_replace("#<title>[^<]*</title>#i", "", $prolog);
			elseif(preg_match("#<title>[^<]*</title>#i", $prolog))
				$prolog = preg_replace("#<title>[^<]*</title>#i", "<title>".$title."</title>", $prolog);
			else
				$prolog = $prolog."\n<title>".htmlspecialcharsbx($title)."</title>\n";
		}
		else
		{
			$p = mb_strpos(mb_substr($prolog, $p), ")") + $p;
			$prolog = mb_substr($prolog, 0, $p + 1).";\n\$APPLICATION->SetTitle(\"".EscapePHPString($title)."\")".mb_substr($prolog, $p + 1);
		}
	}
	return $prolog;
}

function SetPrologProperty($prolog, $property_key, $property_val)
{
	if(preg_match("'(\\\$APPLICATION->SetPageProperty\\(\"".preg_quote(EscapePHPString($property_key), "'")."\" *, *)([\"\\'])(.*?)(?<!\\\\)([\"\\'])(\\);[\r\n]*)'i", $prolog, $regs)
		|| preg_match("'(\\\$APPLICATION->SetPageProperty\\(\\'".preg_quote(EscapePHPString($property_key, "'"), "'")."\\' *, *)([\"\\'])(.*?)(?<!\\\\)([\"\\'])(\\);[\r\n]*)'i", $prolog, $regs))
	{
		if ($property_val == '')
			$prolog = str_replace($regs[1].$regs[2].$regs[3].$regs[4].$regs[5], "", $prolog);
		else
			$prolog = str_replace($regs[1].$regs[2].$regs[3].$regs[4].$regs[5], $regs[1].$regs[2].EscapePHPString($property_val, $regs[2]).$regs[4].$regs[5], $prolog);
	}
	else
	{
		if ($property_val <> '')
		{
			$p = mb_strpos($prolog, "prolog_before");
			if($p===false)
				$p = mb_strpos($prolog, "prolog.php");
			if($p===false)
				$p = mb_strpos($prolog, "header.php");
			if($p!==false)
			{
				$p = mb_strpos(mb_substr($prolog, $p), ")") + $p;
				$prolog = mb_substr($prolog, 0, $p + 1).";\n\$APPLICATION->SetPageProperty(\"".EscapePHPString($property_key)."\", \"".EscapePHPString($property_val)."\")".mb_substr($prolog, $p + 1);
			}
		}
	}
	return $prolog;
}

function IsPHP($src)
{
	if(strpos($src, "<?") !== false)
		return true;
	if(preg_match("/(<script[^>]*language\\s*=\\s*)('|\"|)php('|\"|)([^>]*>)/i", $src))
		return true;
	return false;
}
