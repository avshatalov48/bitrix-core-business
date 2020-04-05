<?
IncludeModuleLangFile(__FILE__);

class CUserTypeInteger extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'integer';

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_INTEGER_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_INT,
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
				return "int(18)";
			case "oracle":
				return "number(18)";
			case "mssql":
				return "int";
		}
	}

	function PrepareSettings($arUserField)
	{
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$min = intval($arUserField["SETTINGS"]["MIN_VALUE"]);
		$max = intval($arUserField["SETTINGS"]["MAX_VALUE"]);

		return array(
			"SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
			"MIN_VALUE" => $min,
			"MAX_VALUE" => $max,
			"DEFAULT_VALUE" => strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0? intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]): "",
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		else
			$value = "";
		if(strlen($value) > 0)
			$value = intval($value);
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_INTEGER_DEFAULT_VALUE").':</td>
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
			<td>'.GetMessage("USER_TYPE_INTEGER_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MIN_VALUE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MIN_VALUE"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_INTEGER_MIN_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_VALUE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_VALUE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MAX_VALUE"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_INTEGER_MAX_VALUE").':</td>
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
			$arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
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
		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.$arHtmlControl["VALUE"].'"'.
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
			return $arHtmlControl["VALUE"];
		else
			return '&nbsp;';
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.$arHtmlControl["VALUE"].'" '.
			'>';
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		if(strlen($value)>0 && $arUserField["SETTINGS"]["MIN_VALUE"]!=0 && intval($value)<$arUserField["SETTINGS"]["MIN_VALUE"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_INTEGER_MIN_VALUE_ERROR",
					array(
						"#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
						"#MIN_VALUE#"=>$arUserField["SETTINGS"]["MIN_VALUE"]
					)
				),
			);
		}
		if(strlen($value)>0 && $arUserField["SETTINGS"]["MAX_VALUE"]<>0 && intval($value)>$arUserField["SETTINGS"]["MAX_VALUE"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_INTEGER_MAX_VALUE_ERROR",
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
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', intval($res), $arUserField['PROPERTY_VALUE_LINK'])).'">'.$res.'</a>';
			}
			else
			{
				$res = intval($res);
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
			$attrList['value'] = $res;
			$attrList['tabindex'] = '0';

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
?>