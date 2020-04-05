<?
IncludeModuleLangFile(__FILE__);

class CUserTypeBoolean extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'boolean';

	const DISPLAY_DROPDOWN = 'DROPDOWN';
	const DISPLAY_RADIO = 'RADIO';
	const DISPLAY_CHECKBOX = 'CHECKBOX';

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_BOOL_DESCRIPTION"),
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
		$label = $arUserField["SETTINGS"]["LABEL"];

		if($label[0] === GetMessage('MAIN_NO'))
		{
			$label[0] = '';
		}
		if($label[1] === GetMessage('MAIN_YES'))
		{
			$label[1] = '';
		}

		$labelCheckbox = $arUserField["SETTINGS"]["LABEL_CHECKBOX"];
		if($labelCheckbox === GetMessage('MAIN_YES'))
		{
			$labelCheckbox = '';
		}


		$def = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		if($def != 1)
		{
			$def = 0;
		}

		$disp = $arUserField["SETTINGS"]["DISPLAY"];
		if($disp!="CHECKBOX" && $disp!="RADIO" && $disp!="DROPDOWN")
		{
			$disp = "CHECKBOX";
		}

		return array(
			"DEFAULT_VALUE" => $def,
			"DISPLAY" => $disp,
			"LABEL" => array(
				$label[0], $label[1]
			),
			"LABEL_CHECKBOX" => $labelCheckbox,
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
		{
			$value = array(
				trim($GLOBALS[$arHtmlControl["NAME"]]["LABEL"][0]),
				trim($GLOBALS[$arHtmlControl["NAME"]]["LABEL"][1]),
			);
		}
		elseif(is_array($arUserField))
		{
			$value = static::getLabels($arUserField);
		}
		else
		{
			$value = array(GetMessage('MAIN_NO'), GetMessage('MAIN_YES'));
		}

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_BOOL_LABELS").':</td>
			<td><table border="0">
				<tr><td>'.GetMessage('MAIN_YES').':</td><td><input type="text" name="'.$arHtmlControl["NAME"].'[LABEL][1]" value="'.\Bitrix\Main\Text\HtmlFilter::encode($value[1]).'"></td></tr>
				<tr><td>'.GetMessage('MAIN_NO').':</td><td><input type="text" name="'.$arHtmlControl["NAME"].'[LABEL][0]" value="'.\Bitrix\Main\Text\HtmlFilter::encode($value[0]).'"></td></tr>
			</table></td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		else
			$value = 1;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_BOOL_DEFAULT_VALUE").':</td>
			<td>
				<select name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]">
				<option value="1" '.($value? 'selected="selected"': '').'>'.GetMessage("MAIN_YES").'</option>
				<option value="0" '.(!$value? 'selected="selected"': '').'>'.GetMessage("MAIN_NO").'</option>
				</select>
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DISPLAY"];
		else
			$value = static::DISPLAY_CHECKBOX;
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_BOOL_DISPLAY").':</td>
			<td>';

		foreach(array(static::DISPLAY_CHECKBOX, static::DISPLAY_RADIO, static::DISPLAY_DROPDOWN) as $display)
		{
			$result .= '<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="'.$display.'" '.($display == $value ? 'checked="checked"' : '').'>'.GetMessage("USER_TYPE_BOOL_".$display).'</label><br />';
		}

		$result .= '
			</td>
		</tr>
		';

		if($bVarsFromForm)
		{
			$value = trim($GLOBALS[$arHtmlControl["NAME"]]["LABEL_CHECKBOX"]);
		}
		elseif(is_array($arUserField))
		{
			$value = trim($arUserField["SETTINGS"]["LABEL_CHECKBOX"]);
			if(strlen($value) <= 0)
			{
				$value = GetMessage('MAIN_YES');
			}
		}
		else
		{
			$value = GetMessage('MAIN_YES');
		}

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_BOOL_LABEL_CHECKBOX").':</td>
			<td><input type="text" name="'.$arHtmlControl["NAME"].'[LABEL_CHECKBOX]" value="'.\Bitrix\Main\Text\HtmlFilter::encode($value).'"></td>
		</tr>
		';

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$label = static::getLabels($arUserField);

		if($arUserField["ENTITY_VALUE_ID"]<1)
		{
			$arHtmlControl["VALUE"] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		}

		switch($arUserField["SETTINGS"]["DISPLAY"])
		{
			case "DROPDOWN":
				$arHtmlControl["VALIGN"] = "middle";
				return '
					<select name="'.$arHtmlControl["NAME"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled"': '').'>
					<option value="1"'.($arHtmlControl["VALUE"]? ' selected': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[1]).'</option>
					<option value="0"'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[0]).'</option>
					</select>
				';
			case "RADIO":
				return '
					<label><input type="radio" value="1" name="'.$arHtmlControl["NAME"].'"'.($arHtmlControl["VALUE"]? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled"': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[1]).'</label><br>
					<label><input type="radio" value="0" name="'.$arHtmlControl["NAME"].'"'.(!$arHtmlControl["VALUE"]? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled"': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[0]).'</label>
				';
			default:
				$arHtmlControl["VALIGN"] = "middle";

				$label = GetMessage('MAIN_YES');
				if(isset($arUserField["SETTINGS"]["LABEL_CHECKBOX"]) && strlen($arUserField["SETTINGS"]["LABEL_CHECKBOX"]) > 0)
				{
					$label = $arUserField["SETTINGS"]["LABEL_CHECKBOX"];
				}

				return '
					<input type="hidden" value="0" name="'.$arHtmlControl["NAME"].'">
					<label><input type="checkbox" value="1" name="'.$arHtmlControl["NAME"].'"'.($arHtmlControl["VALUE"]? ' checked': '').' id="'.$arHtmlControl["NAME"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled"': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label).'</label>
				';
		}
	}
	//Boolean type intentionally made only single valued.
	//There are some code commented out in this method which is a try to implement multiple values editing
	function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		$FIELD_NAME_X = str_replace('_', 'x', $arUserField["FIELD_NAME"]);
		$form_value = $arHtmlControl["VALUE"];
		if (!is_array($form_value))
			$form_value = array($form_value);
		foreach ($form_value as $key=>$value)
		{
			$form_value[$key] = intval($value);
		}
		if (!$form_value)
			$form_value[] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

		$html = '';
		foreach ($form_value as $i => $value)
		{
			$arHtmlControl["VALUE"] = $value;
			/*
			$id = $FIELD_NAME_X.'_'.$i;
			$html .= '<tr id="'.$id.'"><td>'
			 */
			$html .= self::GetEditFormHTML($arUserField, array(
				"NAME" => $arUserField["FIELD_NAME"]."[".$i."]",
				"VALUE" => $value,
			));
			/*
			if ($i > 0)
				$html .= '<a class="bx-action-href" href="javascript:BX(\''.$id.'\').parentNode.removeChild(BX(\''.$id.'\'))">'.GetMessage("MAIN_DELETE").'<a/>';
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';
			*/
			break;
		}
		return $html;
		/*
		return '<table id="table_'.$arUserField["FIELD_NAME"].'" width="10%">'.$html.
		'<tr><td style="padding-top: 6px;"><input type="button" value="'.GetMessage("USER_TYPE_PROP_ADD").'" onClick="addNewRow(\'table_'.$arUserField["FIELD_NAME"].'\', \''.$FIELD_NAME_X.'|'.$arUserField["FIELD_NAME"].'|'.$arUserField["FIELD_NAME"].'_old_id\')"></td></tr>'.
		"<script type=\"text/javascript\">BX.addCustomEvent('onAutoSaveRestore', function(ob, data) {for (var i in data){if (i.substring(0,".(strlen($arUserField['FIELD_NAME'])+1).")=='".CUtil::JSEscape($arUserField['FIELD_NAME'])."['){".
		'addNewRow(\'table_'.$arUserField["FIELD_NAME"].'\', \''.$FIELD_NAME_X.'|'.$arUserField["FIELD_NAME"].'|'.$arUserField["FIELD_NAME"].'_old_id\')'.
		"}}})</script>".
		'</table>'
		;
		*/
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		$label = static::getLabels($arUserField);

		return '
			<select name="'.$arHtmlControl["NAME"].'">
			<option value=""'.(strlen($arHtmlControl["VALUE"])<1? ' selected': '').'>'.GetMessage("MAIN_ALL").'</option>
			<option value="1"'.($arHtmlControl["VALUE"]? ' selected': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[1]).'</option>
			<option value="0"'.(strlen($arHtmlControl["VALUE"])>0 && !$arHtmlControl["VALUE"]? ' selected': '').'>'.\Bitrix\Main\Text\HtmlFilter::encode($label[0]).'</option>
			</select>
		';
	}

	function GetFilterData($arUserField, $arHtmlControl)
	{
		return array(
			"id" => $arHtmlControl["ID"],
			"name" => $arHtmlControl["NAME"],
			"type" => "list",
			"items" => array(
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO")
			),
			"filterable" => ""
		);
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		$label = static::getLabels($arUserField);

		if($arHtmlControl["VALUE"])
			return \Bitrix\Main\Text\HtmlFilter::encode($label[1]);
		else
			return \Bitrix\Main\Text\HtmlFilter::encode($label[0]);
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		return '
			<input type="hidden" value="0" name="'.$arHtmlControl["NAME"].'">
			<input type="checkbox" value="1" name="'.$arHtmlControl["NAME"].'"'.($arHtmlControl["VALUE"]? ' checked': '').'>
		';
	}

	function OnBeforeSave($arUserField, $value)
	{
		if($value)
			return 1;
		else
			return 0;
	}

	public static function getLabels($arUserField)
	{
		$label = array(GetMessage('MAIN_NO'), GetMessage('MAIN_YES'));
		if(is_array($arUserField["SETTINGS"]["LABEL"]))
		{
			foreach($label as $key => $value)
			{
				if(strlen($arUserField["SETTINGS"]["LABEL"][$key]) > 0)
				{
					$label[$key] = $arUserField["SETTINGS"]["LABEL"][$key];
				}
			}
		}

		return $label;
	}

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);
		$label = static::getLabels($arUserField);

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
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', $res, $arUserField['PROPERTY_VALUE_LINK'])).'">'.\Bitrix\Main\Text\HtmlFilter::encode($res ? $label[1] : $label[0]).'</a>';
			}
			else
			{
				$res = \Bitrix\Main\Text\HtmlFilter::encode($res ? $label[1] : $label[0]);
			}

			$html .= static::getHelper()->wrapSingleField($res);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicText($userField)
	{
		$value = static::normalizeFieldValue($userField['VALUE']);
		$label = static::getLabels($userField);

		$text = '';
		$first = true;
		foreach ($value as $res)
		{
			if (!$first)
				$text .= ', ';
			$first = false;

			$text .= $res ? $label[1] : $label[0];
		}

		return $text;
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
			$attrList['tabindex'] = '0';

			$tag = '';
			$valueList = static::getLabels($arUserField);

			switch($arUserField['SETTINGS']['DISPLAY'])
			{
				case static::DISPLAY_DROPDOWN:

					$tag .= '<select '.static::buildTagAttributes($attrList).'>';
					foreach($valueList as $key => $title)
					{
						$tag .= '<option value="'.intval($key).'" '.($res == $key ? 'selected="selected"' : '').'>'.htmlspecialcharsbx($title).'</option>';
					}

					$tag .= '</select>';

				break;

				case static::DISPLAY_RADIO:

					$attrList['type'] = 'radio';

					$first = true;
					foreach($valueList as $key => $title)
					{
						if($first)
						{
							$first = false;
						}
						elseif($arUserField['SETTINGS']['MULTIPLE'] == 'N')
						{
							$tag .= static::getHelper()->getMultipleValuesSeparator();
						}

						$attrList['value'] = $key;

						$tag .= '<label><input '.static::buildTagAttributes($attrList).($res == $key ? ' checked="checked"' : '').' />'.htmlspecialcharsbx($title).'</label>';
					}

				break;

				default:

					$attrList['type'] = 'hidden';
					$attrList['value'] = '0';
					$tag .= '<input '.static::buildTagAttributes($attrList).' />';
					$attrList['type'] = 'checkbox';
					$attrList['value'] = '1';

					if($res)
					{
						$attrList['checked'] = 'checked';
					}

					$label = GetMessage('MAIN_YES');
					if(isset($arUserField["SETTINGS"]["LABEL_CHECKBOX"]))
					{
						if(is_array($arUserField["SETTINGS"]["LABEL_CHECKBOX"]))
						{
							$arUserField["SETTINGS"]["LABEL_CHECKBOX"] = $arUserField["SETTINGS"]["LABEL_CHECKBOX"][LANGUAGE_ID];
						}

						if(strlen($arUserField["SETTINGS"]["LABEL_CHECKBOX"]) > 0)
						{
							$label = $arUserField["SETTINGS"]["LABEL_CHECKBOX"];
						}
					}

					$tag .= '<label><input '.static::buildTagAttributes($attrList).'>'.\Bitrix\Main\Text\HtmlFilter::encode($label).'</label>';

				break;
			}

			$html .= static::getHelper()->wrapSingleField($tag);
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