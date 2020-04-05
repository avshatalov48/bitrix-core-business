<?php
IncludeModuleLangFile(__FILE__);

class CUserTypeUrl extends \CUserTypeString
{
	const USER_TYPE_ID = "url";

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_URL_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'), // inherited from string
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
		);
	}

	function PrepareSettings($arUserField)
	{
		$popup = $arUserField["SETTINGS"]["POPUP"] === 'N' ? 'N' : 'Y';
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$min = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		$max = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);

		return array(
			"POPUP" => $popup,
			"SIZE" => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			"MIN_LENGTH" => $min,
			"MAX_LENGTH" => $max,
			"DEFAULT_VALUE" => $arUserField["SETTINGS"]["DEFAULT_VALUE"],
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
		{
			$value = $GLOBALS[$arHtmlControl["NAME"]]["POPUP"] == 'N' ? 'N' : 'Y';
		}
		elseif(is_array($arUserField))
		{
			$value = $arUserField["SETTINGS"]["POPUP"] == 'N' ? 'N' : 'Y';
		}
		else
		{
			$value = 'Y';
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_URL_POPUP").':</td>
			<td>
				<input type="hidden" name="'.$arHtmlControl["NAME"].'[POPUP]" value="N" />
				<label><input type="checkbox" name="'.$arHtmlControl["NAME"].'[POPUP]" value="Y" '.($value === 'Y' ? ' checked="checked"' : '').' />&nbsp;'.GetMessage('MAIN_YES').'</label>
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]);
		}
		elseif(is_array($arUserField))
		{
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		}
		else
		{
			$value = "";
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_DEFAULT_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="20"  maxlength="225" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["SIZE"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["SIZE"]);
		}
		else
		{
			$value = 20;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MIN_LENGTH"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		}
		else
		{
			$value = 0;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_MIN_LEGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_LENGTH"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);
		}
		else
		{
			$value = 0;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_MAX_LENGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';

		return $result;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		if(strlen($arHtmlControl["VALUE"]) > 0)
		{
			$url = static::encodeUrl($arHtmlControl["VALUE"]);

			return '<a href="'.$url.'"'.($arUserField["SETTINGS"]["POPUP"] !== 'N' ? ' target="_blank"' : '').'>'.$arHtmlControl["VALUE"].'</a>';
		}
		else
		{
			return '&nbsp;';
		}
	}

	function OnBeforeSave($arUserField, $value)
	{
		$value = strval($value);
		if(strlen($value) > 0)
		{
			$value = trim($value);
		}

		return $value;
	}

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;
		foreach($value as $res)
		{

			if(strlen($res) > 0)
			{
				if(!$first)
				{
					$html .= static::getHelper()->getMultipleValuesSeparator();
				}
				$first = false;

				$attrList = array(
					'href' => static::encodeUrl($res),
				);

				if($arUserField["SETTINGS"]["POPUP"] === 'Y')
				{
					$attrList["target"] = "_blank";
				}

				$res = '<a '.static::buildTagAttributes($attrList).'>'.\Bitrix\Main\Text\HtmlFilter::encode($res).'</a>';

				$html .= static::getHelper()->wrapSingleField($res);
			}
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($arUserField)
	{
		$result = array();
		$value = static::normalizeFieldValue($arUserField['VALUE']);
		foreach($value as $res)
		{
			if(is_string($res) && $res !== '')
			{
				$result[] = $res;
			}
		}
		return implode(', ', $result);
	}

	protected static function encodeUrl($url)
	{
		if(!preg_match('/^(callto:|mailto:|[a-z0-9]+:\/\/)/i', $url))
		{
			$url = 'http://'.$url;
		}

		$uri = new \Bitrix\Main\UserField\Uri($url);

		return $uri->getUri();
	}
}