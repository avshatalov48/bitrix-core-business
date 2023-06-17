<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

define ('BT_UT_AUTOCOMPLETE_REP_SYM_OTHER','other');

class CIBlockPropertyElementAutoComplete
{
	const USER_TYPE = 'EAutocomplete';

	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE" => Iblock\PropertyTable::TYPE_ELEMENT,
			"USER_TYPE" => self::USER_TYPE,
			"DESCRIPTION" => Loc::getMessage('BT_UT_EAUTOCOMPLETE_DESCR'),
			"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
			"GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
			"GetAdminListViewHTML" => array(__CLASS__,"GetAdminListViewHTML"),
			"GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
			"GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
			"GetAdminFilterHTML" => array(__CLASS__,'GetAdminFilterHTML'),
			"GetSettingsHTML" => array(__CLASS__,'GetSettingsHTML'),
			"PrepareSettings" => array(__CLASS__,'PrepareSettings'),
			"AddFilterFields" => array(__CLASS__,'AddFilterFields'),
			"GetPublicFilterHTML" => array(__CLASS__,'GetPublicFilterHTML'),
			"GetUIFilterProperty" => array(__CLASS__, 'GetUIFilterProperty'),
			'GetUIEntityEditorProperty' => array(__CLASS__, 'GetUIEntityEditorProperty'),
			'GetUIEntityEditorPropertyEditHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyEditHtml'),
			'GetUIEntityEditorPropertyViewHtml' => array(__CLASS__, 'GetUIEntityEditorPropertyViewHtml'),
		);
	}

	public static function GetValueForAutoComplete($arProperty, $arValue, $arBanSym = "", $arRepSym = "")
	{
		$strResult = '';
		$mxResult = static::GetPropertyValue($arProperty,$arValue);
		if (is_array($mxResult))
		{
			$strResult = htmlspecialcharsbx(str_replace($arBanSym,$arRepSym,$mxResult['~NAME'])).' ['.$mxResult['ID'].']';
		}
		return $strResult;
	}

	public static function GetValueForAutoCompleteMulti($arProperty, $arValues, $arBanSym = "", $arRepSym = "")
	{
		$arResult = false;

		if (is_array($arValues))
		{
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
		return $arResult;
	}

	public static function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		return self::GetAdminFilterHTML($arProperty, $strHTMLControlName);
	}

	public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		if (isset($arSettings['USER_TYPE_SETTINGS']))
		{
			$arSettings = $arSettings['USER_TYPE_SETTINGS'];
		}
		$arSymbols = static::GetSymbols($arSettings);

		$arProperty['LINK_IBLOCK_ID'] = (int)$arProperty['LINK_IBLOCK_ID'];
		$fixIBlock = $arProperty['LINK_IBLOCK_ID'] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		if (isset($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'iblock_element_admin')
		{
			$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
				'&amp;IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
				'&amp;n='.urlencode($strHTMLControlName['VALUE']).
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
			?><?
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
					"START_TEXT" => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_INVITE'),
					"MULTIPLE" => $arProperty["MULTIPLE"],
					"MAX_WIDTH" => $arSettings['MAX_WIDTH'],
					"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
					'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
					'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
					'REP_SYM' => $arSymbols['REP_SYM_STRING'],
					'FILTER' => 'Y'
				), null, array("HIDE_ICONS" => "Y")
			);
			?><?
			if ($arSettings['VIEW'] == 'T')
			{
				$APPLICATION->IncludeComponent(
					'bitrix:main.tree.selector',
					'iblockedit',
					array(
						"INPUT_NAME" => $strHTMLControlName["VALUE"],
						'ONSELECT' => 'jsMLI_'.$control_id.'.SetValue',
						'MULTIPLE' => $arProperty["MULTIPLE"],
						'SHOW_INPUT' => 'N',
						'SHOW_BUTTON' => 'Y',
						'GET_FULL_INFO' => 'Y',
						"START_TEXT" => Loc::getMessage("BT_UT_EAUTOCOMPLETE_MESS_LIST_INVITE"),
						'BUTTON_CAPTION' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_CHOOSE_ELEMENT'),
						'BUTTON_TITLE' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_CHOOSE_ELEMENT_DESCR'),
						"NO_SEARCH_RESULT_TEXT" => Loc::getMessage("BT_UT_EAUTOCOMPLETE_MESS_NO_SEARCH_RESULT_TEXT"),
						"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
						'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
						'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
						'REP_SYM' => $arSymbols['REP_SYM_STRING']
					), null, array("HIDE_ICONS" => "Y")
				);
				?><?
			}
			elseif ($arSettings['VIEW'] == 'E')
			{
				$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
					'&IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
					'&n=&k=&lookup=jsMLI_'.$control_id.
					($fixIBlock ? '&iblockfix=y' : '').
					'&tableId='.$windowTableId;
				?><input style="float: left; margin-right: 10px; margin-top: 5px;"
				type="button" value="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
				title="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<?=$searchUrl; ?>', 900, 700);"><?
			}
			if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock)
			{
				$strButtonCaption = '';
				if ($arSettings['IBLOCK_MESS'] == 'Y')
				{
					$arLangMess = CIBlock::GetMessages($arProperty["LINK_IBLOCK_ID"]);
					$strButtonCaption = $arLangMess['ELEMENT_ADD'];
					unset($arLangMess);
				}
				if ($strButtonCaption == '')
					$strButtonCaption = Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_NEW_ELEMENT');
				?><input type="button" style="margin-top: 5px;" value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
					title="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
					onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/'.CIBlock::GetAdminElementEditLink(
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

	public static function GetPropertyFieldHtmlMulty($arProperty, $arValues, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		if (isset($arSettings['USER_TYPE_SETTINGS']))
		{
			$arSettings = $arSettings['USER_TYPE_SETTINGS'];
		}
		$arSymbols = static::GetSymbols($arSettings);

		$arProperty['LINK_IBLOCK_ID'] = (int)$arProperty['LINK_IBLOCK_ID'];
		$fixIBlock = $arProperty['LINK_IBLOCK_ID'] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		if (isset($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'iblock_element_admin')
		{
			$arResult = [];
			foreach ($arValues as $intPropertyValueID => $arOneValue)
			{
				$mxElement = static::GetPropertyValue($arProperty,$arOneValue);
				if (is_array($mxElement))
				{
					$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
						'&amp;IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
						'&amp;n='.urlencode($strHTMLControlName["VALUE"].'['.$intPropertyValueID.']').
						($fixIBlock ? '&amp;iblockfix=y' : '').
						'&amp;tableId='.$windowTableId;
					$arResult[] = '<input type="text" name="'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" id="'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" value="'.$arOneValue['VALUE'].'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
					'&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'['.$intPropertyValueID.']" >'.$mxElement['NAME'].'</span>';
					unset($searchUrl);
				}
			}

			if ((int)$arProperty['MULTIPLE_CNT'] > 0)
			{
				for ($i = 0; $i < $arProperty['MULTIPLE_CNT']; $i++)
				{
					$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
						'&amp;IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
						'&amp;n='.urlencode($strHTMLControlName["VALUE"].'[n'.$i.']').
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
			?><?
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
					"START_TEXT" => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_INVITE'),
					"MULTIPLE" => $arProperty["MULTIPLE"],
					"MAX_WIDTH" => $arSettings['MAX_WIDTH'],
					"MIN_HEIGHT" => $arSettings['MIN_HEIGHT'],
					"MAX_HEIGHT" => $arSettings['MAX_HEIGHT'],
					"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
					'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
					'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
					'REP_SYM' => $arSymbols['REP_SYM_STRING'],
					'FILTER' => 'Y'
				), null, array("HIDE_ICONS" => "Y")
			);
			?><?
			if ($arSettings['VIEW'] == 'T')
			{
				$APPLICATION->IncludeComponent(
					'bitrix:main.tree.selector',
					'iblockedit',
					array(
						"INPUT_NAME" => $strHTMLControlName['VALUE'],
						'ONSELECT' => 'jsMLI_'.$control_id.'.SetValue',
						'MULTIPLE' => $arProperty["MULTIPLE"],
						'SHOW_INPUT' => 'N',
						'SHOW_BUTTON' => 'Y',
						'GET_FULL_INFO' => 'Y',
						"START_TEXT" => Loc::getMessage("BT_UT_EAUTOCOMPLETE_MESS_LIST_INVITE"),
						'BUTTON_CAPTION' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_CHOOSE_ELEMENT'),
						'BUTTON_TITLE' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_CHOOSE_ELEMENT_MULTI_DESCR'),
						"NO_SEARCH_RESULT_TEXT" => Loc::getMessage("BT_UT_EAUTOCOMPLETE_MESS_NO_SEARCH_RESULT_TEXT"),
						"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
						'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
						'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
						'REP_SYM' => $arSymbols['REP_SYM_STRING']
					), null, array("HIDE_ICONS" => "Y")
				);
				?><?
			}
			elseif ($arSettings['VIEW'] == 'E')
			{
				$searchUrl = static::getSearchUrl().'?lang='.LANGUAGE_ID.
					'&IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].
					'&n=&k=&m=y&lookup=jsMLI_'.$control_id.
					($fixIBlock ? '&iblockfix=y' : '').
					'&tableId='.$windowTableId;

				?><input style="float: left; margin-right: 10px; margin-top: 5px;" type="button" value="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
					title="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT_MULTI_DESCR'); ?>"
					onclick="jsUtils.OpenWindow('<?=$searchUrl; ?>', 900, 700);"><?
				unset($searchUrl);
			}
			if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock)
			{
				$strButtonCaption = '';
				if ($arSettings['IBLOCK_MESS'] == 'Y')
				{
					$arLangMess = CIBlock::GetMessages($arProperty["LINK_IBLOCK_ID"]);
					$strButtonCaption = $arLangMess['ELEMENT_ADD'];
					unset($arLangMess);
				}
				if ('' == $strButtonCaption)
					$strButtonCaption = Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_NEW_ELEMENT');
				?><input type="button" style="margin-top: 5px;" value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
				title="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
				onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/'.CIBlock::GetAdminElementEditLink(
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
				CIBlock::GetAdminElementEditLink(
					$mxResult['IBLOCK_ID'],
					$mxResult['ID'],
					array(
						'WF' => 'Y'
					)
				).'" title="'.Loc::getMessage("BT_UT_EAUTOCOMPLETE_MESS_ELEMENT_EDIT").'">'.$mxResult['ID'].'</a>]';
		}
		return $strResult;
	}

	public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
	{
		static $cache = array();

		$strResult = '';
		$arValue['VALUE'] = (int)$arValue['VALUE'];
		if ($arValue['VALUE'] > 0)
		{
			$viewMode = '';
			$resultKey = '';
			if (!empty($strHTMLControlName['MODE']))
			{
				switch ($strHTMLControlName['MODE'])
				{
					case 'CSV_EXPORT':
						$viewMode = 'CSV_EXPORT';
						$resultKey = 'ID';
						break;
					case 'EXTERNAL_ID':
						$viewMode = 'EXTERNAL_ID';
						$resultKey = '~XML_ID';
						break;
					case 'SIMPLE_TEXT':
						$viewMode = 'SIMPLE_TEXT';
						$resultKey = '~NAME';
						break;
					case 'ELEMENT_TEMPLATE':
						$viewMode = 'ELEMENT_TEMPLATE';
						$resultKey = '~NAME';
						break;
				}
			}
			if (!isset($cache[$arValue['VALUE']]))
			{
				$arFilter = array();
				$intIBlockID = (int)$arProperty['LINK_IBLOCK_ID'];
				if ($intIBlockID > 0)
				{
					$arFilter['IBLOCK_ID'] = $intIBlockID;
				}
				$arFilter['ID'] = $arValue['VALUE'];
				if ($viewMode === '')
				{
					$arFilter['ACTIVE'] = 'Y';
					$arFilter['ACTIVE_DATE'] = 'Y';
					$arFilter['CHECK_PERMISSIONS'] = 'Y';
					$arFilter['MIN_PERMISSION'] = 'R';
				}
				$rsElements = CIBlockElement::GetList(
					array(),
					$arFilter,
					false,
					false,
					array('ID', 'XML_ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
				);
				if (isset($strHTMLControlName['DETAIL_URL']))
				{
					$rsElements->SetUrlTemplates($strHTMLControlName['DETAIL_URL']);
				}
				$cache[$arValue['VALUE']] = $rsElements->GetNext(true, true);
				unset($rsElements);
			}
			if (!empty($cache[$arValue['VALUE']]) && is_array($cache[$arValue['VALUE']]))
			{
				if ($viewMode !== '' && $resultKey !== '')
				{
					$strResult = $cache[$arValue['VALUE']][$resultKey];
				}
				else
				{
					$strResult = '<a href="'.$cache[$arValue['VALUE']]['DETAIL_PAGE_URL'].'">'.$cache[$arValue['VALUE']]['NAME'].'</a>';
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
		if (isset($settings['USER_TYPE_SETTINGS']))
		{
			$settings = $settings['USER_TYPE_SETTINGS'];
		}
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
				'START_TEXT' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_INVITE'),
				'MULTIPLE' => $property['MULTIPLE'],
				'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
				'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
				'BAN_SYM' => $symbols['BAN_SYM_STRING'],
				'REP_SYM' => $symbols['REP_SYM_STRING'],
				'MAX_WIDTH' => $settings['MAX_WIDTH'],
				'MIN_HEIGHT' => $settings['MIN_HEIGHT'],
				'MAX_HEIGHT' => $settings['MAX_HEIGHT'],
				'FILTER' => 'Y'
			),
			(isset($control['PARENT_COMPONENT']) ? $control['PARENT_COMPONENT'] : null),
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

		$strShowAdd = (isset($arFields['USER_TYPE_SETTINGS']['SHOW_ADD']) ? $arFields['USER_TYPE_SETTINGS']['SHOW_ADD'] : '');
		$strShowAdd = ($strShowAdd == 'Y' ? 'Y' : 'N');
		if ((int)$arFields['LINK_IBLOCK_ID'] <= 0)
			$strShowAdd = 'N';

		$intMaxWidth = (int)(isset($arFields['USER_TYPE_SETTINGS']['MAX_WIDTH']) ? $arFields['USER_TYPE_SETTINGS']['MAX_WIDTH'] : 0);
		if ($intMaxWidth <= 0) $intMaxWidth = 0;

		$intMinHeight = (int)(isset($arFields['USER_TYPE_SETTINGS']['MIN_HEIGHT']) ? $arFields['USER_TYPE_SETTINGS']['MIN_HEIGHT'] : 0);
		if ($intMinHeight <= 0) $intMinHeight = 24;

		$intMaxHeight = (int)(isset($arFields['USER_TYPE_SETTINGS']['MAX_HEIGHT']) ? $arFields['USER_TYPE_SETTINGS']['MAX_HEIGHT'] : 0);
		if ($intMaxHeight <= 0) $intMaxHeight = 1000;

		$strBannedSymbols = trim(isset($arFields['USER_TYPE_SETTINGS']['BAN_SYM']) ? $arFields['USER_TYPE_SETTINGS']['BAN_SYM'] : ',;');
		$strBannedSymbols = str_replace(' ','',$strBannedSymbols);
		if (mb_strpos($strBannedSymbols, ',') === false)
			$strBannedSymbols .= ',';
		if (mb_strpos($strBannedSymbols, ';') === false)
			$strBannedSymbols .= ';';

		$strOtherReplaceSymbol = '';
		$strReplaceSymbol = (isset($arFields['USER_TYPE_SETTINGS']['REP_SYM']) ? $arFields['USER_TYPE_SETTINGS']['REP_SYM'] : ' ');
		if ($strReplaceSymbol == BT_UT_AUTOCOMPLETE_REP_SYM_OTHER)
		{
			$strOtherReplaceSymbol = (isset($arFields['USER_TYPE_SETTINGS']['OTHER_REP_SYM'])? mb_substr($arFields['USER_TYPE_SETTINGS']['OTHER_REP_SYM'], 0, 1) : '');
			if ((',' == $strOtherReplaceSymbol) || (';' == $strOtherReplaceSymbol))
				$strOtherReplaceSymbol = '';
			if (('' == $strOtherReplaceSymbol) || in_array($strOtherReplaceSymbol, static::GetReplaceSymList()))
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

		$strIBlockMess = (isset($arFields['USER_TYPE_SETTINGS']['IBLOCK_MESS']) ? $arFields['USER_TYPE_SETTINGS']['IBLOCK_MESS'] : '');
		if ('Y' != $strIBlockMess) $strIBlockMess = 'N';

		return array(
			'VIEW' => $strView,
			'SHOW_ADD' => $strShowAdd,
			'MAX_WIDTH' => $intMaxWidth,
			'MIN_HEIGHT' => $intMinHeight,
			'MAX_HEIGHT' => $intMaxHeight,
			'BAN_SYM' => $strBannedSymbols,
			'REP_SYM' => $strReplaceSymbol,
			'OTHER_REP_SYM' => $strOtherReplaceSymbol,
			'IBLOCK_MESS' => $strIBlockMess,
		);
	}

	public static function GetSettingsHTML($arFields,$strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT","MULTIPLE_CNT"),
			'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_TITLE'),
		);

		$arSettings = static::PrepareSettings($arFields);
		if (isset($arSettings['USER_TYPE_SETTINGS']))
		{
			$arSettings = $arSettings['USER_TYPE_SETTINGS'];
		}

		return '<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_VIEW').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[VIEW]', static::GetPropertyViewsList(true),htmlspecialcharsbx($arSettings['VIEW'])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_SHOW_ADD').'</td>
		<td>'.InputType('checkbox',$strHTMLControlName["NAME"].'[SHOW_ADD]','Y',htmlspecialcharsbx($arSettings["SHOW_ADD"])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_IBLOCK_MESS').'</td>
		<td>'.InputType('checkbox',$strHTMLControlName["NAME"].'[IBLOCK_MESS]','Y',htmlspecialcharsbx($arSettings["IBLOCK_MESS"])).'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_MAX_WIDTH').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_WIDTH]" value="'.(int)$arSettings['MAX_WIDTH'].'">&nbsp;'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_COMMENT_MAX_WIDTH').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_MIN_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MIN_HEIGHT]" value="'.(int)$arSettings['MIN_HEIGHT'].'">&nbsp;'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_COMMENT_MIN_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_MAX_HEIGHT').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[MAX_HEIGHT]" value="'.(int)$arSettings['MAX_HEIGHT'].'">&nbsp;'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_COMMENT_MAX_HEIGHT').'</td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_BAN_SYMBOLS').'</td>
		<td><input type="text" name="'.$strHTMLControlName["NAME"].'[BAN_SYM]" value="'.htmlspecialcharsbx($arSettings['BAN_SYM']).'"></td>
		</tr>
		<tr>
		<td>'.Loc::getMessage('BT_UT_EAUTOCOMPLETE_SETTING_REP_SYMBOL').'</td>
		<td>'.SelectBoxFromArray($strHTMLControlName["NAME"].'[REP_SYM]', static::GetReplaceSymList(true),htmlspecialcharsbx($arSettings['REP_SYM'])).'&nbsp;<input type="text" name="'.$strHTMLControlName["NAME"].'[OTHER_REP_SYM]" size="1" maxlength="1" value="'.htmlspecialcharsbx($arSettings['OTHER_REP_SYM']).'"></td>
		</tr>
		';
	}

	public static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		global $APPLICATION;

		$arSettings = static::PrepareSettings($arProperty);
		if (isset($arSettings['USER_TYPE_SETTINGS']))
		{
			$arSettings = $arSettings['USER_TYPE_SETTINGS'];
		}
		$arSymbols = static::GetSymbols($arSettings);

		$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");

		$isMainUiFilter = ($strHTMLControlName["FORM_NAME"] == "main-ui-filter");
		$inputName = $strHTMLControlName['VALUE'].'[]';
		if ($isMainUiFilter)
		{
			$inputName = $strHTMLControlName['VALUE'];
		}

		$strValue = '';

		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		if (isset($_REQUEST[$strHTMLControlName["VALUE"]]) && (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) || (0 < (int)$_REQUEST[$strHTMLControlName["VALUE"]])))
		{
			$arFilterValues = (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ? $_REQUEST[$strHTMLControlName["VALUE"]] : array($_REQUEST[$strHTMLControlName["VALUE"]]));
			$mxResultValue = static::GetValueForAutoCompleteMulti($arProperty,$arFilterValues,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
			$strValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n",$mxResultValue)) : '');
		}
		elseif (isset($GLOBALS[$strHTMLControlName["VALUE"]]) && (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) || (0 < (int)$GLOBALS[$strHTMLControlName["VALUE"]])))
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
			), null, array("HIDE_ICONS" => "Y")
		);
		$inputStyle = 'float: left; margin-right: 10px;';
		if ($isMainUiFilter)
		{
			$inputStyle = 'float: left; margin-right: 4px; margin-top: 7px; margin-left: 10px;';
		}
		?><input style="<?=$inputStyle?>" type="button"
			value="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT'); ?>"
			title="<? echo Loc::getMessage('BT_UT_EAUTOCOMPLETE_MESS_SEARCH_ELEMENT_MULTI_DESCR'); ?>"
			onclick="jsUtils.OpenWindow('<?=$selfFolderUrl?>iblock_element_search.php?lang=<? echo LANGUAGE_ID; ?>&IBLOCK_ID=<? echo $arProperty["LINK_IBLOCK_ID"]; ?>&n=&k=&m=y&lookup=<? echo 'jsMLI_'.$control_id; ?><?=($fixIBlock ? '&iblockfix=y' : '').'&tableId='.$windowTableId; ?>', 900, 700);">
		<script type="text/javascript">
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
				(0 < (int)$_REQUEST[$strHTMLControlName["VALUE"]])))
			{
				$arFilterValues = (is_array($_REQUEST[$strHTMLControlName["VALUE"]]) ?
					$_REQUEST[$strHTMLControlName["VALUE"]] : array($_REQUEST[$strHTMLControlName["VALUE"]]));
			}
			elseif (isset($GLOBALS[$strHTMLControlName["VALUE"]]) && (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ||
				(0 < (int)$GLOBALS[$strHTMLControlName["VALUE"]])))
			{
				$arFilterValues = (is_array($GLOBALS[$strHTMLControlName["VALUE"]]) ?
					$GLOBALS[$strHTMLControlName["VALUE"]] : array($GLOBALS[$strHTMLControlName["VALUE"]]));
			}
		}

		foreach ($arFilterValues as $key => $value)
		{
			if ((int)($value) <= 0)
				unset($arFilterValues[$key]);
		}

		if (!empty($arFilterValues))
		{
			$arFilter["=PROPERTY_".$arProperty["ID"]] = $arFilterValues;
			$filtered = true;
		}
	}

	/**
	 * @param array $property
	 * @param array $strHTMLControlName
	 * @param array &$fields
	 * @return void
	 */
	public static function GetUIFilterProperty($property, $strHTMLControlName, &$fields)
	{
		$fields["type"] = "custom_entity";
		$fields["property"] = $property;
		$fields["customRender"] = ["\Bitrix\Iblock\Helpers\Filter\Property", "render"];
		$fields["customFilter"] = ["Bitrix\Iblock\Helpers\Filter\Property", "addFilter"];
		$fields["operators"] = array(
			"default" => "=",
			"exact" => "=",
			"enum" => "@"
		);
	}

	protected static function GetLinkElement($intElementID, $intIBlockID)
	{
		static $cache = array();

		$intIBlockID = (int)$intIBlockID;
		if ($intIBlockID <= 0)
			$intIBlockID = 0;
		$intElementID = (int)$intElementID;
		if ($intElementID <= 0)
			return false;
		if (!isset($cache[$intElementID]))
		{
			$arFilter = array();
			if ($intIBlockID > 0)
				$arFilter['IBLOCK_ID'] = $intIBlockID;
			$arFilter['ID'] = $intElementID;
			$arFilter['SHOW_HISTORY'] = 'Y';
			$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array('IBLOCK_ID','ID','NAME'));
			if ($arElement = $rsElements->GetNext())
			{
				$arResult = array(
					'ID' => $arElement['ID'],
					'NAME' => $arElement['NAME'],
					'~NAME' => $arElement['~NAME'],
					'IBLOCK_ID' => $arElement['IBLOCK_ID'],
				);
				$cache[$intElementID] = $arResult;
			}
			else
			{
				$cache[$intElementID] = false;
			}
		}
		return $cache[$intElementID];
	}

	protected static function GetPropertyValue($arProperty, $arValue)
	{
		$mxResult = false;
		if ((int)$arValue['VALUE'] > 0)
		{
			$mxResult = static::GetLinkElement($arValue['VALUE'],$arProperty['LINK_IBLOCK_ID']);
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
		$boolFull = ($boolFull === true);
		if ($boolFull)
		{
			return array(
				'REFERENCE' => array(
					Loc::getMessage('BT_UT_EAUTOCOMPLETE_VIEW_AUTO'),
					Loc::getMessage('BT_UT_EAUTOCOMPLETE_VIEW_TREE'),
					Loc::getMessage('BT_UT_EAUTOCOMPLETE_VIEW_ELEMENT'),
				),
				'REFERENCE_ID' => array(
					'A','T','E'
				),
			);
		}
		return array('A','T','E');
	}

	protected static function GetReplaceSymList($boolFull = false)
	{
		$boolFull = ($boolFull === true);
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
		$strBanSym = $arSettings['BAN_SYM'] ?? '';
		$repSym = $arSettings['REP_SYM'] ?? '';
		$otherRepSym = $arSettings['OTHER_REP_SYM'] ?? '';
		$strRepSym = (BT_UT_AUTOCOMPLETE_REP_SYM_OTHER == $repSym ? $otherRepSym : $repSym);
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
		return (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/').'iblock_element_search.php';
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		return [
			'type' => 'custom'
		];
	}

	public static function GetUIEntityEditorPropertyEditHtml(array $params = []) : string
	{
		$settings = $params['SETTINGS'] ?? [];

		\Bitrix\Main\UI\Extension::load(['ui.entity-selector', 'ui.buttons', 'ui.forms']);
		$fieldName = htmlspecialcharsbx($params['FIELD_NAME']);
		$containerId = $fieldName . '_container';
		$inputsContainerId = $fieldName . '_inputs_container';

		$isMultiple = $settings['MULTIPLE'] === 'Y';
		$isMultiple = CUtil::PhpToJSObject($isMultiple);

		if (!is_array($params['VALUE']))
		{
			$params['VALUE'] = (!empty($params['VALUE'])) ? [$params['VALUE']] : [];
		}

		$preselectedItems = [];
		foreach ($params['VALUE'] as $value)
		{
			if (!$value)
			{
				continue;
			}
			$preselectedItems[] = ['iblock-element', (int)$value];
		}
		$iblockId = (int)$params['SETTINGS']['LINK_IBLOCK_ID'];
		$preselectedItems = CUtil::PhpToJSObject($preselectedItems);

		$messages = [
			'NOT_FOUND' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_SEARCH_NOT_FOUND'),
			'CHANGE_QUERY' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_SEARCH_CHANGE_QUERY'),
			'ENTER_QUERY' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_SEARCH_ENTER_QUERY'),
			'ENTER_QUERY_SUBTITLE' => Loc::getMessage('BT_UT_EAUTOCOMPLETE_SEARCH_ENTER_QUERY_SUBTITLE'),
		];
		$propertyType = self::USER_TYPE;

		return <<<HTML
			<div id="{$containerId}" name="{$containerId}"></div>
			<div id="{$inputsContainerId}" name="{$inputsContainerId}"></div>
			<script>
				(function() {
					var selector = new BX.UI.EntitySelector.TagSelector({
						id: '{$containerId}',
						multiple: {$isMultiple},

						dialogOptions: {
							height: 300,
							id: '{$containerId}',
							multiple: {$isMultiple},
							preselectedItems: {$preselectedItems},
							entities: [
								{
									id: 'iblock-element',
									dynamicLoad: true,
									dynamicSearch: true,
									options: {
										iblockId: {$iblockId},
										propertyType: '{$propertyType}',
									},
								}
							],
							searchOptions: {
								allowCreateItem: false,
							},
							searchTabOptions: {
								stub: true,
								stubOptions: {
									title: '{$messages['NOT_FOUND']}',
									subtitle: '{$messages['CHANGE_QUERY']}',
									arrow: false,
								}
							},
							recentTabOptions: {
								stub: true,
								stubOptions: {
									title: '{$messages['ENTER_QUERY']}',
									subtitle: '{$messages['ENTER_QUERY_SUBTITLE']}',
									arrow: false,
								}
							},
							events: {
								'Item:onSelect': setSelectedInputs.bind(this, 'Item:onSelect'),
								'Item:onDeselect': setSelectedInputs.bind(this, 'Item:onDeselect'),
							},
						},
					})

					function setSelectedInputs(eventName, event)
					{
						var dialog = event.getData().item.getDialog();
						if (!dialog.isMultiple())
						{
							dialog.hide();
						}
						var selectedItems = dialog.getSelectedItems();
						if (Array.isArray(selectedItems))
						{
							var htmlInputs = '';
							selectedItems.forEach(function(item)
							{
								htmlInputs +=
									'<input type="hidden" name="{$fieldName}[]" value="' + item['id'] + '" />'
								;
							});
							if (htmlInputs === '')
							{
								htmlInputs =
									'<input type="hidden" name="{$fieldName}[]" value="" />'
								;
							}
							document.getElementById('{$inputsContainerId}').innerHTML = htmlInputs;
							BX.Event.EventEmitter.emit('onChangeIblockElement');
						}
					}

					selector.renderTo(document.getElementById("{$containerId}"));
				})();

			</script>
HTML;
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
		$elementsResult = CIBlockElement::GetList(
			[],
			$filter,
			false,
			false,
			['ID', 'IBLOCK_ID', 'NAME']
		);

		while ($element = $elementsResult->Fetch())
		{
			$result .= htmlspecialcharsbx($element['NAME']) . '<br>';
		}

		return $result;
	}
}

/** @deprecated */
define ('BT_UT_AUTOCOMPLETE_CODE', CIBlockPropertyElementAutoComplete::USER_TYPE);
