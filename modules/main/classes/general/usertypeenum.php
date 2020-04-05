<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */
IncludeModuleLangFile(__FILE__);

class CUserTypeEnum extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = "enumeration";

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_ENUM_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_ENUM,
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "int(18)";
			case "oracle":
				return "number(18)";
			case "mssql":
				return "int";
		}
		return "int";
	}

	function PrepareSettings($arUserField)
	{
		$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		$disp = $arUserField["SETTINGS"]["DISPLAY"];
		$caption_no_value = trim($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]);
		$show_no_value = $arUserField["SETTINGS"]["SHOW_NO_VALUE"] === 'N' ? 'N' : 'Y';

		if($disp !== "CHECKBOX" && $disp !== "LIST" && $disp !== 'UI')
		{
			$disp = "LIST";
		}

		return array(
			"DISPLAY" => $disp,
			"LIST_HEIGHT" => ($height < 1? 1: $height),
			"CAPTION_NO_VALUE" => $caption_no_value, // no default value - only in output
			"SHOW_NO_VALUE" => $show_no_value, // no default value - only in output
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DISPLAY"];
		else
			$value = "LIST";
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_ENUM_DISPLAY").':</td>
			<td>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="LIST" '.("LIST"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_ENUM_LIST").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="CHECKBOX" '.("CHECKBOX"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_ENUM_CHECKBOX").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="UI" '.("UI"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_ENUM_UI").'</label><br>
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		else
			$value = 5;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_ENUM_LIST_HEIGHT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_HEIGHT]" size="10" value="'.$value.'">
			</td>
		</tr>
		';

		if($bVarsFromForm)
			$value = trim($GLOBALS[$arHtmlControl["NAME"]]["CAPTION_NO_VALUE"]);
		elseif(is_array($arUserField))
			$value = trim($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]);
		else
			$value = '';
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_ENUM_CAPTION_NO_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[CAPTION_NO_VALUE]" size="10" value="'.htmlspecialcharsbx($value).'">
			</td>
		</tr>
		';

		if($bVarsFromForm)
			$value = trim($GLOBALS[$arHtmlControl["NAME"]]["SHOW_NO_VALUE"]);
		elseif(is_array($arUserField))
			$value = trim($arUserField["SETTINGS"]["SHOW_NO_VALUE"]);
		else
			$value = '';
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_ENUM_SHOW_NO_VALUE").':</td>
			<td>
				<input type="hidden" name="'.$arHtmlControl["NAME"].'[SHOW_NO_VALUE]" value="N" />
				<label><input type="checkbox" name="'.$arHtmlControl["NAME"].'[SHOW_NO_VALUE]" value="Y" '.($value === 'N' ? '' : ' checked="checked"').' /> '.GetMessage('MAIN_YES').'</label>
			</td>
		</tr>
		';

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
			$arHtmlControl["VALUE"] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

		$result = '';
		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arUserField,
			)
		);
		if(!$rsEnum)
			return '';

		if($arUserField["SETTINGS"]["DISPLAY"]=="UI")
		{
			CJSCore::Init('ui');

			$startValue = array(
				'NAME' => strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0
					? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"]
					: GetMessage('MAIN_NO'),
				'VALUE' => '',
			);

			$itemList = array();
			if($arUserField["MANDATORY"] != "Y")
			{
				$itemList[] = $startValue;
			}

			while($arEnum = $rsEnum->GetNext())
			{
				$item = array(
					'NAME' => $arEnum["VALUE"],
					'VALUE' => $arEnum["ID"],
				);

				if(
					$arHtmlControl["VALUE"] == $arEnum["ID"]
					|| $arUserField["ENTITY_VALUE_ID"] <= 0 && $arEnum["DEF"] == "Y"
				)
				{
					$startValue = $item;
				}

				$itemList[] = $item;
			}

			$params = \Bitrix\Main\Web\Json::encode(array(
				'isMulti' => false,
				'fieldName' => $arUserField['FIELD_NAME']
			));
			$items = \Bitrix\Main\Web\Json::encode($itemList);
			$value = \Bitrix\Main\Web\Json::encode($startValue);

			$controlNodeId = $arUserField['FIELD_NAME'].'_control';
			$valueContainerId = $arUserField['FIELD_NAME'].'_value';

			$fieldNameJS = \CUtil::JSEscape($arUserField['FIELD_NAME']);
			$htmlFieldNameJS = \CUtil::JSEscape($arHtmlControl["NAME"]);
			$controlNodeIdJS = \CUtil::JSEscape($controlNodeId);
			$valueContainerIdJS = \CUtil::JSEscape($valueContainerId);

			$result .= '<input type="hidden" name="'.$arHtmlControl["NAME"].'" value="'.\Bitrix\Main\Text\HtmlFilter::encode($startValue['VALUE']).'" id="'.$valueContainerId.'">';

			$result .= <<<EOT
<span id="{$controlNodeId}"></span>
<script>
function changeHandler_{$fieldNameJS}(controlObject, value)
{
	if(controlObject.params.fieldName === '{$fieldNameJS}')
	{
		var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));

		if(BX.type.isPlainObject(currentValue))
		{
			BX('{$valueContainerIdJS}').value = currentValue['VALUE'];
		}
	}
}

BX.ready(function(){

	var params = {$params};

	BX('{$controlNodeIdJS}').appendChild(BX.decl({
		block: 'main-ui-select',
		name: '{$fieldNameJS}',
		items: {$items},
		value: {$value},
		params: params,
		valueDelete: false
	}));

	BX.addCustomEvent(
		window,
		'UI::Select::change',
		changeHandler_{$fieldNameJS}
	);

	BX.bind(BX('{$controlNodeIdJS}'), 'click', BX.defer(function(){
		changeHandler_{$fieldNameJS}(
		{
			params: params,
			node: BX('{$controlNodeIdJS}').firstChild
		});
	}));
});
</script>
EOT;
		}
		elseif($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
		{
			$bWasSelect = false;
			$result2 = '';
			while($arEnum = $rsEnum->GetNext())
			{
				$bSelected = (
					($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
					($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
				);
				$bWasSelect = $bWasSelect || $bSelected;
				$result2 .= '<label><input type="radio" value="'.$arEnum["ID"].'" name="'.$arHtmlControl["NAME"].'"'.($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.$arEnum["VALUE"].'</label><br>';
			}
			if($arUserField["MANDATORY"]!="Y")
				$result .= '<label><input type="radio" value="" name="'.$arHtmlControl["NAME"].'"'.(!$bWasSelect? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</label><br>';
			$result .= $result2;
		}
		else
		{
			$bWasSelect = false;
			$result2 = '';
			while($arEnum = $rsEnum->GetNext())
			{
				$bSelected = (
					($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
					($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
				);
				$bWasSelect = $bWasSelect || $bSelected;
				$result2 .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["VALUE"].'</option>';
			}

			if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
			{
				$size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';
			}
			else
			{
				$arHtmlControl["VALIGN"] = "middle";
				$size = '';
			}

			$result = '<select name="'.$arHtmlControl["NAME"].'"'.$size.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
			if($arUserField["MANDATORY"]!="Y")
			{
				$result .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
			}
			$result .= $result2;
			$result .= '</select>';
		}
		return $result;
	}

	function GetGroupActionData($arUserField, $arHtmlControl)
	{
		$result = array();
		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array($arUserField)
		);
		if(!$rsEnum)
			return $result;

		while($arEnum = $rsEnum->GetNext())
			$result[] = array("NAME" => $arEnum["VALUE"], "VALUE" => $arEnum["ID"]);

		return $result;
	}

	function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
			$arHtmlControl["VALUE"] = array(intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]));
		elseif(!is_array($arHtmlControl["VALUE"]))
			$arHtmlControl["VALUE"] = array();

		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arUserField,
			)
		);
		if(!$rsEnum)
			return '';

		$result = '';

		if($arUserField["SETTINGS"]["DISPLAY"] == "UI")
		{
			\CJSCore::Init('ui');

			$emptyValue = array(
				'NAME' => strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0
					? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"]
					: GetMessage('MAIN_NO'),
				'VALUE' => '',
			);

			$startValue = array();
			$itemList = array();
			if($arUserField["MANDATORY"] != "Y")
			{
				$itemList[] = $emptyValue;
			}

			while($arEnum = $rsEnum->GetNext())
			{
				$item = array(
					'NAME' => $arEnum["VALUE"],
					'VALUE' => $arEnum["ID"],
				);

				if(
					in_array($arEnum["ID"], $arHtmlControl["VALUE"])
					|| $arUserField["ENTITY_VALUE_ID"] <= 0 && $arEnum["DEF"] == "Y"
				)
				{
					$startValue[] = $item;
				}

				$itemList[] = $item;
			}

			if(count($startValue) <= 0 && $arUserField['MANDATORY'] != 'Y')
			{
				$startValue[] = $emptyValue;
			}

			$params = \Bitrix\Main\Web\Json::encode(array(
				'isMulti' => true,
				'fieldName' => $arUserField['FIELD_NAME']
			));
			$items = \Bitrix\Main\Web\Json::encode($itemList);
			$value = \Bitrix\Main\Web\Json::encode($startValue);

			$controlNodeId = $arUserField['FIELD_NAME'].'_control';
			$valueContainerId = $arUserField['FIELD_NAME'].'_value';

			$fieldNameJS = \CUtil::JSEscape($arUserField['FIELD_NAME']);
			$htmlFieldNameJS = \CUtil::JSEscape($arHtmlControl["NAME"]);
			$controlNodeIdJS = \CUtil::JSEscape($controlNodeId);
			$valueContainerIdJS = \CUtil::JSEscape($valueContainerId);

			$result .= '<span id="'.\Bitrix\Main\Text\HtmlFilter::encode($valueContainerId).'" style="display: none">';

			for($i = 0, $n = count($startValue); $i < $n; $i++)
			{
				$result .= '<input type="hidden" name="'.$arHtmlControl["NAME"].'" value="'.\Bitrix\Main\Text\HtmlFilter::encode($startValue[$i]['VALUE']).'" />';
			}

			$result .= '</span>';

			$result .= <<<EOT
<span id="{$controlNodeId}"></span>
<script>
function changeHandler_{$fieldNameJS}(controlObject, value)
{
	if(controlObject.params.fieldName === '{$fieldNameJS}')
	{
		var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));

		var s = '';
		if(BX.type.isArray(currentValue))
		{
			if(currentValue.length > 0)
			{
				for(var i = 0; i < currentValue.length; i++)
				{
					s += '<input type="hidden" name="{$htmlFieldNameJS}" value="'+BX.util.htmlspecialchars(currentValue[i].VALUE)+'" />';
				}
			}
			else
			{
				s += '<input type="hidden" name="{$htmlFieldNameJS}" value="" />';
			}

			BX('{$valueContainerIdJS}').innerHTML = s;
		}
	}
}

BX.ready(function(){

	var params = {$params};

	BX('{$controlNodeIdJS}').appendChild(BX.decl({
		block: 'main-ui-multi-select',
		name: '{$fieldNameJS}',
		items: {$items},
		value: {$value},
		params: params,
		valueDelete: true
	}));

	BX.addCustomEvent(
		window,
		'UI::Select::change',
		changeHandler_{$fieldNameJS}
	);

	BX.bind(BX('{$controlNodeIdJS}'), 'click', BX.defer(function(){
		changeHandler_{$fieldNameJS}(
		{
			params: params,
			node: BX('{$controlNodeIdJS}').firstChild
		});
	}));
});
</script>
EOT;
		}
		elseif($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
		{
			$result .= '<input type="hidden" value="" name="'.$arHtmlControl["NAME"].'">';
			$bWasSelect = false;
			while($arEnum = $rsEnum->GetNext())
			{
				$bSelected = (
					(in_array($arEnum["ID"], $arHtmlControl["VALUE"])) ||
					($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
				);
				$bWasSelect = $bWasSelect || $bSelected;
				$result .= '<label><input type="checkbox" value="'.$arEnum["ID"].'" name="'.$arHtmlControl["NAME"].'"'.($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.$arEnum["VALUE"].'</label><br>';
			}
		}
		else
		{
			$result = '<select multiple name="'.$arHtmlControl["NAME"].'" size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': ''). '>';

			if($arUserField["MANDATORY"] <> "Y")
			{
				$result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
			}
			while($arEnum = $rsEnum->GetNext())
			{
				$bSelected = (
					(in_array($arEnum["ID"], $arHtmlControl["VALUE"])) ||
					($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
				);
				$result .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["VALUE"].'</option>';
			}
			$result .= '</select>';
		}
		return $result;
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		if(!is_array($arHtmlControl["VALUE"]))
			$arHtmlControl["VALUE"] = array();

		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arUserField,
			)
		);
		if(!$rsEnum)
			return '';

		if($arUserField["SETTINGS"]["LIST_HEIGHT"] < 5)
			$size = ' size="5"';
		else
			$size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';

		$result = '<select multiple name="'.$arHtmlControl["NAME"].'[]"'.$size.'>';
		$result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.GetMessage("MAIN_ALL").'</option>';
		while($arEnum = $rsEnum->GetNext())
		{
			$result .= '<option value="'.$arEnum["ID"].'"'.(in_array($arEnum["ID"], $arHtmlControl["VALUE"])? ' selected': '').'>'.$arEnum["VALUE"].'</option>';
		}
		$result .= '</select>';
		return $result;
	}

	function GetFilterData($arUserField, $arHtmlControl)
	{
		$rsEnum = call_user_func_array(array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"), array($arUserField));
		$items = array();
		if ($rsEnum)
		{
			while($arEnum = $rsEnum->GetNext())
				$items[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		return array(
			"id" => $arHtmlControl["ID"],
			"name" => $arHtmlControl["NAME"],
			"type" => "list",
			"items" => $items,
			"params" => array("multiple" => "Y"),
			"filterable" => ""
		);
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		static $cache = array();
		$empty_caption = '&nbsp;';//strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? htmlspecialcharsbx($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) : '&nbsp;';

		if(!array_key_exists($arHtmlControl["VALUE"], $cache))
		{
			$rsEnum = call_user_func_array(
				array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
				array(
					$arUserField,
				)
			);
			if(!$rsEnum)
				return $empty_caption;
			while($arEnum = $rsEnum->GetNext())
				$cache[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		if(!array_key_exists($arHtmlControl["VALUE"], $cache))
			$cache[$arHtmlControl["VALUE"]] = $empty_caption;
		return $cache[$arHtmlControl["VALUE"]];
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arUserField,
			)
		);
		if(!$rsEnum)
			return '';

		if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
			$size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';
		else
			$size = '';

		$result = '<select name="'.$arHtmlControl["NAME"].'"'.$size.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
		if($arUserField["MANDATORY"]!="Y")
		{
			$result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
		}
		while($arEnum = $rsEnum->GetNext())
		{
			$result .= '<option value="'.$arEnum["ID"].'"'.($arHtmlControl["VALUE"]==$arEnum["ID"]? ' selected': '').'>'.$arEnum["VALUE"].'</option>';
		}
		$result .= '</select>';
		return $result;
	}

	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
	{
		if(!is_array($arHtmlControl["VALUE"]))
			$arHtmlControl["VALUE"] = array();

		$rsEnum = call_user_func_array(
			array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
			array(
				$arUserField,
			)
		);
		if(!$rsEnum)
			return '';

		$result = '<select multiple name="'.$arHtmlControl["NAME"].'" size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
		if($arUserField["MANDATORY"]!="Y")
		{
			$result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
		}
		while($arEnum = $rsEnum->GetNext())
		{
			$result .= '<option value="'.$arEnum["ID"].'"'.(in_array($arEnum["ID"], $arHtmlControl["VALUE"])? ' selected': '').'>'.$arEnum["VALUE"].'</option>';
		}
		$result .= '</select>';
		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}

	function GetList($arUserField)
	{
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID"=>$arUserField["ID"]));
		return $rsEnum;
	}

	/**
	 * Returns values from multiple enumerations by their ID.
	 * @param array[] $userFields It has to have the "ID" keys in subarrays.
	 * @return bool|CDBResult
	 */
	public static function GetListMultiple(array $userFields)
	{
		$ids = array();
		foreach ($userFields as $field)
		{
			$ids[] = $field["ID"];
		}
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(
			array("USER_FIELD_ID" => "ASC", "SORT" => "ASC", "ID" => "ASC"),
			array("USER_FIELD_ID" => $ids));
		return $rsEnum;
	}

	function OnSearchIndex($arUserField)
	{
		$res = '';

		if(is_array($arUserField["VALUE"]))
			$val = $arUserField["VALUE"];
		else
			$val = array($arUserField["VALUE"]);

		$val = array_filter($val, "strlen");
		if(count($val))
		{
			$ob = new CUserFieldEnum;
			$rs = $ob->GetList(array(), array(
				"USER_FIELD_ID" => $arUserField["ID"],
				"ID" => $val,
			));

			while($ar = $rs->Fetch())
				$res .= $ar["VALUE"]."\r\n";
		}

		return $res;
	}

	protected static function getEnumList(&$arUserField, $arParams = array())
	{
		$enum = array();

		$showNoValue = $arUserField["MANDATORY"] != "Y"
			|| $arUserField['SETTINGS']['SHOW_NO_VALUE'] != 'N'
			|| (isset($arParams["SHOW_NO_VALUE"]) && $arParams["SHOW_NO_VALUE"] == true);

		if($showNoValue
			&& ($arUserField["SETTINGS"]["DISPLAY"] != "CHECKBOX" || $arUserField["MULTIPLE"] <> "Y")
		)
		{
			$enum = array(null => htmlspecialcharsbx(static::getEmptyCaption($arUserField)));
		}

		$obEnum = new \CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => $arUserField["ID"]));

		while($arEnum = $rsEnum->Fetch())
		{
			$enum[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		$arUserField["USER_TYPE"]["FIELDS"] = $enum;
	}

	protected static function getEmptyCaption($arUserField)
	{
		return $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] <> ''
			? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"]
			: GetMessage("USER_TYPE_ENUM_NO_VALUE");
	}

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		static::getEnumList($arUserField, $arAdditionalParameters);

		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;
		$empty = true;

		foreach($value as $res)
		{
			if(array_key_exists($res, $arUserField["USER_TYPE"]["FIELDS"]))
			{
				$textRes = $arUserField['USER_TYPE']['FIELDS'][$res];
				$empty = false;
			}
			else
			{
				continue;
			}

			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			if(strlen($arUserField['PROPERTY_VALUE_LINK']) > 0)
			{
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', $res, $arUserField['PROPERTY_VALUE_LINK'])).'">'.htmlspecialcharsbx($textRes).'</a>';
			}
			else
			{
				$res = htmlspecialcharsbx($textRes);
			}

			$html .= static::getHelper()->wrapSingleField($res);
		}

		if($empty)
		{
			$html .= static::getHelper()->wrapSingleField(
				htmlspecialcharsbx(static::getEmptyCaption($arUserField))
			);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		static::getEnumList($userField);

		$value = static::normalizeFieldValue($userField['VALUE']);

		$text = '';
		$first = true;
		$empty = true;

		foreach ($value as $res)
		{
			if (array_key_exists($res, $userField['USER_TYPE']['FIELDS']))
			{
				if (!$first)
					$text .= ', ';
				$first = false;

				$text .= $userField['USER_TYPE']['FIELDS'][$res];
				$empty = false;
			}
		}

		if ($empty)
		{
			$text = static::getEmptyCaption($userField);
		}

		return $text;
	}

	public function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		static::getEnumList($arUserField, $arAdditionalParameters);

		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$bWasSelect = false;

		$html = '<input type="hidden" name="'.htmlspecialcharsbx($fieldName).'" value="" id="'.htmlspecialcharsbx($arUserField['FIELD_NAME']).'_default" />';
		if($arUserField["SETTINGS"]["DISPLAY"] == "UI")
		{
			\CJSCore::Init('ui');

			$startValue = array();
			$itemList = array();

			foreach($arUserField['USER_TYPE']['FIELDS'] as $key => $val)
			{
				if($key === '' && $arUserField['MULTIPLE'] === 'Y')
				{
					continue;
				}

				$item = array(
					'NAME' => $val,
					'VALUE' => $key,
				);

				if(in_array($key, $value))
				{
					$startValue[] = $item;
				}

				$itemList[] = $item;
			}

			$params = \Bitrix\Main\Web\Json::encode(array(
				'isMulti' => $arUserField['MULTIPLE'] === 'Y',
				'fieldName' => $arUserField['FIELD_NAME']
			));

			$result = '';

			$controlNodeId = $arUserField['FIELD_NAME'].'_control';
			$valueContainerId = $arUserField['FIELD_NAME'].'_value';

			$attrList = array(
				'id' => $valueContainerId,
				'style' => 'display: none'
			);

			$result .= '<span '.static::buildTagAttributes($attrList).'>';

			for($i = 0, $n = count($startValue); $i < $n; $i++)
			{
				$attrList = array(
					'type' => 'hidden',
					'name' => $fieldName,
					'value' => $startValue[$i]['VALUE'],
				);

				$result .= '<input '.static::buildTagAttributes($attrList).' />';
			}

			$result .= '</span>';

			if($arUserField['MULTIPLE'] !== 'Y')
			{
				$startValue = $startValue[0];
			}

			$items = \Bitrix\Main\Web\Json::encode($itemList);
			$currentValue = \Bitrix\Main\Web\Json::encode($startValue);

			$fieldNameJS = \CUtil::JSEscape($arUserField['FIELD_NAME']);
			$htmlFieldNameJS = \CUtil::JSEscape($fieldName);
			$controlNodeIdJS = \CUtil::JSEscape($controlNodeId);
			$valueContainerIdJS = \CUtil::JSEscape($valueContainerId);
			$block = $arUserField['MULTIPLE'] === 'Y' ? 'main-ui-multi-select' : 'main-ui-select';

			$result .= <<<EOT
<span id="{$controlNodeId}"></span>
<script>
function changeHandler_{$fieldNameJS}(controlObject, value)
{
	if(controlObject.params.fieldName === '{$fieldNameJS}')
	{
		var currentValue = JSON.parse(controlObject.node.getAttribute('data-value'));

		var s = '';
		if(!BX.type.isArray(currentValue))
		{
			if(currentValue === null)
			{
				currentValue = [{VALUE:''}];
			}
			else
			{
				currentValue = [currentValue];
			}
		}

		if(currentValue.length > 0)
		{
			for(var i = 0; i < currentValue.length; i++)
			{
				s += '<input type="hidden" name="{$htmlFieldNameJS}" value="'+BX.util.htmlspecialchars(currentValue[i].VALUE)+'" />';
			}
		}
		else
		{
			s += '<input type="hidden" name="{$htmlFieldNameJS}" value="" />';
		}

		BX('{$valueContainerIdJS}').innerHTML = s;
		BX.fireEvent(BX('{$fieldNameJS}_default'), 'change');
	}
}

BX.ready(function(){

	var params = {$params};

	BX('{$controlNodeIdJS}').appendChild(BX.decl({
		block: '{$block}',
		name: '{$fieldNameJS}',
		items: {$items},
		value: {$currentValue},
		params: params,
		valueDelete: false
	}));

	BX.addCustomEvent(
		window,
		'UI::Select::change',
		changeHandler_{$fieldNameJS}
	);

	BX.bind(BX('{$controlNodeIdJS}'), 'click', BX.defer(function(){
		changeHandler_{$fieldNameJS}(
		{
			params: params,
			node: BX('{$controlNodeIdJS}').firstChild
		});
	}));
});
</script>
EOT;

			$html .= static::getHelper()->wrapSingleField($result);
		}
		elseif($arUserField["SETTINGS"]["DISPLAY"] == "CHECKBOX")
		{
			$first = true;
			foreach($arUserField["USER_TYPE"]["FIELDS"] as $key => $val)
			{
				$tag = '';

				if($first)
				{
					$first = false;
				}
				else
				{
					$tag .= static::getHelper()->getMultipleValuesSeparator();
				}

				$bSelected = in_array($key, $value) && (
						(!$bWasSelect) ||
						($arUserField["MULTIPLE"] == "Y")
					);
				$bWasSelect = $bWasSelect || $bSelected;

				$attrList = array(
					'type' => $arUserField['MULTIPLE'] === 'Y' ? 'checkbox' : 'radio',
					'value' => $key,
					'name' => $fieldName,
				);

				if($bSelected)
				{
					$attrList['checked'] = 'checked';
				}

				$attrList['tabindex'] = '0';

				$tag .= '<label><input '.static::buildTagAttributes($attrList).'>'.htmlspecialcharsbx($val).'</label><br />';
				$html .= static::getHelper()->wrapSingleField($tag, array(static::USER_TYPE_ID.'-checkbox'));
			}
		}
		else
		{
			$attrList = array(
				'name' => $fieldName,
				'tabindex' => '0',
			);

			if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
			{
				$attrList['size'] = $arUserField["SETTINGS"]["LIST_HEIGHT"];
			}

			if($arUserField["MULTIPLE"] == "Y")
			{
				$attrList['multiple'] = 'multiple';
			}

			$tag = '<select '.static::buildTagAttributes($attrList).'>';

			foreach($arUserField["USER_TYPE"]["FIELDS"] as $key => $val)
			{
				$bSelected = in_array(strval($key), $value, true) && (
						(!$bWasSelect) ||
						($arUserField["MULTIPLE"] == "Y")
					);
				$bWasSelect = $bWasSelect || $bSelected;

				$attrList = array(
					'value' => $key,
				);

				if($bSelected)
				{
					$attrList['selected'] = 'selected';
				}

				$tag .= '<option '.static::buildTagAttributes($attrList).'>'.htmlspecialcharsbx($val).'</option>';
			}
			$tag .= '</select>';

			$html .= static::getHelper()->wrapSingleField($tag, array(static::USER_TYPE_ID.($arUserField['MULTIPLE'] === 'Y' ? '-multiselect' : '-select')));
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}
}
