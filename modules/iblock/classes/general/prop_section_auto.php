<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertySectionProvider;

class CIBlockPropertySectionAutoComplete extends CIBlockPropertyElementAutoComplete
{
	/** @deprecated */
	public const USER_TYPE = Iblock\PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE;

	public static function GetUserTypeDescription()
	{
		return [
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_SECTION,
			'USER_TYPE' => Iblock\PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE,
			'DESCRIPTION' => Loc::getMessage('BT_UT_SAUTOCOMPLETE_DESCR'),
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetPropertyFieldHtmlMulty' => [__CLASS__,'GetPropertyFieldHtmlMulty'],
			'GetAdminListViewHTML' => [__CLASS__,'GetAdminListViewHTML'],
			'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
			'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
			'GetPublicEditHTMLMulty' => [__CLASS__, 'GetPublicEditHTML'],
			'GetAdminFilterHTML' => [__CLASS__,'GetAdminFilterHTML'],
			'GetSettingsHTML' => [__CLASS__,'GetSettingsHTML'],
			'PrepareSettings' => [__CLASS__,'PrepareSettings'],
			'AddFilterFields' => [__CLASS__,'AddFilterFields'],
			'GetPublicFilterHTML' => [__CLASS__,'GetPublicFilterHTML'],
			'GetUIFilterProperty' => [__CLASS__, 'GetUIFilterProperty'],
			'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
			'GetUIEntityEditorPropertyEditHtml' => [__CLASS__, 'GetUIEntityEditorPropertyEditHtml'],
			'GetUIEntityEditorPropertyViewHtml' => [__CLASS__, 'GetUIEntityEditorPropertyViewHtml'],
		];
	}

	public static function GetValueForAutoComplete($arProperty,$arValue,$arBanSym="",$arRepSym="")
	{
		$strResult = '';
		$mxResult = static::GetPropertyValue($arProperty,$arValue);
		if (is_array($mxResult))
		{
			$strResult = htmlspecialcharsbx(str_replace($arBanSym,$arRepSym,$mxResult['~NAME'])).' ['.$mxResult['ID'].']';
		}

		return $strResult;
	}

	public static function GetValueForAutoCompleteMulti($arProperty,$arValues,$arBanSym="",$arRepSym="")
	{
		$arResult = [];

		if (is_array($arValues))
		{
			if (array_key_exists('VALUE', $arValues))
			{
				if (is_array($arValues['VALUE']))
				{
					$arValues = $arValues['VALUE'];
				}
				else
				{
					$arValues = [$arValues['VALUE']];
				}
			}
			foreach ($arValues as $intPropertyValueID => $arOneValue)
			{
				if (!is_array($arOneValue))
				{
					$strTmp = $arOneValue;
					$arOneValue = array(
						'VALUE' => $strTmp,
					);
				}
				$mxResult = static::GetPropertyValue($arProperty,$arOneValue);
				if (is_array($mxResult))
				{
					$arResult[$intPropertyValueID] = htmlspecialcharsbx(str_replace($arBanSym,$arRepSym,$mxResult['~NAME'])).' ['.$mxResult['ID'].']';
				}
			}
		}

		return !empty($arResult) ? $arResult : false;
	}

	public static function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		return self::GetAdminFilterHTML($arProperty, $strHTMLControlName);
	}

	public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		$arSymbols = static::GetSymbols($arSettings);

		$arProperty['LINK_IBLOCK_ID'] = (int)$arProperty['LINK_IBLOCK_ID'];
		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		if (isset($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'iblock_element_admin')
		{
			$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
				'&amp;IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
				'&amp;n='.urlencode($strHTMLControlName["VALUE"]).
				'&amp;hideiblock='.$arProperty['IBLOCK_ID'].
				($fixIBlock ? '&amp;iblockfix=y' : '').
				'&amp;tableId='.$windowTableId;
			$mxElement = static::GetPropertyValue($arProperty,$arValue);
			if (!is_array($mxElement))
			{
				$strResult = '<input type="text" name="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" id="'.$strHTMLControlName["VALUE"].'" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'" ></span>';
			}
			else
			{
				$strResult = '<input type="text" name="'.$strHTMLControlName["VALUE"].'" id="'.$strHTMLControlName["VALUE"].'" value="'.$arValue['VALUE'].'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'" >'.$mxElement['NAME'].'</span>';
			}
			unset($searchUrl);
		}
		else
		{
			ob_start();
			?><input type="hidden" name="<?=$strHTMLControlName["VALUE"]?>" value="" /><?
			$control_id = $APPLICATION->IncludeComponent(
				"bitrix:main.lookup.input",
				"iblockedit",
				array(
					"CONTROL_ID" => preg_replace(
						"/[^a-zA-Z0-9_]/i",
						"x",
						$strHTMLControlName["VALUE"].'_'.mt_rand(0, 10000)
					),
					"INPUT_NAME" => $strHTMLControlName["VALUE"],
					"INPUT_NAME_STRING" => "inp_".$strHTMLControlName["VALUE"],
					"INPUT_VALUE_STRING" => htmlspecialcharsback(static::GetValueForAutoComplete(
						$arProperty,
						$arValue,
						$arSymbols['BAN_SYM'],
						$arSymbols['REP_SYM']
					)),
					"START_TEXT" => Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_INVITE'),
					"MULTIPLE" => $arProperty["MULTIPLE"],
					"MAX_WIDTH" => $arSettings['MAX_WIDTH'],
					"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
					'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
					'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
					'REP_SYM' => $arSymbols['REP_SYM_STRING'],
					'FILTER' => 'Y',
					'TYPE' => 'SECTION'
				), null, array("HIDE_ICONS" => "Y")
			);
			?><?
			if ($arSettings['VIEW'] == 'E')
			{
				$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
					'&IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
					'&n=&k=&lookup=jsMLI_'.$control_id.
					'&hideiblock='.$arProperty['IBLOCK_ID'].
					($fixIBlock ? '&iblockfix=y' : '').
					'&tableId='.$windowTableId;
				?><input
				style="float: left; margin-right: 10px; margin-top: 5px;"
				type="button" value="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
				title="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<?=$searchUrl; ?>', 900, 700);"><?
				unset($searchUrl);
			}
			if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock)
			{
				$strButtonCaption = '';
				if ($arSettings['IBLOCK_MESS'] == 'Y')
				{
					$arLangMess = CIBlock::GetMessages($arProperty["LINK_IBLOCK_ID"]);
					$strButtonCaption = $arLangMess['SECTION_ADD'];
					unset($arLangMess);
				}
				if ($strButtonCaption == '')
					$strButtonCaption = Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_NEW_ELEMENT');
				?><input
				type="button"
				style="margin-top: 5px;"
				value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
				title="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/'.CIBlock::GetAdminSectionEditLink(
					$arProperty["LINK_IBLOCK_ID"],
					null,
					array(
						'menu' => null,
						'IBLOCK_SECTION_ID' => -1,
						'find_section_section' => -1,
						'lookup' => 'jsMLI_'.$control_id,
						'tableId' => $windowTableId
					),
					($fixIBlock ? '&iblockfix=y' : '')
					); ?>', 900, 700);"
				><?
			}
			$strResult = ob_get_contents();
			ob_end_clean();
		}

		return $strResult;
	}

	public static function GetPropertyFieldHtmlMulty($arProperty, $arValues, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		$arSymbols = static::GetSymbols($arSettings);

		$arProperty['LINK_IBLOCK_ID'] = (int)$arProperty['LINK_IBLOCK_ID'];
		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		if (isset($strHTMLControlName['MODE']) && ('iblock_element_admin' == $strHTMLControlName['MODE']))
		{
			$arResult = [];
			foreach ($arValues as $intPropertyValueID => $arOneValue)
			{
				$mxElement = static::GetPropertyValue($arProperty,$arOneValue);
				if (is_array($mxElement))
				{
					$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
						'&amp;IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].
						'&amp;n='.urlencode($strHTMLControlName["VALUE"].'['.$intPropertyValueID.']').
						'&amp;hideiblock='.$arProperty['IBLOCK_ID'].
						($fixIBlock ? '&amp;iblockfix=y' : '').
						'&amp;tableId='.$windowTableId;
					$arResult[] = '<input type="text" name="'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" id="'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" value="'.$arOneValue['VALUE'].'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" >'.$mxElement['NAME'].'</span>';
					unset($searchUrl);
				}
			}

			if (0 < intval($arProperty['MULTIPLE_CNT']))
			{
				for ($i = 0; $i < $arProperty['MULTIPLE_CNT']; $i++)
				{
					$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
						'&amp;IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].
						'&amp;n='.urlencode($strHTMLControlName["VALUE"].'[n'.$i.']').
						'&amp;hideiblock='.$arProperty['IBLOCK_ID'].
						($fixIBlock ? '&amp;iblockfix=y' : '').
						'&amp;tableId='.$windowTableId;
					$arResult[] = '<input type="text" name="'.$strHTMLControlName["VALUE"].'[n'.$i.']" id="'.$strHTMLControlName["VALUE"].'[n'.$i.']" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'[n'.$i.']" ></span>';
				}
				unset($searchUrl);
			}

			$strResult = implode('<br />',$arResult);
		}
		else
		{
			$mxResultValue = static::GetValueForAutoCompleteMulti($arProperty,$arValues,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
			$strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n",$mxResultValue)) : '');

			ob_start();
			?><input type="hidden" name="<?=$strHTMLControlName["VALUE"]?>" value="" /><?
			$control_id = $APPLICATION->IncludeComponent(
				"bitrix:main.lookup.input",
				"iblockedit",
				array(
					"CONTROL_ID" => preg_replace(
						"/[^a-zA-Z0-9_]/i",
						"x",
						$strHTMLControlName["VALUE"].'_'.mt_rand(0, 10000)
					),
					"INPUT_NAME" => $strHTMLControlName['VALUE'].'[]',
					"INPUT_NAME_STRING" => "inp_".$strHTMLControlName['VALUE'],
					"INPUT_VALUE_STRING" => $strResultValue,
					"START_TEXT" => Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_INVITE'),
					"MULTIPLE" => $arProperty["MULTIPLE"],
					"MAX_WIDTH" => $arSettings['MAX_WIDTH'],
					"MIN_HEIGHT" => $arSettings['MIN_HEIGHT'],
					"MAX_HEIGHT" => $arSettings['MAX_HEIGHT'],
					"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
					'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
					'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
					'REP_SYM' => $arSymbols['REP_SYM_STRING'],
					'FILTER' => 'Y',
					'TYPE' => 'SECTION'
				), null, array("HIDE_ICONS" => "Y")
			);
			?><?
			if ($arSettings['VIEW'] == 'E')
			{
				$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
					'&IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].
					'&m=y&n=&k=&lookup=jsMLI_'.$control_id.
					'&hideiblock='.$arProperty['IBLOCK_ID'].
					($fixIBlock ? '&iblockfix=y' : '').
					'&tableId='.$windowTableId;
				?><input
				style="float: left; margin-right: 10px; margin-top: 5px;"
				type="button" value="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
				title="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT_MULTI_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<?=$searchUrl; ?>', 900, 700);"><?
				unset($searchUrl);
			}
			if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock)
			{
				$strButtonCaption = '';
				if ($arSettings['IBLOCK_MESS'] == 'Y')
				{
					$arLangMess = CIBlock::GetMessages($arProperty["LINK_IBLOCK_ID"]);
					$strButtonCaption = $arLangMess['SECTION_ADD'];
					unset($arLangMess);
				}
				if ($strButtonCaption == '')
					$strButtonCaption = Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_NEW_ELEMENT');
				?><input
				type="button"
				style="margin-top: 5px;"
				value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
				title="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/'.CIBlock::GetAdminSectionEditLink(
					$arProperty["LINK_IBLOCK_ID"],
					null,
					array(
						'menu' => null,
						'IBLOCK_SECTION_ID' => -1,
						'find_section_section' => -1,
						'lookup' => 'jsMLI_'.$control_id,
						'tableId' => $windowTableId
					),
					($fixIBlock ? '&iblockfix=y' : '')
					); ?>', 900, 700);"
				><?
				unset($strButtonCaption);
			}
			$strResult = ob_get_contents();
			ob_end_clean();
		}

		return $strResult;
	}

	public static function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
	{
		$strResult = '';
		$mxResult = static::GetPropertyValue($arProperty,$arValue);
		if (is_array($mxResult))
		{
			$strResult = $mxResult['NAME'].' [<a href="/bitrix/admin/'.
				CIBlock::GetAdminSectionEditLink(
					$mxResult['IBLOCK_ID'],
					$mxResult['ID'],
					array(
						'WF' => 'Y'
					)
				).'" title="'.Loc::getMessage("BT_UT_SAUTOCOMPLETE_MESS_ELEMENT_EDIT").'">'.$mxResult['ID'].'</a>]';
		}

		return $strResult;
	}

	public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
	{
		static $cache = array();

		$strResult = '';
		$arValue['VALUE'] = (int)($arValue['VALUE'] ?? 0);
		if ($arValue['VALUE'] > 0)
		{
			if (!isset($cache[$arValue['VALUE']]))
			{
				$arFilter = array();
				$intIBlockID = intval($arProperty['LINK_IBLOCK_ID']);
				if (0 < $intIBlockID) $arFilter['IBLOCK_ID'] = $intIBlockID;
				$arFilter['ID'] = $arValue['VALUE'];
				$arFilter["ACTIVE"] = "Y";
				$arFilter["CHECK_PERMISSIONS"] = "Y";
				$arFilter["MIN_PERMISSION"] = "R";
				$rsElements = CIBlockSection::GetList(
					array(),
					$arFilter,
					false,
					array("ID","IBLOCK_ID","NAME","SECTION_PAGE_URL")
				);
				if (isset($strHTMLControlName['SECTION_URL']))
				{
					$rsElements->SetUrlTemplates('', $strHTMLControlName['SECTION_URL']);
				}
				$cache[$arValue['VALUE']] = $rsElements->GetNext(true,true);
			}
			if (is_array($cache[$arValue['VALUE']]))
			{
				if (isset($strHTMLControlName['MODE']) && 'CSV_EXPORT' == $strHTMLControlName['MODE'])
				{
					$strResult = $cache[$arValue['VALUE']]['ID'];
				}
				elseif (isset($strHTMLControlName['MODE']) && ('SIMPLE_TEXT' == $strHTMLControlName['MODE'] || 'ELEMENT_TEMPLATE' == $strHTMLControlName['MODE']))
				{
					$strResult = $cache[$arValue['VALUE']]["~NAME"];
				}
				else
				{
					$strResult = '<a href="'.$cache[$arValue['VALUE']]["SECTION_PAGE_URL"].'">'.$cache[$arValue['VALUE']]["NAME"].'</a>';
				}
			}
		}

		return $strResult;
	}

	public static function GetPublicEditHTML($property, $value, $control)
	{
		global $APPLICATION;

		$multi = (isset($property['MULTIPLE']) && $property['MULTIPLE'] == 'Y');
		$settings = static::PrepareSettings($property);
		$symbols = static::GetSymbols($settings);
		$fixIBlock = $property["LINK_IBLOCK_ID"] > 0;

		ob_start();

		if ($multi)
		{
			$resultValue = static::GetValueForAutoCompleteMulti(
				$property,
				$value,
				$symbols['BAN_SYM'],
				$symbols['REP_SYM']
			);
			$resultValue = (is_array($resultValue) ? htmlspecialcharsback(implode("\n",$resultValue)) : '');
		}
		else
		{
			$resultValue = htmlspecialcharsback(static::GetValueForAutoComplete(
				$property,
				$value,
				$symbols['BAN_SYM'],
				$symbols['REP_SYM']
			));
		}

		$APPLICATION->IncludeComponent(
			'bitrix:main.lookup.input',
			'iblockedit',
			array(
				'CONTROL_ID' => preg_replace(
					"/[^a-zA-Z0-9_]/i",
					"x",
					$control['VALUE'].'_'.mt_rand(0, 10000)
				),
				'INPUT_NAME' => $control['VALUE'].($multi ? '[]' : ''),
				'INPUT_NAME_STRING' => 'inp_'.$control['VALUE'],
				'INPUT_VALUE_STRING' => $resultValue,
				'START_TEXT' => Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_INVITE'),
				'MULTIPLE' => $property['MULTIPLE'],
				'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
				'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
				'BAN_SYM' => $symbols['BAN_SYM_STRING'],
				'REP_SYM' => $symbols['REP_SYM_STRING'],
				'MAX_WIDTH' => $settings['MAX_WIDTH'],
				'MIN_HEIGHT' => $settings['MIN_HEIGHT'],
				'MAX_HEIGHT' => $settings['MAX_HEIGHT'],
				'FILTER' => 'Y',
				'TYPE' => 'SECTION'
			),
			($control['PARENT_COMPONENT'] ?? null),
			array('HIDE_ICONS' => 'Y')
		);

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	public static function PrepareSettings($arFields)
	{
		/*
		 * VIEW				- view type
		 * SHOW_ADD			- show button for add new values in linked iblock
		 * MAX_WIDTH		- max width textarea and input in pixels
		 * MIN_HEIGHT		- min height textarea in pixels
		 * MAX_HEIGHT		- max height textarea in pixels
		 * BAN_SYM			- banned symbols string
		 * REP_SYM			- replace symbol
		 * OTHER_REP_SYM	- non standart replace symbol
		 * IBLOCK_MESS		- get lang mess from linked iblock
		 */
		$arViewsList = static::GetPropertyViewsList(false);
		$strView = (isset($arFields['USER_TYPE_SETTINGS']['VIEW']) && in_array($arFields['USER_TYPE_SETTINGS']['VIEW'],$arViewsList) ? $arFields['USER_TYPE_SETTINGS']['VIEW'] : current($arViewsList));

		$strShowAdd = ($arFields['USER_TYPE_SETTINGS']['SHOW_ADD'] ?? '');
		$strShowAdd = ('Y' == $strShowAdd ? 'Y' : 'N');
		if ((int)($arFields['LINK_IBLOCK_ID'] ?? 0) <= 0)
			$strShowAdd = 'N';

		$intMaxWidth = intval($arFields['USER_TYPE_SETTINGS']['MAX_WIDTH'] ?? 0);
		if (0 >= $intMaxWidth) $intMaxWidth = 0;

		$intMinHeight = intval($arFields['USER_TYPE_SETTINGS']['MIN_HEIGHT'] ?? 0);
		if (0 >= $intMinHeight) $intMinHeight = 24;

		$intMaxHeight = intval($arFields['USER_TYPE_SETTINGS']['MAX_HEIGHT'] ?? 0);
		if (0 >= $intMaxHeight) $intMaxHeight = 1000;

		$strBannedSymbols = trim((string)($arFields['USER_TYPE_SETTINGS']['BAN_SYM'] ?? ',;'));
		$strBannedSymbols = str_replace(' ','',$strBannedSymbols);
		if (false === mb_strpos($strBannedSymbols, ','))
			$strBannedSymbols .= ',';
		if (false === mb_strpos($strBannedSymbols, ';'))
			$strBannedSymbols .= ';';

		$strOtherReplaceSymbol = '';
		$strReplaceSymbol = ($arFields['USER_TYPE_SETTINGS']['REP_SYM'] ?? ' ');
		if (BT_UT_AUTOCOMPLETE_REP_SYM_OTHER == $strReplaceSymbol)
		{
			$strOtherReplaceSymbol = (isset($arFields['USER_TYPE_SETTINGS']['OTHER_REP_SYM'])? mb_substr($arFields['USER_TYPE_SETTINGS']['OTHER_REP_SYM'], 0, 1) : '');
			if ((',' == $strOtherReplaceSymbol) || (';' == $strOtherReplaceSymbol))
				$strOtherReplaceSymbol = '';
			if (('' == $strOtherReplaceSymbol) || in_array($strOtherReplaceSymbol,static::GetReplaceSymList()))
			{
				$strReplaceSymbol = $strOtherReplaceSymbol;
				$strOtherReplaceSymbol = '';
			}
		}
		if ('' == $strReplaceSymbol)
		{
			$strReplaceSymbol = ' ';
			$strOtherReplaceSymbol = '';
		}

		$strIBlockMess = ($arFields['USER_TYPE_SETTINGS']['IBLOCK_MESS'] ?? '');
		if ('Y' != $strIBlockMess) $strIBlockMess = 'N';

		return [
			'VIEW' => $strView,
			'SHOW_ADD' => $strShowAdd,
			'MAX_WIDTH' => $intMaxWidth,
			'MIN_HEIGHT' => $intMinHeight,
			'MAX_HEIGHT' => $intMaxHeight,
			'BAN_SYM' => $strBannedSymbols,
			'REP_SYM' => $strReplaceSymbol,
			'OTHER_REP_SYM' => $strOtherReplaceSymbol,
			'IBLOCK_MESS' => $strIBlockMess,
		];
	}

	public static function GetSettingsHTML($arFields,$strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = [
			"HIDE" => ["ROW_COUNT", "COL_COUNT","MULTIPLE_CNT"],
			'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_TITLE'),
		];

		$arSettings = static::PrepareSettings($arFields);

		return '<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_VIEW').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[VIEW]',static::GetPropertyViewsList(true),htmlspecialcharsbx($arSettings['VIEW'])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_SHOW_ADD').'</td>
		<td>'.InputType('checkbox',$strHTMLControlName["NAME"].'[SHOW_ADD]','Y',htmlspecialcharsbx($arSettings["SHOW_ADD"])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_IBLOCK_MESS').'</td>
		<td>'.InputType('checkbox',$strHTMLControlName["NAME"].'[IBLOCK_MESS]','Y',htmlspecialcharsbx($arSettings["IBLOCK_MESS"])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_MAX_WIDTH').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_WIDTH]" value="'.intval($arSettings['MAX_WIDTH']).'">&nbsp;'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_COMMENT_MAX_WIDTH').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_MIN_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MIN_HEIGHT]" value="'.intval($arSettings['MIN_HEIGHT']).'">&nbsp;'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_COMMENT_MIN_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_MAX_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_HEIGHT]" value="'.intval($arSettings['MAX_HEIGHT']).'">&nbsp;'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_COMMENT_MAX_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_BAN_SYMBOLS').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[BAN_SYM]" value="'.htmlspecialcharsbx($arSettings['BAN_SYM']).'"></td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_SAUTOCOMPLETE_SETTING_REP_SYMBOL').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[REP_SYM]',static::GetReplaceSymList(true),htmlspecialcharsbx($arSettings['REP_SYM'])).'&nbsp;<input type="text" name="'.$strHTMLControlName["NAME"].'[OTHER_REP_SYM]" size="1" maxlength="1" value="'.htmlspecialcharsbx($arSettings['OTHER_REP_SYM']).'"></td>
		</tr>
		';
	}

	public static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		$arSymbols = static::GetSymbols($arSettings);
		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_SECTION.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		$isMainUiFilter = ($strHTMLControlName["FORM_NAME"] ?? '') === 'main-ui-filter';
		$inputName = $strHTMLControlName['VALUE'].'[]';
		if ($isMainUiFilter)
		{
			$inputName = $strHTMLControlName['VALUE'];
		}

		$strValue = '';

		if (isset($_REQUEST[$strHTMLControlName["VALUE"]]) && (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) || (0 < intval($_REQUEST[$strHTMLControlName["VALUE"]]))))
		{
			$arFilterValues = (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ? $_REQUEST[$strHTMLControlName["VALUE"]] : array($_REQUEST[$strHTMLControlName["VALUE"]]));
			$mxResultValue = static::GetValueForAutoCompleteMulti($arProperty,$arFilterValues,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
			$strValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n",$mxResultValue)) : '');
		}
		elseif (isset($GLOBALS[$strHTMLControlName["VALUE"]]) && (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) || (0 < intval($GLOBALS[$strHTMLControlName["VALUE"]]))))
		{
			$arFilterValues = (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ? $GLOBALS[$strHTMLControlName["VALUE"]] : array($GLOBALS[$strHTMLControlName["VALUE"]]));
			$mxResultValue = static::GetValueForAutoCompleteMulti($arProperty,$arFilterValues,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
			$strValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n",$mxResultValue)) : '');
		}
		ob_start();
		?><?
		$control_id = $APPLICATION->IncludeComponent(
			"bitrix:main.lookup.input",
			"iblockedit",
			array(
				"INPUT_NAME" => $inputName,
				"INPUT_NAME_STRING" => "inp_".$strHTMLControlName['VALUE'],
				"INPUT_VALUE_STRING" => $strValue,
				"START_TEXT" => '',
				"MULTIPLE" => $isMainUiFilter ? 'N' : 'Y', // TODO
				'MAX_WIDTH' => '200',
				'MIN_HEIGHT' => '24',
				"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
				'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
				'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
				'REP_SYM' => $arSymbols['REP_SYM_STRING'],
				'FILTER' => 'Y',
				'MAIN_UI_FILTER' => ($isMainUiFilter ? 'Y' : 'N'),
				'TYPE' => 'SECTION',
			), null, array("HIDE_ICONS" => "Y")
		);
		$inputStyle = 'float: left; margin-right: 10px;';
		if ($isMainUiFilter)
		{
			$inputStyle = 'float: left; margin-right: 4px; margin-top: 7px; margin-left: 10px;';
		}
		?><input style="<?=$inputStyle?>" type="button" value="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
			title="<? echo Loc::getMessage('BT_UT_SAUTOCOMPLETE_MESS_SEARCH_ELEMENT_MULTI_DESCR'); ?>"
			onclick="jsUtils.OpenWindow('/bitrix/admin/iblock_section_search.php?lang=<? echo LANGUAGE_ID; ?>&IBLOCK_ID=<? echo $arProperty["LINK_IBLOCK_ID"]; ?>&m=Y&n=&k=&lookup=<? echo 'jsMLI_'.$control_id; ?><?=($fixIBlock ? '&iblockfix=y' : '').'&tableId='.$windowTableId; ?>', 900, 700);"
		>
		<script>
			var arClearHiddenFields = arClearHiddenFields;
			if (!!arClearHiddenFields)
			{
				indClearHiddenFields = arClearHiddenFields.length;
				arClearHiddenFields[indClearHiddenFields] = 'jsMLI_<? echo $control_id; ?>';
			}
		</script><?
		$strResult = ob_get_contents();
		ob_end_clean();

		return $strResult;
	}

	public static function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered)
	{
		$filtered = false;

		$arFilterValues = array();

		if (isset($strHTMLControlName["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($strHTMLControlName["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			if (!empty($filterData[$strHTMLControlName["VALUE"]]))
			{
				$arFilterValues = (is_array($filterData[$strHTMLControlName["VALUE"]]) ?
					$filterData[$strHTMLControlName["VALUE"]] : array($filterData[$strHTMLControlName["VALUE"]]));
			}
		}
		else
		{
			if (isset($_REQUEST[$strHTMLControlName["VALUE"]]) && (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ||
				(0 < intval($_REQUEST[$strHTMLControlName["VALUE"]]))))
			{
				$arFilterValues = (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ?
					$_REQUEST[$strHTMLControlName["VALUE"]] : array($_REQUEST[$strHTMLControlName["VALUE"]]));
			}
			elseif (isset($GLOBALS[$strHTMLControlName["VALUE"]]) && (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ||
				(0 < intval($GLOBALS[$strHTMLControlName["VALUE"]]))))
			{
				$arFilterValues = (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ?
					$GLOBALS[$strHTMLControlName["VALUE"]] : array($GLOBALS[$strHTMLControlName["VALUE"]]));
			}
		}

		foreach ($arFilterValues as $key => $value)
		{
			if (0 >= intval($value))
				unset($arFilterValues[$key]);
		}

		if (!empty($arFilterValues))
		{
			$arFilter["=PROPERTY_".$arProperty["ID"]] = $arFilterValues;
			$filtered = true;
		}
	}

	protected static function GetLinkElement($elementId, $iblockId)
	{
		static $cache = [];

		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			$iblockId = 0;
		$elementId = (int)$elementId;
		if ($elementId <= 0)
			return false;
		if (!isset($cache[$elementId]))
		{
			$arFilter = [];
			if (0 < $iblockId)
				$arFilter['IBLOCK_ID'] = $iblockId;
			$arFilter['ID'] = $elementId;
			$sectionRes = CIBlockSection::GetList([], $arFilter, false, ['IBLOCK_ID','ID','NAME']);
			if ($section = $sectionRes->GetNext(true,true))
			{
				$result = [
					'ID' => $section['ID'],
					'NAME' => $section['NAME'],
					'~NAME' => $section['~NAME'],
					'IBLOCK_ID' => $section['IBLOCK_ID'],
				];
				$cache[$elementId] = $result;
			}
			else
			{
				$cache[$elementId] = false;
			}
		}

		return $cache[$elementId];
	}

	protected static function GetPropertyValue($arProperty,$arValue)
	{
		$mxResult = false;

		if ((int)($arValue['VALUE'] ?? 0) > 0)
		{
			$mxResult = static::GetLinkElement((int)$arValue['VALUE'], (int)$arProperty['LINK_IBLOCK_ID']);
			if (is_array($mxResult))
			{
				$mxResult['PROPERTY_ID'] = $arProperty['ID'];
				if (isset($arProperty['PROPERTY_VALUE_ID']))
				{
					$mxResult['PROPERTY_VALUE_ID'] = $arProperty['PROPERTY_VALUE_ID'];
				}
				else
				{
					$mxResult['PROPERTY_VALUE_ID'] = false;
				}
			}
		}
		return $mxResult;
	}

	protected static function GetPropertyViewsList($boolFull)
	{
		$boolFull = (true == $boolFull);
		if ($boolFull)
		{
			return array(
				'REFERENCE' => array(
					Loc::getMessage('BT_UT_SAUTOCOMPLETE_VIEW_AUTO'),
					Loc::getMessage('BT_UT_SAUTOCOMPLETE_VIEW_ELEMENT'),
				),
				'REFERENCE_ID' => array(
					'A','E'
				),
			);
		}
		return array('A','E');
	}

	protected static function GetReplaceSymList($boolFull = false)
	{
		$boolFull = (true == $boolFull);
		if ($boolFull)
		{
			return array(
				'REFERENCE' => array(
					Loc::getMessage('BT_UT_AUTOCOMPLETE_SYM_SPACE'),
					Loc::getMessage('BT_UT_AUTOCOMPLETE_SYM_GRID'),
					Loc::getMessage('BT_UT_AUTOCOMPLETE_SYM_STAR'),
					Loc::getMessage('BT_UT_AUTOCOMPLETE_SYM_UNDERLINE'),
					Loc::getMessage('BT_UT_AUTOCOMPLETE_SYM_OTHER'),

				),
				'REFERENCE_ID' => array(
					' ',
					'#',
					'*',
					'_',
					BT_UT_AUTOCOMPLETE_REP_SYM_OTHER,
				),
			);
		}
		return array(' ', '#', '*','_');
	}

	protected static function GetSymbols($arSettings)
	{
		$strBanSym = $arSettings['BAN_SYM'];
		$strRepSym = (BT_UT_AUTOCOMPLETE_REP_SYM_OTHER == $arSettings['REP_SYM'] ? $arSettings['OTHER_REP_SYM'] : $arSettings['REP_SYM']);
		$arBanSym = str_split($strBanSym,1);
		$arResult = array(
			'BAN_SYM' => $arBanSym,
			'REP_SYM' => array_fill(0,sizeof($arBanSym),$strRepSym),
			'BAN_SYM_STRING' => $strBanSym,
			'REP_SYM_STRING' => $strRepSym,
		);
		return $arResult;
	}

	/**
	 * Returns search page url.
	 *
	 * @return string
	 */
	protected static function getSearchUrl()
	{
		//TODO: need use \CAdminPage::getSelfFolderUrl, but in general it is impossible now
		return (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/').'iblock_section_search.php';
	}

	/**
	 * @param array $property
	 * @param array $strHTMLControlName
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
	{
		unset($fields['value'], $fields['filterable']);
		$fields['type'] = 'entity_selector';
		$fields['params'] = [
			'multiple' => 'Y',
			'dialogOptions' => [
				'entities' => [
					[
						'id' => IblockPropertySectionProvider::ENTITY_ID,
						'dynamicLoad' => true,
						'dynamicSearch' => true,
						'options' => [
							'iblockId' => (int)($property['LINK_IBLOCK_ID'] ?? 0),
						],
					],
				],
				'searchOptions' => [
					'allowCreateItem' => false,
				],
			],
		];
	}

	public static function GetUIEntityEditorPropertyEditHtml(array $params = []) : string
	{
		$property = [
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_SECTION,
			'USER_TYPE' => Iblock\PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE,
			'LINK_IBLOCK_ID' => (int)($params['SETTINGS']['LINK_IBLOCK_ID'] ?? 0),
			'MULTIPLE' => ($params['SETTINGS']['MULTIPLE'] ?? 'N') === 'Y' ? 'Y' : 'N',
		];

		$config = [
			'FIELD_NAME' => $params['FIELD_NAME'],
			'CHANGE_EVENTS' => [
				'onChangeIblockElement',
			],
			'ENTITY_ID' => 'iblock-property-section',
		];

		return Iblock\UI\Input\Section::renderSelector(
			$property,
			$params['VALUE'] ?? null,
			$config
		);
	}

	public static function GetUIEntityEditorPropertyViewHtml(array $params = []): string
	{
		$result = '';

		if (empty($params['VALUE']))
		{
			return '';
		}

		if (!is_array($params['VALUE']))
		{
			$params['VALUE'] = [$params['VALUE']];
		}

		$filter = [
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
			'ID' => $params['VALUE'],
		];
		$elementsResult = CIBlockSection::GetList(
			[],
			$filter,
			false,
			['ID', 'IBLOCK_ID', 'NAME']
		);

		while ($element = $elementsResult->Fetch())
		{
			$result .= htmlspecialcharsbx($element['NAME']) . '<br>';
		}
		unset($elementsResult);

		return $result;
	}
}

/** @deprecated */
const BT_UT_SECTION_AUTOCOMPLETE_CODE = Iblock\PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE;
