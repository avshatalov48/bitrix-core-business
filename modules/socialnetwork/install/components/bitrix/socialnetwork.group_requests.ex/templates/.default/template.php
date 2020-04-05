<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load("ui.buttons");
UI\Extension::load("ui.alerts");
UI\Extension::load("socialnetwork.common");

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CUtil::InitJSCore(array("tooltip", "popup", "sidepanel"));
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		array(
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		),
		false,
		array("HIDE_ICONS" => "Y")
	);

	?><script>
		BX.ready(function() {
			BX.BXSGRE.init({
				iframe: <?=$arResult["IS_IFRAME"] ? 'true' : 'false'?>,
				errorBlockName: 'sonet_group_requests_error_block',
				mode: '<?=CUtil::JSEscape($arResult['MODE'])?>'
			});
		});
		BX.message({
			SONET_GRE_T_ERROR: '<?=GetMessageJS('SONET_GRE_T_ERROR')?>'
		});
	</script><?

	?><div id="sonet_group_requests_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(strlen($arResult["ErrorMessage"]) > 0 ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?

	if (in_array($arResult['MODE'], array('ALL', 'IN')))
	{
		?><div class="invite-main-wrap" id="invite-main-wrap-in"><?
			if ($arResult['MODE'] == 'ALL')
			{
				?><div class="invite-title"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_GRE_T_SUBTITLE_IN_PROJECT" : "SONET_GRE_T_SUBTITLE_IN")?></div><?
			}

			?><form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" id="form_requests"><?
			if (
				!empty($arResult["Requests"])
				&& !empty($arResult["Requests"]["List"])
			)
			{
				?><div class="sonet-group-request-main">
					<div class="sonet-group-request-content">
						<div class="sonet-group-request-row sonet-group-request-head">
							<div class="invite-list-header sonet-group-request-cell">
								<input type="checkbox" id="sonet_group_requests_in_check_all" title="<?=Loc::getMessage("SONET_GRE_T_CHECK_ALL")?>">
							</div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_USER')?></div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_DATE_REQUEST_IN')?></div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_MESSAGE2_IN')?></div>
							<div class="invite-list-header sonet-group-request-cell"></div>
						</div><?

						$ind = 0;
						foreach ($arResult["Requests"]["List"] as $arRequest)
						{
							$tooltip_id = randString(8);
							?><div class="sonet-group-request-row sonet-group-request-first-row">
								<div class="sonet-group-request-cell">
									<input type="checkbox" name="checked_<?=$ind?>" value="Y" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'invite-list-active');">
									<input type="hidden" name="id_<?=$ind ?>" value="<?=$arRequest["ID"] ?>">
								</div>
								<div class="invite-list-img sonet-group-request-cell">
									<div class="invite-active-block">
										<span class="invite-list-img-image" style="<?=(is_array($arRequest["USER_PERSONAL_PHOTO_IMG"]) && strlen($arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
										<div class="sonet-group-request-user-box">
											<a class="invite-user-link" href="<?=($arRequest["SHOW_PROFILE_LINK"] ? htmlspecialcharsback($arRequest["USER_PROFILE_URL"]) : '')?>" bx-user-id="<?=$arRequest["USER_ID"]?>" id="anchor_<?=$tooltip_id?>"><?=$arRequest["USER_NAME_FORMATTED"]?></a>
											<div class="sonet-group-request-desc"><?=$arRequest["USER_WORK_POSITION"]?></div>
										</div>
									</div>
								</div>
								<div class="invite-list-message sonet-group-request-cell">
									<div class="invite-active-block"><?=FormatDateFromDB($arRequest["DATE_CREATE"], $arParams["DATE_TIME_FORMAT"], true)?></div>
								</div>
								<div class="sonet-group-request-cell invite-list-message">
									<div class="invite-active-block"><?=$arRequest["MESSAGE"]?></div>
								</div>
							</div><?

							$ind++;
						}
					?></div>
				</div><?

				?><div class="invite-list-nav"><?
					if (!empty($arResult["Requests"]["NAV_STRING"]))
					{
						?><?=$arResult["Requests"]["NAV_STRING"]?><br /><br /><?
					}
				?></div><?
			}
			else
			{
				?><div class="sonet-group-request-main">
					<div class="sonet-group-request-no-request">
						<div class="sonet-group-request-no-request-icon"></div>
						<div class="sonet-group-request-no-request-text"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_GRE_T_NO_REQUESTS2_PROJECT" : "SONET_GRE_T_NO_REQUESTS2")?></div>
					</div>
				</div><?
			}

			if ($arResult["Requests"] && $arResult["Requests"]["List"])
			{
				?><div class="sonet-slider-footer-fixed">
					<input type="hidden" name="ajax_request" value="Y">
					<input type="hidden" name="max_count" value="<?= $ind ?>">
					<input type="hidden" name="type" value="in">
					<input type="hidden" name="action" id="requests_action_in" value="">
					<?=bitrix_sessid_post()?>
					<span class="sonet-ui-btn-cont sonet-ui-btn-cont-center"><?
						?><button class="ui-btn ui-btn-success" id="sonet_group_requests_in_form_button_submit"><?=Loc::getMessage("SONET_GRE_T_DO_SAVE") ?></button><?
						?><button class="ui-btn ui-btn-danger" id="sonet_group_requests_in_form_button_reject"><?=Loc::getMessage("SONET_GRE_T_REJECT") ?></button><?
					?></span><? // class="sonet-ui-btn-cont"
				?></div><? // sonet-slider-footer-fixed
			}

			?></form><?
		?></div><?
	}

	if (in_array($arResult['MODE'], array('ALL', 'OUT')))
	{
		?><div class="invite-main-wrap" id="invite-main-wrap-out"><?

			if ($arResult['MODE'] == 'ALL')
			{
				?><div class="invite-title"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_GRE_T_SUBTITLE_OUT_PROJECT" : "SONET_GRE_T_SUBTITLE_OUT")?></div><?
			}

			?><form method="post" name="form2" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" id="form_requests_out"><?
			if ($arResult["RequestsOut"] && $arResult["RequestsOut"]["List"])
			{
				?><div class="sonet-group-request-main">
					<div class="sonet-group-request-content">


						<div class="sonet-group-request-row sonet-group-request-head">
							<div class="invite-list-header sonet-group-request-cell">
								<input type="checkbox" id="sonet_group_requests_in_check_all" title="<?=Loc::getMessage("SONET_GRE_T_CHECK_ALL")?>">
							</div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_USER')?></div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_DATE_REQUEST_OUT')?></div>
							<div class="invite-list-header sonet-group-request-cell"><?=Loc::getMessage('SONET_GRE_T_MESSAGE2_OUT')?></div>
							<div class="invite-list-header sonet-group-request-cell"></div>
						</div><?

						$ind = 0;
						foreach ($arResult["RequestsOut"]["List"] as $arRequest)
						{
							$tooltip_id = randString(8);
							?><div class="sonet-group-request-row sonet-group-request-first-row">
								<div class="sonet-group-request-cell">
									<input type="checkbox" name="checked_<?=$ind?>" value="Y" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'invite-list-active');">
									<input type="hidden" name="id_<?=$ind ?>" value="<?=$arRequest["ID"] ?>">
								</div>
								<div class="invite-list-img sonet-group-request-cell">
									<div class="invite-active-block">
										<span class="invite-list-img-image" style="<?=(is_array($arRequest["USER_PERSONAL_PHOTO_IMG"]) && strlen($arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arRequest["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span>
										<div class="sonet-group-request-user-box">
											<a class="invite-user-link" href="<?=($arRequest["SHOW_PROFILE_LINK"] ? htmlspecialcharsback($arRequest["USER_PROFILE_URL"]) : '')?>" bx-user-id="<?=$arRequest["USER_ID"]?>" id="anchor_<?=$tooltip_id?>"><?=$arRequest["USER_NAME_FORMATTED"]?></a>
											<div class="sonet-group-request-desc"><?=$arRequest["USER_WORK_POSITION"]?></div>
										</div>
									</div>
								</div>
								<div class="invite-list-message sonet-group-request-cell">
									<div class="invite-active-block"><?=FormatDateFromDB($arRequest["DATE_CREATE"], $arParams["DATE_TIME_FORMAT"], true)?></div>
								</div>
								<div class="sonet-group-request-cell invite-list-message">
									<div class="invite-active-block"><?=$arRequest["MESSAGE"]?></div>
								</div>
							</div><?

							$ind++;
						}
					?></div>
				</div><?

				?><div class="invite-list-nav"><?
					if (!empty($arResult["RequestsOut"]["NAV_STRING"]))
					{
						?><?=$arResult["RequestsOut"]["NAV_STRING"]?><br /><br /><?
					}
				?></div><?
			}
			else
			{
				?><div class="sonet-group-request-main">
					<div class="sonet-group-request-no-request">
						<div class="sonet-group-request-no-request-icon"></div>
						<div class="sonet-group-request-no-request-text"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_GRE_T_NO_REQUESTS2_OUT_PROJECT" : "SONET_GRE_T_NO_REQUESTS2_OUT")?></div>
					</div>
				</div><?
			}

			if ($arResult["RequestsOut"] && $arResult["RequestsOut"]["List"])
			{
				?><div class="sonet-slider-footer-fixed">
					<input type="hidden" name="ajax_request" value="Y">
					<input type="hidden" name="max_count" value="<?= $ind ?>">
					<input type="hidden" name="type" value="out">
					<input type="hidden" name="action" id="requests_action_out" value="">
					<?=bitrix_sessid_post()?>
					<span class="sonet-ui-btn-cont sonet-ui-btn-cont-center"><?
						?><button class="ui-btn ui-btn-danger" id="sonet_group_requests_out_form_button_reject"><?=Loc::getMessage("SONET_GRE_T_REJECT_OUT") ?></button><?
					?></span><? // class="sonet-ui-btn-cont"
				?></div><? // sonet-slider-footer-fixed
			}

			?></form><?
		?></div><?
	}
}
?>