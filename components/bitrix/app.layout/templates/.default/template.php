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
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

if($arParams['IS_SLIDER'])
{
	$bodyClass = $APPLICATION->getPageProperty("BodyClass", false);
	$bodyClasses = "app-layout-subscribe-slider-modifier";
	if($arParams['USE_PADDING'] != 'N')
	{
		$bodyClasses .= " app-layout-subscribe-renew-modifier-75";
	}
	$APPLICATION->setPageProperty("BodyClass", trim(sprintf("%s %s", $bodyClass, $bodyClasses)));
}

if($arResult['APP_STATUS']['PAYMENT_NOTIFY'] == 'Y')
{

	if(isset($arResult['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']))
	{
		$arResult['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']++;
	}
?>
<div class="app-update-avail"><?=\Bitrix\Rest\AppTable::getStatusMessage($arResult['APP_STATUS']['MESSAGE_SUFFIX'], $arResult['APP_STATUS']['MESSAGE_REPLACE'])?></div>
<?
	if($arResult['APP_STATUS']['PAYMENT_ALLOW'] == 'N')
	{
		return;
	}
}

if($arResult['APP_NEED_REINSTALL'])
{
?>
	<div class="app-update-avail"><?=$arResult['IS_ADMIN'] ? GetMessage('REST_ALT_NEED_REINSTALL_ADMIN', array('#DETAIL_URL#' => $arResult['DETAIL_URL'])) : GetMessage('REST_ALT_NEED_REINSTALL');?></div>
<?
}

if(array_key_exists('UPDATE_VERSION', $arResult)):
?>
<div id="update_note" class="app-update-avail"><?=$arResult['IS_ADMIN'] ? GetMessage('REST_ALT_UPDATE_AVAIL_ADMIN', array('#DETAIL_URL#' => $arResult['DETAIL_URL'])) : GetMessage('REST_ALT_UPDATE_AVAIL');?><span class="app-update-close" onclick="BX.rest.AppLayout.get('<?=$arResult['APP_SID']?>').hideUpdate('<?=$arResult['UPDATE_VERSION']?>',function(){BX.hide(BX('update_note'))});"></span></div>
<?
endif;

$frameName = $arResult['CURRENT_HOST'].'|'.($arResult['CURRENT_HOST_SECURE']?1:0).'|'.$arResult['APP_SID'];

$url = $arResult['APP_URL'];
$url .= (mb_strpos($url, '?') === false ? '?' : '&');
/*
?>
<a href="javascript:void(0)" onclick="BX.rest.AppLayout.get('<?=$arResult['APP_SID']?>').reInstall();">Reinstall app</a>
*/

$frameStyle = array();

if(
	!empty($arResult['PRESET_OPTIONS'])
	|| (
		isset($arParams['PARAM'])
		&& is_array($arParams['PARAM'])
		&& !empty($arParams['PARAM'])
	)
)
{
	if ((int)$arResult['PRESET_OPTIONS']['height'] > 0)
	{
		$frameStyle[] = 'height:' . (int)$arResult['PRESET_OPTIONS']['height'] . 'px';
	}
	elseif(isset($arParams['PARAM']['FRAME_HEIGHT']))
	{
		$frameStyle[] = 'height:' . $arParams['PARAM']['FRAME_HEIGHT'];
	}

	if(isset($arParams['PARAM']['FRAME_WIDTH']))
	{
		$frameStyle[] = 'width:' . $arParams['PARAM']['FRAME_WIDTH'];
	}
}
elseif(isset($arParams['MOBILE']) && $arParams['MOBILE'] == 'Y')
{
	$frameStyle[] = 'width: 100%';
	//$frameStyle[] = 'height: 100%';
}
elseif($arParams['POPUP'])
{
	$frameStyle[] = 'height: calc(100% - 3px)';
}

if($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT)
{
	ob_start();
}
?>

<form name="appform_<?=$arResult['APP_SID']?>" action="<?=htmlspecialcharsbx($url)?>DOMAIN=<?=$arResult['CURRENT_HOST']?>&amp;PROTOCOL=<?=$arResult['CURRENT_HOST_SECURE']?1:0?>&amp;LANG=<?=LANGUAGE_ID?>&amp;APP_SID=<?=$arResult['APP_SID']?>" method="POST" target="<?=htmlspecialcharsbx($frameName)?>">
	<input type="hidden" name="AUTH_ID" value="<?=$arResult['AUTH']['access_token']?>" />
	<input type="hidden" name="AUTH_EXPIRES" value="<?=$arResult['AUTH']['expires_in']?>" />
	<input type="hidden" name="REFRESH_ID" value="<?=$arResult['AUTH']['refresh_token']?>" />
	<input type="hidden" name="member_id" value="<?=htmlspecialcharsbx($arResult['MEMBER_ID'])?>">
	<input type="hidden" name="status" value="<?=htmlspecialcharsbx($arResult['APP_STATUS']['STATUS'])?>">
	<input type="hidden" name="PLACEMENT" value="<?=htmlspecialcharsbx($arParams["PLACEMENT"])?>">
<?
if($arParams['PLACEMENT_OPTIONS']):
?>
	<input type="hidden" name="PLACEMENT_OPTIONS" value="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arParams['~PLACEMENT_OPTIONS']))?>">
<?
endif;
?>
</form>
<?
$formHtml = '';
if($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT)
{
	$formHtml = ob_get_clean();
}
?>
<div
	id="appframe_layout_<?=$arResult['APP_SID']?>" <? if(!empty($frameStyle)) echo ' style="'.implode(';', $frameStyle).'"' ?>
	class="app-frame-layout<?=($arParams['PLACEMENT'] === \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT) ? ' app-frame-layout-default' : ''?>"
>
	<iframe id="appframe_<?=$arResult['APP_SID']?>" name="<?=htmlspecialcharsbx($frameName)?>" frameborder="0" class="app-frame app-loading" allow="geolocation *; microphone *; camera *; autoplay *;"></iframe>
	<div id="appframe_loading_<?=$arResult['APP_SID']?>" class="app-loading-msg" <?php if (isset($arParams['SHOW_LOADER']) && $arParams['SHOW_LOADER'] === 'N'): ?> style="display: none;"<?endif;?>>
		<?=GetMessage('REST_LOADING', array('#APP_NAME#' =>  htmlspecialcharsbx($arResult['APP_NAME'])))?>
	</div>
</div>
<script type="text/javascript">
BX.rest.AppLayout.set(
	'<?=\CUtil::JSEscape($arParams['PLACEMENT'])?>',
	'<?=$arResult['APP_SID']?>',
	{
		<?=$arResult['FIRST_RUN'] ? 'firstRun:true,':''?>

		formName: 'appform_<?=$arResult['APP_SID']?>',
		frameName: 'appframe_<?=$arResult['APP_SID']?>',
		loaderName: 'appframe_loading_<?=$arResult['APP_SID']?>',
		layoutName: 'appframe_layout_<?=$arResult['APP_SID']?>',
		ajaxUrl: '/bitrix/components/bitrix/app.layout/lazyload.ajax.php',
		controlUrl: '/bitrix/tools/rest_control.php',
		appHost: '<?=$arResult['APP_HOST']?>',
		appPort: '<?=$arResult['APP_PORT']?>',
		appProto: '<?=$arResult['APP_PROTO']?>',
		proto: <?=$arResult['CURRENT_HOST_SECURE']?1:0?>,
		restPath: '<?=$arResult['REST_PATH']?>',
		id: '<?=$arResult['ID']?>',
		appId: '<?=$arResult['APP_ID']?>',
		appV: '<?=$arResult['APP_VERSION']?>',
		appI: <?= ($arResult['INSTALL'] ?? null) ? 'true' : 'false'?>,
		memberId: '<?=$arResult['MEMBER_ID']?>',
		authId: '<?=$arResult['AUTH']['access_token']?>',
		authExpires: '<?=$arResult['AUTH']['expires_in']?>',
		refreshId: '<?=$arResult['AUTH']['refresh_token']?>',
		isAdmin: <?=$arResult['IS_ADMIN'] ? 'true' : 'false'?>,
		staticHtml: <?=$arResult['APP_STATIC'] ? 'true' : 'false'?>,
		appOptions: <?=\CUtil::PhpToJsObject($arResult['APP_OPTIONS'])?>,
		userOptions: <?=\CUtil::PhpToJsObject($arResult['USER_OPTIONS'])?>,
		placementId: '<?=(isset($arParams['PLACEMENT_ID']) && $arParams['PLACEMENT_ID'] > 0) ? intVal($arParams['PLACEMENT_ID']) : 0; ?>',
		placementOptions: <?=\CUtil::PhpToJsObject($arParams['PLACEMENT_OPTIONS'])?>

	}
);
<?
if($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT):
?>
BX.rest.AppLayout.get('<?=$arResult['APP_SID']?>').denyInterface(['setTitle', 'setScroll', 'reloadWindow']);
<?
endif;
if($arParams['POPUP']):
?>
BX.rest.AppLayout.get('<?=$arResult['APP_SID']?>').denyInterface(['resizeWindow']);
<?
endif;
if($arParams['INITIALIZE'] !== 'N'):
?>

if(typeof window.BXDesktopSystem !== 'undefined')
{
	window.BXDesktopSystem.AllowFrame('<?=\CUtil::JSEscape($arResult['APP_PROTO'].'://'.$arResult['APP_HOST'])?>');
}

BX.ready(function(){
<?
	if($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT):
?>
	var formContainer = BX.create('DIV', {style:{display:'none'}, html: '<?=\CUtil::JSEscape($formHtml)?>'});
	document.body.appendChild(formContainer);
<?
	endif;
?>

	BX.rest.AppLayout.initialize('<?=\CUtil::JSEscape($arParams['PLACEMENT'])?>', '<?=$arResult['APP_SID']?>');
});
<?
endif;
?>
</script>
