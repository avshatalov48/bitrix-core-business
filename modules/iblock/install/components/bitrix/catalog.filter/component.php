<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

unset($arParams["IBLOCK_TYPE"]); //was used only for IBLOCK_ID setup with Editor
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["FIELD_CODE"][$k]);

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);

if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();
foreach($arParams["PRICE_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PRICE_CODE"][$k]);

if(!is_array($arParams["OFFERS_FIELD_CODE"]))
	$arParams["OFFERS_FIELD_CODE"] = array();
foreach($arParams["OFFERS_FIELD_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["OFFERS_FIELD_CODE"][$k]);

if(!is_array($arParams["OFFERS_PROPERTY_CODE"]))
	$arParams["OFFERS_PROPERTY_CODE"] = array();
foreach($arParams["OFFERS_PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["OFFERS_PROPERTY_CODE"][$k]);

$arParams["SAVE_IN_SESSION"] = $arParams["SAVE_IN_SESSION"]=="Y";

if(strlen($arParams["FILTER_NAME"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
	$arParams["FILTER_NAME"] = "arrFilter";
$FILTER_NAME = $arParams["FILTER_NAME"];

global ${$FILTER_NAME};
${$FILTER_NAME} = array();

$arParams["NUMBER_WIDTH"] = intval($arParams["NUMBER_WIDTH"]);
if($arParams["NUMBER_WIDTH"]<=0)
	$arParams["NUMBER_WIDTH"]=5;
$arParams["TEXT_WIDTH"] = intval($arParams["TEXT_WIDTH"]);
if($arParams["TEXT_WIDTH"]<=0)
	$arParams["TEXT_WIDTH"]=20;
$arParams["LIST_HEIGHT"] = intval($arParams["LIST_HEIGHT"]);
if($arParams["LIST_HEIGHT"]<=0)
	$arParams["LIST_HEIGHT"]=5;

/*************************************************************************
		Processing the  "Filter" and "Reset" button actions
*************************************************************************/
$arDateFields = array(
	"ACTIVE_DATE" => array(
		"from" => "_ACTIVE_DATE_1",
		"to" => "_ACTIVE_DATE_2",
		"days_to_back" => "_ACTIVE_DATE_1_DAYS_TO_BACK",
		"filter_from" => ">=DATE_ACTIVE_FROM",
		"filter_to" => "<=DATE_ACTIVE_TO",
	),
	"DATE_ACTIVE_FROM" => array(
		"from" => "_DATE_ACTIVE_FROM_1",
		"to" => "_DATE_ACTIVE_FROM_2",
		"days_to_back" => "_DATE_ACTIVE_FROM_1_DAYS_TO_BACK",
		"filter_from" => ">=DATE_ACTIVE_FROM",
		"filter_to" => "<=DATE_ACTIVE_FROM",
	),
	"DATE_ACTIVE_TO" => array(
		"from" => "_DATE_ACTIVE_TO_1",
		"to" => "_DATE_ACTIVE_TO_2",
		"days_to_back" => "_DATE_ACTIVE_TO_1_DAYS_TO_BACK",
		"filter_from" => ">=DATE_ACTIVE_TO",
		"filter_to" => "<=DATE_ACTIVE_TO",
	),
	"DATE_CREATE" => array(
		"from" => "_DATE_CREATE_1",
		"to" => "_DATE_CREATE_2",
		"days_to_back" => "_DATE_CREATE_1_DAYS_TO_BACK",
		"filter_from" => ">=DATE_CREATE",
		"filter_to" => "<=DATE_CREATE",
	),
);

/*Init filter values*/
$arrPFV = array();
$arrCFV = array();
$arrFFV = array();//Element fields value
$arrDFV = array();//Element date fields
$arrOFV = array();//Offer fields values
$arrODFV = array();//Offer date fields
$arrOPFV = array();//Offer properties fields
foreach($arDateFields as $id => $arField)
{
	$arField["from"] = array(
		"name" => $FILTER_NAME.$arField["from"],
		"value" => "",
	);
	$arField["to"] = array(
		"name" => $FILTER_NAME.$arField["to"],
		"value" => "",
	);
	$arField["days_to_back"] = array(
		"name" => $FILTER_NAME.$arField["days_to_back"],
		"value" => "",
	);
	$arrDFV[$id] = $arField;

	$arField["from"]["name"] = "OF_".$arField["from"]["name"];
	$arField["to"]["name"] = "OF_".$arField["to"]["name"];
	$arField["days_to_back"]["name"] = "OF_".$arField["days_to_back"]["name"];
	$arrODFV[$id] = $arField;
}

/*Leave filter values empty*/
if(strlen($_REQUEST["del_filter"]) > 0)
{
	foreach($arrDFV as $id => $arField)
		$GLOBALS[$arField["days_to_back"]["name"]] = "";

	foreach($arrODFV as $id => $arField)
		$GLOBALS[$arField["days_to_back"]["name"]] = "";
}
/*Read filter values from request*/
elseif(strlen($_REQUEST["set_filter"]) > 0)
{
	if(isset($_REQUEST[$FILTER_NAME."_pf"]))
		$arrPFV = $_REQUEST[$FILTER_NAME."_pf"];
	if(isset($_REQUEST[$FILTER_NAME."_cf"]))
		$arrCFV = $_REQUEST[$FILTER_NAME."_cf"];
	if(isset($_REQUEST[$FILTER_NAME."_ff"]))
		$arrFFV = $_REQUEST[$FILTER_NAME."_ff"];
	if(isset($_REQUEST[$FILTER_NAME."_of"]))
		$arrOFV = $_REQUEST[$FILTER_NAME."_of"];
	if(isset($_REQUEST[$FILTER_NAME."_op"]))
		$arrOPFV = $_REQUEST[$FILTER_NAME."_op"];

	$now = time();
	foreach($arrDFV as $id => $arField)
	{
		$name = $arField["from"]["name"];
		if(isset($_REQUEST[$name]))
			$arrDFV[$id]["from"]["value"] = $_REQUEST[$name];

		$name = $arField["to"]["name"];
		if(isset($_REQUEST[$name]))
			$arrDFV[$id]["to"]["value"] = $_REQUEST[$name];

		$name = $arField["days_to_back"]["name"];
		if(isset($_REQUEST[$name]))
		{
			$value = $arrDFV[$id]["days_to_back"]["value"] = $_REQUEST[$name];
			if(strlen($value) > 0)
				$arrDFV[$id]["from"]["value"] = GetTime($now - 86400*intval($value));
		}
	}

	foreach($arrODFV as $id => $arField)
	{
		$name = $arField["from"]["name"];
		if(isset($_REQUEST[$name]))
			$arrODFV[$id]["from"]["value"] = $_REQUEST[$name];

		$name = $arField["to"]["name"];
		if(isset($_REQUEST[$name]))
			$arrODFV[$id]["to"]["value"] = $_REQUEST[$name];

		$name = $arField["days_to_back"]["name"];
		if(isset($_REQUEST[$name]))
		{
			$value = $arrODFV[$id]["days_to_back"]["value"] = $_REQUEST[$name];
			if(strlen($value) > 0)
				$arrODFV[$id]["from"]["value"] = GetTime($now - 86400*intval($value));
		}
	}
}
/*No action specified, so read from the session (if parameter is set)*/
elseif($arParams["SAVE_IN_SESSION"])
{
	if(isset($_SESSION[$FILTER_NAME."arrPFV"]))
		$arrPFV = $_SESSION[$FILTER_NAME."arrPFV"];
	if(isset($_SESSION[$FILTER_NAME."arrCFV"]))
		$arrCFV = $_SESSION[$FILTER_NAME."arrCFV"];
	if(isset($_SESSION[$FILTER_NAME."arrFFV"]))
		$arrFFV = $_SESSION[$FILTER_NAME."arrFFV"];
	if(isset($_SESSION[$FILTER_NAME."arrOFV"]))
		$arrOFV = $_SESSION[$FILTER_NAME."arrOFV"];
	if(isset($_SESSION[$FILTER_NAME."arrOPFV"]))
		$arrOPFV = $_SESSION[$FILTER_NAME."arrOPFV"];
	if(isset($_SESSION[$FILTER_NAME."arrDFV"]) && is_array($_SESSION[$FILTER_NAME."arrDFV"]))
	{
		foreach($_SESSION[$FILTER_NAME."arrDFV"] as $id => $arField)
		{
			$arrDFV[$id]["from"]["value"] = $arField["from"]["value"];
			$arrDFV[$id]["to"]["value"] = $arField["to"]["value"];
			$arrDFV[$id]["days_to_back"]["value"] = $arField["days_to_back"]["value"];
		}
	}
	if(isset($_SESSION[$FILTER_NAME."arrODFV"]) && is_array($_SESSION[$FILTER_NAME."arrODFV"]))
	{
		foreach($_SESSION[$FILTER_NAME."arrODFV"] as $id => $arField)
		{
			$arrODFV[$id]["from"]["value"] = $arField["from"]["value"];
			$arrODFV[$id]["to"]["value"] = $arField["to"]["value"];
			$arrODFV[$id]["days_to_back"]["value"] = $arField["days_to_back"]["value"];
		}
	}
}

/*Save filter values to the session*/
if($arParams["SAVE_IN_SESSION"])
{
	$_SESSION[$FILTER_NAME."arrPFV"] = $arrPFV;
	$_SESSION[$FILTER_NAME."arrCFV"] = $arrCFV;
	$_SESSION[$FILTER_NAME."arrFFV"] = $arrFFV;
	$_SESSION[$FILTER_NAME."arrOFV"] = $arrOFV;
	$_SESSION[$FILTER_NAME."arrDFV"] = $arrDFV;
	$_SESSION[$FILTER_NAME."arrODFV"] = $arrODFV;
	$_SESSION[$FILTER_NAME."arrOPFV"] = $arrOPFV;
}

if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("CC_BCF_MODULE_NOT_INSTALLED"));
	return 0;
}

if ($this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	$arResultModules = array(
		'iblock' => true,
		'catalog' => false,
		'currency' => false,
	);

	$arResult["arrProp"] = array();
	$arResult["arrPrice"] = array();
	$arResult["arrSection"] = array();
	$arResult["arrOfferProp"] = array();

	// simple fields
	if (in_array("SECTION_ID", $arParams["FIELD_CODE"]))
	{
		$arResult["arrSection"][0] = GetMessage("CC_BCF_TOP_LEVEL");
		$rsSection = CIBlockSection::GetList(
			Array("left_margin"=>"asc"),
			Array(
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
				"ACTIVE"=>"Y",
			),
			false,
			Array("ID", "DEPTH_LEVEL", "NAME")
		);
		while($arSection = $rsSection->Fetch())
		{
			$arResult["arrSection"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
		}
	}

	// prices
	if (CModule::IncludeModule("catalog"))
	{
		$arResultModules['catalog'] = true;
		if (!empty($arParams["PRICE_CODE"]))
		{
			$rsPrice = CCatalogGroup::GetList(
				array('SORT' => 'ASC', 'ID' => 'ASC'),
				array('=NAME' => $arParams["PRICE_CODE"]),
				false,
				false,
				array('ID', 'NAME', 'NAME_LANG', 'CAN_ACCESS', 'CAN_BUY')
			);
			while ($arPrice = $rsPrice->Fetch())
			{
				if ($arPrice["CAN_ACCESS"] == "Y" || $arPrice["CAN_BUY"] == "Y")
					$arResult["arrPrice"][$arPrice["NAME"]] = array(
						"ID" => $arPrice["ID"],
						"TITLE" => (!empty($arPrice["NAME_LANG"]) ? $arPrice["NAME_LANG"] : $arPrice["NAME"])
					);
			}
		}
	}
	else
	{
		$rsProp = CIBlockProperty::GetList(
			array("sort"=>"asc", "name"=>"asc"),
			array("IBLOCK_ID"=>$arParams["IBLOCK_ID"], "ACTIVE"=>"Y")
		);
		while($arProp = $rsProp->Fetch())
		{
			if(in_array($arProp["CODE"],$arParams["PRICE_CODE"]) && in_array($arProp["PROPERTY_TYPE"], array("N")))
				$arResult["arrPrice"][$arProp["CODE"]] = array("ID"=>$arProp["ID"], "TITLE"=>$arProp["NAME"]);
		}
	}

	// properties
	$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
	while ($arProp = $rsProp->Fetch())
	{
		if(in_array($arProp["CODE"],$arParams["PROPERTY_CODE"]) && $arProp["PROPERTY_TYPE"] != "F")
		{
			$arTemp = array(
				"CODE" => $arProp["CODE"],
				"NAME" => $arProp["NAME"],
				"PROPERTY_TYPE" => $arProp["PROPERTY_TYPE"],
				"MULTIPLE" => $arProp["MULTIPLE"],
			);
			if ($arProp["PROPERTY_TYPE"]=="L")
			{
				$arTemp['LIST_TYPE'] = $arProp['LIST_TYPE'];
				$arrEnum = array();
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					$arrEnum[$arEnum["ID"]] = $arEnum["VALUE"];
				}
				$arTemp["VALUE_LIST"] = $arrEnum;
			}
			$arResult["arrProp"][$arProp["ID"]] = $arTemp;
		}
	}

	// offer properties
	if ($arResultModules['catalog'])
	{
		$arOffersIBlock = CIBlockPriceTools::GetOffersIBlock($arParams["IBLOCK_ID"]);
		if(is_array($arOffersIBlock))
		{
			$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arOffersIBlock["OFFERS_IBLOCK_ID"]));
			while ($arProp = $rsProp->Fetch())
			{
				if(in_array($arProp["CODE"], $arParams["OFFERS_PROPERTY_CODE"]) && $arProp["PROPERTY_TYPE"] != "F")
				{
					$arTemp = array(
						"CODE" => $arProp["CODE"],
						"NAME" => $arProp["NAME"],
						"PROPERTY_TYPE" => $arProp["PROPERTY_TYPE"],
						"MULTIPLE" => $arProp["MULTIPLE"],
					);
					if ($arProp["PROPERTY_TYPE"]=="L")
					{
						$arTemp['LIST_TYPE'] = $arProp['LIST_TYPE'];
						$arrEnum = array();
						$rsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
						while($arEnum = $rsEnum->Fetch())
						{
							$arrEnum[$arEnum["ID"]] = $arEnum["VALUE"];
						}
						$arTemp["VALUE_LIST"] = $arrEnum;
					}
					$arResult["arrOfferProp"][$arProp["ID"]] = $arTemp;
				}
			}
		}
	}

	$arResult['MODULES'] = $arResultModules;

	$this->EndResultCache();
}

$arResult["FORM_ACTION"] = isset($_SERVER['REQUEST_URI'])? htmlspecialcharsbx($_SERVER['REQUEST_URI']): "";
$arResult["FILTER_NAME"] = $FILTER_NAME;

/*************************************************************************
		Adding the titles and input fields
*************************************************************************/

$arResult["arrInputNames"] = array(); // array of the input field names; is being used in the function $APPLICATION->GetCurPageParam

// simple fields
$arResult["ITEMS"] = array();

foreach($arParams["FIELD_CODE"] as $field_code)
{
	$field_res = "";
	$field_type = "";
	$field_names = "";
	$field_values = "";
	$field_list = array();
	$arResult["arrInputNames"][$FILTER_NAME."_ff"]=true;
	$name = $FILTER_NAME."_ff[".$field_code."]";
	$value = $arrFFV[$field_code];
	switch ($field_code)
	{
		case "CODE":
		case "XML_ID":
		case "NAME":
		case "PREVIEW_TEXT":
		case "DETAIL_TEXT":
		case "IBLOCK_TYPE_ID":
		case "IBLOCK_ID":
		case "IBLOCK_CODE":
		case "IBLOCK_NAME":
		case "IBLOCK_EXTERNAL_ID":
		case "SEARCHABLE_CONTENT":
		case "TAGS":
			if(!is_array($value))
			{
				$field_res = '<input type="text" name="'.$name.'" size="'.$arParams["TEXT_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';
				if (strlen($value)>0)
					${$FILTER_NAME}["?".$field_code] = $value;

				$field_type = 'INPUT';
			}
			break;
		case "ID":
		case "SORT":
		case "SHOW_COUNTER":
			$name_left = $FILTER_NAME."_ff[".$field_code."][LEFT]";
			if(is_array($value) && isset($value["LEFT"]))
				$value_left = $value["LEFT"];
			else
				$value_left = "";
			$field_res = '<input type="text" name="'.$name_left.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_left).'" />&nbsp;'.GetMessage("CC_BCF_TILL").'&nbsp;';

			if(strlen($value_left) > 0)
				${$FILTER_NAME}[">=".$field_code] = intval($value_left);

			$name_right = $FILTER_NAME."_ff[".$field_code."][RIGHT]";
			if(is_array($value) && isset($value["RIGHT"]))
				$value_right = $value["RIGHT"];
			else
				$value_right = "";
			$field_res .= '<input type="text" name="'.$name_right.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_right).'" />';

			if(strlen($value_right) > 0)
				${$FILTER_NAME}["<=".$field_code] = intval($value_right);

			$field_type = 'RANGE';
			$field_names = array($name_left, $name_right);
			$field_values = array($value_left, $value_right);
			break;
		case "SECTION_ID":
			$arrRef = array("reference" => array_values($arResult["arrSection"]), "reference_id" => array_keys($arResult["arrSection"]));
			$field_res = SelectBoxFromArray($name, $arrRef, $value, " ", "");

			if (!is_array($value) && $value != "NOT_REF" && strlen($value) > 0)
				${$FILTER_NAME}[$field_code] = intval($value);

			$_name = $FILTER_NAME."_ff[INCLUDE_SUBSECTIONS]";
			$_value = $arrFFV["INCLUDE_SUBSECTIONS"];
			$field_res .= "<br>".InputType("checkbox", $_name, "Y", $_value, false, "", "")."&nbsp;".GetMessage("CC_BCF_INCLUDE_SUBSECTIONS");

			if (isset(${$FILTER_NAME}[$field_code]) && $_value=="Y")
				${$FILTER_NAME}["INCLUDE_SUBSECTIONS"] = "Y";

			$field_type = 'SELECT';
			$field_list = $arResult["arrSection"];
			break;
		case "ACTIVE_DATE":
		case "DATE_ACTIVE_FROM":
		case "DATE_ACTIVE_TO":
		case "DATE_CREATE":
			$arDateField = $arrDFV[$field_code];
			$arResult["arrInputNames"][$arDateField["from"]["name"]]=true;
			$arResult["arrInputNames"][$arDateField["to"]["name"]]=true;
			$arResult["arrInputNames"][$arDateField["days_to_back"]["name"]]=true;

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'FORM_NAME' => $FILTER_NAME."_form",
					'SHOW_INPUT' => 'Y',
					'INPUT_NAME' => $arDateField["from"]["name"],
					'INPUT_VALUE' => $arDateField["from"]["value"],
					'INPUT_NAME_FINISH' => $arDateField["to"]["name"],
					'INPUT_VALUE_FINISH' => $arDateField["to"]["value"],
					'INPUT_ADDITIONAL_ATTR' => 'size="10" class="inputselect inputfield"',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			$field_res = ob_get_contents();
			ob_end_clean();

			if(strlen($arDateField["from"]["value"]) > 0)
				${$FILTER_NAME}[$arDateField["filter_from"]] = $arDateField["from"]["value"];

			if(strlen($arDateField["to"]["value"]) > 0)
				${$FILTER_NAME}[$arDateField["filter_to"]] = $arDateField["to"]["value"];

			$field_type = 'DATE_RANGE';
			$field_names = array($arDateField["from"]["name"], $arDateField["to"]["name"]);
			$field_values = array($arDateField["from"]["value"], $arDateField["to"]["value"]);
			break;
	}

	if($field_res)
	{
		$arResult["ITEMS"][$field_code] = array(
			"NAME" => htmlspecialcharsbx(GetMessage("IBLOCK_FIELD_".$field_code)),
			"INPUT" => $field_res,
			"INPUT_NAME" => $name,
			"INPUT_VALUE" => is_array($value)? array_map("htmlspecialcharsbx", $value): htmlspecialcharsbx($value),
			"~INPUT_VALUE" => $value,
			"TYPE" => $field_type,
			"INPUT_NAMES" => $field_names,
			"INPUT_VALUES" => is_array($field_values)? array_map("htmlspecialcharsbx", $field_values): htmlspecialcharsbx($field_values),
			"~INPUT_VALUES" => $field_values,
			"LIST" => $field_list,
		);
	}
}

foreach($arResult["arrProp"] as $prop_id => $arProp)
{
	$res = "";
	$name = "";
	$value ="";
	$type = "";
	$names = "";
	$values = "";
	$list = array();
	$arResult["arrInputNames"][$FILTER_NAME."_pf"]=true;
	switch ($arProp["PROPERTY_TYPE"])
	{
		case "L":
			$name = $FILTER_NAME."_pf[".$arProp["CODE"]."]";
			$value = $arrPFV[$arProp["CODE"]];

			if ('C' == $arProp['LIST_TYPE'])
			{
				$arListRadio = array();
				if ('Y' == $arProp['MULTIPLE'])
				{
					$type = "CHECKBOX";
					$list = $arProp["VALUE_LIST"];
					$arListValue = (is_array($value) ? $value : array($value));
					foreach ($arProp["VALUE_LIST"] as $key=>$val)
					{
						$arListRadio[] = '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialcharsbx($key).'"'.(in_array($key, $arListValue) ? ' checked' : '').'> '.htmlspecialcharsex($val);
					}
				}
				else
				{
					$type = "RADIO";
					$list[""] = GetMessage("CC_BCF_ALL");
					$arListRadio[] = '<input type="radio" name="'.$name.'" value=""'.($key == $value ? ' checked' : '').'> '.GetMessage("CC_BCF_ALL");
					foreach ($arProp["VALUE_LIST"] as $key=>$val)
					{
						$arListRadio[] = '<input type="radio" name="'.$name.'" value="'.htmlspecialcharsbx($key).'"'.($key == $value ? ' checked' : '').'> '.htmlspecialcharsex($val);
						$list[$key] = $val;
					}
				}
				$res .= implode('<br>', $arListRadio);
			}
			else
			{
				$type = 'SELECT';
				if ($arProp["MULTIPLE"]=="Y")
					$res .= '<select multiple name="'.$name.'[]" size="'.$arParams["LIST_HEIGHT"].'">';
				else
					$res .= '<select name="'.$name.'">';
				$res .= '<option value="">'.GetMessage("CC_BCF_ALL").'</option>';
				$list[""] = GetMessage("CC_BCF_ALL");
				foreach($arProp["VALUE_LIST"] as $key=>$val)
				{
					$res .= '<option';

					if (($arProp["MULTIPLE"] == "Y") && is_array($value))
					{
						if(in_array($key, $value))
							$res .= ' selected';
					}
					else
					{
						if($key == $value)
							$res .= ' selected';
					}

					$res .= ' value="'.htmlspecialcharsbx($key).'">'.htmlspecialcharsbx($val).'</option>';
					$list[$key] = $val;
				}
				$res .= '</select>';
			}

			if ($arProp["MULTIPLE"]=="Y")
			{
				if (is_array($value) && count($value) > 0)
					${$FILTER_NAME}["PROPERTY"][$arProp["CODE"]] = $value;
			}
			else
			{
				if (!is_array($value) && strlen($value) > 0)
					${$FILTER_NAME}["PROPERTY"][$arProp["CODE"]] = $value;
			}
			break;
		case "N":
			$value = $arrPFV[$arProp["CODE"]];
			$name_left = $FILTER_NAME."_pf[".$arProp["CODE"]."][LEFT]";
			if(is_array($value) && isset($value["LEFT"]))
				$value_left = $value["LEFT"];
			else
				$value_left = "";
			$res .= '<input type="text" name="'.$name_left.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_left).'" />&nbsp;'.GetMessage("CC_BCF_TILL").'&nbsp;';

			if (strlen($value_left) > 0)
				${$FILTER_NAME}["PROPERTY"][">=".$arProp["CODE"]] = doubleval($value_left);

			$name_right = $FILTER_NAME."_pf[".$arProp["CODE"]."][RIGHT]";
			if(is_array($value) && isset($value["RIGHT"]))
				$value_right = $value["RIGHT"];
			else
				$value_right = "";
			$res .= '<input type="text" name="'.$name_right.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_right).'" />';

			if (strlen($value_right) > 0)
				${$FILTER_NAME}["PROPERTY"]["<=".$arProp["CODE"]] = doubleval($value_right);

			$type = 'RANGE';
			$names = array($name_left, $name_right);
			$values = array($value_left, $value_right);
			break;
		case "S":
		case "E":
		case "G":
			$name = $FILTER_NAME."_pf[".$arProp["CODE"]."]";
			$value = $arrPFV[$arProp["CODE"]];
			if(!is_array($value))
			{
				$res .= '<input type="text" name="'.$name.'" size="'.$arParams["TEXT_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';

				if (strlen($value) > 0)
					${$FILTER_NAME}["PROPERTY"]["?".$arProp["CODE"]] = $value;
			}
			$type = 'INPUT';
			break;
	}
	if($res)
	{
		$arResult["ITEMS"]["PROPERTY_".$prop_id] = array(
			"NAME" => htmlspecialcharsbx($arProp["NAME"]),
			"INPUT" => $res,
			"INPUT_NAME" => $name,
			"INPUT_VALUE" => is_array($value)? array_map("htmlspecialcharsbx", $value): htmlspecialcharsbx($value),
			"~INPUT_VALUE" => $value,
			"TYPE" => $type,
			"INPUT_NAMES" => $names,
			"INPUT_VALUES" => is_array($values)? array_map("htmlspecialcharsbx", $values): htmlspecialcharsbx($values),
			"~INPUT_VALUES" => $values,
			"LIST" => $list,
		);
	}
}

$bHasOffersFilter = false;
foreach($arParams["OFFERS_FIELD_CODE"] as $field_code)
{
	$field_res = "";
	$field_type = "";
	$field_names = "";
	$field_values = "";
	$field_list = array();
	$arResult["arrInputNames"][$FILTER_NAME."_of"]=true;
	$name = $FILTER_NAME."_of[".$field_code."]";
	$value = $arrOFV[$field_code];
	switch ($field_code)
	{
		case "CODE":
		case "XML_ID":
		case "NAME":
		case "PREVIEW_TEXT":
		case "DETAIL_TEXT":
		case "IBLOCK_TYPE_ID":
		case "IBLOCK_ID":
		case "IBLOCK_CODE":
		case "IBLOCK_NAME":
		case "IBLOCK_EXTERNAL_ID":
		case "SEARCHABLE_CONTENT":
			$field_res = '<input type="text" name="'.$name.'" size="'.$arParams["TEXT_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';
			if (strlen($value)>0)
				${$FILTER_NAME}["OFFERS"]["?".$field_code] = $value;

			$field_type = 'INPUT';
			break;
		case "ID":
		case "SORT":
		case "SHOW_COUNTER":
			$name_left = $FILTER_NAME."_of[".$field_code."][LEFT]";
			$value = $arrOFV[$field_code]["LEFT"];
			$field_res = '<input type="text" name="'.$name_left.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />&nbsp;'.GetMessage("CC_BCF_TILL").'&nbsp;';

			if(strlen($value)>0)
				${$FILTER_NAME}["OFFERS"][">=".$field_code] = intval($value);

			$name_right = $FILTER_NAME."_of[".$field_code."][RIGHT]";
			$value = $arrOFV[$field_code]["RIGHT"];
			$field_res .= '<input type="text" name="'.$name_right.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';

			if(strlen($value)>0)
				${$FILTER_NAME}["OFFERS"]["<=".$field_code] = intval($value);

			$field_type = 'RANGE';
			$field_names = array($name_left, $name_right);
			$field_values = array($value_left, $value_right);
			break;
		case "ACTIVE_DATE":
		case "DATE_ACTIVE_FROM":
		case "DATE_ACTIVE_TO":
		case "DATE_CREATE":
			$arDateField = $arrODFV[$field_code];
			$arResult["arrInputNames"][$arDateField["from"]["name"]]=true;
			$arResult["arrInputNames"][$arDateField["to"]["name"]]=true;
			$arResult["arrInputNames"][$arDateField["days_to_back"]["name"]]=true;

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'FORM_NAME' => $FILTER_NAME."_form",
					'SHOW_INPUT' => 'Y',
					'INPUT_NAME' => $arDateField["from"]["name"],
					'INPUT_VALUE' => $arDateField["from"]["value"],
					'INPUT_NAME_FINISH' => $arDateField["to"]["name"],
					'INPUT_VALUE_FINISH' => $arDateField["to"]["value"],
					'INPUT_ADDITIONAL_ATTR' => 'size="10" class="inputselect inputfield"',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			$field_res = ob_get_contents();
			ob_end_clean();

			if(strlen($arDateField["from"]["value"]) > 0)
				${$FILTER_NAME}["OFFERS"][$arDateField["filter_from"]] = $arDateField["from"]["value"];

			if(strlen($arDateField["to"]["value"]) > 0)
				${$FILTER_NAME}["OFFERS"][$arDateField["filter_to"]] = $arDateField["to"]["value"];

			$field_type = 'DATE_RANGE';
			$field_names = array($arDateField["from"]["name"], $arDateField["to"]["name"]);
			$field_values = array($arDateField["from"]["value"], $arDateField["to"]["value"]);
			break;
	}
	if($field_res)
	{
		$bHasOffersFilter = true;
		$arResult["ITEMS"]["OFFER_".$field_code] = array(
			"NAME" => htmlspecialcharsbx(GetMessage("IBLOCK_FIELD_".$field_code)),
			"INPUT" => $field_res,
			"INPUT_NAME" => $name,
			"INPUT_VALUE" => htmlspecialcharsbx($value),
			"~INPUT_VALUE" => $value,
			"TYPE" => $field_type,
			"INPUT_NAMES" => $field_names,
			"INPUT_VALUES" => is_array($field_values)? array_map("htmlspecialcharsbx", $field_values): htmlspecialcharsbx($field_values),
			"~INPUT_VALUES" => $field_values,
			"LIST" => $field_list,
		);
	}
}

foreach($arResult["arrOfferProp"] as $prop_id => $arProp)
{
	$res = "";
	$name = "";
	$value = "";
	$type = "";
	$names = "";
	$values = "";
	$list = array();
	$arResult["arrInputNames"][$FILTER_NAME."_op"]=true;
	switch ($arProp["PROPERTY_TYPE"])
	{
		case "L":

			$name = $FILTER_NAME."_op[".$arProp["CODE"]."]";
			$value = $arrOPFV[$arProp["CODE"]];

			if ('C' == $arProp['LIST_TYPE'])
			{
				$arListRadio = array();
				if ('Y' == $arProp['MULTIPLE'])
				{
					$type = "CHECKBOX";
					$list = $arProp["VALUE_LIST"];
					$arListValue = (is_array($value) ? $value : array($value));
					foreach ($arProp["VALUE_LIST"] as $key=>$val)
					{
						$arListRadio[] = '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialcharsbx($key).'"'.(in_array($key, $arListValue) ? ' checked' : '').'> '.htmlspecialcharsex($val);
					}
				}
				else
				{
					$type = "RADIO";
					$list[""] = GetMessage("CC_BCF_ALL");
					$arListRadio[] = '<input type="radio" name="'.$name.'" value=""'.($key == $value ? ' checked' : '').'> '.GetMessage("CC_BCF_ALL");
					foreach ($arProp["VALUE_LIST"] as $key=>$val)
					{
						$arListRadio[] = '<input type="radio" name="'.$name.'" value="'.htmlspecialcharsbx($key).'"'.($key == $value ? ' checked' : '').'> '.htmlspecialcharsex($val);
						$list[$key] = $val;
					}
				}
				$res .= implode('<br>', $arListRadio);
			}
			else
			{
				$type = 'SELECT';
				if ($arProp["MULTIPLE"]=="Y")
					$res .= '<select multiple name="'.$name.'[]" size="'.$arParams["LIST_HEIGHT"].'">';
				else
					$res .= '<select name="'.$name.'">';
				$res .= '<option value="">'.GetMessage("CC_BCF_ALL").'</option>';
				$list[""] = GetMessage("CC_BCF_ALL");
				foreach($arProp["VALUE_LIST"] as $key=>$val)
				{
					$res .= '<option';

					if (($arProp["MULTIPLE"] == "Y") && is_array($value))
					{
						if(in_array($key, $value))
							$res .= ' selected';
					}
					else
					{
						if($key == $value)
							$res .= ' selected';
					}

					$res .= ' value="'.htmlspecialcharsbx($key).'">'.htmlspecialcharsbx($val).'</option>';
					$list[$key] = $val;
				}
				$res .= '</select>';
			}

			if ($arProp["MULTIPLE"]=="Y")
			{
				if (is_array($value) && count($value)>0)
					${$FILTER_NAME}["OFFERS"]["PROPERTY"][$arProp["CODE"]] = $value;
			}
			else
			{
				if (strlen($value)>0)
					${$FILTER_NAME}["OFFERS"]["PROPERTY"][$arProp["CODE"]] = $value;
			}

			break;
		case "N":

			$name_left = $FILTER_NAME."_op[".$arProp["CODE"]."][LEFT]";
			$value = $arrOPFV[$arProp["CODE"]]["LEFT"];
			$res .= '<input type="text" name="'.$name_left.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />&nbsp;'.GetMessage("CC_BCF_TILL").'&nbsp;';

			if (strlen($value)>0)
				${$FILTER_NAME}["OFFERS"]["PROPERTY"][">=".$arProp["CODE"]] = intval($value);

			$name_right = $FILTER_NAME."_op[".$arProp["CODE"]."][RIGHT]";
			$value = $arrOPFV[$arProp["CODE"]]["RIGHT"];
			$res .= '<input type="text" name="'.$name_right.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';

			if (strlen($value)>0)
				${$FILTER_NAME}["OFFERS"]["PROPERTY"]["<=".$arProp["CODE"]] = doubleval($value);

			$type = 'RANGE';
			$names = array($name_left, $name_right);
			$values = array(htmlspecialcharsbx($value_left), htmlspecialcharsbx($value_right));
			break;
		case "S":
		case "E":
		case "G":

			$name = $FILTER_NAME."_op[".$arProp["CODE"]."]";
			$value = $arrOPFV[$arProp["CODE"]];
			$res .= '<input type="text" name="'.$name.'" size="'.$arParams["TEXT_WIDTH"].'" value="'.htmlspecialcharsbx($value).'" />';

			if (strlen($value)>0)
				${$FILTER_NAME}["OFFERS"]["PROPERTY"]["?".$arProp["CODE"]] = $value;

			$type = 'INPUT';
			break;
	}
	if($res)
	{
		$bHasOffersFilter = true;
		$arResult["ITEMS"]["OFFER_PROPERTY_".$prop_id] = array(
			"NAME" => htmlspecialcharsbx($arProp["NAME"]),
			"INPUT" => $res,
			"INPUT_NAME" => $name,
			"INPUT_VALUE" => htmlspecialcharsbx($value),
			"~INPUT_VALUE" => $value,
			"TYPE" => $type,
			"INPUT_NAMES" => $names,
			"INPUT_VALUES" => is_array($values)? array_map("htmlspecialcharsbx", $values): htmlspecialcharsbx($values),
			"~INPUT_VALUES" => $values,
			"LIST" => $list,
		);
	}
}

if($bHasOffersFilter)
{
	//This will force to use catalog.section offers price filter
	if(!isset(${$FILTER_NAME}["OFFERS"]))
		${$FILTER_NAME}["OFFERS"] = array();
}

foreach($arResult["arrPrice"] as $price_code => $arPrice)
{
	$res_price = "";
	$arResult["arrInputNames"][$FILTER_NAME."_cf"]=true;

	$name_left = $FILTER_NAME."_cf[".$arPrice["ID"]."][LEFT]";
	$value_left = $arrCFV[$arPrice["ID"]]["LEFT"];

	if (strlen($value_left)>0)
	{
		if ($arResult['MODULES']['catalog'])
			${$FILTER_NAME}[">=CATALOG_PRICE_".$arPrice["ID"]] = $value_left;
		else
			${$FILTER_NAME}[">=PROPERTY_".$arPrice["ID"]] = $value_left;
	}

	$res_price .= '<input type="text" name="'.$name_left.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_left).'" />&nbsp;'.GetMessage("CC_BCF_TILL").'&nbsp;';

	$name_right = $FILTER_NAME."_cf[".$arPrice["ID"]."][RIGHT]";
	$value_right = $arrCFV[$arPrice["ID"]]["RIGHT"];

	if (strlen($value_right)>0)
	{
		if ($arResult['MODULES']['catalog'])
			${$FILTER_NAME}["<=CATALOG_PRICE_".$arPrice["ID"]] = $value_right;
		else
			${$FILTER_NAME}["<=PROPERTY_".$arPrice["ID"]] = $value_right;
	}

	$res_price .= '<input type="text" name="'.$name_right.'" size="'.$arParams["NUMBER_WIDTH"].'" value="'.htmlspecialcharsbx($value_right).'" />';

	$arResult["ITEMS"]["PRICE_".$price_code] = array(
		"NAME" => htmlspecialcharsbx($arPrice["TITLE"]),
		"INPUT" => $res_price,
		"TYPE" => "RANGE",
		"INPUT_NAMES" => array($name_left, $name_right),
		"INPUT_VALUES" => array(htmlspecialcharsbx($value_left), htmlspecialcharsbx($value_right)),
		"~INPUT_VALUES" => array($value_left, $value_right),
		"LIST" => array(),
	);

}

if (
	!empty($arParams["PAGER_PARAMS_NAME"])
	&& preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"])
)
{
	if (!is_array($GLOBALS[$arParams["PAGER_PARAMS_NAME"]]))
		$GLOBALS[$arParams["PAGER_PARAMS_NAME"]] = array();

	foreach ($arResult["ITEMS"] as $arItem)
	{
		if (isset($arItem["INPUT_NAMES"]) && is_array($arItem["INPUT_NAMES"]))
		{
			foreach ($arItem["INPUT_NAMES"] as $i => $name)
			{
				$value = $arItem["~INPUT_VALUES"][$i];
				if (strlen($value) > 0)
				{
					$GLOBALS[$arParams["PAGER_PARAMS_NAME"]][$name] = $value;
				}
			}
		}
		elseif (isset($arItem["INPUT_NAME"]) && is_array($arItem["~INPUT_VALUE"]))
		{
			foreach ($arItem["~INPUT_VALUE"] as $value)
			{
				if (strlen($value) > 0)
				{
					$GLOBALS[$arParams["PAGER_PARAMS_NAME"]][$arItem["INPUT_NAME"]][] = $value;
				}
			}
		}
		elseif (isset($arItem["INPUT_NAME"]) && strlen($arItem["~INPUT_VALUE"]) > 0)
		{
			$GLOBALS[$arParams["PAGER_PARAMS_NAME"]][$arItem["INPUT_NAME"]] = $arItem["~INPUT_VALUE"];
		}
	}

	if (strlen($_REQUEST["del_filter"]) > 0)
	{
		//$GLOBALS[$arParams["PAGER_PARAMS_NAME"]]["del_filter"] = $_REQUEST["del_filter"];
	}
	elseif (strlen($_REQUEST["set_filter"]) > 0)
	{
		$GLOBALS[$arParams["PAGER_PARAMS_NAME"]]["set_filter"] = $_REQUEST["set_filter"];
	}
}

$arResult["arrInputNames"]["set_filter"]=true;
$arResult["arrInputNames"]["del_filter"]=true;

$arSkip = array(
	"AUTH_FORM" => true,
	"TYPE" => true,
	"USER_LOGIN" => true,
	"USER_CHECKWORD" => true,
	"USER_PASSWORD" => true,
	"USER_CONFIRM_PASSWORD" => true,
	"USER_EMAIL" => true,
	"captcha_word" => true,
	"captcha_sid" => true,
	"login" => true,
	"Login" => true,
	"backurl" => true,
);

foreach(array_merge($_GET, $_POST) as $key=>$value)
{
	if(
		!isset($arResult["arrInputNames"][$key])
		&& !isset($arSkip[$key])
	)
	{
		$arResult["ITEMS"]["HIDDEN_".htmlspecialcharsEx($key)] = array(
			"HIDDEN" => true,
			"INPUT" => '<input type="hidden" name="'.htmlspecialcharsbx($key).'" value="'.htmlspecialcharsbx($value).'" />',
		);
	}
}

$this->IncludeComponentTemplate();
?>