<?php

use Bitrix\Highloadblock as HL;
use Bitrix\Highloadblock\Integration\UI\EntitySelector\ElementProvider;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

/**
 * Class CIBlockPropertyDirectory
 */
class CIBlockPropertyDirectory
{
	const TABLE_PREFIX = 'b_hlbd_';

	const USER_TYPE = 'directory';

	protected static $arFullCache = array();
	protected static $arItemCache = array();
	protected static $directoryMap = array();
	protected static $hlblockCache = array();
	protected static $hlblockClassNameCache = array();

	/**
	 * Returns property type description.
	 *
	 * @return array
	 */
	public static function GetUserTypeDescription(): array
	{
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => self::USER_TYPE,
			'DESCRIPTION' => Loc::getMessage('HIBLOCK_PROP_DIRECTORY_DESCRIPTION'),
			'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
			'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
			'PrepareSettings' => array(__CLASS__, 'PrepareSettings'),
			'GetAdminListViewHTML' => array(__CLASS__, 'GetAdminListViewHTML'),
			'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML'),
			'GetPublicEditHTML' => array(__CLASS__, 'GetPublicEditHTML'),
			'GetPublicEditHTMLMulty' => array(__CLASS__, 'GetPublicEditHTMLMulty'),
			'GetAdminFilterHTML' => array(__CLASS__, 'GetAdminFilterHTML'),
			'GetExtendedValue' => array(__CLASS__, 'GetExtendedValue'),
			'GetSearchContent' => array(__CLASS__, 'GetSearchContent'),
			'AddFilterFields' => array(__CLASS__, 'AddFilterFields'),
			'GetUIFilterProperty' => array(__CLASS__, 'GetUIFilterProperty'),
			'GetUIEntityEditorProperty' => array(__CLASS__, 'GetUIEntityEditorProperty'),
			'GetUIEntityEditorPropertyEditHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyEditHtml'),
			'GetUIEntityEditorPropertyViewHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyViewHtml'),
		);
	}

	/**
	 * Prepare settings for property.
	 *
	 * @param array $arProperty				Property description.
	 * @return array
	 */
	public static function PrepareSettings($arProperty): array
	{
		$size = 1;
		$width = 0;
		$multiple = "N";
		$group = "N";
		$directoryTableName = '';

		if (!empty($arProperty["USER_TYPE_SETTINGS"]) && is_array($arProperty["USER_TYPE_SETTINGS"]))
		{
			if (isset($arProperty["USER_TYPE_SETTINGS"]["size"]))
			{
				$size = (int)$arProperty["USER_TYPE_SETTINGS"]["size"];
				if ($size <= 0)
					$size = 1;
			}

			if (isset($arProperty["USER_TYPE_SETTINGS"]["width"]))
			{
				$width = (int)$arProperty["USER_TYPE_SETTINGS"]["width"];
				if ($width < 0)
					$width = 0;
			}

			if (isset($arProperty["USER_TYPE_SETTINGS"]["group"]) && $arProperty["USER_TYPE_SETTINGS"]["group"] === "Y")
				$group = "Y";

			if (isset($arProperty["USER_TYPE_SETTINGS"]["multiple"]) && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y")
				$multiple = "Y";

			if (isset($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
				$directoryTableName = (string)$arProperty["USER_TYPE_SETTINGS"]['TABLE_NAME'];
		}

		$extendedSettings = false;
		$result = array(
			'size' =>  $size,
			'width' => $width,
			'group' => $group,
			'multiple' => $multiple,
			'TABLE_NAME' => $directoryTableName
		);
		$defaultValue = '';
		if ($directoryTableName !== '')
		{
			$iterator = HL\HighloadBlockTable::getList([
				'select' => ['ID'],
				'filter' => ['=TABLE_NAME' => $directoryTableName]
			]);
			$row = $iterator->fetch();
			if (!empty($row))
			{
				$defaultValue = self::getDefaultXmlId($row['ID']);
				if ($defaultValue !== null)
					$extendedSettings = true;
			}
			unset($row, $iterator);
		}

		if (!$extendedSettings)
			return $result;

		$arProperty['USER_TYPE_SETTINGS'] = $result;
		$arProperty['DEFAULT_VALUE'] = $defaultValue;

		return $arProperty;
	}

	/**
	 * Returns html for show in edit property page.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $strHTMLControlName		Control description.
	 * @param array $arPropertyFields		Property fields for edit form.
	 * @return string
	 */
	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields): string
	{
		$iblockID = 0;
		if (isset($arProperty['IBLOCK_ID']))
			$iblockID = (int)$arProperty['IBLOCK_ID'];
		CJSCore::Init(array('translit'));
		$settings = static::PrepareSettings($arProperty);
		if (isset($settings['USER_TYPE_SETTINGS']))
			$settings = $settings['USER_TYPE_SETTINGS'];
		$arPropertyFields = array(
			'HIDE' => ['ROW_COUNT', 'COL_COUNT', 'MULTIPLE_CNT', 'DEFAULT_VALUE', 'WITH_DESCRIPTION'],
			'SET' => ['DEFAULT_VALUE' => '']
		);

		$directory = [];
		$cellOption = '<option value="-1"'.('' == $settings["TABLE_NAME"] ? ' selected' : '').'>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_NEW_DIRECTORY').'</option>';

		$rsData = HL\HighloadBlockTable::getList(array(
			'select' => array('*', 'NAME_LANG' => 'LANG.NAME'),
			'order' => array('NAME_LANG' => 'ASC', 'NAME' => 'ASC')
		));
		while($arData = $rsData->fetch())
		{
			if ($settings['TABLE_NAME'] == $arData['TABLE_NAME'])
			{
				$directory = $arData;
				unset($directory['NAME_LANG']);
			}
			$arData['NAME_LANG'] = (string)$arData['NAME_LANG'];
			$hlblockTitle = ($arData['NAME_LANG'] != '' ? $arData['NAME_LANG'] : $arData['NAME']).' ('.$arData["TABLE_NAME"].')';
			$selected = ($settings["TABLE_NAME"] == $arData['TABLE_NAME']) ? ' selected' : '';
			$cellOption .= '<option '.$selected.' value="'.htmlspecialcharsbx($arData["TABLE_NAME"]).'">'.htmlspecialcharsbx($hlblockTitle).'</option>';
			unset($hlblockTitle);
		}
		unset($arData, $rsData);

		if (!empty($directory))
		{
			$defaultValue = self::getDefaultXmlId($directory);
			if ($defaultValue !== null)
				$arPropertyFields['SET']['DEFAULT_VALUE'] = $defaultValue;
			unset($defaultValue);
		}
		unset($directory);

		$multiple = $arProperty['MULTIPLE'];

		$tablePrefix = self::TABLE_PREFIX;
		$selectDir = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_SELECT_DIR");
		$headingXmlId = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_XML_ID");
		$headingName = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_NAME");
		$headingSort = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_SORT");
		$headingDef = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_DEF");
		$headingLink = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_LINK");
		$headingFile = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_FILE");
		$headingDescription = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_DECSRIPTION");
		$headingFullDescription = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_FULL_DESCRIPTION");
		$directoryName = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_NEW_NAME");
		$directoryMore = Loc::getMessage("HIBLOCK_PROP_DIRECTORY_MORE");

		$emptyDefaultValue = '';
		if ($multiple == 'N')
		{
			$emptyDefaultValue = '<tr id="hlbl_property_tr_empty">'.
				'<td colspan="6" style="text-align: center;">'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_DEFAULT_VALUE').'</td>'.
				'<td style="text-align:center;">'.
				'<input type="radio" name="PROPERTY_VALUES_DEF" id="PROPERTY_VALUES_DEF_EMPTY" value="-1" checked="checked">'.
				'<td colspan="2">&nbsp;</td>'.
				'</tr>';
		}

		return <<<"HIBSELECT"
<script>
function getTableHead()
{
	BX('hlb_directory_table').innerHTML = '<tr class="heading"><td></td><td>$headingName</td><td>$headingSort</td><td>$headingXmlId</td><td>$headingFile</td><td>$headingLink</td><td>$headingDef</td><td>$headingDescription</td><td>$headingFullDescription</td></tr>$emptyDefaultValue';
}

function getDirectoryTableRow(addNew)
{
	addNew = (addNew === 'row' ? 'row' : 'full');
	var obSelectHLBlock = BX('hlb_directory_table_id');
	if (!!obSelectHLBlock)
	{
		var rowNumber = parseInt(BX('hlb_directory_row_number').value, 10);
		if (BX('IB_MAX_ROWS_COUNT'))
			rowNumber = parseInt(BX('IB_MAX_ROWS_COUNT').value, 10);
		if (isNaN(rowNumber))
			rowNumber = 0;
		var hlBlock = (-1 < obSelectHLBlock.selectedIndex ? obSelectHLBlock.options[obSelectHLBlock.selectedIndex].value : '');
		var selectHLBlockValue = hlBlock;

		if (addNew === 'full')
		{
			if (selectHLBlockValue == '-1')
			{
				getTableHead();
				BX('hlb_directory_table_tr').style.display = 'table-row';
				BX('hlb_directory_title_tr').style.display = 'table-row';
				BX('hlb_directory_table_name').style.display = 'table-row';
				BX('hlb_directory_table_name').disabled = false;

				addNew = 'row';
				rowNumber = 0;
			}
			else
			{
				BX('hlb_directory_table_name').disabled = true;
				BX('hlb_directory_title_tr').style.display = 'none';

				BX.ajax.post(
					'highloadblock_directory_ajax.php',
					{
						lang: BX.message('LANGUAGE_ID'),
						sessid: BX.bitrix_sessid(),
						hlBlock: hlBlock,
						rowNumber: rowNumber,
						getTitle: 'Y',
						IBLOCK_ID: '{$iblockID}',
						multiple: '{$multiple}'
					},
					BX.delegate(function(result) {
						BX('hlb_directory_table').innerHTML = result;
					})
				);

			}
		}
		if (addNew === 'row')
		{
			BX.ajax.loadJSON(
				'highloadblock_directory_ajax.php',
				{
					lang: BX.message('LANGUAGE_ID'),
					sessid: BX.bitrix_sessid(),
					hlBlock: hlBlock,
					rowNumber: rowNumber,
					addEmptyRow: 'Y',
					IBLOCK_ID: '{$iblockID}',
					multiple: '{$multiple}'
				},
				BX.delegate(function(result) {
					var obRow = null,
						obTable = BX('hlb_directory_table'),
						i = '',
						obCell = null,
						rowNumber = 0;

					if (!!obTable && 'object' === typeof result)
					{
						rowNumber = parseInt(BX('hlb_directory_row_number').value, 10);
						if (!!BX('IB_MAX_ROWS_COUNT'))
							rowNumber = parseInt(BX('IB_MAX_ROWS_COUNT').value, 10);
						if (isNaN(rowNumber))
							rowNumber = 0;
						obRow = obTable.insertRow(obTable.rows.length);
						obRow.id = 'hlbl_property_tr_'+rowNumber;
						for (i in result)
						{
							obCell = obRow.insertCell(-1);
							BX.adjust(obCell, { style: result[i].style, html: result[i].html });
						}
						BX('hlb_directory_row_number').value = rowNumber + 1;
						if(BX('IB_MAX_ROWS_COUNT'))
							BX('IB_MAX_ROWS_COUNT').value = rowNumber + 1;
					}
				})
			);
		}
	}
}
function getDirectoryTableHead(e)
{
	e.value = BX.translit(e.value, {
		'change_case' : 'L',
		'replace_space' : '',
		'delete_repeat_replace' : true
	});

	var obSelectHLBlock = BX('hlb_directory_table_id');
	if (!!obSelectHLBlock)
	{
		if (-1 < obSelectHLBlock.selectedIndex && '-1' == obSelectHLBlock.options[obSelectHLBlock.selectedIndex].value)
		{
			BX('hlb_directory_table_id_hidden').disabled = false;
			BX('hlb_directory_table_id_hidden').value = '{$tablePrefix}'+BX('hlb_directory_table_name').value;
			BX('hlb_directory_table_id_hidden').value = BX('hlb_directory_table_id_hidden').value.substr(0, 30);
		}
	}
}

</script>
<tr>
	<td>{$selectDir}:</td>
	<td>
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[TABLE_NAME]" disabled id="hlb_directory_table_id_hidden">
		<select name="{$strHTMLControlName["NAME"]}[TABLE_NAME]" id="hlb_directory_table_id" onchange="getDirectoryTableRow('full');"/>
			$cellOption
		</select>
	</td>
</tr>
<tr id="hlb_directory_title_tr" class="adm-detail-required-field">
	<td>$directoryName</td>
	<td>
		<input type="hidden" value="0" id="hlb_directory_row_number">
		<input type="text" name="HLB_NEW_TITLE" size="30" id="hlb_directory_table_name" onchange="getDirectoryTableHead(this);">
	</td>
</tr>
<tr id="hlb_directory_table_tr">
	<td colspan="2" style="text-align: center;">
		<table class="internal" id="hlb_directory_table" style="margin: 0 auto;">
			<script>getDirectoryTableRow('full');</script>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2" style="text-align: center;">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_NAME]" value="{$headingName}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_SORT]" value="{$headingSort}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_XML_ID]" value="{$headingXmlId}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_FILE]" value="{$headingFile}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_LINK]" value="{$headingLink}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_DEF]" value="{$headingDef}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_DESCRIPTION]" value="{$headingDescription}">
		<input type="hidden" name="{$strHTMLControlName["NAME"]}[LANG][UF_FULL_DESCRIPTION]" value="{$headingFullDescription}">
		<div style="width: 100%; text-align: center; margin: 10px 0;">
		<input type="button" value="{$directoryMore}" onclick="getDirectoryTableRow('row');" id="hlb_directory_table_button" class="adm-btn-big">
		</div>
	</td>
</tr>
HIBSELECT;
	}

	/**
	 * Return html for edit single value.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $value					Current value.
	 * @param array $strHTMLControlName		Control description.
	 * @return string
	 */
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName): string
	{
		$settings = CIBlockPropertyDirectory::PrepareSettings($arProperty);
		$size = ($settings["size"] > 1 ? ' size="'.$settings["size"].'"' : '');
		$width = ($settings["width"] > 0 ? ' style="width:'.$settings["width"].'px"' : '');

		$options = CIBlockPropertyDirectory::GetOptionsHtml($arProperty, array($value["VALUE"]));
		$html = '<select name="'.$strHTMLControlName["VALUE"].'"'.$size.$width.'>';
		$html .= $options;
		$html .= '</select>';
		return  $html;
	}

	/**
	 * Return html for public edit value.
	 *
	 * @param array $property			Property description.
	 * @param array $value				Current value.
	 * @param array $control			Control description.
	 * @return string
	 */
	public static function GetPublicEditHTML($property, $value, $control): string
	{
		$multi = (isset($property['MULTIPLE']) && $property['MULTIPLE'] == 'Y');

		$settings = CIBlockPropertyDirectory::PrepareSettings($property);
		$size = ($settings['size'] > 1 ? ' size="'.$settings['size'].'"' : '');
		$width = ($settings['width'] > 0 ? ' style="width:'.$settings['width'].'px"' : ' style="margin-bottom:3px"');

		$html = '<select '.($multi ? 'multiple' : '').' name="'.$control['VALUE'].($multi ? '[]' : '').'"'.$size.$width.'>';
		$html .= CIBlockPropertyDirectory::GetOptionsHtml($property, $value);
		$html .= '</select>';

		return $html;
	}

	/**
	 * Return html for public edit multi values.
	 *
	 * @param array $property			Property description.
	 * @param array $value				Current value.
	 * @param array $control			Control description.
	 * @return string
	 */
	public static function GetPublicEditHTMLMulty($property, $value, $control): string
	{
		$settings = CIBlockPropertyDirectory::PrepareSettings($property);
		$settings['size'] = ($settings['size'] <= 1 ? 5 : $settings['size']);

		$width = ($settings['width'] > 0 ? ' style="width:'.$settings['width'].'px"' : ' style="margin-bottom:3px"');

		$html = '<select multiple name="'.$control['VALUE'].'[]" size="'.$settings['size'].'"'.$width.'>';
		$html .= CIBlockPropertyDirectory::GetOptionsHtml($property, self::normalizeValue($value));
		$html .= '</select>';

		return $html;
	}

	/**
	 * Returns list values.
	 *
	 * @param array $arProperty			Property description.
	 * @param array $values				Current value.
	 * @return string
	 */
	public static function GetOptionsHtml($arProperty, $values): string
	{
		$selectedValue = false;
		$cellOption = '';
		$defaultOption = '';
		$highLoadIBTableName = ($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"] ?? '');
		if($highLoadIBTableName != '')
		{
			if (empty(self::$arFullCache[$highLoadIBTableName]))
			{
				self::$arFullCache[$highLoadIBTableName] = self::getEntityFieldsByFilter(
					$highLoadIBTableName,
					array(
						'select' => array('UF_XML_ID', 'UF_NAME', 'ID')
					)
				);
			}
			foreach(self::$arFullCache[$highLoadIBTableName] as $data)
			{
				$options = '';
				if(in_array($data["UF_XML_ID"], $values))
				{
					$options = ' selected';
					$selectedValue = true;
				}
				$cellOption .= '<option '.$options.' value="'.htmlspecialcharsbx($data['UF_XML_ID']).'">'.htmlspecialcharsEx($data["UF_NAME"].' ['.$data["ID"]).']</option>';
			}
			$defaultOption = '<option value=""'.($selectedValue ? '' : ' selected').'>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_VALUE').'</option>';
		}
		else
		{
			$cellOption = '<option value="" selected>'.Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_VALUE').'</option>';
		}
		return $defaultOption.$cellOption;
	}

	/**
	 * Returns data for list.
	 *
	 * @param array $arProperty Property description.
	 * @return array
	 */
	public static function GetOptionsData($arProperty): array
	{
		$listData = array();

		if(isset($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
		{
			$highLoadIBTableName = $arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"];
			if (empty(self::$arFullCache[$highLoadIBTableName]))
			{
				self::$arFullCache[$highLoadIBTableName] = self::getEntityFieldsByFilter(
					$highLoadIBTableName,
					array("select" => array("UF_XML_ID", "UF_NAME", "ID"))
				);
			}
			foreach(self::$arFullCache[$highLoadIBTableName] as $data)
			{
				$listData[$data['UF_XML_ID']] = $data["UF_NAME"]." [".$data["ID"]."]";
			}
		}

		return $listData;
	}

	/**
	 * Returns data for smart filter.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $value					Current value.
	 * @return false|array
	 */
	public static function GetExtendedValue($arProperty, $value)
	{
		if (!isset($value['VALUE']))
			return false;

		if (is_array($value['VALUE']) && count($value['VALUE']) == 0)
			return false;

		if (empty($arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']))
			return false;

		$tableName = $arProperty['USER_TYPE_SETTINGS']['TABLE_NAME'];
		if (!isset(self::$arItemCache[$tableName]))
			self::$arItemCache[$tableName] = array();

		if (is_array($value['VALUE']) || !isset(self::$arItemCache[$tableName][$value['VALUE']]))
		{
			$data = self::getEntityFieldsByFilter(
				$arProperty['USER_TYPE_SETTINGS']['TABLE_NAME'],
				array(
					'select' => array('UF_XML_ID', 'UF_NAME'),
					'filter' => array('=UF_XML_ID' => $value['VALUE'])
				)
			);

			if (!empty($data))
			{
				foreach ($data as $item)
				{
					if (isset($item['UF_XML_ID']))
					{
						$item['VALUE'] = $item['UF_NAME'];
						if (isset($item['UF_FILE']))
						{
							$item['FILE_ID'] = $item['UF_FILE'];
						}
						self::$arItemCache[$tableName][$item['UF_XML_ID']] = $item;
					}
				}
			}
		}

		if (is_array($value['VALUE']))
		{
			$result = array();
			foreach ($value['VALUE'] as $prop)
			{
				if (isset(self::$arItemCache[$tableName][$prop]))
				{
					$result[$prop] = self::$arItemCache[$tableName][$prop];
				}
				else
				{
					$result[$prop] = false;
				}
			}
			return $result;
		}
		else
		{
			if (isset(self::$arItemCache[$tableName][$value['VALUE']]))
			{
				return self::$arItemCache[$tableName][$value['VALUE']];
			}
		}
		return false;
	}

	/**
	 * Returns admin list view html.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $value					Current value.
	 * @param array $strHTMLControlName		Control description.
	 * @return string
	 */
	public static function GetAdminListViewHTML(
		$arProperty,
		$value,
		/** @noinspection PhpUnusedParameterInspection */$strHTMLControlName
	): string
	{
		$dataValue = self::GetExtendedValue($arProperty, $value);
		if ($dataValue)
		{
			return htmlspecialcharsbx($dataValue['UF_NAME']);
		}
		return '';
	}

	/**
	 * Return public list view html (module list).
	 *
	 * @param array $arProperty				Property description.
	 * @param array $value					Current value.
	 * @param array $strHTMLControlName		Control description.
	 * @return string
	 */
	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName): string
	{
		$dataValue = self::GetExtendedValue($arProperty, $value);
		if ($dataValue)
		{
			if (isset($strHTMLControlName['MODE']) && 'CSV_EXPORT' == $strHTMLControlName['MODE'])
				return $dataValue['UF_XML_ID'];
			elseif (isset($strHTMLControlName['MODE']) && ('SIMPLE_TEXT' == $strHTMLControlName['MODE'] || 'ELEMENT_TEMPLATE' == $strHTMLControlName['MODE']))
				return $dataValue['UF_NAME'];
			else
				return htmlspecialcharsbx($dataValue['UF_NAME']);
		}
		return '';
	}

	/**
	 * Return admin filter html.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $strHTMLControlName		Control description.
	 * @return string
	 */
	public static function GetAdminFilterHTML($arProperty, $strHTMLControlName): string
	{
		$lAdmin = new CAdminList($strHTMLControlName["TABLE_ID"]);
		$lAdmin->InitFilter(array($strHTMLControlName["VALUE"]));
		$filterValue = $GLOBALS[$strHTMLControlName["VALUE"]];

		if(isset($filterValue) && is_array($filterValue))
			$values = $filterValue;
		else
			$values = array();

		$settings = CIBlockPropertyDirectory::PrepareSettings($arProperty);
		$size = ($settings["size"] > 1 ? ' size="'.$settings["size"].'"' : '');
		$width = ($settings["width"] > 0 ? ' style="width:'.$settings["width"].'px"' : '');

		$options = CIBlockPropertyDirectory::GetOptionsHtml($arProperty, $values);
		$html = '<select name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.' multiple>';
		$html .= $options;
		$html .= '</select>';
		return  $html;
	}

	/**
	 * Return property value for search.
	 *
	 * @param array $arProperty				Property description.
	 * @param array $value					Current value.
	 * @param array $strHTMLControlName		Control description.
	 * @return string
	 */
	public static function GetSearchContent(
		$arProperty,
		$value,
		/** @noinspection PhpUnusedParameterInspection */$strHTMLControlName
	): string
	{
		if (!isset($value['VALUE']))
			return '';

		if (is_array($value['VALUE']) && empty($value['VALUE'])) // order not change!
			return '';

		$dataValue = self::GetExtendedValue($arProperty, $value);
		if (!empty($dataValue) && is_array($dataValue))
		{
			$result = [];
			if (is_array($value['VALUE']))
			{
				foreach ($value['VALUE'] as $item)
				{
					if (empty($dataValue[$item]) && !is_array($dataValue[$item]))
					{
						continue;
					}
					$result[] = $dataValue[$item]['UF_NAME'] ?? $dataValue[$item]['UF_XML_ID'];
				}
			}
			else
			{
				$result[] = $dataValue['UF_NAME'] ?? $dataValue['UF_XML_ID'];
			}

			return implode(' / ', $result);
		}

		return '';
	}

	/**
	 * Add values in filter.
	 *
	 * @param array $arProperty
	 * @param array $strHTMLControlName
	 * @param array &$arFilter
	 * @param bool &$filtered
	 * @return void
	 */
	public static function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered): void
	{
		$filtered = false;
		$values = array();

		if (isset($_REQUEST[$strHTMLControlName["VALUE"]]))
			$values = (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ? $_REQUEST[$strHTMLControlName["VALUE"]] : array($_REQUEST[$strHTMLControlName["VALUE"]]));
		elseif (isset($GLOBALS[$strHTMLControlName["VALUE"]]))
			$values = (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ? $GLOBALS[$strHTMLControlName["VALUE"]] : array($GLOBALS[$strHTMLControlName["VALUE"]]));

		if (!empty($values))
		{
			$clearValues = array();
			foreach ($values as $oneValue)
			{
				$oneValue = (string)$oneValue;
				if ($oneValue != '')
					$clearValues[] = $oneValue;
			}
			$values = $clearValues;
			unset($oneValue, $clearValues);
		}
		if (!empty($values))
		{
			$filtered = true;
			$arFilter['=PROPERTY_'.$arProperty['ID']] = $values;
		}
	}

	/**
	 * Returns table name for new entity.
	 *
	 * @param string $name			Entity name
	 * @return bool|string
	 */
	public static function createHighloadTableName($name)
	{
		$name = trim((string)$name);
		if ($name == '')
			return false;
		$name = mb_substr(self::TABLE_PREFIX.$name, 0, 30);
		return $name;
	}

	/**
	 * @param array $property
	 * @param array $strHTMLControlName
	 * @param array &$field
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $strHTMLControlName, &$field)
	{
		$tableName = (string)($property['USER_TYPE_SETTINGS']['TABLE_NAME'] ?? '');
		if ($tableName === '')
		{
			return;
		}

		unset($field['value']); // aftefact from \Bitrix\Iblock\Helpers\Filter\PropertyManager::getFilterFields

		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getRow([
			'select' => ['ID'],
			'filter' => ['=TABLE_NAME' => $tableName]
		]);
		if ($hlblock)
		{
			$field['type'] = 'entity_selector';
			$field['params'] = [
				'multiple' => 'Y',
				'dialogOptions' => [
					'entities' => [
						[
							'id' => ElementProvider::ENTITY_ID,
							'dynamicLoad' => true,
							'dynamicSearch' => true,
							'options' => [
								'highloadblockId' => $hlblock['ID'],
							],
						],
					],
					'searchOptions' => [
						'allowCreateItem' => false,
					],
				],
			];
		}
	}

	/**
	 * Returns entity data.
	 *
	 * @param string $tableName				HL table name.
	 * @param array $listDescr				Params for getList.
	 * @return array
	 */
	private static function getEntityFieldsByFilter($tableName, $listDescr = array()): array
	{
		$arResult = array();
		$tableName = (string)$tableName;
		if (!is_array($listDescr))
			$listDescr = array();
		if (!empty($tableName))
		{
			if (!isset(self::$hlblockCache[$tableName]))
			{
				self::$hlblockCache[$tableName] = HL\HighloadBlockTable::getList(
					array(
						'select' => array('TABLE_NAME', 'NAME', 'ID'),
						'filter' => array('=TABLE_NAME' => $tableName)
					)
				)->fetch();
			}
			if (!empty(self::$hlblockCache[$tableName]))
			{
				if (!isset(self::$directoryMap[$tableName]))
				{
					$entity = HL\HighloadBlockTable::compileEntity(self::$hlblockCache[$tableName]);
					self::$hlblockClassNameCache[$tableName] = $entity->getDataClass();
					self::$directoryMap[$tableName] = $entity->getFields();
					unset($entity);
				}
				if (!isset(self::$directoryMap[$tableName]['UF_XML_ID']))
					return $arResult;
				$entityDataClass = self::$hlblockClassNameCache[$tableName];

				$nameExist = isset(self::$directoryMap[$tableName]['UF_NAME']);
				if (!$nameExist)
					$listDescr['select'] = array('UF_XML_ID', 'ID');
				$fileExists = isset(self::$directoryMap[$tableName]['UF_FILE']);
				if ($fileExists)
					$listDescr['select'][] = 'UF_FILE';

				$sortExist = isset(self::$directoryMap[$tableName]['UF_SORT']);
				$listDescr['order'] = array();
				if ($sortExist)
				{
					$listDescr['order']['UF_SORT'] = 'ASC';
					$listDescr['select'][] = 'UF_SORT';
				}
				if ($nameExist)
					$listDescr['order']['UF_NAME'] = 'ASC';
				else
					$listDescr['order']['UF_XML_ID'] = 'ASC';
				$listDescr['order']['ID'] = 'ASC';
				/** @var \Bitrix\Main\DB\Result $rsData */
				$rsData = $entityDataClass::getList($listDescr);
				while($arData = $rsData->fetch())
				{
					if (!$nameExist)
						$arData['UF_NAME'] = $arData['UF_XML_ID'];
					$arData['SORT'] = ($sortExist ? $arData['UF_SORT'] : $arData['ID']);
					$arResult[] = $arData;
				}
				unset($arData, $rsData);
			}
		}
		return $arResult;
	}

	private static function normalizeValue($value): array
	{
		$result = [];
		if (!is_array($value))
		{
			$value = (string)$value;
			if ($value !== '')
				$result[] = $value;
		}
		else
		{
			if (!empty($value))
			{
				if (isset($value['VALUE']) && is_array($value['VALUE']))
				{
					$value = $value['VALUE'];
				}
				foreach ($value as $row)
				{
					$oneValue = '';
					if (is_array($row))
					{
						if (isset($row['VALUE']))
							$oneValue = (string)$row['VALUE'];
					}
					else
					{
						$oneValue = (string)$row;
					}
					if ($oneValue !== '')
						$result[] = $oneValue;
				}
				unset($oneValue, $row);
			}
		}
		return $result;
	}

	/**
	 * @param mixed $identifier
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getDefaultXmlId($identifier): ?string
	{
		$result = null;
		$entity = HL\HighloadBlockTable::compileEntity($identifier);
		$fields = $entity->getFields();
		if (isset($fields['UF_DEF']) && isset($fields['UF_XML_ID']))
		{
			$entityClassName = $entity->getDataClass();

			$select = ['ID', 'UF_XML_ID'];
			$order = [];
			if (isset($fields['UF_SORT']))
			{
				$select[] = 'UF_SORT';
				$order['UF_SORT'] = 'ASC';
			}
			if (isset($fields['UF_NAME']))
			{
				$select[] = 'UF_NAME';
				$order['UF_NAME'] = 'ASC';
			}
			$order['ID'] = 'ASC';

			$iterator = $entityClassName::getList([
				'select' => $select,
				'filter' => ['=UF_DEF' => 1],
				'order' => $order,
				'limit' => 1
			]);
			$row = $iterator->fetch();
			if (!empty($row))
				$result = $row['UF_XML_ID'];
			unset($row, $iterator);
			unset($entityClassName);
		}
		unset($fields, $entity);

		return $result;
	}

	private static function getEntityFieldsForTable($hlTableName)
	{
		if (!isset(self::$arFullCache[$hlTableName]))
		{
			self::$arFullCache[$hlTableName] = static::getEntityFieldsByFilter($hlTableName, [
				'select' => ['UF_XML_ID', 'UF_NAME', 'ID']
			]);
		}

		return self::$arFullCache[$hlTableName];
	}

	public static function GetUIEntityEditorProperty($settings, $value): array
	{
		$hlTableName = (string)($settings['USER_TYPE_SETTINGS']['TABLE_NAME'] ?? '');

		if ($hlTableName === '')
		{
			return [
				'type' => 'custom',
			];
		}

		$gridMode = ($settings['GRID_MODE'] ?? false) === true;
		$hasImages = false;
		$items = [];

		foreach (static::getEntityFieldsForTable($hlTableName) as $data)
		{
			$item = [
				'NAME' => $data['UF_NAME'] ?? '',
				'TEXT' => $data['UF_NAME'] ?? '',
				'VALUE' => $data['UF_XML_ID'],
				'DESCRIPTION' => $data['UF_DESCRIPTION'] ?? '',
			];

			if (isset($data['UF_FILE']) && (int)$data['UF_FILE'] >= 0)
			{
				$hasImages = true;
				$item['IMAGE'] = $data['UF_FILE'];
			}

			if ($hasImages)
			{
				$image = \CFile::GetFileArray($data['UF_FILE']) ?: null;
				$item['IMAGE_SRC'] = $image['SRC'];
				if ($image)
				{
					if ($settings['MULTIPLE'] === 'Y')
					{
						$item['HTML'] = "<span class=\"catalog-multi-list-dictionary-select-icon\" style=\"background-image:url('{$image['SRC']}');\"></span> ".htmlspecialcharsbx($item['NAME']);
					}
					else
					{
						$item['NAME'] = "<span class=\"catalog-list-dictionary-select-icon\" style=\"background-image:url('{$image['SRC']}');\"></span> ".htmlspecialcharsbx($item['NAME']);
					}
				}
				else
				{
					if ($settings['MULTIPLE'] !== 'Y')
					{
						$item['NAME'] = htmlspecialcharsbx($item['NAME']);
					}
				}
			}

			$items[] = $item;
		}

		if ($settings['MULTIPLE'] === 'Y')
		{
			$type = 'multilist';
		}
		elseif ($hasImages && $gridMode)
		{
			$type = 'custom';
		}
		else
		{
			$type = 'list';
		}

		return [
			'type' => $type,
			'data' => [
				'userType' => 'directory',
				'isHtml' => $hasImages,
				'items' => $items,
				'enableEmptyItem' => $settings['IS_REQUIRED'] === 'N',
			],
		];
	}

	public static function GetUIEntityEditorPropertyEditHtml(array $params = []) : string
	{
		$settings = $params['SETTINGS'] ?? [];
		$hlTableName = (string)($settings['USER_TYPE_SETTINGS']['TABLE_NAME'] ?? '');

		if ($hlTableName === '')
		{
			return '';
		}

		$propertyId = $settings['ID'];
		$popupId = 'directory_popup_'.CUtil::JSEscape($propertyId);

		$inputHtml = '';
		$labelHtml = '';
		$selectedHtml = '';

		$entityFields = static::getEntityFieldsForTable($hlTableName);

		if ($settings['IS_REQUIRED'] === 'N')
		{
			array_unshift($entityFields, [
				'UF_XML_ID' => '0',
				'UF_NAME' => Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_GRID_VALUE'),
			]);
		}

		$checkedXmlId = null;

		foreach ($entityFields as $field)
		{
			if ($field['UF_XML_ID'] === $params['VALUE'])
			{
				$checkedXmlId = $field['UF_XML_ID'];
				break;
			}
		}

		if (!$checkedXmlId && !empty($entityFields))
		{
			$checkedXmlId = reset($entityFields)['UF_XML_ID'];
		}

		foreach ($entityFields as $field)
		{
			$checked = $field['UF_XML_ID'] === $checkedXmlId;
			$name = HtmlFilter::encode($field['UF_NAME']);
			$xmlId = HtmlFilter::encode($field['UF_XML_ID']);

			$image = null;
			if (!empty($field['UF_FILE']))
			{
				$image = \CFile::GetFileArray($field['UF_FILE']) ?: null;
			}

			if ($checked)
			{
				if (!empty($image['SRC']))
				{
					$selectedHtml .= "<span class=\"catalog-productcard-select-btn-color-icon\" style=\"background-image:url('{$image['SRC']}');\"></span>";
				}

				$selectedHtml .= " <span class=\"catalog-productcard-select-param-text\">{$name}</span>";
			}

			$inputName = $params['FIELD_NAME'].'_'.$params['ELEMENT_ID'];
			$inputId = $params['FIELD_NAME'].'_'.$xmlId.'_'.$params['ELEMENT_ID'];
			$checkedValue = $checked ? 'checked="checked"' : '';
			$inputHtml .= "<input style=\"display: none;\" type=\"radio\" name=\"$inputName\" id=\"$inputId\" value=\"$xmlId\" $checkedValue />";

			$class = $checked ? ' selected' : '';
			$imageHtml = '';

			if (!empty($image['SRC']))
			{
				$imageHtml .= "<span class=\"catalog-productcard-select-btn-color-icon\" style=\"background-image:url('{$image['SRC']}');\"></span>";
			}

			$html = <<<LABEL
<li class="catalog-productcard-popup-select-item$class">
	<label for="$inputId" data-role="label_$xmlId" class="catalog-productcard-popup-select-label"
		onclick="selectDropDownItem(event, this, '$popupId')">
		$imageHtml <span class="catalog-productcard-popup-select-text">$name</span>
	</label>
</li>
LABEL;
			$labelHtml .= $html;
		}

		if ($selectedHtml === '')
		{
			$selectedHtml = Loc::getMessage('HIBLOCK_PROP_DIRECTORY_EMPTY_GRID_VALUE');
		}

		return <<<HTML
<div class="catalog-productcard-select">
	<div class="catalog-productcard-select-container">
		<div class="catalog-productcard-select-block" onclick="showDropDownPopup(event, this, '$popupId')">
			<div class="catalog-productcard-select-text fix" data-role="currentOption">
				$selectedHtml
			</div>
			<div class="catalog-productcard-select-arrow"></div>
			$inputHtml
			<div class="catalog-productcard-popup-select" data-role="dropdownContent" style="display: none">
				<ul class="catalog-productcard-popup-select-inner" data-propertyId="$propertyId">$labelHtml</ul>
			</div>
		</div>
	</div>
</div>
<script>
	if (!window.showDropDownPopup)
	{
		window.showDropDownPopup = function(event, element, popupId)
		{
			var popup = BX.Main.PopupManager.getPopupById("prop_directory_" + popupId);
			if (popup)
			{
				popup.close();
				return;
			}

			var contentNode = BX.clone(element.querySelector('[data-role="dropdownContent"]'));
			var items = contentNode.querySelectorAll('label');
			for (var i in items)
			{
				if (items.hasOwnProperty(i))
				{
					var input = document.getElementById(items[i].getAttribute('for'));
					if (BX.type.isDomNode(input) && input.checked)
					{
						BX.addClass(items[i].parentNode, 'selected');
					}
					else
					{
						BX.removeClass(items[i].parentNode, 'selected');
					}
				}
			}

			popup = BX.Main.PopupManager.create(
				"prop_directory_" + popupId,
				element,
				{
					cacheable: false,
					autoHide: true,
					offsetLeft: 0,
					padding: 0,
					offsetTop: 3,
					minWidth: 400,
					overlay: false,
					draggable: {restrict: true},
					closeByEsc: true,
					content: contentNode
				}
			);
			popup.show();
		};
	}

	if (!window.selectDropDownItem)
	{
		window.selectDropDownItem = function(event, element, popupId)
		{
			var popup = BX.Main.PopupManager.getPopupById("prop_directory_" + popupId);
			if (popup)
			{
				var currentOption = popup.bindElement.querySelector('[data-role="currentOption"]');
				currentOption.innerHTML = element.innerHTML;
			}
		};
	}
</script>
HTML;
	}

	public static function GetUIEntityEditorPropertyViewHtml(array $params = []) : string
	{
		$settings = $params['SETTINGS'] ?? [];
		$value = ['VALUE' => $params['VALUE'] ?? ''];

		$viewHtml = '';

		$dataValue = static::getExtendedValue($settings, $value);
		if (isset($dataValue['UF_NAME']))
		{
			$viewHtml .= '<div class="brandblock-block-wrapper">';

			if (!empty($dataValue['UF_FILE']))
			{
				$image = \CFile::GetFileArray($dataValue['UF_FILE']);
				if ($image)
				{
					$viewHtml .= '<span class="brandblock-block" style="background-image:url(\'';
					$viewHtml .= $image['SRC'];
					$viewHtml .= '\');"></span>';
				}
			}

			$viewHtml .= htmlspecialcharsbx($dataValue['UF_NAME']);
			$viewHtml .= '</div>';
		}


		return $viewHtml;
	}
}
