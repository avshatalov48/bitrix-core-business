<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI;

UI\Extension::load([
	"ui.design-tokens",
	"ui.tooltip"
]);

if (($arResult["NEED_AUTH"] ?? null) === "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (($arResult["FatalError"] ?? '') <> '')
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CUtil::InitJSCore(array("popup"));
	if(!empty($arResult["ErrorMessage"]))
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}
	if($arResult["InfoMessage"] <> '')
	{
		?><span class='infotext'><?=$arResult["InfoMessage"]?></span><br /><?
	}
	?>
	<script>
	BX.ready(function()
	{
		BX.addCustomEvent(window, "onImConfirmNotify", BX.proxy(function(params){ __hideInvitationItem(params); }, this));
	});
	</script>
	<?
	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		array(
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"] ?? null,
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"] ?? null,
			"SHOW_YEAR" => $arParams["SHOW_YEAR"] ?? null,
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"] ?? null,
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"] ?? null,
		),
		false,
		array("HIDE_ICONS" => "Y")
	);
	?>
	<div class="invite-main-wrap">
		<div class="invite-title"><?=GetMessage("SONET_URE_T_SUBTITLE_IN")?></div>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" id="form_requests"><?
		$noItems = true;
		if (
			$arResult["RequestsIn"]
			&& ($arResult["RequestsIn"]["List"] ?? null)
		)
		{
			$noItems = false;
			?><table class="invite-list" cellspacing="0">
			<tr>
				<td class="invite-list-header"><input type="checkbox" title="<?=GetMessage("SONET_URE_T_CHECK_ALL")?>" onclick="__URECheckedAll(this)"/></td>
				<td class="invite-list-header" colspan="2"><?=GetMessage("SONET_URE_T_SENDER")?></td>
				<td class="invite-list-header"><?=GetMessage("SONET_URE_T_MESSAGE_IN")?></td>
			</tr><?
			$ind = 0;
			foreach ($arResult["RequestsIn"]["List"] as $arRequest)
			{
				?><tr id="<?=$arRequest["EVENT_TYPE"]."_".$arRequest["ID"]?>">
					<td class="invite-list-checkbox">
						<div class="invite-active-block">
							<input type="checkbox" name="checked_<?= $ind ?>" value="Y" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'invite-list-active');" />
							<input type="hidden" name="id_<?=$ind ?>" value="<?=$arRequest["ID"] ?>">
							<input type="hidden" name="type_<?=$ind ?>" value="<?=$arRequest["EVENT_TYPE"] ?>">
						</div>
					</td>

					<td class="invite-list-img">
						<div class="invite-active-block">
							<? if ($arRequest["EVENT_TYPE"] == "INVITE_USER"): ?>
								<span class="invite-list-img-image" style="<?=(is_array($arRequest["USER_PERSONAL_PHOTO_IMG"]) && $arRequest["USER_PERSONAL_PHOTO_IMG"]["src"] <> '' ? "background: url('".$arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
							<? else: ?>
								<span class="invite-list-img-image" style="<?=(is_array($arRequest["GROUP_IMG"]) && $arRequest["GROUP_IMG"]["src"] <> '' ? "background: url('".$arRequest["GROUP_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
							<? endif; ?>
						</div>
					</td>

					<td class="invite-list-name">
						<div class="invite-active-block">
						<? if ($arRequest["EVENT_TYPE"] == "INVITE_USER"): ?>
							<? if ($arRequest["SHOW_PROFILE_LINK"]): ?>
								<a href="<?=htmlspecialcharsback($arRequest["USER_PROFILE_URL"])?>" class="invite-user-link" bx-tooltip-user-id="<?=$arRequest["USER_ID"]?>"><?=$arRequest["USER_NAME_FORMATTED"]?></a>
							<? else: ?>
								<span class="invite-user-link" bx-tooltip-user-id="<?=$arRequest["USER_ID"]?>"><?=$arRequest["USER_NAME_FORMATTED"]?></span>
							<? endif; ?>
							</div>
						<? else: ?>
							<a href="<?=htmlspecialcharsback($arRequest["GROUP_URL"])?>" class="invite-user-link"><?=$arRequest["GROUP_NAME"]?></a>
						<? endif; ?>
					</td>
					<td class="invite-list-message"><div class="invite-active-block"><?=htmlspecialcharsback($arRequest["MESSAGE"])?><br /><i><?=$arRequest["DATE_CREATE"]?></i></div></td>
				</tr><?

				$ind++;
			}
			?></table>

			<div class="invite-list-nav"><?
			if ($arResult["RequestsIn"]["NAV_STRING"] <> ''):
				?><?=$arResult["RequestsIn"]["NAV_STRING"]?><br /><br /><?
			endif;
			?></div><?
		}
		?>
		<span class="sonet-group-requests-info <?=($noItems) ? '': 'sonet-group-requests-info-hidden'?>">
			<?=GetMessage("SONET_URE_T_NO_REQUESTS")?><br /><?=GetMessage("SONET_URE_T_NO_REQUESTS_DESCR")?>
		</span>
		<div class="invite-buttons-block">
			<span class="invite-buttons-area <?=($noItems) ? 'invite-buttons-area-hidden': ''?>">
				<a class="sonet-group-requests-smbutton sonet-group-requests-smbutton-accept" href="#" onclick="__URESubmitForm('in', 'accept');">
					<span class="sonet-group-requests-smbutton-left"></span>
					<span class="sonet-group-requests-smbutton-text"><?=GetMessage("SONET_URE_T_DO_SAVE")?></span>
					<span class="sonet-group-requests-smbutton-right"></span>
				</a>
				<span class="popup-window-button popup-window-button-link popup-window-button-link-cancel" onclick="__URESubmitForm('in', 'reject');">
					<span class="popup-window-button-link-text"><?=GetMessage("SONET_URE_T_REJECT")?></span>
				</span>
			</span>
		</div>
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<input type="hidden" name="type" value="in">
		<input type="hidden" name="action" id="requests_action_in" value=""><?
		?><?=bitrix_sessid_post()?><?
		?></form><?
	?>
	</div>

	<div class="invite-main-wrap invite-main-wrap-out">
		<div class="invite-title"><?=GetMessage("SONET_URE_T_SUBTITLE_OUT")?></div>
		<form method="post" name="form2" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" id="form_requests_out"><?
		if (
			$arResult["RequestsOut"]
			&& ($arResult["RequestsOut"]["List"] ?? null)
		)
		{
			?><table class="invite-list" cellspacing="0">
			<tr>
				<td class="invite-list-header"><input type="checkbox" title="<?=GetMessage("SONET_URE_T_CHECK_ALL")?>" onclick="__URECheckedAll(this)"/></td>
				<td class="invite-list-header" colspan="2"><?=GetMessage("SONET_URE_T_RECIPIENT")?></td>
				<td class="invite-list-header"><?=GetMessage("SONET_URE_T_MESSAGE_OUT")?></td>
			</tr><?
			$ind = 0;
			foreach ($arResult["RequestsOut"]["List"] as $arRequest)
			{
				?><tr id="<?=$arRequest["EVENT_TYPE"]."_".$arRequest["ID"]?>">
					<td class="invite-list-checkbox">
						<div class="invite-active-block">
							<input type="checkbox" name="checked_<?= $ind ?>" value="Y" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'invite-list-active');" />
							<input type="hidden" name="id_<?=$ind ?>" value="<?=$arRequest["ID"] ?>">
							<input type="hidden" name="type_<?=$ind ?>" value="<?=$arRequest["EVENT_TYPE"] ?>">
						</div>
					</td>
					<td class="invite-list-img">
						<div class="invite-active-block">
							<? if ($arRequest["EVENT_TYPE"] == "INVITE_USER"): ?>
								<span class="invite-list-img-image" style="<?=(is_array($arRequest["USER_PERSONAL_PHOTO_IMG"]) && $arRequest["USER_PERSONAL_PHOTO_IMG"]["src"] <> '' ? "background: url('".$arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
							<? else: ?>
								<span class="invite-list-img-image" style="<?=(is_array($arRequest["GROUP_IMG"]) && $arRequest["GROUP_IMG"]["src"] <> '' ? "background: url('".$arRequest["GROUP_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
							<? endif; ?>
						</div>
					</td>
					<td class="invite-list-name">
						<div class="invite-active-block">
						<? if ($arRequest["EVENT_TYPE"] == "INVITE_USER"): ?>
							<? if ($arRequest["SHOW_PROFILE_LINK"]): ?>
								<a href="<?=htmlspecialcharsback($arRequest["USER_PROFILE_URL"])?>" class="invite-user-link" bx-tooltip-user-id="<?=$arRequest["USER_ID"]?>"><?=$arRequest["USER_NAME_FORMATTED"]?></a>
							<? else: ?>
								<span class="invite-user-link" bx-tooltip-user-id="<?=$arRequest["USER_ID"]?>"><?=$arRequest["USER_NAME_FORMATTED"]?></span>
							<? endif; ?>
							</div>
						<? else: ?>
							<a href="<?=htmlspecialcharsback($arRequest["GROUP_URL"])?>" class="invite-user-link"><?=$arRequest["GROUP_NAME"]?></a>
						<? endif; ?>
					</td>
					<td class="invite-list-message"><div class="invite-active-block"><?=htmlspecialcharsback($arRequest["MESSAGE"])?><br /><i><?=$arRequest["DATE_CREATE"]?></i></div></td>
				</tr><?

				$ind++;
			}
			?></table>

			<div class="invite-list-nav"><?
			if ($arResult["RequestsOut"]["NAV_STRING"] <> ''):
				?><?=$arResult["RequestsOut"]["NAV_STRING"]?><br /><br /><?
			endif;
			?></div><?
		}
		else
		{
			?><span class="sonet-group-requests-info"><?=GetMessage("SONET_URE_T_NO_REQUESTS_OUT")?><br /><?=GetMessage("SONET_URE_T_NO_REQUESTS_OUT_DESCR")?></span><?
		}

		?><div class="invite-buttons-block"><?
		if (
			$arResult["RequestsOut"]
			&& ($arResult["RequestsOut"]["List"] ?? null)
		)
		{
			?><a class="sonet-group-requests-smbutton" href="#" onclick="__URESubmitForm('out', 'reject');"><?
				?><span class="sonet-group-requests-smbutton-left"></span><?
				?><span class="sonet-group-requests-smbutton-text"><?=GetMessage("SONET_URE_T_REJECT_OUT")?></span><?
				?><span class="sonet-group-requests-smbutton-right"></span><?
			?></a><?
		}
		?></div>
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<input type="hidden" name="type" value="out">
		<input type="hidden" name="action" id="requests_action_out" value=""><?
		?><?=bitrix_sessid_post()?><?
		?></form></div><?
}

?>