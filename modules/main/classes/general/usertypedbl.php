<?
IncludeModuleLangFile(__FILE__);

class CUserTypeDouble extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'double';

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_DOUBLE_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_DOUBLE,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "double";
			case "oracle":
				return "number";
			case "mssql":
				return "float";
		}
		return null;
	}

	function PrepareSettings($arUserField)
	{
		$prec = intval($arUserField["SETTINGS"]["PRECISION"]);
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$min = doubleval($arUserField["SETTINGS"]["MIN_VALUE"]);
		$max = doubleval($arUserField["SETTINGS"]["MAX_VALUE"]);

		return array(
			"PRECISION" => ($prec < 0? 0: ($prec > 12? 12: $prec)),
			"SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
			"MIN_VALUE" => $min,
			"MAX_VALUE" => $max,
			"DEFAULT_VALUE" => strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0? doubleval($arUserField["SETTINGS"]["DEFAULT_VALUE"]): "",
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["PRECISION"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["PRECISION"]);
		else
			$value = 4;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_DOUBLE_PRECISION").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[PRECISION]" size="20"  maxlength="225" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = doubleval($GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]);
		elseif(is_array($arUserField))
			$value = doubleval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		else
			$value = "";
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_DOUBLE_DEFAULT_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="20"  maxlength="225" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["SIZE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["SIZE"]);
		else
			$value = 20;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_DOUBLE_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = doubleval($GLOBALS[$arHtmlControl["NAME"]]["MIN_VALUE"]);
		elseif(is_array($arUserField))
			$value = doubleval($arUserField["SETTINGS"]["MIN_VALUE"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_DOUBLE_MIN_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_VALUE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = doubleval($GLOBALS[$arHtmlControl["NAME"]]["MAX_VALUE"]);
		elseif(is_array($arUserField))
			$value = doubleval($arUserField["SETTINGS"]["MAX_VALUE"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_DOUBLE_MAX_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_VALUE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
			$arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		if(strlen($arHtmlControl["VALUE"])>0)
			$arHtmlControl["VALUE"] = round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
		$arHtmlControl["VALIGN"] = "middle";
		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.$arHtmlControl["VALUE"].'" '.
			($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
			'>';
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		if(strlen($arHtmlControl["VALUE"]))
			$value = round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
		else
			$value = "";

		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.$value.'" '.
			'>';
	}

	function GetFilterData($arUserField, $arHtmlControl)
	{
		return array(
			"id" => $arHtmlControl["ID"],
			"name" => $arHtmlControl["NAME"],
			"type" => "number",
			"filterable" => ""
		);
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		if(strlen($arHtmlControl["VALUE"])>0)
			return round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
		else
			return '&nbsp;';
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]).'" '.
			'>';
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();

		$value = str_replace(array(',', ' '), array('.', ''), $value);

		if(strlen($value)>0 && $arUserField["SETTINGS"]["MIN_VALUE"]!=0 && doubleval($value)<$arUserField["SETTINGS"]["MIN_VALUE"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_DOUBLE_MIN_VALUE_ERROR",
					array(
						"#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
						"#MIN_VALUE#"=>$arUserField["SETTINGS"]["MIN_VALUE"]
					)
				),
			);
		}
		if(strlen($value)>0 && $arUserField["SETTINGS"]["MAX_VALUE"]<>0 && doubleval($value)>$arUserField["SETTINGS"]["MAX_VALUE"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_DOUBLE_MAX_VALUE_ERROR",
					array(
						"#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
						"#MAX_VALUE#"=>$arUserField["SETTINGS"]["MAX_VALUE"]
					)
				),
			);
		}
		return $aMsg;
	}

	function OnSearchIndex($arUserField)
	{
		if(is_array($arUserField["VALUE"]))
			return implode("\r\n", $arUserField["VALUE"]);
		else
			return $arUserField["VALUE"];
	}

	function OnBeforeSave($arUserField, $value)
	{
		$value = str_replace(array(',', ' '), array('.', ''), $value);
		if(strlen($value)>0)
		{
			return "".round(doubleval($value), $arUserField["SETTINGS"]["PRECISION"]);
		}
		return null;
	}


	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;
		foreach($value as $res)
		{
			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			if(strlen($arUserField['PROPERTY_VALUE_LINK']) > 0)
			{
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', urlencode($res), $arUserField['PROPERTY_VALUE_LINK'])).'">'.$res.'</a>';
			}

			$html .= static::getHelper()->wrapSingleField($res);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}


	public function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$html = '';

		foreach($value as $res)
		{
			$attrList = array();

			if($arUserField["EDIT_IN_LIST"] != "Y")
			{
				$attrList['disabled'] = 'disabled';
			}

			if($arUserField["SETTINGS"]["SIZE"] > 0)
			{
				$attrList['size'] = intval($arUserField["SETTINGS"]["SIZE"]);
			}

			if(array_key_exists('attribute', $arAdditionalParameters))
			{
				$attrList = array_merge($attrList, $arAdditionalParameters['attribute']);
			}

			if(isset($attrList['class']) && is_array($attrList['class']))
			{
				$attrList['class'] = implode(' ', $attrList['class']);
			}

			$attrList['class'] = static::getHelper()->getCssClassName().(isset($attrList['class']) ? ' '.$attrList['class'] : '');

			$attrList['name'] = $fieldName;

			$attrList['type'] = 'text';
			$attrList['tabindex'] = '0';
			$attrList['value'] = $res;

			$html .= static::getHelper()->wrapSingleField('<input '.static::buildTagAttributes($attrList).'/>');
		}

		if($arUserField["MULTIPLE"] == "Y" && $arAdditionalParameters["SHOW_BUTTON"] != "N")
		{
			$html .= static::getHelper()->getCloneButton($fieldName);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}


}
