<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

?>

<script>
	if(!!obCrm['<?=$arParams['NAME'] ?>'])
	{
		obCrm['<?=$arParams['NAME'] ?>'].Clear();
		delete obCrm['<?=$arParams['NAME'] ?>'];
	}

	obCrm['<?=$arParams['NAME']?>'] = new CRM(
		'<?=$arParams['NAME']?>',
		null,
		null,
		'<?=$arParams['NAME']?>',
		<?=CUtil::PhpToJSObject($arResult['ELEMENT'])?>,
		true,
		<?=$arParams['MULTIPLE'] == 'Y' ? 'true' : 'false'?>,
		<?=CUtil::PhpToJSObject($arParams['ENTITY_TYPE'])?>,
		{
			'lead': '<?=GetMessageJS('REST_CRM_FF_LEAD')?>',
			'contact': '<?=GetMessageJS('REST_CRM_FF_CONTACT')?>',
			'company': '<?=GetMessageJS('REST_CRM_FF_COMPANY')?>',
			'deal': '<?=GetMessageJS('REST_CRM_FF_DEAL')?>',
			'quote': '<?=GetMessageJS('REST_CRM_FF_QUOTE')?>',
			'ok': '<?=GetMessageJS('REST_CRM_FF_OK')?>',
			'cancel': '<?=GetMessageJS('REST_CRM_FF_CANCEL')?>',
			'close': '<?=GetMessageJS('REST_CRM_FF_CLOSE')?>',
			'wait': '<?=GetMessageJS('REST_CRM_FF_SEARCH')?>',
			'noresult': '<?=GetMessageJS('REST_CRM_FF_NO_RESULT')?>',
			'add': '<?=GetMessageJS('REST_CRM_FF_CHOISE')?>',
			'edit': '<?=GetMessageJS('REST_CRM_FF_CHANGE')?>',
			'search': '<?=GetMessageJS('REST_CRM_FF_SEARCH')?>',
			'last': '<?=GetMessageJS('REST_CRM_FF_LAST')?>'
		},
		true,
		{}
	);

	obCrm['<?=$arParams['NAME'] ?>'].Init();
</script>
