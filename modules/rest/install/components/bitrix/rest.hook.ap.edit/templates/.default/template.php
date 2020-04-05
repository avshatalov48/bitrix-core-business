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

use Bitrix\Main\Localization\Loc;

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/rest/scope.php');

if(!$arResult['HTTPS']):
?>
<div class="content-edit-form-notice-error"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=Loc::getMessage(defined('REST_APAUTH_ALLOW_HTTP') ? "REST_HAPE_HTTPS_WARNING" : "REST_HAPE_HTTPS_ERROR")?></span></div>
<?php
endif;

if ($arResult["ERROR"]):
?>
<div class="content-edit-form-notice-error"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=$arResult["ERROR"]?></span></div>
<?php
endif;

if (empty($arResult["ERROR"]) && isset($_GET["success"])):
?>
<div class="content-edit-form-notice-successfully"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=Loc::getMessage("REST_HAPE_SUCCESS")?></span></div>
<?php
endif;

if(!empty($arResult['INFO']['PASSWORD'])):
?>

<table id="content-edit-form-config" class="content-edit-form" cellspacing="0" cellpadding="0">
	<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9;">
		<td colspan="3" style="padding:10px">

			<div class="content-edit-form-notice-successfully">
				<span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=Loc::getMessage("REST_HAPE_AP_NOTE")?></span>
			</div>

			<div style="text-align: center; font-size: 200%; padding: 20px; font-weight: bold;">
				<?=$c->encode($arResult['INFO']['PASSWORD'])?>
			</div>
			<div style="text-align: center">
				<?=Loc::getMessage('REST_HAPE_AP_EXAMPLE')?>: <b><?=$c->encode($arResult['EXAMPLE'])?></b>
			</div>
		</td>
	</tr>
</table><br /><br />
<?php
endif;

?>

<form method="post" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" name="bx_ap_edit_form">
	<?=bitrix_sessid_post();?>
	<table id="content-edit-form-config" class="content-edit-form" cellspacing="0" cellpadding="0">

<?php
if($arResult["INFO"]['ID'] > 0):
?>
		<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9;">
			<td colspan="3" style="padding-bottom:10px" class="content-edit-form-field-name content-edit-form-field-name-left">
				<?=Loc::getMessage('REST_HAPE_DATE_CREATE')?>: <?=$arResult["INFO"]["DATE_CREATE"]?><br/><br/>
				<?=Loc::getMessage('REST_HAPE_DATE_LOGIN')?>: <?=$arResult["INFO"]["DATE_LOGIN"] ? $arResult["INFO"]["DATE_LOGIN"] : Loc::getMessage('REST_HAPE_DATE_LOGIN_NEVER')?>
<?php
	if($arResult["INFO"]["DATE_LOGIN"]):
?>
				<br/><br/>
				<?=Loc::getMessage('REST_HAPE_LAST_IP')?>: <?=$arResult["INFO"]["LAST_IP"]?>

<?php
	endif;
?>
			</td>
		</tr>
<?php
endif;
?>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="min-width: 300px;"><?=Loc::getMessage('REST_HAPE_TITLE')?></td>
			<td class="content-edit-form-field-text" style="min-width: 400px;">
				<input type="text" name="TITLE" class="content-edit-form-field-input-text" value="<?=$c->encode($arResult['INFO']['TITLE'])?>" /><br />
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=Loc::getMessage('REST_HAPE_COMMENT')?></td>
			<td class="content-edit-form-field-textarea">
				<textarea name="COMMENT" class="content-edit-form-field-input-textarea"><?=$c->encode($arResult['INFO']['COMMENT'])?></textarea><br />
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
<?php
if(is_array($arResult["SCOPE"])):
?>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=Loc::getMessage('REST_HAPE_SCOPE')?><br/><span style="font-weight: normal;color:#AEA8A8"><?=Loc::getMessage("REST_HAPE_SCOPE_DESC")?></span></td>
			<td class="content-edit-form-field-input">
<?php
	foreach($arResult["SCOPE"] as $scope):
		$scopeName = $c->encode(Loc::getMessage("REST_SCOPE_".toUpper($scope)));
		$scope = $c->encode($scope);

		if(strlen($scopeName) <= 0)
		{
			$scopeName = $scope;
		}

		$scopeName .= ' <small>('.$scope.')</small>';
?>
				<input type="checkbox" name="SCOPE[]" id="APP_<?=$scope?>" value="<?=$scope?>" <?if(in_array($scope, $arResult["INFO"]["SCOPE"])):?>checked="checked"<?endif?>/>
				<label for="APP_<?=$scope?>"><?=$scopeName?></label><br/>
<?php
	endforeach;
?>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
<?php
endif;
?>
		<tr>
			<td></td>
			<td style="padding-top: 25px">
				<span onclick="BX.addClass(this, 'webform-button-wait webform-button-active');BX.submit(document.forms['bx_ap_edit_form']);" class="webform-button webform-button-create"><?=GetMessage("REST_HAPE_SAVE")?></span>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
	</table>
</form>