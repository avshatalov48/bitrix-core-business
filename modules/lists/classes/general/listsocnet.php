<?
IncludeModuleLangFile(__FILE__);

class CListsSocnet
{
	function OnFillSocNetFeaturesList(&$arSocNetFeaturesSettings)
	{
		$arSocNetFeaturesSettings["group_lists"] = array(
			"allowed" 		=> array(SONET_ENTITY_GROUP),
			"operations"	=> array(
				"write"	=> array(SONET_ENTITY_GROUP => SONET_ROLES_MODERATOR),
				"view" 	=> array(SONET_ENTITY_GROUP => SONET_ROLES_USER),
			),
			"minoperation"	=> array("view"),
/*
waiting for integration lists into events_user_view

			"subscribe_events"	=> array(
				"lists" =>  array(
					"ENTITIES"	=>	array(
						SONET_SUBSCRIBE_ENTITY_GROUP => array(
							"TITLE" 			=> GetMessage("LISTS_SOCNET_LOG_GROUP"),
							"TITLE_SETTINGS"	=> GetMessage("LISTS_SOCNET_LOG_GROUP_SETTINGS"),
						),
					),
					"OPERATION"		=> "view",
					"CLASS_FORMAT"	=> "CListsSocnet",
					"METHOD_FORMAT"	=> "FormatEvent_Lists",
					"HAS_CB"		=> "Y"
				),
				"lists_del" =>  array(
					"ENTITIES"	=>	array(
						SONET_SUBSCRIBE_ENTITY_GROUP => array(
							"TITLE" 			=> GetMessage("LISTS_DEL_SOCNET_LOG_GROUP"),
						),
					),
					"OPERATION"		=> "view",
					"CLASS_FORMAT"	=> "CListsSocnet",
					"METHOD_FORMAT"	=> "FormatEvent_Lists",
					"HIDDEN"		=> true,
					"HAS_CB"		=> "Y"
				)
			),
*/
			"title" 					=> GetMessage("LISTS_SOCNET_TAB"),
			"hide_operations_settings"	=> true,
		);
	}

	function OnFillSocNetMenu(&$arResult, $arParams = array())
	{
		global $USER;

		$arResult["AllowSettings"]["group_lists"] = true;

		$arResult["CanView"]["group_lists"] = ((array_key_exists("ActiveFeatures", $arResult) ? array_key_exists("group_lists", $arResult["ActiveFeatures"]) : true) && CSocNetFeaturesPerms::CanPerformOperation(
			$USER->GetID(),
			$arParams["ENTITY_TYPE"],
			$arParams["ENTITY_ID"], "group_lists", "view",
			CSocNetUser::IsCurrentUserModuleAdmin()
		));

		$arResult["Title"]["group_lists"] = (array_key_exists("ActiveFeatures", $arResult) && array_key_exists("group_lists", $arResult["ActiveFeatures"]) && strlen($arResult["ActiveFeatures"]["group_lists"]) > 0 ? $arResult["ActiveFeatures"]["group_lists"] : GetMessage("LISTS_SOCNET_TAB"));

		if (!array_key_exists("SEF_MODE", $arResult) || $arResult["SEF_MODE"] != "N")
		{
			if(isset($arResult["Urls"]))
				$arResult["Urls"]["group_lists"] = $arResult["Urls"]["view"]."lists/";
		}
		else
		{
			if (!array_key_exists("PAGE_VAR", $arResult))
				$arResult["PAGE_VAR"] = "page";

			if (!array_key_exists("GROUP_VAR", $arResult))
				$arResult["GROUP_VAR"] = "group_id";

			$arResult["Urls"]["group_lists"] = "?".$arResult["PAGE_VAR"]."=group_lists&".$arResult["GROUP_VAR"]."=".$arResult["Group"]["ID"];
		}
	}

	function OnParseSocNetComponentPath(&$arUrlTemplates, &$arCustomPagesPath, $arParams)
	{
		if ($arParams["SEF_MODE"] == "N")
		{
			$arMyUrlTemplates = array(
				"group_lists" => "page=group_lists&group_id=#group_id#",
				"group_list_view" => "page=group_list_view&group_id=#group_id#&list_id=#list_id#&section_id=#section_id#",
				"group_list_sections" => "page=group_list_sections&group_id=#group_id#&list_id=#list_id#&section_id=#section_id#",
				"group_list_edit" => "page=group_list_edit&group_id=#group_id#&list_id=#list_id#",
				"group_list_fields" => "page=group_list_fields&group_id=#group_id#&list_id=#list_id#",
				"group_list_field_edit" => "page=group_list_field_edit&group_id=#group_id#&list_id=#list_id#&field_id=#field_id#",
				"group_list_element_edit" => "page=group_list_element_edit&group_id=#group_id#&list_id=#list_id#&section_id=#section_id#&element_id=#element_id#",
				"group_list_file" => "page=group_list_file&group_id=#group_id#&list_id=#list_id#&section_id=#section_id#&element_id=#element_id#&field_id=#field_id#&file_id=#file_id#",
				"group_bizproc_log" => "page=group_bizproc_log&group_id=#group_id#&list_id=#list_id#&document_state_id=#document_state_id#",
				"group_bizproc_workflow_start" => "page=group_bizproc_workflow_start&group_id=#group_id#&list_id=#list_id#&element_id=#element_id#",
				"group_bizproc_task" => "page=group_bizproc_task&group_id=#group_id#&list_id=#list_id#&section_id=#section_id#&element_id=#element_id#&task_id=#task_id#",
				"group_bizproc_workflow_admin" => "page=group_bizproc_workflow_admin&group_id=#group_id#&list_id=#list_id#",
				"group_bizproc_workflow_edit" => "page=group_bizproc_workflow_admin&group_id=#group_id#&list_id=#list_id#&ID=#ID#",
				"group_list_export_excel" => "page=group_list_export_excel&group_id=#group_id#&list_id=#list_id#",
			);
		}
		else
		{
			$arMyUrlTemplates = array(
				"group_lists" => "group/#group_id#/lists/",
				"group_list_view" => "group/#group_id#/lists/#list_id#/view/#section_id#/",
				"group_list_sections" => "group/#group_id#/lists/#list_id#/edit/#section_id#/",
				"group_list_edit" => "group/#group_id#/lists/#list_id#/edit/",
				"group_list_fields" => "group/#group_id#/lists/#list_id#/fields/",
				"group_list_field_edit" => "group/#group_id#/lists/#list_id#/field/#field_id#/",
				"group_list_element_edit" => "group/#group_id#/lists/#list_id#/element/#section_id#/#element_id#/",
				"group_list_file" => "group/#group_id#/lists/#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/",
				"group_bizproc_log" => "group/#group_id#/lists/#list_id#/bp_log/#document_state_id#/",
				"group_bizproc_workflow_start" => "group/#group_id#/lists/#list_id#/bp_start/#element_id#/",
				"group_bizproc_task" => "group/#group_id#/lists/#list_id#/bp_task/#section_id#/#element_id#/#task_id#/",
				"group_bizproc_workflow_admin" => "group/#group_id#/lists/#list_id#/bp_admin/",
				"group_bizproc_workflow_edit" => "group/#group_id#/lists/#list_id#/bp_edit/#ID#/",
				"group_list_export_excel" => "group/#group_id#/lists/#list_id#/excel/",
			);
		}

		static $base_path = false;
		if(!$base_path)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/lists/".SITE_ID."/group_lists.php"))
				$base_path = "/bitrix/php_interface/lists/".SITE_ID."/";
			elseif(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/lists/group_lists.php"))
				$base_path = "/bitrix/php_interface/lists/";
			else
				$base_path = "/bitrix/modules/lists/socnet/";
		}

		foreach($arMyUrlTemplates as $page => $url)
		{
			$arUrlTemplates[$page] = $url;
			$arCustomPagesPath[$page] = $base_path;
		}

		Bitrix\Main\UrlPreview\Router::setRouteHandler(
				$arParams['SEF_FOLDER'].$arUrlTemplates['group_list_element_edit'],
				'lists',
				'\Bitrix\Lists\Preview\Element',
				array(
						'listId' => '$list_id',
						'sectionId' => '$section_id',
						'elementId' => '$element_id',
						'IBLOCK_TYPE_ID' => COption::GetOptionString("lists", "socnet_iblock_type_id")
				)
		);
	}

	function OnInitSocNetComponentVariables(&$arVariableAliases, &$arCustomPagesPath)
	{
		$arVariableAliases['list_id'] = 'list_id';
		$arVariableAliases['section_id'] = 'section_id';
		$arVariableAliases['element_id'] = 'element_id';
		$arVariableAliases['field_id'] = 'field_id';
		$arVariableAliases['task_id'] = 'task_id';
		$arVariableAliases['file_id'] = 'file_id';
		$arVariableAliases['document_state_id'] = 'document_state_id';
		$arVariableAliases['mode'] = 'mode';
		$arVariableAliases['ID'] = 'ID';
	}
}
?>