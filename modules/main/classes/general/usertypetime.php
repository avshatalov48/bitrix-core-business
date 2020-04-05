<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Main;
use Bitrix\Main\Type;

class CUserTypeDateTime extends Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'datetime';

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_DT_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_DATETIME,
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		if(strtolower($DB->type) == "oracle")
		{
			return "date";
		}
		return "datetime";
	}

	function PrepareSettings($arUserField)
	{
		$def = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		if(!is_array($def))
			$def = array("TYPE"=>"NONE","VALUE"=>"");
		else
		{
			if($def["TYPE"]=="FIXED")
				$def["VALUE"] = CDatabase::FormatDate($def["VALUE"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS");
			elseif($def["TYPE"]=="NOW")
				$def["VALUE"] = "";
			else
				$def = array("TYPE"=>"NONE","VALUE"=>"");
		}

		return array(
			"DEFAULT_VALUE" => array("TYPE"=>$def["TYPE"], "VALUE"=>$def["VALUE"]),
			"USE_SECOND" => $arUserField["SETTINGS"]["USE_SECOND"] === 'N' ? 'N' : 'Y',
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$type = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]["TYPE"];
		elseif(is_array($arUserField) && is_array($arUserField["SETTINGS"]["DEFAULT_VALUE"]))
			$type = $arUserField["SETTINGS"]["DEFAULT_VALUE"]["TYPE"];
		else
			$type = "NONE";
		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]["VALUE"];
		elseif(is_array($arUserField) && is_array($arUserField["SETTINGS"]["DEFAULT_VALUE"]))
			$value = str_replace(" 00:00:00", "", CDatabase::FormatDate($arUserField["SETTINGS"]["DEFAULT_VALUE"]["VALUE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL")));
		else
			$value = "";
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_DT_DEFAULT_VALUE").':</td>
			<td>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE][TYPE]" value="NONE" '.("NONE"==$type? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_DT_NONE").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE][TYPE]" value="NOW" '.("NOW"==$type? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_DT_NOW").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE][TYPE]" value="FIXED" '.("FIXED"==$type? 'checked="checked"': '').'>'.CAdminCalendar::CalendarDate($arHtmlControl["NAME"].'[DEFAULT_VALUE][VALUE]', $value, 20, true).'</label><br>
			</td>
		</tr>
		';

		if($bVarsFromForm)
		{
			$value = $GLOBALS[$arHtmlControl["NAME"]]["USE_SECOND"] === 'N' ? 'N' : 'Y';
		}
		elseif(is_array($arUserField))
		{
			$value = $arUserField["SETTINGS"]["USE_SECOND"] === 'N' ? 'N' : 'Y';
		}
		else
		{
			$value = "Y";
		}

		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_DT_USE_SECOND").':</td>
			<td>
				<input type="hidden" name="'.$arHtmlControl["NAME"].'[USE_SECOND]" value="N" />
				<label><input type="checkbox" value="Y" name="'.$arHtmlControl["NAME"].'[USE_SECOND]" '.($value === 'Y' ? ' checked="checked"' : '').' />&nbsp;'.GetMessage('MAIN_YES').'</label>
			</td>
		</tr>
		';

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$arHtmlControl["VALIGN"] = "middle";
		if($arUserField["EDIT_IN_LIST"]=="Y")
		{
			if($arUserField["ENTITY_VALUE_ID"]<1 && $arUserField["SETTINGS"]["DEFAULT_VALUE"]["TYPE"]!="NONE")
			{
				if($arUserField["SETTINGS"]["DEFAULT_VALUE"]["TYPE"]=="NOW")
					$arHtmlControl["VALUE"] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
				else
					$arHtmlControl["VALUE"] = str_replace(" 00:00:00", "", CDatabase::FormatDate($arUserField["SETTINGS"]["DEFAULT_VALUE"]["VALUE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL")));
			}
			return CAdminCalendar::CalendarDate($arHtmlControl["NAME"], $arHtmlControl["VALUE"], 20, true);
		}
		elseif(strlen($arHtmlControl["VALUE"])>0)
			return $arHtmlControl["VALUE"];
		else
			return '&nbsp;';
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return CalendarPeriod(
							$arHtmlControl['NAME'].'_from',
							$GLOBALS[$arHtmlControl['NAME'].'_from'],
							$arHtmlControl['NAME'].'_to',
							$GLOBALS[$arHtmlControl['NAME'].'_to'],
							'find_form', 'Y');
	}

	function GetFilterData($arUserField, $arHtmlControl)
	{
		return array(
			"id" => $arHtmlControl["ID"],
			"name" => $arHtmlControl["NAME"],
			"type" => "date",
			'time' => true
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
		if($arUserField["EDIT_IN_LIST"]=="Y")
			return CAdminCalendar::CalendarDate($arHtmlControl["NAME"], $arHtmlControl["VALUE"], 20, true);
		elseif(strlen($arHtmlControl["VALUE"])>0)
			return $arHtmlControl["VALUE"];
		else
			return '&nbsp;';
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		$value = (string)$value;
		if ($value != "")
		{
			try
			{
				Type\DateTime::createFromUserTime($value);
			}
			catch (Main\ObjectException $e)
			{
				$aMsg[] = array(
					"id" => $arUserField["FIELD_NAME"],
					"text" => GetMessage("USER_TYPE_DT_ERROR",
						array(
							"#FIELD_NAME#"=>($arUserField["EDIT_FORM_LABEL"] <> ''? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]),
						)
					),
				);
			}
		}
		return $aMsg;
	}

	/**
	 * Returns string time in user timezone
	 *
	 * @param $userfield
	 * @param $fetched
	 *
	 * @return string
	 */
	public function onAfterFetch($userfield, $fetched)
	{
		$value = $fetched['VALUE'];

		if ($userfield['MULTIPLE'] == 'Y' && !($value instanceof Type\DateTime))
		{
			//Invalid value
			if (strlen($value) <= 1)
			{
				//will be ignored by the caller
				return null;
			}

			try
			{
				//try new independent datetime format
				$value = new Type\DateTime($value, \Bitrix\Main\UserFieldTable::MULTIPLE_DATETIME_FORMAT);
			}
			catch (Main\ObjectException $e)
			{
				//try site format
				try
				{
					$value = new Type\DateTime($value);
				}
				catch (Main\ObjectException $e)
				{
					//try short format
					$value = Type\DateTime::createFromUserTime($value);
				}
			}
		}
		elseif (!($value instanceof Type\DateTime))
		{
			// is not necessary, $value is a valid string already
			//$value = Type\DateTime::createFromUserTime($value);
		}

		return (string) $value;
	}


	/**
	 * Returns  time object in server timezone
	 *
	 * @param $userfield
	 * @param $value
	 *
	 * @return Type\DateTime|string
	 */
	public function onBeforeSave($userfield, $value)
	{
		if (strlen($value) && !($value instanceof Type\DateTime))
		{
			$value = Type\DateTime::createFromUserTime($value);
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
			$res = \CDatabase::FormatDate($res, \CLang::GetDateFormat("FULL"), static::getFormat($res, $arUserField));

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

		return static::getHelper()->wrapDisplayResult($html);
	}


	public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$html = '';

		$first = true;
		foreach($value as $res)
		{
			$tag = '';

			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			$attrList = array();

			if(array_key_exists('attribute', $arAdditionalParameters))
			{
				$attrList = array_merge($attrList, $arAdditionalParameters['attribute']);
			}

			if($arUserField["EDIT_IN_LIST"] != "Y")
			{
				$attrList['disabled'] = 'disabled';
			}
			else
			{
				$attrList['onclick'] = 'BX.calendar({node: this, field: this, bTime: true, bSetFocus: false, bUseSecond: '.($arUserField['SETTINGS']['USE_SECOND'] === 'N' ? 'false' : 'true').'})';
			}

			if(isset($attrList['class']) && is_array($attrList['class']))
			{
				$attrList['class'] = implode(' ', $attrList['class']);
			}

			$attrList['name'] = $fieldName;

			$attrList['type'] = 'text';
			$attrList['tabindex'] = '0';
			$attrList['value'] = \CDatabase::FormatDate($res, \CLang::GetDateFormat("FULL"), static::getFormat($res, $arUserField));

			$tag .= '<input '.static::buildTagAttributes($attrList).'/>';
			$tag .= '<i '.static::buildTagAttributes(array(
					'class' => static::getHelper()->getCssClassName().' icon',
					'onclick' => 'BX.calendar({node: this.previousSibling, field: this.previousSibling, bTime: true, bSetFocus: false, bUseSecond: '.($arUserField['SETTINGS']['USE_SECOND'] === 'N' ? 'false' : 'true').'});',
				)).'></i>';

			$html .= static::getHelper()->wrapSingleField($tag);
		}

		if($arUserField["MULTIPLE"] == "Y" && $arAdditionalParameters["SHOW_BUTTON"] != "N")
		{
			$html .= static::getHelper()->getCloneButton($fieldName);
		}

		static::initDisplay(array('date'));

		return static::getHelper()->wrapDisplayResult($html);

	}

	protected static function getFormat($value, $arUserField)
	{
		$format = CLang::GetDateFormat("FULL");

		if($arUserField['SETTINGS']['USE_SECOND'] === 'N' && MakeTimeStamp($value) % 60 <= 0)
		{
			$format = str_replace(':SS', '', $format);
		}

		return $format;
	}

	/**
	 * @param array $arUserField
	 * @param string $fieldName
	 * @return string
	 */
	public static function FormatField(array $arUserField, $fieldName)
	{
		global $DB;
		return $DB->DateToCharFunction($fieldName, "FULL");
	}
}
