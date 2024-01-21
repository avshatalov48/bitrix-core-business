<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

class CIBlockPropertyXmlID
{
	/** @deprecated  */
	const USER_TYPE = Iblock\PropertyTable::USER_TYPE_XML_ID;

	public static function GetUserTypeDescription()
	{
		return [
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
			'USER_TYPE' => Iblock\PropertyTable::USER_TYPE_XML_ID,
			'DESCRIPTION' => Loc::getMessage('IBLOCK_PROP_XMLID_DESC'),
			'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
			'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
			'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
			'GetUIEntityEditorPropertyEditHtml' => [__CLASS__, 'GetUIEntityEditorPropertyEditHtml'],
			'GetUIEntityEditorPropertyViewHtml' => [__CLASS__, 'GetUIEntityEditorPropertyViewHtml'],
		];
	}

	public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if(isset($strHTMLControlName['MODE']) && $strHTMLControlName["MODE"] == "CSV_EXPORT")
		{
			return $value["VALUE"];
		}
		elseif($value["VALUE"] <> '')
		{
			if(!isset($cache[$value["VALUE"]]))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = $ar_res;
				else
					$cache[$value["VALUE"]] = $value["VALUE"];
			}

			if (isset($strHTMLControlName['MODE']) && ($strHTMLControlName["MODE"] == "SIMPLE_TEXT" || $strHTMLControlName["MODE"] == 'ELEMENT_TEMPLATE'))
			{
				if (is_array($cache[$value["VALUE"]]))
					return $cache[$value["VALUE"]]["~NAME"];
				else
					return $cache[$value["VALUE"]];
			}
			else
			{
				if (is_array($cache[$value["VALUE"]]))
					return '<a href="'.$cache[$value["VALUE"]]["DETAIL_PAGE_URL"].'">'.$cache[$value["VALUE"]]["NAME"].'</a>';
				else
					return htmlspecialcharsex($cache[$value["VALUE"]]);
			}
		}
		else
		{
			return '';
		}
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if($value["VALUE"] <> '')
		{
			if(!array_key_exists($value["VALUE"], $cache))
			{
				$db_res = CIBlockElement::GetList(
					array(),
					array("=XML_ID"=>$value["VALUE"], "SHOW_HISTORY"=>"Y"),
					false,
					false,
					array("ID", "IBLOCK_TYPE_ID", "IBLOCK_ID", "NAME")
				);
				$ar_res = $db_res->GetNext();
				if($ar_res)
					$cache[$value["VALUE"]] = htmlspecialcharsbx($ar_res['NAME']).
					' [<a href="'.
					'/bitrix/admin/iblock_element_edit.php?'.
					'type='.urlencode($ar_res['IBLOCK_TYPE_ID']).
					'&amp;IBLOCK_ID='.$ar_res['IBLOCK_ID'].
					'&amp;ID='.$ar_res['ID'].
					'&amp;lang='.LANGUAGE_ID.
					'" title="'.Loc::getMessage("IBLOCK_PROP_EL_EDIT").'">'.$ar_res['ID'].'</a>]';
				else
					$cache[$value["VALUE"]] = htmlspecialcharsbx($value["VALUE"]);
			}
			return $cache[$value["VALUE"]];
		}
		else
		{
			return '&nbsp;';
		}
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$ar_res = false;
		if($value["VALUE"] <> '')
		{
			$db_res = CIBlockElement::GetList(
				array(),
				array("=XML_ID" => $value["VALUE"], "SHOW_HISTORY" => "Y"),
				false,
				false,
				array("ID", "IBLOCK_ID", "NAME")
			);
			$ar_res = $db_res->GetNext();
		}

		if(!$ar_res)
			$ar_res = array("NAME" => "");

		$arProperty["LINK_IBLOCK_ID"] ??= 0;
		$fixIBlock = $arProperty["LINK_IBLOCK_ID"] > 0;
		$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];

		return  '<input name="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" id="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" value="'.htmlspecialcharsEx($value["VALUE"]).'" size="20" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.CUtil::JSEscape('/bitrix/admin/iblock_element_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($strHTMLControlName["VALUE"]).'&get_xml_id=Y&a=b'.($fixIBlock ? '&iblockfix=y' : '').'&tableId='.$windowTableId).'\', 900, 700);">'.
			'&nbsp;<span id="sp_'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" >'.$ar_res['NAME'].'</span>';
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT", "WITH_DESCRIPTION"),
		);
		return '';
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		return [
			'type' => 'custom',
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
			$element = self::getElementByXmlId($value);
			if ($element)
			{
				$preselectedItems[] = ['iblock-property-element-xml', (string)$element['ID']];
			}
		}

		$preselectedItems = CUtil::PhpToJSObject($preselectedItems);
		$messages = [
			'NOT_FOUND' => Loc::getMessage('BT_UT_XML_ID_SEARCH_NOT_FOUND'),
			'CHANGE_QUERY' => Loc::getMessage('BT_UT_XML_ID_SEARCH_CHANGE_QUERY'),
			'ENTER_QUERY' => Loc::getMessage('BT_UT_XML_ID_SEARCH_ENTER_QUERY'),
			'ENTER_QUERY_SUBTITLE' => Loc::getMessage('BT_UT_XML_ID_SEARCH_ENTER_QUERY_SUBTITLE'),
		];
		$propertyType = Iblock\PropertyTable::USER_TYPE_XML_ID;

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
									id: 'iblock-property-element-xml',
									dynamicLoad: true,
									dynamicSearch: true,
									options: {
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
									'<input type="hidden" name="{$fieldName}[]" value="' + BX.util.htmlspecialchars(item['customData'].get('xmlId')) + '" />'
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

		foreach ($params['VALUE'] as $value)
		{
			$filter = [
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
				'XML_ID' => $value,
			];
			$element = CIBlockElement::GetList(
				[],
				$filter,
				false,
				false,
				['ID', 'XML_ID', 'IBLOCK_ID', 'NAME']
			)->Fetch();

			$result .= htmlspecialcharsbx($element['NAME']) . '<br>';
		}

		return $result;
	}

	/**
	 * @param string $xmlId
	 * @return array|false
	 */
	private static function getElementByXmlId(string $xmlId): bool|array
	{
		$filter = [
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
			'=XML_ID' => $xmlId,
			'ACTIVE' => 'Y',
		];

		return \CIBlockElement::GetList(
			[],
			$filter,
			false,
			['nTopCount' => 1],
			['ID', 'NAME', 'XML_ID']
		)->Fetch();
	}
}
