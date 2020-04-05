<?
IncludeModuleLangFile(__FILE__);

class CUserTypeStringFormatted extends \CUserTypeString
{
	const USER_TYPE_ID = "string_formatted";

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_STRINGFMT_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'), // inherited from string
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
		);
	}

	function PrepareSettings($arUserField)
	{
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$rows = intval($arUserField["SETTINGS"]["ROWS"]);
		$min = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		$max = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);

		return array(
			"SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
			"ROWS" =>  ($rows <= 1?  1: ($rows >  50?  50: $rows)),
			"REGEXP" => $arUserField["SETTINGS"]["REGEXP"],
			"MIN_LENGTH" => $min,
			"MAX_LENGTH" => $max,
			"DEFAULT_VALUE" => $arUserField["SETTINGS"]["DEFAULT_VALUE"],
			"PATTERN" => $arUserField["SETTINGS"]["PATTERN"],
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["PATTERN"]);
		elseif(is_array($arUserField))
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["PATTERN"]);
		else
			$value = "#VALUE#";

		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_STRINGFMT_PATTERN").':</td>
			<td>
				<textarea name="'.$arHtmlControl["NAME"].'[PATTERN]" cols="40" rows="5">'.$value.'</textarea>
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]);
		elseif(is_array($arUserField))
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		else
			$value = "";

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRINGFMT_DEFAULT_VALUE").':</td>
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
			<td>'.GetMessage("USER_TYPE_STRINGFMT_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["ROWS"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["ROWS"]);
		else
			$value = 1;
		if($value < 1) $value = 1;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRINGFMT_ROWS").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[ROWS]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MIN_LENGTH"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRINGFMT_MIN_LEGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_LENGTH"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRINGFMT_MAX_LENGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["REGEXP"]);
		elseif(is_array($arUserField))
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["REGEXP"]);
		else
			$value = "";
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRINGFMT_REGEXP").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[REGEXP]" size="20"  maxlength="200" value="'.$value.'">
			</td>
		</tr>
		';
		return $result;
	}

	public static function GetPublicViewHTML($arUserField, $arHtmlControl)
	{
		$val = $arHtmlControl["VALUE"];
		if (strlen(trim($val)) <= 0)
		{
			$val = '';
		}

		return $val;
	}


	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';

		$first = true;
		foreach($value as $i => $val)
		{
			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			else
			{
				$first = false;
			}

			$name = str_replace("[]", "[".$i."]", $arUserField["FIELD_NAME"]);
			if($val != "")
			{
				$html .= static::getHelper()->wrapSingleField(str_replace(
					array("#VALUE#"),
					array(
						static::GetPublicViewHTML(
							array(
								"SETTINGS" => $arUserField["SETTINGS"]
							),
							array(
								"NAME" => $name,
								"VALUE" => $val
							)
						)
					),
					$arUserField["SETTINGS"]["PATTERN"]
				));
			}
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}
}
