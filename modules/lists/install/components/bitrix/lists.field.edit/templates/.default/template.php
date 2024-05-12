<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

CJSCore::Init(array('lists', 'translit'));
$jsClass = 'ListsFieldEditClass_'.$arResult['RAND_STRING'];
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
$generateCode = false;
if(!$arResult["FIELD_ID"] && $arResult["IS_PROPERTY"])
	$generateCode = true;

$listAction = array();
if($arResult["FIELD_ID"] && $arResult["FIELD_ID"] != "NAME")
{
	$listAction[] = array(
		"id" => "deleteField",
		"text" => GetMessage("CT_BLFE_TOOLBAR_DELETE"),
		"action" => "BX.Lists['".$jsClass."'].deleteField('form_".$arResult["FORM_ID"]."',
			'".GetMessage("CT_BLFE_TOOLBAR_DELETE_WARNING")."')"
	);
}

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
	$pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<a href="<?=$arResult["LIST_FIELDS_URL"]?>" class="ui-btn ui-btn-sm ui-btn-link ui-btn-themes lists-list-back">
		<?=GetMessage("CT_BLFE_TOOLBAR_RETURN_LIST_ELEMENT")?>
	</a>
	<?if($listAction):?>
		<span id="lists-title-action" class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-dropdown ui-btn-themes">
			<?=GetMessage("CT_BLFE_TOOLBAR_ACTION")?>
		</span>
	<?endif;?>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$customHtml = "";

$arTab1Fields = array(
	array(
		"id"=>"NAME",
		"name"=>GetMessage("CT_BLFE_FIELD_NAME"),
		"required"=>true,
		"params" => array("id" => 'bx-lists-field-name')
	)
);

if($arResult["FIELD_ID"] == "NAME" && $arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$arTab1Fields[] = array(
		"id"=>"DEFAULT_VALUE",
		"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
		"type"=>"text",
		"value"=>$arResult["FORM_DATA"]["DEFAULT_VALUE"]
	);
}

if($arResult["IS_READ_ONLY"])
	$arTab1Fields[] = array(
		"id"=>"IS_REQUIRED",
		"name"=>GetMessage("CT_BLFE_FIELD_IS_REQUIRED"),
		"type"=>"custom",
		"value"=>'<input type="hidden" name="IS_REQUIRED" value="N">'.GetMessage("MAIN_NO"),
	);
elseif($arResult["CAN_BE_OPTIONAL"])
	$arTab1Fields[] = array(
		"id"=>"IS_REQUIRED",
		"name"=>GetMessage("CT_BLFE_FIELD_IS_REQUIRED"),
		"type"=>"checkbox",
	);
else
	$arTab1Fields[] = array(
		"id"=>"IS_REQUIRED",
		"name"=>GetMessage("CT_BLFE_FIELD_IS_REQUIRED"),
		"type"=>"custom",
		"value"=>'<input type="hidden" name="IS_REQUIRED" value="Y">'.GetMessage("MAIN_YES"),
	);

if($arResult["IS_MULTIPLE_ONLY"])
{
	$arTab1Fields[] = array(
		"id"=>"MULTIPLE",
		"name"=>GetMessage("CT_BLFE_FIELD_MULTIPLE"),
		"type"=>"custom",
		"value"=>'<input type="hidden" name="MULTIPLE" value="Y">'.GetMessage("MAIN_YES"),
	);
}
else
{
	if($arResult["CAN_BE_MULTIPLE"])
	{
		$arTab1Fields[] = array(
			"id"=>"MULTIPLE",
			"name"=>GetMessage("CT_BLFE_FIELD_MULTIPLE"),
			"type"=>"checkbox",
		);
	}
	else
	{
		$arTab1Fields[] = array(
			"id"=>"MULTIPLE",
			"name"=>GetMessage("CT_BLFE_FIELD_MULTIPLE"),
			"type"=>"label",
			"value"=>GetMessage("MAIN_NO"),
		);
	}
}

if ($arResult["FIELD_ID"])
{
	$arTab1Fields[] = array(
		"id"=>"TYPE",
		"name"=>GetMessage("CT_BLFE_FIELD_TYPE"),
		"type"=>"label",
		"value"=>$arResult["TYPES"][$arResult["FIELD"]["TYPE"]],
	);
	$customHtml .= '<input type="hidden" name="TYPE" value="'.$arResult["FIELD"]["TYPE"].'">';
}
else
{
	$arTab1Fields[] = array(
		"id"=>"TYPE",
		"name"=>GetMessage("CT_BLFE_FIELD_TYPE"),
		"type"=>"list",
		"items"=>$arResult["TYPES"],
		"params"=>array(
			"OnChange"=>"javascript:BX.Lists['".$jsClass."'].changeType('form_".$arResult["FORM_ID"]."')",
		),
	);
}

$arUserType = $arResult["FIELD"] ? $arResult["FIELD"]["PROPERTY_USER_TYPE"] : null;
$arPropertyFields = array();
$USER_TYPE_SETTINGS_HTML = "";
if(is_array($arUserType))
{
	if(array_key_exists("GetSettingsHTML", $arUserType))
	{
		$USER_TYPE_SETTINGS_HTML = call_user_func_array($arUserType["GetSettingsHTML"],
			array(
				$arResult["FIELD"],
				array(
					"NAME"=>"USER_TYPE_SETTINGS",
				),
				&$arPropertyFields,
			));
	}
}

$readOnlyAdd = true;
$readOnlyEdit = true;
$showAddForm = true;
if($arResult["IS_READ_ONLY"])
{
	$readOnlyAdd = false;
	$readOnlyEdit = false;
	$showAddForm = false;
}
elseif($arResult["FORM_DATA"]["TYPE"] == "NAME")
{
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE",
		"name" => GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
	);

	$readOnlyAdd = false;
}
elseif($arResult["FORM_DATA"]["TYPE"] == "SORT")
{
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE",
		"name" => GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
	);
}
elseif($arResult["FORM_DATA"]["TYPE"] == "ACTIVE_FROM")
{
	$arTab1Fields[] = array(
		"id"=>"DEFAULT_VALUE",
		"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
		"type"=>"list",
		"items" => array(
			"" => GetMessage("CT_BLFE_FIELD_ACTIVE_FROM_EMPTY"),
			"=now" => GetMessage("CT_BLFE_FIELD_ACTIVE_FROM_NOW"),
			"=today" => GetMessage("CT_BLFE_FIELD_ACTIVE_FROM_TODAY"),
		),
	);
}
elseif($arResult["FORM_DATA"]["TYPE"] == "ACTIVE_TO")
{
	//TODO
	$readOnlyAdd = false;
}
elseif($arResult["FORM_DATA"]["TYPE"] == "PREVIEW_PICTURE")
{
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[FROM_DETAIL]",
		"name" => GetMessage("CT_BLFE_FIELD_PREVIEW_PICTURE_FROM_DETAIL"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["FROM_DETAIL"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["FROM_DETAIL"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[DELETE_WITH_DETAIL]",
		"name" => GetMessage("CT_BLFE_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[UPDATE_WITH_DETAIL]",
		"name" => GetMessage("CT_BLFE_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[SCALE]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_SCALE_1"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["SCALE"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["SCALE"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[WIDTH]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_WIDTH"),
		"params" => array("size" => 7),
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["WIDTH"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["WIDTH"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[HEIGHT]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_HEIGHT"),
		"params" => array("size" => 7),
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["HEIGHT"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["HEIGHT"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[IGNORE_ERRORS]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_IGNORE_ERRORS"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["IGNORE_ERRORS"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] : '',
	);

	$readOnlyAdd = false;
}
elseif($arResult["FORM_DATA"]["TYPE"] == "PREVIEW_TEXT" || $arResult["FORM_DATA"]["TYPE"] == "DETAIL_TEXT")
{
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[USE_EDITOR]",
		"name"=>GetMessage("CT_BLFE_TEXT_USE_EDITOR"),
		"type"=>"checkbox",
		"value" => $arResult["FORM_DATA"]["SETTINGS"]["USE_EDITOR"] ?? false,
	);
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[WIDTH]",
		"name"=>GetMessage("CT_BLFE_TEXT_WIDTH_NEW"),
		"params" => array("size" => 7),
		"value" => $arResult["FORM_DATA"]["SETTINGS"]["WIDTH"] ?? 600,
	);
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[HEIGHT]",
		"name"=>GetMessage("CT_BLFE_TEXT_HEIGHT_NEW"),
		"params" => array("size" => 7),
		"value"=>$arResult["FORM_DATA"]["SETTINGS"]["HEIGHT"] ?? 200,
	);
	$arTab1Fields[] = array(
		"id"=>"DEFAULT_VALUE",
		"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
		"type"=>"textarea",
		"rows"=>"5"
	);
}
elseif($arResult["FORM_DATA"]["TYPE"] == "DETAIL_PICTURE")
{
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[SCALE]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_SCALE_1"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["SCALE"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["SCALE"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[WIDTH]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_WIDTH"),
		"params" => array("size" => 7),
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["WIDTH"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["WIDTH"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[HEIGHT]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_HEIGHT"),
		"params" => array("size" => 7),
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["HEIGHT"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["HEIGHT"] : '',
	);
	$arTab1Fields[] = array(
		"id" => "DEFAULT_VALUE[IGNORE_ERRORS]",
		"name" => GetMessage("CT_BLFE_FIELD_PICTURE_IGNORE_ERRORS"),
		"type" => "checkbox",
		"value" => isset($arResult["FORM_DATA"]["DEFAULT_VALUE"]["IGNORE_ERRORS"]) ? $arResult["FORM_DATA"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] : '',
	);

	$readOnlyAdd = false;
}
elseif($arResult["FORM_DATA"]["TYPE"] == "S")
{
	if (($arResult["FORM_DATA"]["ROW_COUNT"] ?? 0) > 1)
	{
		$arTab1Fields[] = array(
			"id" => "DEFAULT_VALUE",
			"name" => GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
			"type" => "textarea",
			"params" => array(
				"cols" => $arResult["FORM_DATA"]["COL_COUNT"],
				"rows" => $arResult["FORM_DATA"]["ROW_COUNT"],
				"style" => "width:auto;height:auto;",
			),
		);
	}
	else
	{
		$arTab1Fields[] = array(
			"id" => "DEFAULT_VALUE",
			"name" => GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
			"params" => array(
				"size" => ($arResult["FORM_DATA"]["COL_COUNT"] ?? 0),
			),
		);
	}
}
elseif($arResult["FORM_DATA"]["TYPE"] == "N")
{
	$arTab1Fields[] = array(
		"id"=>"DEFAULT_VALUE",
		"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
	);
}
elseif(preg_match("/^(L|L:)/", $arResult["FORM_DATA"]["TYPE"]))
{

}
elseif(preg_match("/^(F|F:)/", $arResult["FORM_DATA"]["TYPE"]))
{
	//No default value input

	$readOnlyAdd = false;
}
elseif(preg_match("/^(G|G:)/", $arResult["FORM_DATA"]["TYPE"]))
{
	$LINK = $arResult["FORM_DATA"]["LINK_IBLOCK_ID"] ?? 0;
	if($LINK <= 0)
		$LINK = key($arResult["LINK_IBLOCKS"]);

	$items = array("" => GetMessage("CT_BLFE_NO_VALUE"));
	if ($LINK > 0)
	{
		$rsSections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$LINK));
		while($ar = $rsSections->Fetch())
			$items[$ar["ID"]] = str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"];
	}

	$arTab1Fields[] = array(
		"id"=>"DEFAULT_VALUE",
		"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
		"type"=>"list",
		"items"=>$items,
	);
}
elseif($arResult["FORM_DATA"]["TYPE"] == "N:Sequence")
{
	$readOnlyAdd = false;
	$readOnlyEdit = false;
}
elseif(preg_match("/^(E|E:)/", $arResult["FORM_DATA"]["TYPE"]))
{
	//No default value input
	$readOnlyAdd = false;
}
elseif(!isset($arPropertyFields["HIDE"]) || is_array($arPropertyFields["HIDE"]) && !in_array("DEFAULT_VALUE", $arPropertyFields["HIDE"]))
{//Show default property value input if it was not cancelled by property
	if(is_array($arUserType))
	{
		switch($arUserType["USER_TYPE"])
		{
			case "DiskFile":
				$readOnlyAdd = false;
				break;
			case "map_yandex":
				$arResult["FIELD"]["MULTIPLE"] =
					isset($arResult["FIELD"]["MULTIPLE"]) ? $arResult["FIELD"]["MULTIPLE"] : "N";
				break;
			case 'employee':
				$arResult["FIELD"]['SETTINGS']['USE_ENTITY_SELECTOR'] = 'Y';
				break;
		}

		if(array_key_exists("GetPublicEditHTML", $arUserType))
		{
			$html = '';
			$html = call_user_func_array($arUserType["GetPublicEditHTML"],
				array(
					$arResult["FIELD"],
					array(
						"VALUE"=>$arResult["FORM_DATA"]["~DEFAULT_VALUE"],
						"DESCRIPTION"=>""
					),
					array(
						"VALUE"=>"DEFAULT_VALUE",
						"DESCRIPTION"=>"",
						"MODE" => "EDIT_FORM",
						"FORM_NAME" => "form_".$arResult["FORM_ID"],
						"MULTIPLE" => $arResult["FORM_DATA"]["MULTIPLE"]
					),
				)
			);

			$arTab1Fields[] = array(
				"id"=>"DEFAULT_VALUE",
				"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
				"type"=>"custom",
				"value"=> $html
			);
		}
		else
		{
			$arTab1Fields[] = array(
				"id"=>"DEFAULT_VALUE",
				"name"=>GetMessage("CT_BLFE_FIELD_DEFAULT_VALUE"),
			);
		}
	}
}

if($USER_TYPE_SETTINGS_HTML)
{
	$arTab1Fields[] = array(
		"id"=>"USER_TYPE_SETTINGS",
		"type"=>"custom",
		"value"=>$USER_TYPE_SETTINGS_HTML,
		"colspan"=>true,
	);
}

if(preg_match("/^(G|G:)/", $arResult["FORM_DATA"]["TYPE"]))
{
	$arTab1Fields[] = array(
		"id"=>"LINK_IBLOCK_ID",
		"name"=>GetMessage("CT_BLFE_FIELD_SECTION_LINK_IBLOCK_ID"),
		"type"=>"list",
		"items"=>$arResult["LINK_IBLOCKS"],
		"params"=>array("OnChange"=>"javascript:BX.Lists['".$jsClass."'].changeType('form_".$arResult["FORM_ID"]."')"),
	);

	if($arResult["FIELD_ID"])
		$customHtml .= '<input type="hidden" name="TYPE" value="'.$arResult["FORM_DATA"]["TYPE"].'">';
}
elseif(preg_match("/^(E|E:)/", $arResult["FORM_DATA"]["TYPE"]))
{
	$arTab1Fields[] = array(
		"id"=>"LINK_IBLOCK_ID",
		"name"=>GetMessage("CT_BLFE_FIELD_ELEMENT_LINK_IBLOCK_ID"),
		"type"=>"list",
		"items"=>$arResult["LINK_IBLOCKS"],
	);

	if($arResult["FIELD_ID"])
		$customHtml .= '<input type="hidden" name="TYPE" value="'.$arResult["FORM_DATA"]["TYPE"].'">';
}
elseif($arResult["FORM_DATA"]["TYPE"] === "S")
{
	$arTab1Fields[] = array(
		"id"=>"INPUT_SIZE",
		"name"=>GetMessage("CT_BLFE_FIELD_INPUT_SIZE"),
		"type"=>"custom",
		"value"=>'<input type="text" size="2" maxlength="10" name="ROW_COUNT" value="'.intval($arResult["FORM_DATA"]["ROW_COUNT"] ?? 0).'"> x <input type="text" size="2" maxlength="10" name="COL_COUNT" value="'.intval($arResult["FORM_DATA"]["COL_COUNT"] ?? 0).'">',
	);
}

if($arResult["IS_PROPERTY"])
{
	$arTab1Fields[] = array(
		"id"=>"CODE",
		"name"=>GetMessage("CT_BLFE_FIELD_CODE"),
		"params"=>array("id"=>"bx-lists-field-code")
	);
}

$arTab1Fields[] = array("id"=>"SORT", "name"=>GetMessage("CT_BLFE_FIELD_SORT"), "params"=>array("size"=>5));

$checkedAdd = true;
$checkedEdit = true;
$checkedReadAdd = false;
$checkedReadEdit = false;
$checkedPreview = false;
if(
	isset($arResult["FORM_DATA"]["SETTINGS"]["SHOW_ADD_FORM"]) &&
	$arResult["FORM_DATA"]["SETTINGS"]["SHOW_ADD_FORM"] == "N"
)
	$checkedAdd = false;
if(
	isset($arResult["FORM_DATA"]["SETTINGS"]["SHOW_EDIT_FORM"]) &&
	$arResult["FORM_DATA"]["SETTINGS"]["SHOW_EDIT_FORM"] == "N"
)
	$checkedEdit = false;
if(
	isset($arResult["FORM_DATA"]["SETTINGS"]["ADD_READ_ONLY_FIELD"]) &&
	$arResult["FORM_DATA"]["SETTINGS"]["ADD_READ_ONLY_FIELD"] == "Y"
)
	$checkedReadAdd = true;
if(
	isset($arResult["FORM_DATA"]["SETTINGS"]["EDIT_READ_ONLY_FIELD"]) &&
	$arResult["FORM_DATA"]["SETTINGS"]["EDIT_READ_ONLY_FIELD"] == "Y"
)
	$checkedReadEdit = true;

if(
	isset($arResult["FORM_DATA"]["SETTINGS"]["SHOW_FIELD_PREVIEW"]) &&
	$arResult["FORM_DATA"]["SETTINGS"]["SHOW_FIELD_PREVIEW"] == "Y"
)
{
	$checkedPreview = true;
}

$params = array();
/* Marker display field */
if($showAddForm)
{
	$params["id"] = "bx-lists-show-add-form";
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[SHOW_ADD_FORM]",
		"name"=>GetMessage("CT_BLFE_FIELD_SHOW_ADD_FORM"),
		"type"=>"checkbox",
		"value"=>$checkedAdd,
		"params"=>$params
	);
}
$params["id"] = "bx-lists-show-edit-form";
$arTab1Fields[] = array(
	"id"=>"SETTINGS[SHOW_EDIT_FORM]",
	"name"=>GetMessage("CT_BLFE_FIELD_SHOW_EDIT_FORM"),
	"type"=>"checkbox",
	"value"=>$checkedEdit,
	"params"=>$params
);

/* Marker "read-only" field */
if($readOnlyAdd)
{
	$params["id"] = "bx-lists-add-read-only-field";
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[ADD_READ_ONLY_FIELD]",
		"name"=>GetMessage("CT_BLFE_FIELD_ADD_READ_ONLY_FIELD"),
		"type"=>"checkbox",
		"value" => $checkedReadAdd,
		"params"=>$params
	);
}
if($readOnlyEdit)
{
	$params["id"] = "bx-lists-edit-read-only-field";
	$arTab1Fields[] = array(
		"id"=>"SETTINGS[EDIT_READ_ONLY_FIELD]",
		"name"=>GetMessage("CT_BLFE_FIELD_EDIT_READ_ONLY_FIELD"),
		"type"=>"checkbox",
		"value"=>$checkedReadEdit,
		"params"=>$params
	);
}

$params["id"] = "bx-lists-edit-show-field-preview";
$arTab1Fields[] = array(
	"id"=>"SETTINGS[SHOW_FIELD_PREVIEW]",
	"name"=>GetMessage("CT_BLFE_FIELD_SHOW_FIELD_PREVIEW"),
	"type"=>"checkbox",
	"value"=>$checkedPreview,
	"params"=>$params
);

$arTabs = array(
	array("id"=>"tab1", "name"=>GetMessage("CT_BLFE_TAB_EDIT"), "title"=>GetMessage("CT_BLFE_TAB_EDIT_TITLE"), "icon"=>"", "fields"=>$arTab1Fields),
);

//List properties
if(is_array($arResult["LIST"]))
{
	if(preg_match("/^(L|L:)/", $arResult["FORM_DATA"]["TYPE"]))
	{
		$sort = 10;
		$html = '<div id="divTable"><table id="tblLIST" width="100%" class="tableList">';
		foreach($arResult["LIST"] as $arEnum)
		{
			$html .= '
				<tr>
				<td style="display:none;"></td>
				<td align="center" class="sort-td" title="'.GetMessage("CT_BLFE_SORT_TITLE").'"></td>
				<td class="tdInput">
					<input type="hidden" name="LIST['.htmlspecialcharsbx($arEnum["ID"]).'][SORT]" value="'.$sort.'" class="sort-input">
					<input type="text" size="35" name="LIST['.htmlspecialcharsbx($arEnum["ID"]).'][VALUE]" value="'.htmlspecialcharsbx($arEnum["~VALUE"] ?? '').'" class="value-input">
				</td>
				<td align="center" class="delete-action"><div class="delete-action"
					onclick="BX.Lists[\''.$jsClass.'\'].deleteListItem(this);" title="'.GetMessage("CT_BLFE_DELETE_TITLE").'"></div></td>
				</tr>
			';
			$sort += 10;
		}

		$html .= '</table></div>';
		$html .= '<input type="button" value="'.GetMessage("CT_BLFE_LIST_ITEM_ADD").'"
			onclick="javascript:BX.Lists[\''.$jsClass.'\'].addNewTableRow(\'tblLIST\', /LIST\[(n)([0-9]*)\]/g, 2)">';

		$html .= '
			<br><br>
			<a class="href-action" href="javascript:void(0)" onclick="BX.Lists[\''.$jsClass.'\'].toggleInput(\'import\'); return false;">'.GetMessage("CT_BLFE_ENUM_IMPORT").'</a>
			<div id="import" style="'.($arResult["FORM_DATA"]["LIST_TEXT_VALUES"] <> ''? '': 'display:none; ').'width:100%">
				<p>'.GetMessage("CT_BLFE_ENUM_IMPORT_HINT").'</p>
				<textarea name="LIST_TEXT_VALUES" id="LIST_TEXT_VALUES" style="width:100%" rows="20">'.htmlspecialcharsex($arResult["FORM_DATA"]["LIST_TEXT_VALUES"]).'</textarea>
			</div>
		';

		$html .= '
			<br><br>
			<a class="href-action" href="javascript:void(0)" onclick="BX.Lists[\''.$jsClass.'\'].toggleInput(\'defaults\'); return false;">'.($arResult["FORM_DATA"]["MULTIPLE"] == "Y"? GetMessage("CT_BLFE_ENUM_DEFAULTS"): GetMessage("CT_BLFE_ENUM_DEFAULT")).'</a>
			<div id="defaults" style="'.($arResult["FORM_DATA"]["LIST_TEXT_VALUES"] <> ''? '': 'display:none; ').'width:100%">
			<br>
		';

		if($arResult["FORM_DATA"]["MULTIPLE"] == "Y")
			$html .= '<select multiple name="LIST_DEF[]" id="LIST_DEF" size="10">';
		else
			$html .= '<select name="LIST_DEF[]" id="LIST_DEF" size="1">';

		if (!isset($arResult["FORM_DATA"]["IS_REQIRED"]) || $arResult["FORM_DATA"]["IS_REQIRED"] != "Y")
			$html .= '<option value=""'.(count($arResult["LIST_DEF"])==0? ' selected': '').'>'.GetMessage("CT_BLFE_ENUM_NO_DEFAULT").'</option>';

		foreach($arResult["LIST"] as $arEnum)
			$html .= '<option value="'.htmlspecialcharsbx($arEnum["ID"]).'"'.(isset($arResult["LIST_DEF"][htmlspecialcharsbx($arEnum["ID"])])? ' selected': '').'>'.htmlspecialcharsbx($arEnum["~VALUE"] ?? '').'</option>';

		$html .= '
				</select>
			</div>
		';

		$arTabs[] = array(
			"id"=>"tab2",
			"name"=>GetMessage("CT_BLFE_TAB_LIST"),
			"title"=>GetMessage("CT_BLFE_TAB_LIST_TITLE"),
			"icon"=>"",
			"fields"=>array(
				array(
					"id" => "LIST",
					"colspan" => true,
					"type" => "custom",
					"value" => $html,
				),
			),
		);
		?>
		<script>
			BX.ready(function ()
			{
				var table = BX('divTable');
				dragTable(table.getElementsByTagName('table')[0], {
					start: function (table, el, index)
					{
					},
					stop: function (table, el, indexBefore, index)
					{
						enumerationValues(table);
					}
				});
			});
		</script>
		<?
	}
	else
	{
		foreach($arResult["LIST"] as $arEnum)
		{
			$customHtml .= '<input type="hidden" name="LIST['.htmlspecialcharsbx($arEnum["ID"]).'][SORT]" value="'.$arEnum["SORT"].'">'
				.'<input type="hidden" name="LIST['.htmlspecialcharsbx($arEnum["ID"]).'][VALUE]" value="'
				.htmlspecialcharsbx($arEnum["~VALUE"] ?? '').'">';
		}
	}
}

$customHtml .= '<input type="hidden" name="action" id="action" value="">';

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.form",
	"",
	array(
		"FORM_ID"=>$arResult["FORM_ID"],
		"TABS"=>$arTabs,
		"BUTTONS"=>array("back_url"=>$arResult["~LIST_FIELDS_URL"], "custom_html"=>$customHtml),
		"DATA"=>$arResult["FORM_DATA"],
		"SHOW_SETTINGS"=>"N",
		"THEME_GRID_ID"=>$arResult["GRID_ID"],
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>

<script>
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsFieldEditClass({
			randomString: '<?=$arResult['RAND_STRING']?>',
			iblockTypeId: '<?=$arParams['IBLOCK_TYPE_ID']?>',
			iblockId: '<?=$arResult['IBLOCK_ID']?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			generateCode: '<?=$generateCode?>',
			listAction: <?=\Bitrix\Main\Web\Json::encode($listAction)?>
		});

		BX.message({
			CT_BLFE_SAVE_BUTTON: '<?=GetMessageJS("CT_BLFE_SAVE_BUTTON")?>',
			CT_BLFE_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLFE_CANCEL_BUTTON")?>'
		});
	});
</script>
