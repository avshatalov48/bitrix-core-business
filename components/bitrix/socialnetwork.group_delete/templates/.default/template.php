<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load([
	"ui.design-tokens",
	"ui.buttons",
	"ui.alerts",
	"socialnetwork.common",
]);

if (($arResult["NEED_AUTH"] ?? '') == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (($arResult["FatalError"] ?? '') <> '')
{
	?><div class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger"><?=$arResult["FatalError"]?></div><?
}
else
{
	if (($arResult["ErrorMessage"] ?? '') <> '')
	{
		?><div class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger"><?=$arResult["ErrorMessage"]?></div><?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?><script>
			BX.ready(function() {
				BX.BXSGD.init({
					groupId: <?=intval($arResult["Group"]["ID"])?>,
					groupType: '<?=CUtil::JSEscape($arResult["groupTypeCode"])?>',
					errorBlockName: 'sonet_group_delete_error_block'
				});
			});
		</script>

		<div
			id="sonet_group_delete_error_block"
			class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(($arResult["ErrorMessage"] ?? '') <> '' ? "" : " sonet-ui-form-error-block-invisible")?>"
		><?=$arResult["ErrorMessage"] ?? ''?></div>

		<div class="socialnetwork-group-delete-content">
			<div class="socialnetwork-group-delete-text"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_C9_SUBTITLE_PROJECT" : "SONET_C9_SUBTITLE")?></div>
			<form method="post" name="sonet-group-delete-form" id="sonet-group-delete-form" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"><?php

				$buttons = [
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-danger" id="sonet_group_delete_button_submit">' . Loc::getMessage($arResult['Group']['PROJECT'] === 'Y' ? 'SONET_C9_DO_DEL_PROJECT' : 'SONET_C9_DO_DEL') . '</button>',
					],
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-light-border" id="sonet_group_delete_button_cancel">' . Loc::getMessage('SONET_C9_DO_CANCEL') . '</button>',
					],

				];

				$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
					'BUTTONS' => $buttons,
				]);

				?><input type="hidden" name="SONET_GROUP_ID" value="<?= $arResult["Group"]["ID"] ?>">
				<input type="hidden" name="ajax_request" value="Y">
				<input type="hidden" name="save" value="Y">
				<?=bitrix_sessid_post()?><?php
			?></form>
		</div><?
	}
	else
	{
		?><?=Loc::getMessage("SONET_C9_SUCCESS") ?><br><br><?
	}
}
?>