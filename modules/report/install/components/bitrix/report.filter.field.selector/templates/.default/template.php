<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */

$initActions = array();

if (!is_array($arResult['SELECTOR_ITEMS']) || empty($arResult['SELECTOR_ITEMS']))
	return;

?>
<script type="text/javascript">
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

					CUtil::InitJSCore(array('ajax', 'popup'));

					\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
					\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');
				}

				$selectorConfig['MESSAGES'] = array(
					'choise' => GetMessage('CRM_FF_CHOISE'),
					'lead' => GetMessage('CRM_FF_LEAD'),
					'contact' => GetMessage('CRM_FF_CONTACT'),
					'company' => GetMessage('CRM_FF_COMPANY'),
					'deal' => GetMessage('CRM_FF_DEAL'),
					'quote' => GetMessage('CRM_FF_QUOTE'),
					'ok' => GetMessage('CRM_FF_OK'),
					'cancel' => GetMessage('CRM_FF_CANCEL'),
					'close' => GetMessage('CRM_FF_CLOSE'),
					'wait' => GetMessage('CRM_FF_SEARCH'),
					'noresult' => GetMessage('CRM_FF_NO_RESULT'),
					'add' => GetMessage('CRM_FF_CHOISE'),
					'edit' => GetMessage('CRM_FF_CHANGE'),
					'search' => GetMessage('CRM_FF_SEARCH'),
					'last' => GetMessage('CRM_FF_LAST')
				);
?>
		BX.Report.FilterFieldSelectorManager.addSelector(<?=CUtil::PhpToJSObject($selectorConfig)?>);
<?php
				break;
			case "crm_status":
			case "iblock_element":
			case "iblock_section":
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
