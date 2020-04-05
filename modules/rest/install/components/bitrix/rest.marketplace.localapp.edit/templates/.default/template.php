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
 * @global CUser $USER
 */

$onlyApi =
		isset($_POST["APP_ONLY_API"])
		|| $arResult["APP"]["ID"] > 0
			&& (
				!is_array($arResult["APP"]["MENU_NAME"])
				|| !implode('', $arResult['APP']['MENU_NAME'])
		);

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

\Bitrix\Main\Localization\Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/rest/scope.php');

if ($arResult["ERROR"]):
?>
<div class="content-edit-form-notice-error"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=$arResult["ERROR"]?></span></div>
<?php
endif;

if (empty($arResult["ERROR"]) && isset($_GET["success"])):
?>
<div class="content-edit-form-notice-successfully"><span class="content-edit-form-notice-text"><span class="content-edit-form-notice-icon"></span><?=GetMessage("MP_APP_EDIT_SUCCESS")?></span></div>
<?php
endif;
?>

<form method="post" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" name="BXLocalAppForm">
	<?=bitrix_sessid_post();?>
	<table id="content-edit-form-config" class="content-edit-form" cellspacing="0" cellpadding="0">
<?php
if($arResult["APP"]['ID'] > 0):
?>
		<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9;">
			<td colspan="3" style="padding-bottom:10px" class="content-edit-form-field-name content-edit-form-field-name-left">
				<?=GetMessage('MP_ADD_APP_CLIENT_ID')?>: <?=$arResult["APP"]["CLIENT_ID"]?><br/><br/>
				<?=GetMessage('MP_ADD_APP_CLIENT_SECRET')?>: <?=$arResult["APP"]["CLIENT_SECRET"]?>
			</td>
		</tr>
<?php
endif;
?>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="min-width: 300px;"><?=GetMessage('MP_APP_NAME')?>*</td>
			<td class="content-edit-form-field-input">
				<input type="text" name="APP_NAME" value="<?=$c->encode($arResult['APP']['APP_NAME'])?>"  class="content-edit-form-field-input-text"/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><label for="APP_ONLY_API"><?=GetMessage('MP_APP_API')?><br/><span style="font-weight: normal;color:#AEA8A8"><?=GetMessage("MP_APP_API_DESC")?></span></label></td>
			<td class="content-edit-form-field-input">
				<input type="checkbox" name="APP_ONLY_API" id="APP_ONLY_API" <?if ($onlyApi):?>checked<?endif?>/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr data-role="app_menu_name" <?if($onlyApi):?>style="display: none" <?endif?>>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="min-width: 300px;"><?=GetMessage('MP_APP_MOBILE')?></td>
			<td class="content-edit-form-field-input">
				<input type="checkbox" name="MOBILE" id="MOBILE" <? if($arResult['APP']['MOBILE'] == 'Y'): ?>checked<? endif ?>/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr data-role="app_menu_name" <?if($onlyApi):?>style="display: none" <?endif?>>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=GetMessage('MP_APP_MENU_NAME')?>*</td>
			<td>
				<table>
<?php
foreach($arResult['LANG'] as $lang => $langName):
?>
					<tr>
						<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=$c->encode($langName)?> (<?=$lang?>)</td>
						<td class="content-edit-form-field-input"><input type="text" name="APP_MENU_NAME[<?=$lang?>]" value="<?=$c->encode($arResult['APP']['MENU_NAME'][$lang])?>" class="content-edit-form-field-input-text"/></td>
					</tr>
<?php
endforeach;
?>
				</table>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

<?php
if(is_array($arResult["SCOPE"])):
?>
		<tr>
			<td class="content-edit-form-field-name content-edit-form-field-name-left"><?=GetMessage('MP_APP_SCOPE')?><br/><span style="font-weight: normal;color:#AEA8A8"><?=GetMessage("MP_APP_SCOPE_DESC")?></span></td>
			<td class="content-edit-form-field-input">
<?php
	foreach($arResult["SCOPE"] as $scope):
		$scopeName = GetMessage("REST_SCOPE_".toUpper($scope));
		if(strlen($scopeName) <= 0)
		{
			$scopeName = $scope;
		}

		$scopeName .= ' <small>('.$scope.')</small>';
?>
				<input type="checkbox" name="SCOPE[]" id="APP_<?=$scope?>" value="<?=$scope?>" <?if(in_array($scope, $arResult["APP"]["SCOPE"])):?>checked="checked"<?endif?>/>
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

		<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9;">
			<td style="padding-top:20px" class="content-edit-form-field-name content-edit-form-field-name-left"><?=GetMessage('MP_APP_URL')?>*<br/><span style="font-weight: normal;color:#AEA8A8"><?=GetMessage("MP_APP_URL_DESC")?></span><br/></td>
			<td style="padding-top:20px" class="content-edit-form-field-input">
				<input type="text" name="APP_URL" value="<?=$c->encode($arResult["APP"]["URL"]);?>"  class="content-edit-form-field-input-text"/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9;">
			<td style="padding-bottom:20px" class="content-edit-form-field-name content-edit-form-field-name-left"><span id="mp_url_install_title" style="display: <?=$onlyApi ? 'none' : 'inline'?>;"><?=GetMessage('MP_APP_URL_INSTALL')?></span><span id="mp_url_install_title_api" style="display: <?=$onlyApi?'inline':'none'?>;"><?=GetMessage('MP_APP_URL_INSTALL_API')?></span></td>
			<td style="padding-bottom:20px" class="content-edit-form-field-input">
				<input type="text" name="APP_URL_INSTALL" value="<?=$c->encode($arResult["APP"]["URL_INSTALL"])?>"  class="content-edit-form-field-input-text"/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

<?php
if($arResult['ALLOW_ZIP']):
?>

		<tr data-role="app_menu_name" <? if($onlyApi): ?>style="display: none" <? endif ?>>
			<td class="content-edit-form-field-name content-edit-form-field-name-left" style="padding-bottom:10px"><?=GetMessage('MP_APP_OR')?></td>
			<td class="content-edit-form-field-input">
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

		<tr style="box-sizing:border-box;width: 100%;padding: 35px 45px;border-radius: 3px;background-color: #f7f9f9; <? if($onlyApi): ?>display: none;<? endif ?>" data-role="app_menu_name">
			<td style="padding-top:20px; padding-bottom:20px" class="content-edit-form-field-name content-edit-form-field-name-left"><?=GetMessage('MP_APP_UPLOAD')?>*</td>
			<td class="content-edit-form-field-input">
				<input type="file" name="APP_ZIP" value=""/>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>

<?
endif;
?>

		<tr>
			<td colspan="3">
				<div class="mp_notify_message" style="margin: 20px 0px 10px 0px">
					<?if ($arResult["APP"]["ID"] <= 0):?><?=GetMessage("MP_APP_INFO")?><?endif?>
					<?=GetMessage("MP_APP_DOC")?>
				</div>
			</td>
		</tr>

		<tr>
			<td></td>
			<td style="padding-top: 25px">
					<span onclick="BX.addClass(this, 'webform-button-wait webform-button-active');BX.submit(document.forms['BXLocalAppForm']);" class="webform-button webform-button-create"><?=GetMessage("MP_APP_SAVE")?></span>
			</td>
			<td class="content-edit-form-field-error"></td>
		</tr>
	</table>
</form>

<script>
	BX.ready(function(){
		BX.bind(BX("APP_ONLY_API"), "change", function(){
			var menu = this.form.querySelectorAll('[data-role="app_menu_name"]');
			for(var i=0; i<menu.length; i++)
			{
				menu[i].style.display = (this.checked ? "none" : "table-row");
			}

			BX('mp_url_install_title').style.display = (this.checked ? "none" : "inline");
			BX('mp_url_install_title_api').style.display = (this.checked ? "inline" : "none");
		});
	});
</script>