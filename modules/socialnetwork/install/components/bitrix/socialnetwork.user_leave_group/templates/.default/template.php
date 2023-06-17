<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
$component = $this->getComponent();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load([
	"ui.design-tokens",
	"ui.buttons",
	"ui.alerts",
	"socialnetwork.common",
]);

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?><script>
			BX.ready(function() {
				BX.BXSULG.init({
					groupId: <?=intval($arResult["Group"]["ID"])?>,
					errorBlockName: 'sonet_group_user_leave_error_block'
				});
			});
		</script><?

		?><div id="sonet_group_user_leave_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(!empty($arResult["ErrorMessage"]) ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?
		?><div class="socialnetwork-group-leave-content"><?
			?><div class="socialnetwork-group-leave-text"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_C37_T_PROMT_PROJECT" : "SONET_C37_T_PROMT")?></div><?
			?><form method="post" id="sonet_group_user_leave_form" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"><?php

				$buttons = [
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-danger" id="sonet_group_user_leave_button_submit">' . Loc::getMessage($arResult['Group']['PROJECT'] === 'Y' ? 'SONET_C37_T_SAVE_PROJECT' : 'SONET_C37_T_SAVE') . '</button>',
					],
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-light-border" id="sonet_group_user_leave_button_cancel">' .Loc::getMessage($arResult['Group']['PROJECT'] === 'Y' ? 'SONET_C37_T_CANCEL_PROJECT' : 'SONET_C37_T_CANCEL') . '</button>',
					],
				];

				$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
					'BUTTONS' => $buttons,
				]);

				?><input type="hidden" name="SONET_GROUP_ID" value="<?=$arResult["Group"]["ID"] ?>">
				<input type="hidden" name="ajax_request" value="Y">
				<?=bitrix_sessid_post()?><?php
			?></form><?
		?></div><?
	}
	else
	{
		?><?= GetMessage("SONET_C37_T_SUCCESS") ?><br><br><?
		if ($arResult["CurrentUserPerms"]["UserCanSeeGroup"])
		{
			?><a href="<?= $arResult["Urls"]["Group"] ?>"><?= $arResult["Group"]["NAME"]; ?></a><?
		}
	}
}
?>