<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */

$initActions = array();

if (!is_array($arResult['SELECTOR_ITEMS']) || empty($arResult['SELECTOR_ITEMS']))
	return;

?>
<script>
	if (typeof(BX.Report.FilterFieldSelectorManager) === 'undefined')
	{
		BX.Report.FilterFieldSelectorManager = new BX.Report.FilterFieldSelectorManagerClass();
	}
	if (BX.Report.FilterFieldSelectorManager)
	{
<?php

foreach ($arResult['SELECTOR_ITEMS'] as $selectorConfig)
{
	if (isset($selectorConfig['USER_TYPE_ID']))
	{
		switch ($selectorConfig['USER_TYPE_ID'])
		{
			case "crm":
				if (!isset($initActions['crm']) || !$initActions['crm'])
				{
					$initActions['crm'] = true;

					CUtil::InitJSCore(array('ajax', 'popup', 'ui.fonts.opensans'));

					\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
					\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');
				}
				$selectorConfig['MESSAGES'] = is_array($selectorConfig['MESSAGES']) ? $selectorConfig['MESSAGES'] : [];
				$selectorConfig['MESSAGES'] += [
					'choise' => GetMessage('CRM_FF_CHOISE'),
					'ok' => GetMessage('CRM_FF_OK'),
					'cancel' => GetMessage('CRM_FF_CANCEL'),
					'close' => GetMessage('CRM_FF_CLOSE'),
					'wait' => GetMessage('CRM_FF_SEARCH'),
					'noresult' => GetMessage('CRM_FF_NO_RESULT'),
					'add' => GetMessage('CRM_FF_CHOISE'),
					'edit' => GetMessage('CRM_FF_CHANGE'),
					'search' => GetMessage('CRM_FF_SEARCH'),
					'last' => GetMessage('CRM_FF_LAST')
				];
?>
		BX.Report.FilterFieldSelectorManager.addSelector(<?=CUtil::PhpToJSObject($selectorConfig)?>);
<?php
				break;
			case "enumeration":
			case "crm_status":
			case "iblock_element":
			case "iblock_section":
?>
		BX.Report.FilterFieldSelectorManager.addSelector(<?=CUtil::PhpToJSObject($selectorConfig)?>);
<?php
				break;
			case "money":
				if (!isset($initActions['money']) || !$initActions['money'])
				{
					$initActions['money'] = true;

					CJSCore::Init(array('decl', 'core_money_editor'));

					Bitrix\Main\Page\Asset::getInstance()->addJs(
						'/bitrix/components/bitrix/currency.money.input/templates/.default/script.js'
					);
				}
?>
		BX.Report.FilterFieldSelectorManager.addSelector(<?=CUtil::PhpToJSObject($selectorConfig)?>);
<?php
				break;
		}
	}
}

		?>
	}
</script>
