<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("form")>"D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "form",
		"sort" => 100,
		"text" => GetMessage("FORM_MENU_MAIN"),
		"title" => GetMessage("FORM_MENU_MAIN_TITLE"),
		"icon" => "form_menu_icon",
		"page_icon" => "form_page_icon",
		"module_id" => "form",
		"items_id" => "menu_webforms",
		"items" => array(),
	);

	$arFormsList = array();
	if (method_exists($this, "IsSectionActive") && $this->IsSectionActive("menu_webforms_list") || defined('BX_ADMIN_FORM_MENU_OPEN') && BX_ADMIN_FORM_MENU_OPEN == 1)
	{
		CModule::IncludeModule('form');

		$z = CForm::GetMenuList(array("LID"=>LANGUAGE_ID));
		while ($zr=$z->GetNext())
		{
			if (strlen($zr["MENU"])>0)
			{
				$alt = str_replace("#NAME#",$zr["NAME"],htmlspecialcharsbx(GetMessage("FORM_RESULTS_ALT")));
				$arFormsList[] = array(
					"text" => $zr["MENU"],
					"url" => "form_result_list.php?lang=".LANGUAGE_ID."&amp;WEB_FORM_ID=".$zr["ID"],
					"page_icon" => "form_page_icon",
					"more_url" => array(
						"form_result_list.php?WEB_FORM_ID=".$zr["ID"],
						"form_result_edit.php?WEB_FORM_ID=".$zr["ID"],
						"form_result_print.php?WEB_FORM_ID=".$zr["ID"],
						"form_result_view.php?WEB_FORM_ID=".$zr["ID"]
					),
					"title" => $alt
				 );
			}
		}
	}

	$aMenu["items"][] = array(
		"text" => GetMessage("FORM_RESULTS_ALL"),
		"dynamic" => true,
		"module_id" => "form",
		"title" => GetMessage("FORM_RESULTS_ALL_ALT"),
		"items_id" => "menu_webforms_list",
		"items" => $arFormsList,
		"more_url" => array(
			"form_result_list.php",
			"form_result_edit.php",
		)
	);

	$aMenu["items"][] = array(
		"text" => GetMessage("FORM_MENU_FORMS"),
		"url" => "form_list.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"form_edit.php",
			"form_field_list.php",
			"form_field_edit.php",
			"form_field_edit_simple.php",
			"form_status_list.php",
			"form_status_edit.php",
		),
		"title" => GetMessage("FORM_MENU_FORMS_ALT")
	);

	return $aMenu;
}
return false;
?>
