<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
if (!empty($arResult["VALUE"]))
{
	foreach ($arResult["VALUE"] as $entityType => $arEntity)
	{
		$r = array();
		foreach($arEntity as $entityId => $entity)
		{
			$r[] = '<a href="' . $entity['url'] . '" onclick="if(window[\'BXMobileApp\']){BXMobileApp.PageManager.loadPageUnique({url:this.href,bx24ModernStyle:true});return BX.PreventDefault(event);}">' . htmlspecialcharsbx($entity['title']) . '</a>';
		}
		if (!empty($r))
		{
			?><dl class="mobile-grid-field-crm-view"><?
				?><dt><?= GetMessage('CRM_ENTITY_TYPE_' . $entityType) ?>:</dt><?
				?><dd><?=implode(", ", $r)?></dd><?
			?></dl><?
		}
	}
}