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
	'ui.design-tokens',
	'ui.buttons',
	'ui.alerts',
	'socialnetwork.common',
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
	if(
		!empty($arResult["ErrorMessage"])
		&& $arResult["ShowForm"] != "Input"
	)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?><script>
			BX.ready(function() {
				BX.BXSURG.init({
					iframe: <?=$arResult["IS_IFRAME"] ? 'true' : 'false'?>,
					errorBlockName: 'sonet_group_user_request_error_block'
				});
			});
			BX.message({
				SONET_C39_T_ERROR: '<?=GetMessageJS('SONET_C39_T_ERROR')?>'
			});
		</script><?

		?><div id="sonet_group_user_request_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(!empty($arResult["ErrorMessage"]) ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?

		?><form method="post" name="form1" id="sonet_group_user_request_form" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<table cellspacing="0">
				<tr>
					<td colspan="2" class="sonet-user-request-group-description-colspan"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_C39_T_PROMT2_PROJECT" : "SONET_C39_T_PROMT2") ?></td>
				</tr>
				<tr>
					<td class="sonet-user-request-group-description-left-col" nowrap><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_C39_T_GROUP_PROJECT" : "SONET_C39_T_GROUP") ?>:</td>
					<td class="sonet-user-request-group-description"><?=$arResult["Group"]["NAME"]?></td>
				</tr>
				<tr>
					<td class="sonet-user-request-group-description-left-col" nowrap><?= GetMessage("SONET_C39_T_MESSAGE") ?>:</td>
					<td class="sonet-user-request-group-description"><textarea id="sonet_group_user_request_message" name="MESSAGE" class="sonet-user-request-group-message-text" rows="5"><?= htmlspecialcharsex($_POST["MESSAGE"]); ?></textarea></td>
				</tr>
			</table><?php

			$buttons = [
				[
					'TYPE' => 'custom',
					'LAYOUT' => '<button class="ui-btn ui-btn-success" id="sonet_group_user_request_button_submit">' . Loc::getMessage('SONET_C39_T_SEND_BUTTON_TITLE'). '</button>',
				],
				[
					'TYPE' => 'custom',
					'LAYOUT' => '<button class="ui-btn ui-btn-light-border" id="sonet_group_user_request_button_cancel">' . Loc::getMessage('SONET_C39_T_CANCEL_BUTTON_TITLE') . '</button>',
				],

			];

			$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
				'BUTTONS' => $buttons,
			]);

			?><input type="hidden" name="SONET_GROUP_ID" value="<?=$arResult["Group"]["ID"] ?>">
			<input type="hidden" name="ajax_request" value="Y">
			<input type="hidden" name="save" value="Y">
			<?=bitrix_sessid_post()?><?php
		?></form>
		<?
	}
	elseif(is_array($arResult["Events"]) && count($arResult["Events"]) > 0)
	{
		?>
		<div class="sonet-cntnr-user-request-group">
		<table width="100%" class="sonet-user-profile-friends data-table">
			<tr>
				<th width="10%"><?= GetMessage("SONET_C39_T_SENDER") ?></th>
				<th width="90%"><?= GetMessage("SONET_C39_T_MESSAGE") ?></th>
				<th width="0%"><?= GetMessage("SONET_C39_T_ACTIONS") ?></th>
			</tr>
			<?foreach ($arResult["Events"] as $event):?>
				<tr>
					<td valign="top" width="10%" nowrap>
						<?if ($event["EventType"] == "GroupRequest"):?>
							<?= $event["Event"]["GROUP_IMAGE_ID_IMG"]; ?><br>
							<?
							if ($event["Event"]["SHOW_GROUP_LINK"])
								echo "<a href=\"".$event["Event"]["GROUP_PROFILE_URL"]."\">";
							echo $event["Event"]["GROUP_NAME"];
							if ($event["Event"]["SHOW_GROUP_LINK"])
								echo "</a>";
							?>
						<?endif;?>
					</td>
					<td valign="top" width="90%">
						<?if ($event["EventType"] == "GroupRequest"):?>
							<?= GetMessage("SONET_C39_T_USER") ?>
							<?

							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $event["Event"]["USER_ID"],
									"HTML_ID" => "messages_requests_".$event["Event"]["USER_ID"],
									"NAME" => htmlspecialcharsback($event["Event"]["USER_NAME"]),
									"LAST_NAME" => htmlspecialcharsback($event["Event"]["USER_LAST_NAME"]),
									"SECOND_NAME" => htmlspecialcharsback($event["Event"]["USER_SECOND_NAME"]),
									"LOGIN" => htmlspecialcharsback($event["Event"]["USER_LOGIN"]),
									"USE_THUMBNAIL_LIST" => "N",
									"PROFILE_URL" => $event["Event"]["USER_PROFILE_URL"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
									"INLINE" => "Y",
								),
								false,
								array("HIDE_ICONS" => "Y")
							);

							?>
							<?= GetMessage("SONET_C39_T_INVITE") ?>:<br /><br />
							<?= $event["Event"]["MESSAGE"]; ?><br /><br />
							<i><?= $event["Event"]["DATE_CREATE"]; ?></i>
						<?endif;?>
					</td>
					<td valign="top" width="0%" nowrap>
						<?if ($event["EventType"] == "GroupRequest"):?>
							<a href="<?= $event["Urls"]["FriendAdd"] ?>"><?= GetMessage("SONET_C39_T_DO_AGREE") ?></a><br><br>
							<a href="<?= $event["Urls"]["FriendReject"] ?>"><?= GetMessage("SONET_C39_T_DO_DENY") ?></a>
						<?endif;?>
					</td>
				</tr>
			<?endforeach;?>
		</table>
		</div>
		<br /><br />
		<?
	}
	else
	{
		?>
		<?if (array_key_exists("Success", $arResult) && $arResult["Success"] == "Rejected"):?>
			<?= GetMessage("SONET_C39_T_REJECTED") ?>
		<?elseif (array_key_exists("Success", $arResult) && $arResult["Success"] == "Added"):?>
			<?= GetMessage("SONET_C39_T_SUCCESS_ALT") ?>
		<?elseif ($arResult["Group"]["OPENED"] == "Y"):?>
			<?= GetMessage("SONET_C39_T_SUCCESS_ALT") ?>
		<?else:?>
			<?= GetMessage("SONET_C39_T_SUCCESS") ?>
		<?endif;?>
		<br><br>
		<a href="<?= $arResult["Urls"]["Group"] ?>"><?= $arResult["Group"]["NAME"]; ?></a>
		<?
	}
}
?>