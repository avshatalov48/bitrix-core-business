<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CUserTypeHlblock extends CUserTypeEnum
{
	const USER_TYPE_ID = "hlblock";

	const DISPLAY_LIST = 'LIST';
	const DISPLAY_CHECKBOX = 'CHECKBOX';

	public static function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => self::USER_TYPE_ID,
			"CLASS_NAME" => "CUserTypeHlblock",
			"DESCRIPTION" => GetMessage('USER_TYPE_HLEL_DESCRIPTION'),
			"BASE_TYPE" => "int",
		);
	}

	public static function GetDBColumnType($arUserField)
	{
		global $DB;
		switch($DB->type)
		{
			case "MYSQL":
				return "int(18)";
			case "ORACLE":
				return "number(18)";
			case "MSSQL":
				return "int";
		}
		return "int";
	}

	function PrepareSettings($arUserField)
	{
		$multiple = false;
		if (isset($arUserField['MULTIPLE']) && $arUserField['MULTIPLE'] === 'Y')
		{
			$multiple = true;
		}

		$settings = [];
		if (!empty($arUserField['SETTINGS']) && is_array($arUserField['SETTINGS']))
		{
			$settings = $arUserField['SETTINGS'];
		}

		return self::verifySettings($settings, $multiple);
	}

	function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if (empty($arUserField) || !is_array($arUserField))
		{
			$arUserField = null;
		}
		if (empty($arHtmlControl) || !is_array($arHtmlControl))
		{
			$arHtmlControl = null;
		}
		if ($arHtmlControl === null)
		{
			return $result;
		}

		$name = $arHtmlControl['NAME'];
		$multiple = false;
		if (isset($arUserField['MULTIPLE']) && $arUserField['MULTIPLE'] === 'Y')
		{
			$multiple = true;
		}
		$defaultSettings = self::getDefaultSettings($multiple);
		if ($bVarsFromForm)
		{
			$settings = self::getSettingsFromForm($arUserField, $arHtmlControl);
		}
		else
		{
			$settings = $arUserField["SETTINGS"] ?? $defaultSettings;
		}
		if (empty($settings) || !is_array($settings))
		{
			$settings = $defaultSettings;
		}

		$settings = self::verifySettings($settings, $multiple);

		$moduleIncluded = Loader::includeModule('highloadblock');
		if ($moduleIncluded)
		{
			$result .= '
			<tr>
				<td>' . Loc::getMessage('USER_TYPE_HLEL_DISPLAY') . ':</td>
				<td>'
				. self::getHighloadblockSelectorHtml(
					$name,
					$settings
				)
				. '</td>
			</tr>
			';
		}
		if (
			$moduleIncluded
			&& $settings['HLBLOCK_ID'] > 0
			&& $settings['HLFIELD_ID'] > 0
		)
		{


			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_HLEL_DEFAULT_VALUE").':</td>
				<td>
					<select name="' . htmlspecialcharsbx($name) . '[DEFAULT_VALUE]'.($multiple ? '[]' : '').'"' . ($multiple ? ' multiple' : '') . ' size="5">
						<option value="">'.GetMessage("IBLOCK_VALUE_ANY").'</option>
			';

			$rows = static::getHlRows(['SETTINGS' => $settings]);

			foreach ($rows as $row)
			{
				$selected = '';
				if ($multiple)
				{
					if (in_array($row['ID'], $settings['DEFAULT_VALUE']))
					{
						$selected = ' selected';
					}
				}
				else
				{
					if ($row['ID'] === $settings['DEFAULT_VALUE'])
					{
						$selected = ' selected';
					}
				}
				$result .= '<option value="'.$row["ID"].'"' . $selected .'>'.htmlspecialcharsbx($row['VALUE']).'</option>';
			}
			unset($row, $rows);

			$result .= '</select>';

		}
		else
		{
			$result .= '<tr>'
				. '<td>' . Loc::getMessage('USER_TYPE_HLEL_DEFAULT_VALUE') . ':</td>'
				. '<td>'
			;
			if ($multiple)
			{
				foreach ($settings['DEFAULT_VALUE'] as $value)
				{
					$result .= self::getDefaultValueRowHtml($name, (string)$value, true)
						. '<br>'
					;
				}
				$result .= self::getDefaultValueRowHtml($name, '', true);
			}
			else
			{
				$result .= self::getDefaultValueRowHtml($name, (string)$settings['DEFAULT_VALUE'], false);
			}
			$result .= '</td>'
				. '</tr>'
			;
		}

		$result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_ENUM_DISPLAY").':</td>
			<td>
				<label><input type="radio" name="'.htmlspecialcharsbx($name).'[DISPLAY]" value="'.self::DISPLAY_LIST.'" '.(self::DISPLAY_LIST == $settings['DISPLAY'] ? 'checked="checked"' : '').'>'.GetMessage("USER_TYPE_HLEL_LIST").'</label><br>
				<label><input type="radio" name="'.htmlspecialcharsbx($name).'[DISPLAY]" value="'.self::DISPLAY_CHECKBOX.'" '.(self::DISPLAY_CHECKBOX == $settings['DISPLAY'] ? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_HLEL_CHECKBOX").'</label><br>
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_HLEL_LIST_HEIGHT").':</td>
			<td>
				<input type="text" name="'.htmlspecialcharsbx($name).'[LIST_HEIGHT]" size="10" value="'.$settings['LIST_HEIGHT'].'">
			</td>
		</tr>
		';

		return $result;
	}

	private static function verifySettings(array $settings, bool $multiple): array
	{
		$defaultSettings = self::getDefaultSettings($multiple);

		$height = (int)($settings['LIST_HEIGHT'] ?? $defaultSettings['LIST_HEIGHT']);
		if ($height < 1)
		{
			$height = $defaultSettings['LIST_HEIGHT'];
		}

		$display = (string)($settings['DISPLAY'] ?? $defaultSettings['DISPLAY']);
		if ($display !== self::DISPLAY_CHECKBOX && $display !== self::DISPLAY_LIST)
		{
			$display = $defaultSettings['DISPLAY'];
		}

		$hlblockId = (int)($settings['HLBLOCK_ID'] ?? $defaultSettings['HLBLOCK_ID']);
		if ($hlblockId < 0)
		{
			$hlblockId = $defaultSettings['HLBLOCK_ID'];
		}

		$hlfieldId = (int)($settings['HLFIELD_ID'] ?? $defaultSettings['HLFIELD_ID']);
		if ($hlfieldId < 0)
		{
			$hlfieldId = $defaultSettings['HLFIELD_ID'];
		}

		$defaultValue = $settings['DEFAULT_VALUE'] ?? $defaultSettings['DEFAULT_VALUE'];
		if ($multiple)
		{
			if (!is_array($defaultValue))
			{
				$defaultValue = [$defaultValue];
			}
			Main\Type\Collection::normalizeArrayValuesByInt($defaultValue, true);
		}
		else
		{
			if (!is_int($defaultValue) && !is_string($defaultValue))
			{
				$defaultValue = $defaultSettings['DEFAULT_VALUE'];
			}
			$defaultValue = (int)$defaultValue;
			if ($defaultValue < 0)
			{
				$defaultValue = $defaultSettings['DEFAULT_VALUE'];
			}
		}

		return [
			'DISPLAY' => $display,
			'LIST_HEIGHT' => $height,
			'HLBLOCK_ID' => $hlblockId,
			'HLFIELD_ID' => $hlfieldId,
			'DEFAULT_VALUE' => $defaultValue,
		];
	}

	private static function getDefaultSettings(bool $multiple = false): array
	{
		return [
			'DISPLAY' => self::DISPLAY_LIST,
			'LIST_HEIGHT' => $multiple ? 5 : 1,
			'HLBLOCK_ID' => 0,
			'HLFIELD_ID' => 0,
			'DEFAULT_VALUE' => ($multiple ? [] : ''),
		];
	}

	private function getSettingsFromForm(?array $userField, ?array $control): array
	{
		$multiple = ($userField['MULTIPLE'] ?? 'N') === 'Y';
		$result = self::getDefaultSettings($multiple);
		if (empty($userField) || empty($control))
		{
			return $result;
		}

		$name = trim($control['NAME'] ?? '');
		if ($name === '' || !isset($GLOBALS[$name]))
		{
			return $result;
		}

		$result['DISPLAY'] = (string)($GLOBALS[$name]['DISPLAY'] ?? $result['DISPLAY']);
		$result['LIST_HEIGHT'] = (int)($GLOBALS[$name]['LIST_HEIGHT'] ?? $result['DISPLAY']);
		$result['HLBLOCK_ID'] = (int)($GLOBALS[$name]['HLBLOCK_ID'] ?? $result['HLBLOCK_ID']);
		$result['HLFIELD_ID'] = (int)($GLOBALS[$name]['HLFIELD_ID'] ?? $result['HLFIELD_ID']);
		if (isset($GLOBALS[$name]['DEFAULT_VALUE']))
		{
			if ($multiple)
			{
				$result['DEFAULT_VALUE'] = is_array($GLOBALS[$name]['DEFAULT_VALUE'])
					? $GLOBALS[$name]['DEFAULT_VALUE']
					: [$GLOBALS[$name]['DEFAULT_VALUE']]
				;
			}
			else
			{
				$result['DEFAULT_VALUE'] = is_string($GLOBALS[$name]['DEFAULT_VALUE']) && is_int($GLOBALS[$name]['DEFAULT_VALUE'])
					? $GLOBALS[$name]['DEFAULT_VALUE']
					: ''
				;
			}
		}

		return $result;
	}

	private static function getDefaultValueRowHtml(string $name, string $value, bool $multiple): string
	{
		return '<input type="text" size="8" name="'
			. htmlspecialcharsbx($name).'[DEFAULT_VALUE]'
			. ($multiple ? '[]' : '') . '"'
			.' value="' . htmlspecialcharsbx($value)
			. '">'
		;
	}

	private static function getHighloadblockSelectorHtml(string $name, array $select): string
	{
		$name = htmlspecialcharsbx($name);

		$list = static::getDropDownData();

		// hlblock selector
		$html = '<select name="' . $name . '[HLBLOCK_ID]" onchange="hlChangeFieldOnHlblockChanged(this)">';
		$html .= '<option value="">'.htmlspecialcharsbx(GetMessage('USER_TYPE_HLEL_SEL_HLBLOCK')).'</option>';

		foreach ($list as $_hlblockId => $hlblockData)
		{
			$html .= '<option value="'.$_hlblockId.'"'
				. ($_hlblockId === $select['HLBLOCK_ID'] ? ' selected' : '') . '>'
				. htmlspecialcharsbx($hlblockData['name']) . '</option>'
			;
		}

		$html .= '</select> &nbsp; ';

		// field selector
		$html .= '<select name="' . $name . '[HLFIELD_ID]" id="hl_ufsett_field_selector">';
		$html .= '<option value="">'.htmlspecialcharsbx(GetMessage('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD')).'</option>';

		if ($select['HLBLOCK_ID'] > 0)
		{
			foreach ($list[$select['HLBLOCK_ID']]['fields'] as $fieldId => $fieldName)
			{
				$html .= '<option value="'.$fieldId.'"'.($fieldId === $select['HLFIELD_ID'] ? ' selected' : '').'>'.htmlspecialcharsbx($fieldName).'</option>';
			}
		}

		$html .= '</select>';

		// js: changing field selector
		$html .= '
			<script type="text/javascript">
				function hlChangeFieldOnHlblockChanged(hlSelect)
				{
					var list = '.CUtil::PhpToJSObject($list).';
					var fieldSelect = BX("hl_ufsett_field_selector");

					for(var i=fieldSelect.length-1; i >= 0; i--)
						fieldSelect.remove(i);

					var newOption = new Option(\''.GetMessageJS('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD').'\', "", false, false);
					fieldSelect.options.add(newOption);

					if (list[hlSelect.value])
					{
						for(var j in list[hlSelect.value]["fields"])
						{
							var newOption = new Option(list[hlSelect.value]["fields"][j], j, false, false);
							fieldSelect.options.add(newOption);
						}
					}
				}
			</script>
		';

		return $html;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}

	public static function GetList($arUserField)
	{
		$rs = false;

		if(CModule::IncludeModule('highloadblock'))
		{
			$rows = static::getHlRows($arUserField, true);

			$rs = new CDBResult();
			$rs->InitFromArray($rows);

		}

		return $rs;
	}

	function getEntityReferences($userfield, \Bitrix\Main\Entity\ScalarField $entityField)
	{
		if ($userfield['SETTINGS']['HLBLOCK_ID'])
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($userfield['SETTINGS']['HLBLOCK_ID'])->fetch();

			if ($hlblock)
			{
				if (class_exists($hlblock['NAME'].'Table'))
				{
					$hlentity = \Bitrix\Main\Entity\Base::getInstance($hlblock['NAME']);
				}
				else
				{
					$hlentity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
				}

				return array(
					new \Bitrix\Main\Entity\ReferenceField(
						$entityField->getName().'_REF',
						$hlentity,
						array('=this.'.$entityField->getName() => 'ref.ID')
					)
				);
			}
		}

		return array();
	}

	public static function getHlRows($userfield, $clearValues = false): array
	{
		global $USER_FIELD_MANAGER;

		$rows = array();

		$hlblock_id = $userfield['SETTINGS']['HLBLOCK_ID'];
		$hlfield_id = $userfield['SETTINGS']['HLFIELD_ID'];

		if (!empty($hlblock_id))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
		}

		if (!empty($hlblock))
		{
			$userfield = null;

			if ($hlfield_id == 0)
			{
				$userfield = array('FIELD_NAME' => 'ID');
			}
			else
			{
				$userfields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

				foreach ($userfields as $_userfield)
				{
					if ($_userfield['ID'] == $hlfield_id)
					{
						$userfield = $_userfield;
						break;
					}
				}
			}

			if ($userfield)
			{
				// validated successfully. get data
				$hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
				$rows = $hlDataClass::getList(array(
					'select' => array('ID', $userfield['FIELD_NAME']),
					'order' => 'ID'
				))->fetchAll();

				foreach ($rows as &$row)
				{
					$row['ID'] = (int)$row['ID'];
					if ($userfield['FIELD_NAME'] == 'ID')
					{
						$row['VALUE'] = $row['ID'];
					}
					else
					{
						//see #0088117
						if ($userfield['USER_TYPE_ID'] != 'enumeration' && $clearValues)
						{
							$row['VALUE'] = $row[$userfield['FIELD_NAME']];
						}
						else
						{
							$row['VALUE'] = $USER_FIELD_MANAGER->getListView($userfield, $row[$userfield['FIELD_NAME']]);
						}
						$row['VALUE'] .= ' ['.$row['ID'].']';
					}
				}
			}
		}

		return $rows;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		static $cache = array();
		$empty_caption = '&nbsp;';

		$cacheKey = $arUserField['SETTINGS']['HLBLOCK_ID'].'_v'.$arHtmlControl["VALUE"];

		if(!array_key_exists($cacheKey, $cache) && !empty($arHtmlControl["VALUE"]))
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
				$cache[$arUserField['SETTINGS']['HLBLOCK_ID'].'_v'.$arEnum["ID"]] = $arEnum["VALUE"];
		}
		if(!array_key_exists($cacheKey, $cache))
			$cache[$cacheKey] = $empty_caption;

		return $cache[$cacheKey];
	}

	public static function getDropDownData(): array
	{
		global $USER_FIELD_MANAGER;

		$hlblocks = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('order' => 'NAME'))->fetchAll();

		$list = array();

		foreach ($hlblocks as $hlblock)
		{
			// add hlblock itself
			$list[$hlblock['ID']] = array(
				'name' => $hlblock['NAME'],
				'fields' => array(
					0 => 'ID'
				)
			);

			$userfields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

			foreach ($userfields as $userfield)
			{
				$fieldTitle = $userfield['LIST_COLUMN_LABEL'] <> ''? $userfield['LIST_COLUMN_LABEL'] : $userfield['FIELD_NAME'];
				$list[$hlblock['ID']]['fields'][(int)$userfield['ID']] = $fieldTitle;
			}
		}

		return $list;
	}

	public static function getDropDownHtml($hlblockId = null, $hlfieldId = null): string
	{
		return self::getHighloadblockSelectorHtml(
			'SETTINGS',
			[
				'HLBLOCK_ID' => (int)$hlblockId,
				'HLFIELD_ID' => (int)$hlfieldId,
			]
		);
/*		$list = static::getDropDownData();

		// hlblock selector
		$html = '<select name="SETTINGS[HLBLOCK_ID]" onchange="hlChangeFieldOnHlblockChanged(this)">';
		$html .= '<option value="">'.htmlspecialcharsbx(GetMessage('USER_TYPE_HLEL_SEL_HLBLOCK')).'</option>';

		foreach ($list as $_hlblockId => $hlblockData)
		{
			$html .= '<option value="'.$_hlblockId.'" '.($_hlblockId == $hlblockId?'selected':'').'>'.htmlspecialcharsbx($hlblockData['name']).'</option>';
		}

		$html .= '</select> &nbsp; ';

		// field selector
		$html .= '<select name="SETTINGS[HLFIELD_ID]" id="hl_ufsett_field_selector">';
		$html .= '<option value="">'.htmlspecialcharsbx(GetMessage('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD')).'</option>';

		if ($hlblockId)
		{
			if($hlfieldId <> '')
			{
				$hlfieldId = (int)$hlfieldId;
			}

			foreach ($list[$hlblockId]['fields'] as $fieldId => $fieldName)
			{
				$html .= '<option value="'.$fieldId.'" '.($fieldId === $hlfieldId?'selected':'').'>'.htmlspecialcharsbx($fieldName).'</option>';
			}
		}

		$html .= '</select>';

		// js: changing field selector
		$html .= '
			<script type="text/javascript">
				function hlChangeFieldOnHlblockChanged(hlSelect)
				{
					var list = '.CUtil::PhpToJSObject($list).';
					var fieldSelect = BX("hl_ufsett_field_selector");

					for(var i=fieldSelect.length-1; i >= 0; i--)
						fieldSelect.remove(i);

					var newOption = new Option(\''.GetMessageJS('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD').'\', "", false, false);
					fieldSelect.options.add(newOption);

					if (list[hlSelect.value])
					{
						for(var j in list[hlSelect.value]["fields"])
						{
							var newOption = new Option(list[hlSelect.value]["fields"][j], j, false, false);
							fieldSelect.options.add(newOption);
						}
					}
				}
			</script>
		';

		return $html; */
	}
}
