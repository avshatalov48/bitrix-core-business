<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arToolbar = array();
if($arResult["SECTION_ID"])
{
	$arToolbar[] = array(
		"TEXT"=>GetMessage("CT_BLS_TOOLBAR_UP"),
		"TITLE"=>GetMessage("CT_BLS_TOOLBAR_UP_TITLE"),
		"LINK"=>$arResult["LIST_PARENT_URL"],
		"ICON"=>"btn-parent-section",
	);
}

if($arResult["CAN_ADD_SECTION"])
{
	$arToolbar[] = array(
		"TEXT"=>$arResult["IBLOCK"]["SECTION_ADD"],
		"TITLE"=>GetMessage("CT_BLS_TOOLBAR_ADD_SECTION_TITLE"),
		"LINK"=>"javascript:addNewSection('form_section_add', '".CUtil::JSEscape(GetMessage("CT_BLS_NEW_SECTION_NAME"))."');",
		"ICON"=>"btn-add-section",
	);
}

$arToolbar[] = array(
	"SEPARATOR"=>"Y",
);
$arToolbar[] = array(
	"TEXT"=>$arResult["IBLOCK"]["ELEMENTS_NAME"],
	"TITLE"=>GetMessage("CT_BLS_TOOLBAR_ELEMENTS_TITLE"),
	"LINK"=>$arResult["LIST_URL"],
	"ICON"=>"btn-view-elements",
);

?>

<form name="form_section_add" id="form_section_add" action="<?echo POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
	<?=bitrix_sessid_post();?>
	<input type="hidden" id="new_section_name" name="new_section_name" value="">
	<input type="hidden" id="old_section_id" name="old_section_id" value="">
	<input type="hidden" id="form_section_action" name="form_section_action" value="">
</form>

<?
if(count($arToolbar))
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS"=>$arToolbar,
		),
		$component, array("HIDE_ICONS" => "Y")
	);
}

if($arResult["IBLOCK_PERM"] >= "W")
{
	$arActions = array("delete"=>true);
	$bEditable = true;
}
else
{
	$arActions = false;
	$bEditable = false;
	$found = false;
	foreach ($arResult["SECTIONS_ROWS"] as $i => $aRow)
	{
		if ($aRow["canDelete"])
		{
			$arResult["SECTIONS_ROWS"][$i]["columns"]["NAME"] .= '<div style="display:none"><input type="checkbox" name="ID[]" id="ID_'.$aRow["id"].'" value="'.$aRow["id"].'"></div>';
			if (!$found)
			{
				$found = true;
				$arResult["SECTIONS_ROWS"][$i]["columns"]["NAME"] .= '<input type="hidden" value="" name="action_button_'.$arResult["GRID_ID"].'">';
			}
		}
	}
}

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID"=>$arResult["GRID_ID"],
		"HEADERS"=>array(
			array("id"=>"NAME", "name"=>GetMessage("CT_BLS_SECTION_NAME"), "default"=>true, "editable"=>$bEditable),
		),
		"ROWS"=>$arResult["SECTIONS_ROWS"],
		"ACTIONS"=>$arActions,
		"NAV_OBJECT"=>$arResult["NAV_OBJECT"],
	),
	$component, array("HIDE_ICONS" => "Y")
);

?>