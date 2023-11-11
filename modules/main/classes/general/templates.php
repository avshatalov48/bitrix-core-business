<?php

class CTemplates
{
	public static function GetList($arFilter = array(), $arCurrentValues = array(), $template_id = array())
	{
		if(!is_set($arFilter, "FOLDER"))
		{
			$arr = CTemplates::GetFolderList();
			$arFilter["FOLDER"] = array_keys($arr);
		}

		$arTemplates = array();
		foreach($arFilter["FOLDER"] as $folder)
		{
			$folder = _normalizePath($folder);
			$arTemplates[$folder] = array();
			$arPath = array(
				"/bitrix/modules/".$folder."/install/templates/",
				BX_PERSONAL_ROOT."/templates/.default/",
			);

			if(is_array($template_id))
			{
				foreach($template_id as $v)
					$arPath[] = BX_PERSONAL_ROOT."/templates/"._normalizePath($v)."/";
			}
			elseif($template_id <> '')
			{
				$arPath[] = BX_PERSONAL_ROOT."/templates/"._normalizePath($template_id)."/";
			}

			foreach($arPath as $path)
				CTemplates::__FindTemplates($path, $arTemplates[$folder], $arCurrentValues, $folder);

			if(empty($arTemplates[$folder]))
			{
				unset($arTemplates[$folder]);
			}
			else
			{
				$arTemplate = $arTemplates[$folder];
				$arTemplateTemp = array();
				$arSeparators = array();
				foreach($arTemplate as $k=>$val)
					if($val["SEPARATOR"]=="Y")
						$arSeparators[$k] = $val;

				foreach($arSeparators as $sep_id=>$val_sep)
				{
					$arTemplateTemp[$sep_id] = $val_sep;
					reset($arTemplate);
					while(list($k, $val) = current($arTemplate))
					{
						next($arTemplate);

						if($val===false)
							continue;

						if($k==$sep_id)
						{
							while(list($k, $val) = current($arTemplate))
							{
								next($arTemplate);

								if($val === false)
									continue;
								if($val["SEPARATOR"]=="Y")
									break;
								if($val["PARENT"] <> '' && $val["PARENT"]!=$sep_id)
									continue;

								$arTemplateTemp[$k] = $val;
								$arTemplate[$k] = false;
							}
							//continue;
						}
						if($val["PARENT"]==$sep_id)
						{
							$arTemplateTemp[$k] = $val;
							$arTemplate[$k] = false;
						}
					}
				}

				$bW = true;
				foreach($arTemplate as $k=>$val)
				{
					if($val===false || $val["SEPARATOR"] == "Y")
						continue;
					if($bW)
					{
						if(!empty($arSeparators))
							$arTemplateTemp[md5(uniqid(rand(), true))] = array("NAME"=> "----------------------------", "SEPARATOR"=>"Y");
						$bW = false;
					}
					$arTemplateTemp[$k] = $val;
					$arTemplate[$k] = false;
				}

				$arTemplates[$folder] = $arTemplateTemp;
			}
		}
		return $arTemplates;
	}

	public static function GetByID($id, $arCurrentValues = array(), $templateID = array())
	{
		$folder = mb_substr($id, 0, mb_strpos($id, "/"));
		$arRes = CTemplates::GetList(array("FOLDER"=>array($folder)), $arCurrentValues, $templateID);
		$all_templates = $arRes[$folder];
		if(is_set($all_templates, $id))
			return $all_templates[$id];
		return false;
	}

	public static function __FindTemplates($root, &$arTemplates, $arCurrentValues=array(), $init="")
	{
		if(is_dir($_SERVER['DOCUMENT_ROOT'].$root.$init))
		{
			$arTemplateDescription = array();
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$root.$init."/.description.php"))
			{
				include($_SERVER['DOCUMENT_ROOT'].$root.$init."/.description.php");
				foreach($arTemplateDescription as $path=>$desc)
				{
					$desc["REAL_PATH"] = $root.$init."/".$path;
					if($desc["PARENT"] <> '')
						$desc["PARENT"] = $init."/".$desc["PARENT"];
					$arTemplates[$init."/".$path] = $desc;
				}
			}

			if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$root.$init))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..") continue;
					CTemplates::__FindTemplates($root, $arTemplates, $arCurrentValues, $init."/".$file);
				}
			}
		}
	}

	public static function GetFolderList($template_id = false)
	{
		$arTemplateFolders = array();
		$arTemplateFoldersSort = array();
		$path = "/bitrix/modules";
		if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
		{
			while(($module_name = readdir($handle)) !== false)
			{
				if($module_name == "." || $module_name == "..") continue;
				if(is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$module_name))
				{
					$path_mod = $path."/".$module_name."/install/templates";
					if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod))
					{
						if($handle_mod = @opendir($_SERVER["DOCUMENT_ROOT"].$path_mod))
						{
							while(($file_templ = readdir($handle_mod)) !== false)
							{
								if($file_templ == "." || $file_templ == ".." || $file_templ=="lang")
									continue;
								if(is_dir($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ))
								{
									$sSectionName = false;
									$iSort = 500;
									if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ."/.description.php"))
									{
										if(file_exists(($fname = $_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/".LangSubst(LANGUAGE_ID)."/".$module_name."/.description.php")))
											__IncludeLang($fname);
										if(LANGUAGE_ID <> "ru" && file_exists(($fname = $_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/".LANGUAGE_ID."/".$module_name."/.description.php")))
											__IncludeLang($fname);
										include($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ."/.description.php");
									}
									if($sSectionName)
									{
										$arTemplateFolders[$module_name] = $sSectionName;
										$arTemplateFoldersSort[$module_name] = $iSort;
									}
								}
							}
							@closedir($handle_mod);
						}
					}
				}
			}
			@closedir($handle);
		}

		$arPath = array(BX_PERSONAL_ROOT."/templates/.default");
		if($template_id)
			$arPath[] = BX_PERSONAL_ROOT."/templates/".$template_id;

		foreach($arPath as $path)
		{
			if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
			{
				while(($folder_name = readdir($handle)) !== false)
				{
					if($folder_name == "." || $folder_name == ".." || $folder_name=="lang")
						continue;
					if(is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name))
					{
						$sSectionName = false;
						$iSort = 500;
						if(file_exists($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name."/.description.php"))
							include($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name."/.description.php");
						if($sSectionName)
						{
							$arTemplateFolders[$folder_name] = $sSectionName;
							$arTemplateFoldersSort[$folder_name] = $iSort;
						}
					}
				}
				@closedir($handle);
			}
		}
		array_multisort($arTemplateFoldersSort, $arTemplateFolders);

		return $arTemplateFolders;
	}
}
