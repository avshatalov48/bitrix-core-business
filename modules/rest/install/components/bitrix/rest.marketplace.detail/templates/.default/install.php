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
ob_start();
?>
<form method="POST" action="<?echo POST_FORM_ACTION_URI;?>" id="APP_INSTALL_FORM">
	<?=bitrix_sessid_post()?>

	<div style="width: 450px; padding: 5px; overflow-y: auto; margin: 5px;">
		<div class="mp_dt_title_icon">

	<?
	if(!empty($arResult["APP"]["ICON"])):
	?>
			<span class="mp_sc_ls_img">
				<span><img src="<?=htmlspecialcharsbx($arResult["APP"]["ICON"])?>" alt=""></span>
			</span>
	<?
	else:
	?>
			<span class="mp_sc_ls_img">
				<span class="mp_empty_icon"></span>
			</span>
			<span class="mp_sc_ls_shadow"></span>
	<?
	endif;
	?>
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
		foreach($arResult["APP"]["RIGHTS"] as $key => $scope)
		{
			$scope = is_array($scope) ? $scope : ["TITLE" => $scope, "DESCRIPTION" => ""];
			?>
			<li <?= (array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' bx-denied="Y"' : '');?><?= (array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' style="color:#d83e3e"' : ''); ?>><dl><dt><?=$scope["TITLE"]?></dt><dd><?=$scope["DESCRIPTION"]?></dd></dl></li>
			<?
		}
	?>
			</ul>
		</div>
	<?
	endif;
	$license_link = !empty($arResult["APP"]["EULA_LINK"]) ? $arResult["APP"]["EULA_LINK"] : GetMessage("BX24_APP_INSTALL_EULA_LINK", ["#CODE#" => urlencode($arResult["APP"]["CODE"])]);
	$privacy_link = !empty($arResult["APP"]["PRIVACY_LINK"]) ? $arResult["APP"]["PRIVACY_LINK"] : GetMessage("BX24_APP_INSTALL_PRIVACY_LINK");

	?>
		<div class="mp_pp_content" style="margin-left: 20px;">
			<div id="mp_detail_error" style="color: red; margin-bottom: 10px; font-size: 12px;"></div>
			<? if($arResult['TERMS_OF_SERVICE_LINK']):?>
				<div style="margin-bottom: 8px;">
					<input type="checkbox" id="mp_tos_license" value="N">
					<label for="mp_tos_license"><?=GetMessage("BX24_APP_INSTALL_TERMS_OF_SERVICE_TEXT_2", ["#LINK#" => $arResult['TERMS_OF_SERVICE_LINK']])?></label>
				</div>
			<? endif;?>
			<?if (LANGUAGE_ID == "ru" || LANGUAGE_ID == "ua" || $arResult["APP"]["EULA_LINK"]):?>
			<div style="margin-bottom: 8px;">
				<input type="checkbox" id="mp_detail_license" value="N">
				<label for="mp_detail_license"><?=GetMessage("BX24_APP_INSTALL_EULA_TEXT", ["#LINK#" => $license_link])?></label>
			</div>
			<?endif?>
			<div>
				<input type="checkbox" id="mp_detail_confidentiality" value="N">
				<label for="mp_detail_confidentiality"><?=GetMessage("BX24_APP_INSTALL_PRIVACY_TEXT", ["#LINK#" => $privacy_link])?></label>
			</div>
		</div>
	</div>
</form>
<?

$arJsonData = [
	"title" => $arResult["APP"]["NAME"],
	"content" => ob_get_clean()
];

if (\Bitrix\Main\Context::getCurrent()->getRequest()->getPost("dataType") == "json")
{
	$APPLICATION->RestartBuffer();
	$app = \Bitrix\Main\Application::getInstance();
	(new \Bitrix\Main\Engine\Response\AjaxJson($arJsonData))->send();
	$app->terminate();
}
?><?=$arJsonData["content"]?>