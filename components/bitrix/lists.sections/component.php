<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLS_MODULE_NOT_INSTALLED"));
	return;
}

if (
	intval($arParams["~SOCNET_GROUP_ID"]) > 0
	&& CModule::IncludeModule("socialnetwork")
)
{
	$arSonetGroup = CSocNetGroup::GetByID(intval($arParams["~SOCNET_GROUP_ID"]));
	if (
		is_array($arSonetGroup)
		&& $arSonetGroup["CLOSED"] == "Y"
		&& !CSocNetUser::IsCurrentUserModuleAdmin()
		&& (
			$arSonetGroup["OWNER_ID"] != $GLOBALS["USER"]->GetID()
			|| COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y"
		)
	)
	{
		$arResult["IS_SOCNET_GROUP_CLOSED"] = true;
	}
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	intval($arParams["~IBLOCK_ID"]),
	$arParams["~SOCNET_GROUP_ID"]
);

if($lists_perm < 0)
{
	switch($lists_perm)
	{
		case CListPermissions::WRONG_IBLOCK_TYPE:
			ShowError(GetMessage("CC_BLS_WRONG_IBLOCK_TYPE"));
			return;
		case CListPermissions::WRONG_IBLOCK:
			ShowError(GetMessage("CC_BLS_WRONG_IBLOCK"));
			return;
		case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
			ShowError(GetMessage("CC_BLS_LISTS_FOR_SONET_GROUP_DISABLED"));
			return;
		default:
			ShowError(GetMessage("CC_BLS_UNKNOWN_ERROR"));
			return;
	}
}
elseif(
	$lists_perm < CListPermissions::CAN_WRITE
	&& !CIBlockSectionRights::UserHasRightTo(intval($arParams["~IBLOCK_ID"]), intval($arParams["~SECTION_ID"]), "section_read")
)
{
	ShowError(GetMessage("CC_BLS_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = $lists_perm >= CListPermissions::IS_ADMIN;
$arResult["CAN_ADD_SECTION"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		($lists_perm >= CListPermissions::CAN_WRITE) 
		|| CIBlockSectionRights::UserHasRightTo(intval($arParams["~IBLOCK_ID"]), intval($arParams["~SECTION_ID"]), "section_section_bind")
	)
);
$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
$arResult["IBLOCK_ID"] = $arIBlock["ID"];

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_list_sections";

$section_id = intval($arParams["~SECTION_ID"]);
$arSection = false;
if($section_id)
{
	$rsSection = CIBlockSection::GetList(array(), array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"ID" => $section_id,
		"GLOBAL_ACTIVE"=>"Y",
		"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::IS_ADMIN? "N": "Y"),
	));
	$arSection = $rsSection->GetNext();
}
$arResult["SECTION"] = $arSection;
if($arResult["SECTION"])
{
	$arResult["SECTION_ID"] = $arResult["SECTION"]["ID"];
	$arResult["PARENT_SECTION_ID"] = $arResult["SECTION"]["IBLOCK_SECTION_ID"];
	$arResult["SECTION_PATH"] = array();
	$rsPath = CIBlockSection::GetNavChain($arResult["IBLOCK_ID"], $arResult["SECTION_ID"]);
	while($arPath = $rsPath->Fetch())
	{
		$arResult["SECTION_PATH"][] = array(
			"NAME" => htmlspecialcharsex($arPath["NAME"]),
			"URL" => str_replace(
				array("#list_id#", "#section_id#", "#group_id#"),
				array($arResult["IBLOCK_ID"], intval($arPath["ID"]), $arParams["SOCNET_GROUP_ID"]),
				$arParams["LIST_SECTIONS_URL"]
			),
		);
	}
}
else
{
	$arResult["SECTION_ID"] = false;
	$arResult["PARENT_SECTION_ID"] = false;
}

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
if(intval($arResult["SECTION_ID"]) <= 0)
	$arResult["~LIST_URL"] = CHTTP::urlAddParams($arResult["~LIST_URL"], array("list_section_id" => ""));
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_SECTION_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
);
$arResult["LIST_SECTION_URL"] = htmlspecialcharsbx($arResult["~LIST_SECTION_URL"]);

$arResult["~LIST_PARENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["PARENT_SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
);
$arResult["LIST_PARENT_URL"] = htmlspecialcharsbx($arResult["~LIST_PARENT_URL"]);

$obList = new CList($arIBlock["ID"]);

//Form submitted
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& (
		isset($_POST["action_button_".$arResult["GRID_ID"]])
		|| isset($_POST["new_section_name"])
	)
)
{
	$obSection = new CIBlockSection;
	$new_section_name = trim($_POST["new_section_name"], " \n\r\t");
	$old_section_id = intval($_POST["old_section_id"]);

	if(
		isset($_POST["form_section_action"])
		&& $_POST["form_section_action"] == "rename"
		&& !$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& (
			(
				$lists_perm >= CListPermissions::CAN_WRITE
			) || (
				$old_section_id > 0
				&& CIBlockSectionRights::UserHasRightTo($arIBlock["ID"], $section_id, "section_edit")
			)
		)
	)
	{
		if($new_section_name && $old_section_id > 0)
		{
			//Check if section belongs proper iblock
			$rsSection = CIBlockSection::GetList(array(), array(
				"IBLOCK_ID" => $arResult["IBLOCK_ID"],
				"ID" => $old_section_id,
				"GLOBAL_ACTIVE"=>"Y",
				"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::CAN_WRITE? "N": "Y"), //This cancels iblock permissions for trusted users
			));
			if($rsSection->GetNext())
			{
				$arFields = array(
					"IBLOCK_ID" => $arResult["IBLOCK_ID"],
					"NAME" => $new_section_name,
				);
				$obSection->Update($old_section_id, $arFields);
			}
		}
	}

	if(
		isset($_POST["form_section_action"])
		&& $_POST["form_section_action"] == "add"
		&& !$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& (
			(
				$lists_perm >= CListPermissions::CAN_WRITE
			) || (
				$old_section_id == 0
				&& CIBlockSectionRights::UserHasRightTo($arIBlock["ID"], $arResult["SECTION_ID"], "section_section_bind")
			)
		)
	)
	{
		$new_section_name = trim($_POST["new_section_name"], " \n\r\t");
		if($new_section_name)
		{
			$arFields = array(
				"IBLOCK_ID" => $arResult["IBLOCK_ID"],
				"NAME" => $new_section_name,
				"IBLOCK_SECTION_ID" => $arResult["SECTION_ID"],
				"CHECK_PERMISSIONS" => "N",
			);
			$obSection->Add($arFields);
		}
	}
	elseif(
		$_POST["action_button_".$arResult["GRID_ID"]] == "delete"
		&& isset($_POST["ID"])
		&& is_array($_POST["ID"])
		&& !$arResult["IS_SOCNET_GROUP_CLOSED"]
	)
	{
		foreach($_POST["ID"] as $ID)
		{
			if(
				$lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::UserHasRightTo($arIBlock["ID"], $ID, "section_delete")
			)
				$obSection->Delete($ID, false);
		}
	}
	elseif(
		$_POST["action_button_".$arResult["GRID_ID"]] == "edit"
		&& isset($_POST["FIELDS"])
		&& is_array($_POST["FIELDS"])
		&& !$arResult["IS_SOCNET_GROUP_CLOSED"]
	)
	{
		foreach($_POST["FIELDS"] as $ID => $arField)
		{
			if(
				$lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::UserHasRightTo($arIBlock["ID"], $ID, "section_edit")
			)
			{
				$arFields = array(
					"NAME" => $arField["NAME"],
					"CHECK_PERMISSIONS" => "N",
				);
				$obSection->Update($ID, $arFields);
			}
		}
	}

	if(!isset($_POST["AJAX_CALL"]))
		LocalRedirect($arResult["LIST_SECTION_URL"]);
}

$grid_options = new CGridOptions($arResult["GRID_ID"]);

$rsSections = CIBlockSection::GetList(
	array("left_margin" => "asc"), array(
	"IBLOCK_ID" => $arResult["IBLOCK_ID"],
	"GLOBAL_ACTIVE" => "Y",
	"SECTION_ID" => $arResult["SECTION_ID"],
	"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::IS_ADMIN? "N": "Y"), //This cancels iblock permissions for trusted users
));
$rsSections->NavStart($grid_options->GetNavParams(), false);

$arResult["SECTIONS_ROWS"] = array();
while($data = $rsSections->GetNext())
{
	$aCols = array(
		"NAME" => '<a href="'.str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($data["IBLOCK_ID"], $data["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams['LIST_SECTIONS_URL']
		).'">'.$data["NAME"].'</a>',
	);

	$aActions = array();
	if(
		!$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& (
			$lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockSectionRights::UserHasRightTo($data["IBLOCK_ID"], $data["ID"], "section_edit")
		)
	)
		$aActions[] = array(
			"ICONCLASS" => "edit",
			"TEXT" => GetMessage("CC_BLS_SECTION_ACTION_MENU_RENAME"),
			"ONCLICK" =>"renameSection('form_section_add', '".CUtil::JSEscape(GetMessage("CC_BLS_NEW_SECTION_NAME_PROMPT"))."', ".$data["ID"].", '".CUtil::JSEscape($data["NAME"])."');",
			"DEFAULT" => true,
		);

	if(
		!$arResult["IS_SOCNET_GROUP_CLOSED"]
		&& (
			$lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockSectionRights::UserHasRightTo($data["IBLOCK_ID"], $data["ID"], "section_delete")
		)
	)
	{
		$aActions[] = array(
			"ICONCLASS" => "delete",
			"TEXT" => GetMessage("CC_BLS_SECTION_ACTION_MENU_DELETE"),
			"ONCLICK" => "bxGrid_".$arResult["GRID_ID"].".DeleteItem('".$data["ID"]."', '".GetMessage("CC_BLS_SECTION_DELETE_PROPMT")."')",
		);
		$canDelete = true;
	}
	else
	{
		$canDelete = false;
	}

	$arResult["SECTIONS_ROWS"][] = array(
		"id" => $data["ID"],
		"data" => $data,
		"actions" => $aActions,
		"columns" => $aCols,
		"canDelete" => $canDelete,
	);
}

$rsSections->bShowAll = false;
$arResult["NAV_OBJECT"] = $rsSections;

if(defined("BX_AJAX_PARAM_ID"))
	$return_url = $APPLICATION->GetCurPageParam("", array(BX_AJAX_PARAM_ID));
else
	$return_url = $APPLICATION->GetCurPageParam();

$this->IncludeComponentTemplate();

$APPLICATION->SetTitle(GetMessage("CC_BLS_PAGE_TITLE", array("#NAME#" => $arResult["IBLOCK"]["NAME"])));

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => "")));

$APPLICATION->AddChainItem(GetMessage("CC_BLS_CHAIN_TITLE", array("#NAME#" => $arResult["IBLOCK"]["NAME"])), str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
));
if($arResult["SECTION"])
{
	foreach($arResult["SECTION_PATH"] as $arPath)
	{
		$APPLICATION->AddChainItem($arPath["NAME"], $arPath["URL"]);
	}
}

?>