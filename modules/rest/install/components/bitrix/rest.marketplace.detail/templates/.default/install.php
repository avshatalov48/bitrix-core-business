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

?>
<form method="POST" action="<?echo POST_FORM_ACTION_URI;?>" id="APP_INSTALL_FORM">
	<?=bitrix_sessid_post()?>

	<div style="width: 450px; padding: 5px; overflow-y: auto; margin: 5px;">
		<div class="mp_dt_title_icon">
			<span class="mp_sc_ls_img">
	<?
	if($arResult["APP"]["ICON"]):
	?>
				<span><img src="<?=htmlspecialcharsbx($arResult["APP"]["ICON"])?>" alt=""></span>
	<?
	else:
	?>
				<span class="mp_empty_icon"></span>
	<?
	endif;
	?>
			</span>
			<span class="mp_sc_ls_shadow"></span>
		</div>
		<h2 class="mp_dt_pp_title_section"><?=htmlspecialcharsbx($arResult["APP"]["NAME"]);?></h2>

		<p style="margin: 20px 0 0 125px;"><?=GetMessage("B24_APP_INSTALL_VERSION")?> <?=htmlspecialcharsbx($arResult["APP"]["VER"])?></p>

		<div style="clear:both"></div>

		<div class="mp_notify_message" style="<?=$arResult['IS_HTTPS'] ? 'display: none;' : ''?>" id="mp_error">
<?
	if(!$arResult['IS_HTTPS'])
	{
		echo GetMessage('BX24_APP_INSTALL_HTTPS_WARNING');
	}
?>

		</div>

		<hr class="mp_pp_hr"/>
	<?
	if(is_array($arResult["APP"]["RIGHTS"])):
	?>
		<div class="mp_pp_content">
			<p><?=GetMessage("BX24_APP_INSTALL_RIGHTS")?></p>
	<?
		if(!empty($arResult["SCOPE_DENIED"])):
			$b24 = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
	?>
			<div class="mp_notify_message"><?
				echo (\Bitrix\Main\Loader::includeModule("bitrix24") ? GetMessage("BX24_APP_INSTALL_MODULE_UNINSTALL_BITRIX24", array("#PATH_CONFIGS#" => CBitrix24::PATH_CONFIGS)) : GetMessage("BX24_APP_INSTALL_MODULE_UNINSTALL"))
			?></div>
	<?
		endif;
	?>
			<ul class="mp_pp_ul">
	<?
		foreach($arResult["APP"]["RIGHTS"] as $key => $scope):
	?>
				<li<?=(array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' style="color:#d83e3e"' : '');?>><?=htmlspecialcharsbx($scope)?></li>
	<?
		endforeach;
	?>
			</ul>
		</div>
		<hr class="mp_pp_hr"/>
	<?
	endif;
	?>
		<div class="mp_pp_content" style="margin-left: 20px;">
			<div id="mp_detail_error" style="color: red; margin-bottom: 10px; font-size: 12px;"></div>
			<?if (LANGUAGE_ID == "ru" || LANGUAGE_ID == "ua"):?>
			<div style="margin-bottom: 8px;">
				<input type="checkbox" id="mp_detail_license" value="N">
				<label for="mp_detail_license"><?=GetMessage("BX24_APP_INSTALL_LICENSE_CHECKBOX", ["#CODE#" => urlencode($arResult["APP"]["CODE"])])?></label>
			</div>
			<?endif?>
			<div>
				<input type="checkbox" id="mp_detail_confidentiality" value="N">
				<label for="mp_detail_confidentiality"><?=GetMessage("BX24_APP_INSTALL_LICENSE_CONFIDENTIALITY")?></label>
			</div>
		</div>
	</div>
</form>