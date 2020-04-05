<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

IncludeModuleLangFile(__FILE__);

class CTopPanel
{
	//Check permissions functions
	public static function IsCanCreatePage($currentDirPath, $documentRoot, $filemanExists)
	{
		global $USER;

		$io = CBXVirtualIo::GetInstance();

		if (!$io->DirectoryExists($documentRoot.$currentDirPath) || !$USER->CanDoFileOperation("fm_create_new_file", Array(SITE_ID, $currentDirPath)))
			return false;

		if ($filemanExists)
			return $USER->CanDoOperation("fileman_admin_files");

		return true;
	}

	public static function IsCanCreateSection($currentDirPath, $documentRoot, $filemanExists)
	{
		global $USER;

		$io = CBXVirtualIo::GetInstance();

		if (!$io->DirectoryExists($documentRoot.$currentDirPath) ||
			!$USER->CanDoFileOperation("fm_create_new_folder", Array(SITE_ID, $currentDirPath)) ||
			!$USER->CanDoFileOperation("fm_create_new_file", Array(SITE_ID, $currentDirPath)))
			return false;

		if ($filemanExists)
			return ($USER->CanDoOperation("fileman_admin_folders") && $USER->CanDoOperation("fileman_admin_files"));

		return true;
	}

	public static function IsCanEditPage($currentFilePath, $documentRoot, $filemanExists)
	{
		global $USER;

		$io = CBXVirtualIo::GetInstance();

		if (!$io->FileExists($documentRoot.$currentFilePath) || !$USER->CanDoFileOperation("fm_edit_existent_file",Array(SITE_ID, $currentFilePath)))
			return false;

		//need fm_lpa for every .php file, even with no php code inside
		if (in_array(GetFileExtension($currentFilePath), GetScriptFileExt()) && !$USER->CanDoFileOperation('fm_lpa', Array(SITE_ID, $currentFilePath)) && !$USER->CanDoOperation('edit_php'))
			return false;

		if ($filemanExists)
			return ($USER->CanDoOperation("fileman_admin_files") && $USER->CanDoOperation("fileman_edit_existent_files"));

		return true;
	}

	public static function IsCanEditSection($currentDirPath, $filemanExists)
	{
		global $USER;

		if (!$USER->CanDoFileOperation("fm_edit_existent_folder", Array(SITE_ID, $currentDirPath)))
			return false;

		if ($filemanExists)
			return ($USER->CanDoOperation("fileman_edit_existent_folders") && $USER->CanDoOperation("fileman_admin_folders"));

		return true;
	}

	public static function IsCanEditPermission($currentFilePath, $documentRoot, $filemanExists)
	{
		global $USER;

		$io = CBXVirtualIo::GetInstance();

		if (!($io->FileExists($documentRoot.$currentFilePath) || $io->DirectoryExists($documentRoot.$currentFilePath)) ||
			!$USER->CanDoFileOperation("fm_edit_existent_folder",Array(SITE_ID, $currentFilePath)) ||
			!$USER->CanDoFileOperation("fm_edit_permission",Array(SITE_ID, $currentFilePath)))
				return false;

		if ($filemanExists)
			return ($USER->CanDoOperation("fileman_edit_existent_folders") && $USER->CanDoOperation("fileman_admin_folders"));

		return true;
	}

	public static function IsCanDeletePage($currentFilePath, $documentRoot, $filemanExists)
	{
		global $USER;

		$io = CBXVirtualIo::GetInstance();

		if (!$io->FileExists($documentRoot.$currentFilePath) || !$USER->CanDoFileOperation("fm_delete_file",Array(SITE_ID, $currentFilePath)))
			return false;

		if ($filemanExists)
			return ($USER->CanDoOperation("fileman_admin_files"));

		return true;
	}

	public static function GetStandardButtons()
	{
		global $USER, $APPLICATION, $DB;

		if (isset($_SERVER["REAL_FILE_PATH"]) && $_SERVER["REAL_FILE_PATH"] != "")
		{
			$currentDirPath = dirname($_SERVER["REAL_FILE_PATH"]);
			$currentFilePath = $_SERVER["REAL_FILE_PATH"];
		}
		else
		{
			$currentDirPath = $APPLICATION->GetCurDir();
			$currentFilePath = $APPLICATION->GetCurPage(true);
		}

		$encCurrentDirPath = urlencode($currentDirPath);
		$encCurrentFilePath = urlencode($currentFilePath);
		$encRequestUri = urlencode($_SERVER["REQUEST_URI"]);
		$encSiteTemplateId = urlencode(SITE_TEMPLATE_ID);

		$documentRoot = CSite::GetSiteDocRoot(SITE_ID);
		$filemanExists = IsModuleInstalled("fileman");

		//create button
		$defaultUrl = "";
		$bCanCreatePage = CTopPanel::IsCanCreatePage($currentDirPath, $documentRoot, $filemanExists);
		$bCanCreateSection = CTopPanel::IsCanCreateSection($currentDirPath, $documentRoot, $filemanExists);

		if ($bCanCreatePage || $bCanCreateSection)
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin_tools.php");
			//create page from new template
			$arActPageTemplates = CPageTemplate::GetList(array(SITE_TEMPLATE_ID));
			//create page from old template
			$arPageTemplates = GetFileTemplates(SITE_ID, array(SITE_TEMPLATE_ID));
		}

		// CREATE PAGE button and submenu
		$arMenu = Array();
		if ($bCanCreatePage)
		{
			$defaultUrl = $APPLICATION->GetPopupLink(
				Array(
					"URL"=>"/bitrix/admin/public_file_new.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&templateID=".$encSiteTemplateId.
						"&path=".$encCurrentDirPath."&back_url=".$encRequestUri,
					"PARAMS"=> Array("min_width"=>450, "min_height" => 250)
				)
			);

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_create_page"),
				"TITLE"=>GetMessage("top_panel_create_page_title"),
				"ICON"=>"panel-new-file",
				"ACTION"=> $defaultUrl,
				"DEFAULT"=>true,
				"SORT" => 10,
				"HK_ID"=>"top_panel_create_page"
			);

			//templates menu for pages
			$arSubmenu = array();
			if(!empty($arActPageTemplates))
			{
				foreach($arActPageTemplates as $pageTemplate)
				{
					if($pageTemplate['type'] == '' || $pageTemplate['type'] == 'page')
					{
						$arSubmenu[] = array(
							"TEXT"=>$pageTemplate['name'],
							"TITLE"=>GetMessage("top_panel_template")." ".$pageTemplate['file'].($pageTemplate['description'] <> ''? "\n".$pageTemplate['description']:""),
							"ICON"=>($pageTemplate['icon'] == ''? "panel-new-file-template":""),
							"IMAGE"=>($pageTemplate['icon'] <> ''? $pageTemplate['icon']:""),
							"ACTION"=> str_replace("public_file_new.php?", "public_file_new.php?wiz_template=".urlencode($pageTemplate['file'])."&", $defaultUrl),
						);
					}
				}
			}

			if(!empty($arPageTemplates) && (!empty($arSubmenu) || count($arPageTemplates)>1))
			{
				foreach($arPageTemplates as $pageTemplate)
					$arSubmenu[] = array(
						"TEXT"=>$pageTemplate['name'],
						"TITLE"=>GetMessage("top_panel_template")." ".$pageTemplate['file'],
						"ICON"=>"panel-new-file-template",
						"ACTION"=> str_replace("public_file_new.php?", "public_file_new.php?page_template=".urlencode($pageTemplate['file'])."&", $defaultUrl),
					);
			}

			//page from template
			if($bCanCreatePage && !empty($arSubmenu))
			{
				$arMenu[] = array(
					"TEXT"=>GetMessage("top_panel_create_from_template"),
					"TITLE"=>GetMessage("top_panel_create_from_template_title"),
					"ICON"=>"panel-new-file-template",
					"MENU"=>$arSubmenu,
					"SORT" => 20
				);
			}
		}

		if (!empty($arMenu))
		{
			$APPLICATION->AddPanelButton(Array(
				"HREF"=> ($defaultUrl == "" ? "" : "javascript:".$defaultUrl),
				'TYPE' => 'BIG',
				"ID"=>"create",
				"ICON"=>"bx-panel-create-page-icon",
				"ALT"=>GetMessage("top_panel_create_title"),
				"TEXT"=> GetMessage("top_panel_create_new"),//GetMessage("top_panel_create"),
				"MAIN_SORT"=>"100",
				"SORT"=>10,
				"MENU"=> $arMenu,
				"RESORT_MENU" => true,
				"HK_ID"=>"top_panel_create_new",
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_create_new_tooltip_title"),
					"TEXT" => GetMessage("top_panel_create_new_tooltip")
				),
				"HINT_MENU" => array(
					"TITLE" => GetMessage("top_panel_create_new_menu_tooltip_title"),
					"TEXT" => GetMessage("top_panel_create_new_menu_tooltip")
				)
			));
		}

		// CREATE SECTION button and submenu
		$arMenu = array();
		if ($bCanCreateSection)
		{
			$defaultUrl = $APPLICATION->GetPopupLink(Array(
				"URL"=>"/bitrix/admin/public_file_new.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&templateID=".$encSiteTemplateId.
					"&newFolder=Y&path=".$encCurrentDirPath."&back_url=".$encRequestUri,
				"PARAMS"=>Array("min_width"=>450, "min_height" => 250)));

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_create_folder"),
				"TITLE"=>GetMessage("top_panel_create_folder_title"),
				"ICON"=>"panel-new-folder",
				'DEFAULT'=> true,
				"ACTION"=> $defaultUrl,
				"SORT"=>10,
				"HK_ID"=>"top_panel_create_folder",
			);

			//templates menu for sections
			$arSectSubmenu = array();
			if(!empty($arActPageTemplates))
			{
				foreach($arActPageTemplates as $pageTemplate)
				{
					if($pageTemplate['type'] == '' || $pageTemplate['type'] == 'section')
					{
						$arSectSubmenu[] = array(
							"TEXT"=>$pageTemplate['name'],
							"TITLE"=>GetMessage("top_panel_template")." ".$pageTemplate['file'].($pageTemplate['description'] <> ''? "\n".$pageTemplate['description']:""),
							"ICON"=>($pageTemplate['icon'] == ''? "panel-new-file-template":""),
							"IMAGE"=>($pageTemplate['icon'] <> ''? $pageTemplate['icon']:""),
							"ACTION"=> str_replace("public_file_new.php?", "public_file_new.php?newFolder=Y&wiz_template=".urlencode($pageTemplate['file'])."&", $defaultUrl),
						);
					}
				}
			}

			if(!empty($arPageTemplates) && (!empty($arSectSubmenu) || count($arPageTemplates)>1))
			{
				if(!empty($arSectSubmenu))
					$arSectSubmenu[] = array("SEPARATOR"=>true);

				foreach($arPageTemplates as $pageTemplate)
				{
					$arSectSubmenu[] = array(
						"TEXT"=>$pageTemplate['name'],
						"TITLE"=>GetMessage("top_panel_template")." ".$pageTemplate['file'],
						"ICON"=>"panel-new-file-template",
						"ACTION"=> str_replace("public_file_new.php?", "public_file_new.php?newFolder=Y&page_template=".urlencode($pageTemplate['file'])."&", $defaultUrl),
					);
				}
			}

			//section from template
			if($bCanCreateSection && !empty($arSectSubmenu))
			{
				$arMenu[] = array(
					"TEXT"=>GetMessage("top_panel_create_folder_template"),
					"TITLE"=>GetMessage("top_panel_create_folder_template_title"),
					"ICON"=>"panel-new-folder-template",
					"MENU"=>$arSectSubmenu,
					"SORT"=>20,
				);
			}
		}

		if (!empty($arMenu))
		{
			$APPLICATION->AddPanelButton(Array(
				"HREF"=> ($defaultUrl == "" ? "" : "javascript:".$defaultUrl),
				'TYPE' => 'BIG',
				"ID"=>"create_section",
				"ICON"=>"bx-panel-create-section-icon",
				"ALT"=>GetMessage("top_panel_create_title"),
				"TEXT"=>GetMessage("top_panel_create_folder_new"),//GetMessage("top_panel_create"),
				"MAIN_SORT"=>"100",
				"SORT"=>20,
				"MENU"=> $arMenu,
				"RESORT_MENU" => true,
				"HK_ID" => "top_panel_create_folder_new",
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_create_folder_new_tooltip_title"),
					"TEXT" => GetMessage("top_panel_create_folder_new_tooltip")
				),
				"HINT_MENU" => array(
					"TITLE" => GetMessage("top_panel_create_folder_new_menu_tooltip_title"),
					"TEXT" => GetMessage("top_panel_create_folder_new_menu_tooltip")
				)
			));
		}


		// EDIT PAGE button and submenu
		$defaultUrl = "";
		$arMenu = Array();
		if (CTopPanel::IsCanEditPage($currentFilePath, $documentRoot, $filemanExists))
		{
			$defaultUrl = $APPLICATION->GetPopupLink(array(
				"URL"=> "/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&path=".$encCurrentFilePath."&site=".SITE_ID."&back_url=".$encRequestUri."&templateID=".$encSiteTemplateId,
				"PARAMS"=>array(
					"width"=>780,
					"height"=>470,
					"resizable"=>true,
					"min_width"=> 780,
					"min_height"=> 400,
					'dialog_type' => 'EDITOR'
				),
			));

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_edit_page"),
				"TITLE"=>GetMessage("top_panel_edit_page_title"),
				"ICON"=>"panel-edit-visual",
				"ACTION"=> $defaultUrl,
				"DEFAULT"=>true,
				"SORT"=>10,
				"HK_ID"=>"top_panel_edit_page",
			);

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_page_prop"),
				"TITLE"=>GetMessage("top_panel_page_prop_title"),
				"ICON"=>"panel-file-props",
				"ACTION"=> $APPLICATION->GetPopupLink(Array(
					"URL"=>"/bitrix/admin/public_file_property.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentFilePath."&back_url=".$encRequestUri,
					"PARAMS" => Array("min_width"=>450, "min_height" => 250))
				),
				"SORT" => 20,
				"HK_ID"=>"top_panel_page_prop"
			);

			$arMenu[] = Array("SEPARATOR" => true, "SORT"=>49);

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_edit_page_html"),
				"TITLE"=>GetMessage("top_panel_edit_page_html_title"),
				"ICON"=>"panel-edit-text",
				"ACTION"=>$APPLICATION->GetPopupLink(Array(
					"URL"=>"/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&noeditor=Y&path=".$encCurrentFilePath."&site=".SITE_ID."&back_url=".$encRequestUri,
					"PARAMS"=>array("width"=>780, "height"=>470, 'dialog_type' => 'EDITOR', "min_width" => 700, "min_height" => 400))
				),
				"SORT" => 50,
				"HK_ID"=>"top_panel_edit_page_html",
			);

			if ($USER->CanDoOperation("edit_php"))
			{
				$arMenu[] = Array(
					"TEXT"=>GetMessage("top_panel_edit_page_php"),
					"TITLE"=>GetMessage("top_panel_edit_page_php_title"),
					"ICON"=>"panel-edit-php",
					"ACTION"=>$APPLICATION->GetPopupLink(Array(
						"URL" => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&path=".$encCurrentFilePath."&site=".SITE_ID."&back_url=".$encRequestUri."&templateID=".$encSiteTemplateId,
						"PARAMS" => Array("width"=>770, "height" => 470, 'dialog_type' => 'EDITOR', "min_width" => 700, "min_height" => 400))
					),
					"SORT" => 60,
					"HK_ID"=>"top_panel_edit_page_php",
				);
			}
		}

		$bNeedSep = false;
		if (CTopPanel::IsCanEditPermission($currentFilePath, $documentRoot, $filemanExists))
		{
			$bNeedSep = true;
			//access button
			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_access_page_new"),//GetMessage("top_panel_access_page"),
				"TITLE"=>GetMessage("top_panel_access_page_title"),
				"ICON"=>"panel-file-access",
				"ACTION"=>$APPLICATION->GetPopupLink(Array(
					"URL"=>"/bitrix/admin/public_access_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentFilePath."&back_url=".$encRequestUri,
					"PARAMS" => Array("min_width"=>450, "min_height" => 250)
				)),
				"SORT" => 30,
				"HK_ID"=>"top_panel_access_page_new"
			);
		}


		//delete button
		if (CTopPanel::IsCanDeletePage($currentFilePath, $documentRoot, $filemanExists))
		{
			$bNeedSep = true;
			$arMenu[] = array(
				"ID" => "delete",
				"ICON" => "icon-delete",
				"ALT" => GetMessage("top_panel_del_page"),
				"TEXT" => GetMessage("top_panel_del_page"),
				"ACTION" => $APPLICATION->GetPopupLink(array(
					"URL" => "/bitrix/admin/public_file_delete.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentFilePath,
					"PARAMS" => Array(
						"min_width"=>250,
						"min_height" => 180,
						'height' => 180,
						'width' => 440
					)
				)),
				"SORT" => 40,
				"HK_ID" => "top_panel_del_page"
			);
		}

		if($bNeedSep)
			$arMenu[] = Array("SEPARATOR" => true, "SORT"=>29);

		if (!empty($arMenu))
		{
			//check anonymous access
			$arOperations = CUser::GetFileOperations(array(SITE_ID, $currentFilePath), array(2));
			$bAllowAnonymous = in_array("fm_view_file", $arOperations);

			$APPLICATION->AddPanelButton(array(
				"HREF"=>($defaultUrl == "" ? "" : "javascript:".$defaultUrl),
				"TYPE" => "BIG",
				"ID"=>"edit",
				"ICON"=>($bAllowAnonymous? "bx-panel-edit-page-icon":"bx-panel-edit-secret-page-icon"),
				"ALT"=>GetMessage("top_panel_edit_title"),
				"TEXT"=>GetMessage("top_panel_edit_new"),//GetMessage("top_panel_edit"),
				"MAIN_SORT"=>"200",
				"SORT"=>10,
				"MENU"=> $arMenu,
				"HK_ID"=>"top_panel_edit_new",
				"RESORT_MENU" => true,
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_edit_new_tooltip_title"),
					"TEXT" => GetMessage("top_panel_edit_new_tooltip")
				),
				"HINT_MENU" => array(
					"TITLE" => GetMessage("top_panel_edit_new_menu_tooltip_title"),
					"TEXT" => GetMessage("top_panel_edit_new_menu_tooltip")
				)
			));
		}

		// EDIT SECTION button
		$arMenu = array();
		if (CTopPanel::IsCanEditSection($currentDirPath, $filemanExists))
		{
			$defaultUrl = 'javascript:'.$APPLICATION->GetPopupLink(array(
					"URL"=>"/bitrix/admin/public_folder_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".urlencode($APPLICATION->GetCurDir())."&back_url=".$encRequestUri,
					"PARAMS" => Array("min_width"=>450, "min_height" => 250)
				)
			);

			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_folder_prop"),
				"TITLE"=>GetMessage("top_panel_folder_prop_title"),
				"ICON"=>"panel-folder-props",
				"DEFAULT" => true,
				"ACTION"=> $defaultUrl,
				"SORT"=>10,
				"HK_ID"=>"top_panel_folder_prop",
			);
		}


		if (CTopPanel::IsCanEditPermission($currentDirPath, $documentRoot, $filemanExists))
		{
			$arMenu[] = Array(
				"TEXT"=>GetMessage("top_panel_access_folder_new"), //GetMessage("top_panel_access_folder"),
				"TITLE"=>GetMessage("top_panel_access_folder_title"),
				"ICON"=>"panel-folder-access",
				"ACTION"=>$APPLICATION->GetPopupLink(Array(
					"URL"=>"/bitrix/admin/public_access_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentDirPath."&back_url=".$encRequestUri,
					"PARAMS" => Array("min_width"=>450, "min_height" => 250))
				),
				"SORT"=>30,
				"HK_ID" => "top_panel_access_folder_new",
			);
		}

		if (!empty($arMenu))
		{
			//check anonymous access
			$arOperations = CUser::GetFileOperations(array(SITE_ID, $currentDirPath), array(2));
			$bAllowAnonymous = in_array("fm_view_listing", $arOperations);

			$APPLICATION->AddPanelButton(array(
				"HREF"=> $defaultUrl,
				"ID" => 'edit_section',
				"TYPE" => "BIG",
				"TEXT"=>GetMessage("top_panel_folder_prop_new"),//GetMessage("top_panel_folder_prop"),
				"TITLE"=>GetMessage("top_panel_folder_prop_title"),
				"ICON"=>($bAllowAnonymous? "bx-panel-edit-section-icon":"bx-panel-edit-secret-section-icon"),
				"MAIN_SORT" => "200",
				"SORT" => 20,
				"MENU" => $arMenu,
				"HK_ID"=>"top_panel_folder_prop_new",
				"RESORT_MENU" => true,
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_folder_prop_new_tooltip_title"),
					"TEXT" => GetMessage("top_panel_folder_prop_new_tooltip")
				),
				"HINT_MENU" => array(
					"TITLE" => GetMessage("top_panel_folder_prop_new_menu_tooltip_title"),
					"TEXT" => GetMessage("top_panel_folder_prop_new_menu_tooltip")
				)
			));
		}

		// STRUCTURE button and submenu
		if($USER->CanDoOperation('fileman_view_file_structure') && $USER->CanDoFileOperation('fm_edit_existent_folder', array(SITE_ID, "/")))
		{
			$defaultUrl = $APPLICATION->GetPopupLink(array(
				"URL" => "/bitrix/admin/public_structure.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".$encCurrentFilePath."&templateID=".$encSiteTemplateId,
				"PARAMS" => Array("width"=>350, "height"=>470, "resize" => true)));
			$arMenu = Array();
			if($filemanExists)
			{
				$arMenu[] = array(
					"TEXT" => GetMessage("main_top_panel_struct"),
					"TITLE"=> GetMessage("main_top_panel_struct_title"),
					"ACTION" => $defaultUrl,
					"DEFAULT" => true,
					"HK_ID" => "main_top_panel_struct",
				);
				$arMenu[] = array('SEPARATOR'=>true);
				$arMenu[] = array(
					"TEXT" => GetMessage("main_top_panel_struct_panel"),
					"TITLE" => GetMessage("main_top_panel_struct_panel_title"),
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/fileman_admin.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&path=".urlencode($APPLICATION->GetCurDir()))."')",
					"HK_ID" => "main_top_panel_struct_panel",
				);
			}

			$APPLICATION->AddPanelButton(Array(
				"HREF"=>"javascript:".$defaultUrl,
				"ID"=>"structure",
				"ICON"=>"bx-panel-site-structure-icon",
				"ALT"=>GetMessage("main_top_panel_struct_title"),
				"TEXT"=>GetMessage("main_top_panel_structure"),
				"MAIN_SORT"=>"300",
				"SORT"=>30,
				"MENU"=> $arMenu,
				"HK_ID"=>"main_top_panel_structure",
				"HINT" => array(
					"TITLE" => GetMessage("main_top_panel_structure_tooltip_title"),
					"TEXT" => GetMessage("main_top_panel_structure_tooltip")
				),
			));
		}

		//cache button
		if ($USER->CanDoOperation("cache_control"))
		{
			//recreate cache on the current page
			$arMenu = Array(
				array(
					"TEXT"=>GetMessage("top_panel_cache_page"),
					"TITLE"=>GetMessage("top_panel_cache_page_title"),
					"ICON"=>"panel-page-cache",
					"ACTION" => "BX.clearCache()",
					"DEFAULT"=>true,
					"HK_ID" => "top_panel_cache_page",
				),
			);
			if (!empty($APPLICATION->aCachedComponents))
			{
				$arMenu[] = array(
					"TEXT"=>GetMessage("top_panel_cache_comp"),
					"TITLE"=>GetMessage("top_panel_cache_comp_title"),
					"ICON"=>"panel-comp-cache",
					"ACTION"=>"jsComponentUtils.ClearCache('component_name=".CUtil::addslashes(implode(",", $APPLICATION->aCachedComponents))."&site_id=".SITE_ID."&".bitrix_sessid_get()."');",
					"HK_ID" => "top_panel_cache_comp",
				);
			}
			$arMenu[] = array("SEPARATOR"=>true);

			$sessionClearCache = (isset($_SESSION["SESS_CLEAR_CACHE"]) && $_SESSION["SESS_CLEAR_CACHE"] == "Y");
			$arMenu[] = array(
				"TEXT"=>GetMessage("top_panel_cache_not"),
				"TITLE"=>GetMessage("top_panel_cache_not_title"),
				"CHECKED"=>$sessionClearCache,
				"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("clear_cache_session=".($sessionClearCache? "N" : "Y"), array("clear_cache_session")))."');",
				"HK_ID" => "top_panel_cache_not",
			);

			$APPLICATION->AddPanelButton(array(
				"HREF" => "javascript:BX.clearCache()",
				"TYPE" => "BIG",
				"ICON"=>"bx-panel-clear-cache-icon",
				"TEXT"=> GetMessage("top_panel_cache_new"),//GetMessage("top_panel_cache"),
				"ALT"=>GetMessage("top_panel_clear_cache"),
				"MAIN_SORT"=>"400",
				"SORT"=>10,
				"MENU"=>$arMenu,
				"HK_ID"=>"top_panel_clear_cache",
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_cache_new_tooltip_title"),
					"TEXT" => GetMessage("top_panel_cache_new_tooltip")
				),
				"HINT_MENU" => array(
					"TITLE" => GetMessage("top_panel_cache_new_menu_tooltip_title"),
					"TEXT" => GetMessage("top_panel_cache_new_menu_tooltip")
				),
			));
		}

		$bHideComponentsMenu = false;
		if ($USER->CanDoOperation('edit_php') || !empty($APPLICATION->arPanelFutureButtons['components']))
		{
			if (empty($APPLICATION->arPanelFutureButtons['components']))
			{
				if ($APPLICATION->GetShowIncludeAreas() != 'Y')
				{
					$APPLICATION->AddPanelButtonMenu('components',
						array(
							"TEXT"=>GetMessage("top_panel_edit_mode"),
							"TITLE"=>GetMessage("top_panel_edit_mode_title"),
							"ACTION"=>"jsUtils.Redirect([], BX('bx-panel-toggle').href);",
							"HK_ID" => "top_panel_edit_mode",
						)
					);
				}
				else
				{
					$bHideComponentsMenu = true;
				}
			}

			if ($bHideComponentsMenu)
			{
				$APPLICATION->AddPanelButton(array(
					"ID"=>"components_empty",
					"HREF"=>"javascript:void(0)",
					"ICON"=>"bx-panel-components-icon",
					"TEXT"=>GetMessage("top_panel_comp"),
					"MAIN_SORT"=>"500",
					"SORT"=>10,
					"HINT" => array(
						"TITLE" => GetMessage("top_panel_comp_tooltip_title"),
						"TEXT" => GetMessage('top_panel_comp_tooltip_empty')
					),
				));
			}
			else
			{
				$APPLICATION->AddPanelButton(array(
					"ID"=>"components",
					"ICON"=>"bx-panel-components-icon",
					"TEXT"=>GetMessage("top_panel_comp"),
					"MAIN_SORT"=>"500",
					"SORT"=>10,
					"HINT" => array(
						"TITLE" => GetMessage("top_panel_comp_tooltip_title"),
						"TEXT" => GetMessage("top_panel_comp_tooltip")
					),
				));
			}
		}

		//TEMPLATE button and submenu
		if ($USER->CanDoOperation("edit_php") || $USER->CanDoOperation("lpa_template_edit"))
		{
			$arMenu = array();
			$bUseSubmenu = false;

			$defaultUrl = '';

			if ($USER->CanDoOperation("edit_php"))
			{
				$filePath = SITE_TEMPLATE_PATH."/styles.css";

				if (file_exists($_SERVER['DOCUMENT_ROOT'].$filePath))
				{
					$arMenu[] = array(
						"TEXT"	=> GetMessage("top_panel_templ_site_css"),
						"TITLE"	=> GetMessage("top_panel_templ_site_css_title"),
						"ICON"	=> "panel-edit-text",
						"HK_ID" => "top_panel_templ_site_css",
						"ACTION"=> $APPLICATION->GetPopupLink(
							array(
								"URL" => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&path=".urlencode($filePath)."&site=".SITE_ID."&back_url=".$encRequestUri,
								"PARAMS" => array(
									"width" => 770,
									'height' => 470,
									'resize' => true,
									'dialog_type' => 'EDITOR',
									"min_width" => 700,
									"min_height" => 400
								)
							)
						),
					);
					$bUseSubmenu = true;
				}

				$filePath = SITE_TEMPLATE_PATH."/template_styles.css";

				if (file_exists($_SERVER['DOCUMENT_ROOT'].$filePath))
				{
					$arMenu[] = array(
							"TEXT"   => GetMessage("top_panel_templ_templ_css"),
							"TITLE"  => GetMessage("top_panel_templ_templ_css_title"),
							"ICON"   => "panel-edit-text",
							"HK_ID"  => "top_panel_templ_templ_css",
							"ACTION" => $APPLICATION->GetPopupLink(
								array(
									"URL" => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&path=".urlencode($filePath)."&site=".SITE_ID."&back_url=".$encRequestUri,
									"PARAMS" => array(
										"width" => 770,
										'height' => 470,
										'resize' => true,
										'dialog_type' => 'EDITOR',
										"min_width" => 700,
										"min_height" => 400
									)
								)
							),
						);
					$bUseSubmenu = true;
				}
			}

			$arSubMenu = array(
				array(
					"TEXT"		=>GetMessage("top_panel_templ_edit"),
					"TITLE"		=>GetMessage("top_panel_templ_edit_title"),
					"ICON"		=>"icon-edit",
					"ACTION"	=> "jsUtils.Redirect([], '/bitrix/admin/template_edit.php?lang=".LANGUAGE_ID."&ID=".$encSiteTemplateId."')",
					"DEFAULT"	=>!$bUseSubmenu,
					"HK_ID"		=>"top_panel_templ_edit",
				),

				array(
					"TEXT"		=> GetMessage("top_panel_templ_site"),
					"TITLE"		=> GetMessage("top_panel_templ_site_title"),
					"ICON"		=> "icon-edit",
					"ACTION"	=> "jsUtils.Redirect([], '/bitrix/admin/site_edit.php?lang=".LANGUAGE_ID."&LID=".SITE_ID."')",
					"DEFAULT"	=> false,
					"HK_ID"		=>"top_panel_templ_site",
				),
			);

			if ($bUseSubmenu)
			{
				$arMenu[] = array('SEPARATOR' => "Y");

				$arMenu[] = array(
					"TEXT" => GetMessage("top_panel_cp"),
					"MENU" => $arSubMenu,
				);
			}
			else
			{
				$arMenu = $arSubMenu;
				$defaultUrl = "javascript:".$arSubMenu[0]['ACTION'];
			}

			$APPLICATION->AddPanelButton(Array(
				"HREF" => $defaultUrl,
				"ICON" => "bx-panel-site-template-icon",
				"ALT" => GetMessage("top_panel_templ_title"),
				"TEXT" => GetMessage("top_panel_templ"),
				"MAIN_SORT" => "500",
				"SORT" => 30,
				"MENU" => $arMenu,
				"HK_ID"=>"top_panel_templ",
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_templ_tooltip_title"),
					"TEXT" => GetMessage("top_panel_templ_tooltip")
				),
			));
		}

		//statistics buttons
		if ($USER->CanDoOperation("edit_php"))
		{
			//show debug information
			$sessionShowIncludeTimeExec = isset($_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"]) && $_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y";
			$sessionShowTimeExec = isset($_SESSION["SESS_SHOW_TIME_EXEC"]) && $_SESSION["SESS_SHOW_TIME_EXEC"]=="Y";
			$cmd = ($sessionShowIncludeTimeExec && $sessionShowTimeExec && $DB->ShowSqlStat? "N" : "Y");
			$url = $APPLICATION->GetCurPageParam("show_page_exec_time=".$cmd."&show_include_exec_time=".$cmd."&show_sql_stat=".$cmd, array("show_page_exec_time", "show_include_exec_time", "show_sql_stat"));
			$arMenu = array(
				array(
					"TEXT"=>GetMessage("top_panel_debug_summ"),
					"TITLE"=>GetMessage("top_panel_debug_summ_title"),
					"CHECKED"=>($cmd == "N"),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($url)."');",
					"DEFAULT"=>true,
					"HK_ID" => "top_panel_debug_summ",
				),
				array("SEPARATOR"=>true),
				array(
					"TEXT"=>GetMessage("top_panel_debug_sql"),
					"TITLE"=>GetMessage("top_panel_debug_sql_title"),
					"CHECKED"=>(!!$DB->ShowSqlStat),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("show_sql_stat=".($DB->ShowSqlStat? "N" : "Y"), array("show_sql_stat")))."');",
					"HK_ID" => "top_panel_debug_sql",
				),
				array(
					"TEXT"=>GetMessage("top_panel_debug_cache"),
					"TITLE"=>GetMessage("top_panel_debug_cache_title"),
					"CHECKED"=>(!!\Bitrix\Main\Data\Cache::getShowCacheStat()),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("show_cache_stat=".(\Bitrix\Main\Data\Cache::getShowCacheStat()? "N" : "Y"), array("show_cache_stat")))."');",
					"HK_ID" => "top_panel_debug_cache",
				),
				array(
					"TEXT"=>GetMessage("top_panel_debug_incl"),
					"TITLE"=>GetMessage("top_panel_debug_incl_title"),
					"CHECKED"=>$sessionShowIncludeTimeExec,
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("show_include_exec_time=".($sessionShowIncludeTimeExec? "N" : "Y"), array("show_include_exec_time")))."');",
					"HK_ID"	=> "top_panel_debug_incl",
				),
				array(
					"TEXT"=>GetMessage("top_panel_debug_time"),
					"TITLE"=>GetMessage("top_panel_debug_time_title"),
					"CHECKED"=>$sessionShowTimeExec,
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("show_page_exec_time=".($sessionShowTimeExec? "N" : "Y"), array("show_page_exec_time")))."');",
					"HK_ID"	=>"top_panel_debug_time",
				),
			);
			if(\Bitrix\Main\Loader::includeModule("compression") && CCompress::CheckCanGzip() !== 0)
			{
				$bShowCompressed = isset($_SESSION["SESS_COMPRESS"]) && $_SESSION["SESS_COMPRESS"] == "Y";
				if(isset($_GET["compress"]))
				{
					if($_GET["compress"] === "Y" || $_GET["compress"] === "y")
						$bShowCompressed = true;
					elseif($_GET["compress"] === "N" || $_GET["compress"] === "n")
						$bShowCompressed = false;
				}

				$arMenu[] = array("SEPARATOR"=>true);
				$arMenu[] = array(
					"TEXT"=>GetMessage("top_panel_debug_compr"),
					"TITLE"=>GetMessage("top_panel_debug_compr_title"),
					"CHECKED"=>(!!$bShowCompressed),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($APPLICATION->GetCurPageParam("compress=".($bShowCompressed? "N" : "Y"), array("compress")))."');",
					"HK_ID"=>"top_panel_debug_compr",
				);
			}

			$APPLICATION->AddPanelButton(array(
				"HREF"=>$url,
				"ICON"=>"bx-panel-performance-icon",
				"TEXT"=>GetMessage("top_panel_debug"),
				"ALT"=>GetMessage("top_panel_show_debug"),
				"MAIN_SORT"=>"500",
				"SORT"=>40,
				"MENU"=>$arMenu,
				"HK_ID"=>"top_panel_debug",
				"HINT" => array(
					"TITLE" => GetMessage("top_panel_debug_tooltip_title"),
					"TEXT" => GetMessage("top_panel_debug_tooltip")
				),
			));
		}

		///////////////////////     SHORT URIs     ////////////////////////////////////////
		if($USER->CanDoOperation('manage_short_uri'))
		{
			$url = $APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/short_uri_edit.php?lang=".LANGUAGE_ID."&public=Y&bxpublic=Y&str_URI=".urlencode($APPLICATION->GetCurPageParam("", array("clear_cache", "sessid", "login", "logout", "register", "forgot_password", "change_password", "confirm_registration", "confirm_code", "confirm_user_id", "bitrix_include_areas", "show_page_exec_time", "show_include_exec_time", "show_sql_stat", "show_link_stat")))."&site=".SITE_ID."&back_url=".$encRequestUri,
					"PARAMS" => array(
						"width" => 770,
						'height' => 270,
						'resize' => true,
					)
				)
			);
			$APPLICATION->AddPanelButton(array(
				"HREF" => "javascript:".$url,
				"ICON" => "bx-panel-short-url-icon",
				"ALT" => GetMessage("MTP_SHORT_URI_ALT"),
				"TEXT" => GetMessage("MTP_SHORT_URI"),
				"MAIN_SORT" => 1000,
				"HK_ID"=>"MTP_SHORT_URI",
				"MENU" => array(
					array(
						"TEXT" => GetMessage("MTP_SHORT_URI1"),
						"TITLE" => GetMessage("MTP_SHORT_URI_ALT1"),
						"ACTION" => "javascript:".$url,
						"DEFAULT" => true,
						"HK_ID"=>"MTP_SHORT_URI1",
					),
					array(
						"TEXT" => GetMessage("MTP_SHORT_URI_LIST"),
						"TITLE" => GetMessage("MTP_SHORT_URI_LIST_ALT"),
						"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes("/bitrix/admin/short_uri_admin.php?lang=".LANGUAGE_ID."")."');",
						"HK_ID"=>"MTP_SHORT_URI_LIST",
					)
				),
				"MODE" => "view",
				"HINT" => array(
					"TITLE" => GetMessage("MTP_SHORT_URI_HINT"),
					"TEXT" => GetMessage("MTP_SHORT_URI_HINT_ALT"),
				)
			));
		}
	}

	public static function InitPanelIcons()
	{
		static $bPanelIcons = false;
		if ($bPanelIcons)
			return;
		$bPanelIcons = true;

		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DOCUMENT_ROOT, $APPLICATION, $USER, $MESS; //don't remove!

		if(isset($USER) && is_object($USER) && $USER->IsAuthorized())
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/include/add_top_panel.php"))
				include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/include/add_top_panel.php");

			CTopPanel::GetStandardButtons();

			foreach(GetModuleEvents("main", "OnPanelCreate", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent);
			}
		}
	}

	public static function ShowPanelScripts($bReturn=false)
	{
		global $APPLICATION, $adminPage;

		static $bPanelScriptsIncluded = false;
		if ($bPanelScriptsIncluded)
			return null;
		$bPanelScriptsIncluded = true;

		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");

		if (!$bReturn)
		{
			CUtil::InitJSCore(array('window', 'ajax', 'admin'));
			$APPLICATION->AddHeadString($adminPage->ShowScript());
			$APPLICATION->AddHeadScript('/bitrix/js/main/public_tools.js');
			$APPLICATION->SetAdditionalCSS(ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/pubstyles.css');
		}
		else
		{
			return
				CUtil::InitJSCore(array('window', 'ajax', 'admin'), true)
				.$adminPage->ShowScript()
				.'
<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL('/bitrix/js/main/public_tools.js', true).'"></script>
<link rel="stylesheet" type="text/css" href="'.CUtil::GetAdditionalFileURL(ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/pubstyles.css', true).'" />
';
		}
		return null;
	}

	public static function IsShownForUser($bShowPanel)
	{
		global $USER;

		$userCodes = null;

		//we have settings in the main module options
		if($bShowPanel == false)
		{
			$arCodes = unserialize(COption::GetOptionString("main", "show_panel_for_users"));
			if(!empty($arCodes))
			{
				$userCodes = $USER->GetAccessCodes();
				$diff = array_intersect($arCodes, $userCodes);
				if(!empty($diff))
				{
					$bShowPanel = true;
				}
			}
		}
		//hiding the panel has the higher priority
		if($bShowPanel == true)
		{
			$arCodes = unserialize(COption::GetOptionString("main", "hide_panel_for_users"));
			if(!empty($arCodes))
			{
				if($userCodes == null)
				{
					$userCodes = $USER->GetAccessCodes();
				}
				$diff = array_intersect($arCodes, $userCodes);
				if(!empty($diff))
				{
					$bShowPanel = false;
				}
			}
		}
		return $bShowPanel;
	}

	/**
	 * Checks whether the panel should show
	 * @return bool
	 */
	public static function shouldShowPanel()
	{
		static $bShowPanel = null;
		if ($bShowPanel !== null)
		{
			return $bShowPanel;
		}

		global $USER, $APPLICATION;
		$bShowPanel = false;
		if ($APPLICATION->ShowPanel === false || (!$USER->IsAuthorized() && $APPLICATION->ShowPanel !== true))
		{
			return $bShowPanel;
		}

		CTopPanel::InitPanelIcons();

		foreach ($APPLICATION->arPanelButtons as $arValue)
		{
			if (trim($arValue["HREF"]) <> "" || is_array($arValue["MENU"]) && !empty($arValue["MENU"]))
			{
				$bShowPanel = true;
				break;
			}
		}

		$bShowPanel = self::IsShownForUser($bShowPanel);

		return $bShowPanel;
	}

	public static function InitPanel()
	{
		if (self::shouldShowPanel())
		{
			CTopPanel::ShowPanelScripts();
		}
	}

	public static function AddAttrHint($hint_title, $hint_text = false)
	{
		if (!$hint_text)
			return 'onmouseover="BX.hint(this, \''.htmlspecialcharsbx(CUtil::JSEscape($hint_title)).'\')"';
		else
			return 'onmouseover="BX.hint(this, \''.htmlspecialcharsbx(CUtil::JSEscape($hint_title)).'\', \''.htmlspecialcharsbx(CUtil::JSEscape($hint_text)).'\')"';
	}

	public static function AddConstantHint($element_id, $hint_title, $hint_text, $hint_id = false)
	{
		return '<script type="text/javascript">BX.ready(function() {BX.hint(BX(\''.CUtil::JSEscape($element_id).'\'), \''.CUtil::JSEscape($hint_title).'\', \''.CUtil::JSEscape($hint_text).'\''.($hint_id ? ', \''.CUtil::JSEscape($hint_id).'\'' : '').')});</script>';
	}

	public static function GetPanelHtml()
	{
		global $USER, $APPLICATION, $adminPage;

		if ($APPLICATION->ShowPanel === false || (!$USER->IsAuthorized() && $APPLICATION->ShowPanel !== true))
		{
			return "";
		}

		$bShowPanel = self::shouldShowPanel();
		if ($bShowPanel == false && $APPLICATION->ShowPanel !== true)
		{
			return "";
		}

		$APPLICATION->PanelShowed = true;

		if (
			isset($_GET["back_url_admin"])
			&& $_GET["back_url_admin"] != ""
			&& strpos($_GET["back_url_admin"], "/") === 0
		)
			$_SESSION["BACK_URL_ADMIN"] = $_GET["back_url_admin"];

		$aUserOpt = CUserOptions::GetOption("admin_panel", "settings");
		$aUserOptGlobal = CUserOptions::GetOption("global", "settings");

		$toggleModeSet = false;
		if (isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
		{
			$APPLICATION->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");
			$toggleModeSet = true;
		}

		$params = DeleteParam(array("bitrix_include_areas", "bitrix_show_mode", "back_url_admin"));
		$href = $APPLICATION->GetCurPage();
		$hrefEnc = htmlspecialcharsbx($href);

		$toggleModeDynamic = $aUserOptGlobal['panel_dynamic_mode'] == 'Y';
		$toggleMode = $toggleModeDynamic && !$toggleModeSet
			? $aUserOpt['edit'] == 'on'
			: $APPLICATION->GetShowIncludeAreas() == 'Y';

		//Save if changed
		$old_edit = $aUserOpt['edit'];
		$aUserOpt['edit'] = $toggleMode ? 'on' : 'off';
		if($old_edit !== $aUserOpt['edit'])
			CUserOptions::SetOption('admin_panel', 'settings', $aUserOpt);

		$toggleModeLink = $hrefEnc.'?bitrix_include_areas='.($toggleMode ? 'N' : 'Y').($params<>""? "&amp;".htmlspecialcharsbx($params):"");

		$result = CTopPanel::ShowPanelScripts(true);
		$result .= '
	<!--[if lte IE 7]>
	<style type="text/css">#bx-panel {display:none !important;}</style>
	<div id="bx-panel-error">'.GetMessage("top_panel_browser").'</div><![endif]-->
	<script type="text/javascript">BX.admin.dynamic_mode='.($toggleModeDynamic ? 'true' : 'false').'; BX.admin.dynamic_mode_show_borders = '.($toggleMode ? 'true' : 'false').';</script>
	<div style="display:none; overflow:hidden;" id="bx-panel-back"></div>
	<div id="bx-panel"'.($aUserOpt["collapsed"] == "on" ? ' class="bx-panel-folded"':'').'>
		<div id="bx-panel-top">
			<div id="bx-panel-top-gutter"></div>
			<div id="bx-panel-tabs">
	';
		$result .= '
				<a id="bx-panel-menu" href="" '.CTopPanel::AddAttrHint(GetMessage('top_panel_start_menu_tooltip_title'), GetMessage('top_panel_start_menu_tooltip')).'><span id="bx-panel-menu-icon"></span><span id="bx-panel-menu-text">'.GetMessage("top_panel_menu").'</span></a><a id="bx-panel-view-tab"><span>'.GetMessage("top_panel_site").'</span></a><a id="bx-panel-admin-tab" href="'.(
						isset($_SESSION["BACK_URL_ADMIN"]) && $_SESSION["BACK_URL_ADMIN"] <> ""
						? htmlspecialcharsbx($_SESSION["BACK_URL_ADMIN"]).(strpos($_SESSION["BACK_URL_ADMIN"], "?") !== false? "&amp;":"?")
						: '/bitrix/admin/index.php?lang='.LANGUAGE_ID.'&amp;'
					).'back_url_pub='.urlencode($href.($params<>""? "?".$params:"")).'"><span>'.GetMessage("top_panel_admin").'</span></a>';

		$back_url = CUtil::JSUrlEscape(CUtil::addslashes($href.($params<>""? "?".$params:"")));
		$arStartMenuParams = array(
			'DIV' => 'bx-panel-menu',
			'ACTIVE_CLASS' => 'bx-pressed',
			'MENU_URL' => '/bitrix/admin/get_start_menu.php?lang='.LANGUAGE_ID.'&back_url_pub='.urlencode($back_url).'&'.bitrix_sessid_get(),
			'MENU_PRELOAD' => ($aUserOptGlobal["start_menu_preload"] == 'Y')
		);

		$result .= '<script type="text/javascript">BX.message({MENU_ENABLE_TOOLTIP: '.($aUserOptGlobal['start_menu_title'] <> 'N' ? 'true' : 'false').'}); new BX.COpener('.CUtil::PhpToJsObject($arStartMenuParams).');</script>';

		$hkInstance = CHotKeys::getInstance();

		$Execs = $hkInstance->GetCodeByClassName("top_panel_menu",GetMessage("top_panel_menu"));
		$result .= $hkInstance->PrintJSExecs($Execs);
		$Execs = $hkInstance->GetCodeByClassName("top_panel_admin",GetMessage("top_panel_admin"));
		$result .= $hkInstance->PrintJSExecs($Execs);

		$informerItemsCount = CAdminInformer::InsertMainItems();

		if($informerItemsCount>0)
			$result .= '<a class="adm-header-notif-block" id="adm-header-notif-block" onclick="return BX.adminInformer.Toggle(this);" href="" title="'.GetMessage("top_panel_notif_block_title").'"><span class="adm-header-notif-icon"></span><span id="adm-header-notif-counter" class="adm-header-notif-counter">'.CAdminInformer::$alertCounter.'</span></a>';

		if ($USER->CanDoOperation("cache_control"))
		{
			$result .= '<a id="bx-panel-clear-cache" href="" onclick="BX.clearCache(); return false;"><span id="bx-panel-clear-cache-icon"></span><span id="bx-panel-clear-cache-text">'.GetMessage("top_panel_cache_new_tooltip_title").'</span></a>';
		}

		$result .= '
			</div>
			<div id="bx-panel-userinfo">
	';

		$bCanProfile = $USER->CanDoOperation('view_own_profile') || $USER->CanDoOperation('edit_own_profile');

		$userName = CUser::FormatName(
			CSite::GetNameFormat(false),
			array(
				"NAME"	=> $USER->GetFirstName(),
				"LAST_NAME"	=> $USER->GetLastName(),
				"SECOND_NAME" => $USER->GetSecondName(),
				"LOGIN"		=> $USER->GetLogin()
			),
			$bUseLogin = true,
			$bHTMLSpec = true
		);

		if ($bCanProfile)
		{
			$result .= '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$USER->GetID().'" id="bx-panel-user" '.CTopPanel::AddAttrHint(GetMessage('top_panel_profile_tooltip')).'><span id="bx-panel-user-icon"></span><span id="bx-panel-user-text">'.$userName.'</span></a>';
		}
		else
		{
			$result .= '<a id="bx-panel-user"><span id="bx-panel-user-icon"></span><span id="bx-panel-user-text">'.$userName.'</span></a>';
		}

		$result .= '<a href="'.$hrefEnc.'?logout=yes'.htmlspecialcharsbx(($s=DeleteParam(array("logout"))) == ""? "":"&".$s).'" id="bx-panel-logout" '.CTopPanel::AddAttrHint(GetMessage('top_panel_logout_tooltip').$hkInstance->GetTitle("bx-panel-logout",true)).'>'.GetMessage("top_panel_logout").'</a>';

		$toggleCaptionOn = '<span id="bx-panel-toggle-caption-mode-on">'.GetMessage("top_panel_on").'</span>';
		$toggleCaptionOff = '<span id="bx-panel-toggle-caption-mode-off">'.GetMessage("top_panel_off").'</span>';
		$toggleCaptions = $toggleMode ? $toggleCaptionOn.$toggleCaptionOff : $toggleCaptionOff.$toggleCaptionOn;
		$toogle = '<a href="'.$toggleModeLink.'" id="bx-panel-toggle" class="bx-panel-toggle'.($toggleMode ? '-on' : '-off').'"'.($toggleModeDynamic ? '' : ' '.CTopPanel::AddAttrHint(GetMessage("top_panel_edit_mode_new_tooltip_title"), GetMessage('top_panel_toggle_tooltip').$hkInstance->GetTitle("bx-panel-small-toggle",true))).'><span id="bx-panel-switcher-gutter-left"></span><span id="bx-panel-toggle-indicator"><span id="bx-panel-toggle-icon"></span><span id="bx-panel-toggle-icon-overlay"></span></span><span class="bx-panel-break"></span><span id="bx-panel-toggle-caption">'.GetMessage("top_panel_edit_mode_new").'</span><span class="bx-panel-break"></span><span id="bx-panel-toggle-caption-mode">'.$toggleCaptions.'</span><span id="bx-panel-switcher-gutter-right"></span></a>';
		if ($aUserOpt["collapsed"] == "on")
			$result .= $toogle;

		$result .= '<a href="" id="bx-panel-expander" '.CTopPanel::AddAttrHint(GetMessage("top_panel_expand_tooltip_title"), GetMessage("top_panel_expand_tooltip").$hkInstance->GetTitle("bx-panel-expander",true)).'><span id="bx-panel-expander-text">'.GetMessage("top_panel_expand").'</span><span id="bx-panel-expander-arrow"></span></a>';

		if($hkInstance->IsActive())
		{
			$result .= '<a id="bx-panel-hotkeys" href="javascript:void(0)" onclick="BXHotKeys.ShowSettings();" '.CTopPanel::AddAttrHint(GetMessage("HK_PANEL_TITLE").$hkInstance->GetTitle("bx-panel-hotkeys",true)).'></a>';
		}

		$result .= '<a href="javascript:void(0)" id="bx-panel-pin"'.($aUserOpt['fix'] == 'on' ? ' class="bx-panel-pin-fixed"' : '').' '.CTopPanel::AddAttrHint(GetMessage('top_panel_pin_tooltip')).'></a>';

		$Execs = $hkInstance->GetCodeByClassName("bx-panel-logout",GetMessage('top_panel_logout_tooltip'));
		$result .= $hkInstance->PrintJSExecs($Execs);
		$Execs = $hkInstance->GetCodeByClassName("bx-panel-small-toggle",GetMessage("top_panel_edit_mode_new_tooltip_title"),'location.href="'.$href.'?bitrix_include_areas='.($toggleMode ? 'N' : 'Y').($params<>""? "&".$params:"").'";');
		$result .= $hkInstance->PrintJSExecs($Execs);
		$Execs = $hkInstance->GetCodeByClassName("bx-panel-expander",GetMessage("top_panel_expand_tooltip_title")."/".GetMessage("top_panel_collapse_tooltip_title"));
		$result .= $hkInstance->PrintJSExecs($Execs);

		$result .= '
			</div>
		</div>
	';


		/* BUTTONS */
		$result .= '<div id="bx-panel-site-toolbar"><div id="bx-panel-buttons-gutter"></div><div id="bx-panel-switcher">';

		if ($aUserOpt["collapsed"] != "on")
			$result .= $toogle;

		$result .= '<a href="" id="bx-panel-hider" '.CTopPanel::AddAttrHint(GetMessage("top_panel_collapse_tooltip_title"), GetMessage("top_panel_collapse_tooltip").$hkInstance->GetTitle("bx-panel-expander",true)).'>'.GetMessage("top_panel_collapse").'<span id="bx-panel-hider-arrow"></span></a>';

		$result .= '</div><div id="bx-panel-buttons"><div id="bx-panel-buttons-inner">';

		$main_sort = "";
		$last_btn_type = '';
		$last_btn_small_cnt = 0;
		$groupId = -1;

		$result .= '<span class="bx-panel-button-group" data-group-id="'.++$groupId.'">';

		$arPanelButtons = &$APPLICATION->arPanelButtons;
		sortByColumn($arPanelButtons, array("MAIN_SORT" => SORT_ASC, "SORT" => SORT_ASC));

		foreach($arPanelButtons as $key=>$arButton)
		{
			$result .= $hkInstance->PrintTPButton($arButton);

			if($main_sort != $arButton["MAIN_SORT"] && $main_sort<>"")
			{
				$result .= '</span><span class="bx-panel-button-separator"></span><span class="bx-panel-button-group" data-group-id="'.++$groupId.'">';
				$last_btn_small_cnt = 0;
			}

			if(!isset($arButton['TYPE']) || $arButton['TYPE'] != 'BIG')
				$arButton['TYPE'] =  'SMALL';

			//very old behaviour
			if(is_set($arButton, "SRC_0"))
				$arButton["SRC"] = $arButton["SRC_0"];

			$arButton['HREF'] = isset($arButton['HREF'])? trim($arButton['HREF']): '';
			$bHasAction = $arButton['HREF'] != '';

			if (array_key_exists("RESORT_MENU", $arButton) && $arButton["RESORT_MENU"] === true && is_array($arButton['MENU']) && !empty($arButton['MENU']))
				sortByColumn($arButton['MENU'], "SORT", '', PHP_INT_MAX/*nulls last*/);

			$bHasMenu = is_array($arButton['MENU']) && count($arButton['MENU']) > 0;

			if ($bHasMenu && !$bHasAction)
			{
				foreach ($arButton['MENU'] as $arItem)
				{
					if (isset($arItem['DEFAULT']) && $arItem['DEFAULT'])
					{
						$arButton['HREF'] = $arItem['HREF'];
						$bHasAction = true;
					}
				}
			}

			if ($last_btn_type != '' && $arButton['TYPE'] != $last_btn_type && $main_sort == $arButton["MAIN_SORT"])
			{
				$result .= '</span><span class="bx-panel-button-group" data-group-id="'.++$groupId.'">';
				$last_btn_small_cnt = 0;
			}

			if ($bHasAction && substr(strtolower($arButton['HREF']), 0, 11) == 'javascript:')
			{
				$arButton['ONCLICK'] = substr($arButton['HREF'], 11);
				$arButton['HREF'] = 'javascript:void(0)';
			}

			if ($arButton['HINT'])
			{
				if (isset($arButton['HINT']['ID']) && $arButton['HINT']['ID'])
				{
					$hintOptions = CUtil::GetPopupOptions($arButton['HINT']['ID']);

					if($hintOptions['display'] == 'off')
					{
						unset($arButton['HINT']);
					}
				}

				if ($arButton['HINT'])
					unset($arButton['ALT']);

				if ($bHasMenu && (!isset($arButton['HINT_MENU']) || !$arButton['HINT_MENU']))
					$arButton['HINT']['TARGET'] = 'parent';
			}

			$title = isset($arButton['ALT'])? htmlspecialcharsbx($arButton['ALT']): '';
			$onClick = isset($arButton['ONCLICK'])? htmlspecialcharsbx($arButton['ONCLICK']): '';
			$onClickJs = isset($arButton['ONCLICK'])? CUtil::JSEscape($arButton['ONCLICK']): '';

			$hintMenu = isset($arButton['HINT_MENU'])? CUtil::PhpToJsObject($arButton['HINT_MENU']): '';

			switch ($arButton['TYPE'])
			{
				case 'SMALL':
					if ($last_btn_small_cnt >= 3 && $main_sort == $arButton["MAIN_SORT"])
					{
						$result .= '</span><span class="bx-panel-button-group" data-group-id="'.++$groupId.'">';
						$last_btn_small_cnt = 0;
					}
					elseif ($last_btn_small_cnt > 0)
					{
						$result .= '<span class="bx-panel-break"></span>';
					}

					$result .= '<span class="bx-panel-small-button"><span class="bx-panel-small-button-inner">';

					$button_icon = '<span class="bx-panel-small-button-icon'.($arButton['ICON'] ? ' '.$arButton['ICON'] : '').'"'.(isset($arButton['SRC']) && $arButton['SRC'] ? ' style="background: scroll transparent url('.htmlspecialcharsbx($arButton['SRC']).') no-repeat center center !important;"' : '').'></span>';
					$button_text = '<span class="bx-panel-small-button-text">'.htmlspecialcharsbx($arButton['TEXT']).'</span>';
					$button_text_js = CUtil::JSEscape($arButton['TEXT']);

					if ($bHasAction)
					{
						$result .= '<a href="'.htmlspecialcharsbx($arButton['HREF']).'" onclick="'.$onClick.';BX.removeClass(this.parentNode.parentNode, \'bx-panel-small-button'.($bHasMenu ? '-text' : '').'-active\')" id="bx_topmenu_btn_'.$key.'"'.($title ? ' title="'.$title.$hkInstance->GetTitle("bx_topmenu_btn_".$key).'"' : '').'>'.$button_icon.$button_text.'</a>';

						$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'\', TYPE: \'SMALL\', ACTIVE_CSS: \'bx-panel-small-button'.($bHasMenu ? '-text' : '').'-active\', HOVER_CSS: \'bx-panel-small-button'.($bHasMenu ? '-text' : '').'-hover\''.($arButton['HINT'] ? ', HINT: '.CUtil::PhpToJsObject($arButton['HINT']) : '').', GROUP_ID : '.$groupId.', SKIP : '.($bHasMenu ? "true" : "false").', LINK: "'.CUtil::JSEscape($arButton['HREF']).'", ACTION : "'.$onClickJs.'",TEXT :  "'.$button_text_js.'" })</script>';
						if ($bHasMenu)
						{
							$result .= '<a href="javascript:void(0)" class="bx-panel-small-button-arrow" id="bx_topmenu_btn_'.$key.'_menu"><span class="bx-panel-small-button-arrow"></span></a>';
							$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'_menu\', TYPE: \'SMALL\', MENU: '.CUtil::PhpToJsObject($arButton['MENU']).', ACTIVE_CSS: \'bx-panel-small-button-arrow-active\', HOVER_CSS: \'bx-panel-small-button-arrow-hover\''.($hintMenu ? ', HINT: '.$hintMenu : '').', GROUP_ID : '.$groupId.', TEXT :  "'.$button_text_js.'"})</script>';
						}
					}
					elseif ($bHasMenu)
					{
						$result .= '<a href="javascript:void(0)" id="bx_topmenu_btn_'.$key.'"'.($title ? ' title="'.$title.'"' : '').'>'.$button_icon.$button_text.'<span class="bx-panel-small-single-button-arrow"></span></a>';
						$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'\', TYPE: \'SMALL\', MENU: '.CUtil::PhpToJsObject($arButton['MENU']).', ACTIVE_CSS: \'bx-panel-small-button-active\', HOVER_CSS: \'bx-panel-small-button-hover\''.($arButton['HINT'] ? ', HINT: '.CUtil::PhpToJsObject($arButton['HINT']) : '').', GROUP_ID : '.$groupId.', TEXT :  "'.$button_text_js.'"})</script>';
					}

					$result .= '</span></span>';

					$last_btn_small_cnt++;

				break;

				case 'BIG':
					$last_btn_small_cnt = 0;

					$result .= '<span class="bx-panel-button"><span class="bx-panel-button-inner">';

					$button_icon = '<span class="bx-panel-button-icon'.($arButton['ICON'] ? ' '.$arButton['ICON'] : '').'"'.(isset($arButton['SRC']) && $arButton['SRC'] ? ' style="background: scroll transparent url('.htmlspecialcharsbx($arButton['SRC']).') no-repeat center center !important;"' : '').'></span>';
					$button_text_js = CUtil::JSEscape(str_replace('#BR#', ' ', $arButton['TEXT']));

					if ($bHasAction && $bHasMenu)
					{
						$button_text = '<span class="bx-panel-button-text">'.str_replace('#BR#', '<span class="bx-panel-break"></span>', $arButton['TEXT']).'&nbsp;<span class="bx-panel-button-arrow"></span></span>';
						$result .= '<a href="'.htmlspecialcharsbx($arButton['HREF']).'" onclick="'.$onClick.';BX.removeClass(this.parentNode.parentNode, \'bx-panel-button-icon-active\');" id="bx_topmenu_btn_'.$key.'"'.($title? ' title="'.$title.'"': '').'>'.$button_icon.'</a><a id="bx_topmenu_btn_'.$key.'_menu" href="javascript:void(0)">'.$button_text.'</a>';
						$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'\', TYPE: \'BIG\', ACTIVE_CSS: \'bx-panel-button-icon-active\', HOVER_CSS: \'bx-panel-button-icon-hover\''.($arButton['HINT'] ? ', HINT: '.CUtil::PhpToJsObject($arButton['HINT']) : '').', GROUP_ID : '.$groupId.', SKIP : true }); BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'_menu\', TYPE: \'BIG\', MENU: '.CUtil::PhpToJsObject($arButton['MENU']).', ACTIVE_CSS: \'bx-panel-button-text-active\', HOVER_CSS: \'bx-panel-button-text-hover\''.($hintMenu ? ', HINT: '.$hintMenu : '').', GROUP_ID : '.$groupId.', TEXT :  "'.$button_text_js.'"})</script>';
					}
					else if ($bHasAction)
					{
						$button_text = '<span class="bx-panel-button-text">'.str_replace('#BR#', '<span class="bx-panel-break"></span>', $arButton['TEXT']).'</span>';
						$result .= '<a href="'.htmlspecialcharsbx($arButton['HREF']).'" onclick="'.$onClick.';BX.removeClass(this.parentNode.parentNode, \'bx-panel-button-active\');" id="bx_topmenu_btn_'.$key.'"'.($title ? ' title="'.$title.'"' : '').'>'.$button_icon.$button_text.'</a>';
						$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'\', TYPE: \'BIG\', ACTIVE_CSS: \'bx-panel-button-active\', HOVER_CSS: \'bx-panel-button-hover\''.($arButton['HINT'] ? ', HINT: '.CUtil::PhpToJsObject($arButton['HINT']) : '').', GROUP_ID : '.$groupId.', LINK: "'.CUtil::JSEscape($arButton['HREF']).'", ACTION : "'.$onClickJs.'", TEXT :  "'.$button_text_js.'"});</script>';
					}
					else // if $bHasMenu
					{
						$button_text = '<span class="bx-panel-button-text">'.str_replace('#BR#', '<span class="bx-panel-break"></span>', $arButton['TEXT']).'&nbsp;<span class="bx-panel-button-arrow"></span></span>';
						$result .= '<a href="javascript:void(0)" id="bx_topmenu_btn_'.$key.'_menu">'.$button_icon.$button_text.'</a>';
						$result .= '<script type="text/javascript">BX.admin.panel.RegisterButton({ID: \'bx_topmenu_btn_'.$key.'_menu\', TYPE: \'BIG\', MENU: '.CUtil::PhpToJsObject($arButton['MENU']).', ACTIVE_CSS: \'bx-panel-button-active\', HOVER_CSS: \'bx-panel-button-hover\''.($arButton['HINT'] ? ', HINT: '.CUtil::PhpToJsObject($arButton['HINT']) : '').', GROUP_ID : '.$groupId.', TEXT :  "'.$button_text_js.'"});</script>';
					}

					$result .= '</span></span>';
				break;
			}

			$main_sort = $arButton["MAIN_SORT"];
			$last_btn_type = $arButton['TYPE'];
		}
		$result .= '</span>';

		$result .= '</div>
			</div>
		</div>';

		if ($USER->IsAdmin())
			$result .= CAdminNotify::GetHtml();

		$result .= '
	</div>
	';

		$result .= '<script type="text/javascript">
		BX.admin.panel.state = {
			fixed: '.($aUserOpt["fix"] == "on" ? 'true' : 'false').',
			collapsed: '.($aUserOpt["collapsed"] == "on" ? 'true' : 'false').'
		};
		BX.admin.moreButton.init({ buttonTitle : "'.GetMessageJS("top_panel_more_button_title").'"});
		</script>';

		//start menu preload
		// if($aUserOptGlobal["start_menu_preload"] == 'Y')
		// 	$result .= '<script type="text/javascript">BX.ready(function(){jsStartMenu.PreloadMenu(\''.CUtil::JSEscape($href.($params<>""? "?".$params:"")).'\');});</script>';

		//show script to play sound
		$result .= $adminPage->ShowSound();

		return $result;
	}
}
