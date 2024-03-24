<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

$arGadgetParams["SHOW_FEATURES"] = "Y";

?><?=htmlspecialcharsback($arGadgetParams["IMAGE"])?><br /><?

if ($arGadgetParams['IS_ONLINE'] || $arGadgetParams['IS_BIRTHDAY'] || $arGadgetParams['IS_ABSENT'] || $arGadgetParams["IS_HONOURED"]):
	?><div class="bx-user-control">
	<ul>
		<?if ($arGadgetParams['IS_ONLINE']):?><li class="bx-icon bx-icon-online"><?= GetMessage("GD_SONET_USER_LINKS_ONLINE") ?></li><?endif;?>
		<?if ($arGadgetParams['IS_BIRTHDAY']):?><li class="bx-icon bx-icon-birth"><?= GetMessage("GD_SONET_USER_LINKS_BIRTHDAY") ?></li><?endif;?>
		<?if ($arGadgetParams["IS_HONOURED"]):?><li class="bx-icon bx-icon-featured"><?= GetMessage("GD_SONET_USER_LINKS_HONOURED") ?></li><?endif;?>
		<?if ($arGadgetParams['IS_ABSENT']):?><li class="bx-icon bx-icon-away"><?= GetMessage("GD_SONET_USER_LINKS_ABSENT") ?></li><?endif;?>
	</ul>
	</div><?
endif;

if ($GLOBALS["USER"]->IsAuthorized()):
	if (!$arGadgetParams["IS_CURRENT_USER"]):
		?><div class="bx-user-control">
		<ul><?
			if ($arGadgetParams["CAN_MESSAGE"] && $arParams["USER_ACTIVE"] != "N"):
				?><li class="bx-icon-action bx-icon-message"><a href="<?=$arGadgetParams["URL_MESSAGE_CHAT"] ?>" onclick="if (typeof(BX) != 'undefined' && BX.IM) { BXIM.openMessenger(<?=$arParams["USER_ID"]?>); return false; } else { window.open('<?= $arGadgetParams["URL_MESSAGE_CHAT"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }"><?= GetMessage("GD_SONET_USER_LINKS_SEND_MESSAGE") ?></a></li><?
			endif;
			if ($arGadgetParams["CAN_VIDEOCALL"] && $arParams["USER_ACTIVE"] != "N"):
				?><li class="bx-icon-action bx-icon-video-call"><a href="<?= $arGadgetParams["URL_VIDEOCALL"] ?>" onclick="window.open('<?= $arGadgetParams["URL_VIDEOCALL"]?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;"><?= GetMessage("GD_SONET_USER_VIDEOCALL") ?></a></li><?
			endif;
			if ($arGadgetParams["CAN_MESSAGE"]):
				?><li class="bx-icon-action bx-icon-history"><a href="<?= $arGadgetParams["URL_USER_MESSAGES"] ?>" onclick="if (typeof BXIM !== 'undefined') { BXIM.openHistory(<?=$arParams["USER_ID"]?>); return false; }"><?= GetMessage("GD_SONET_USER_LINKS_SHOW_MESSAGES") ?></a></li><?
			endif;
			if (CSocNetUser::IsFriendsAllowed() && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite())):
				if ($arGadgetParams["RELATION"] == SONET_RELATIONS_FRIEND):
					?><li class="bx-icon-action bx-icon-friend-remove"><a href="<?= $arGadgetParams["URL_FRIENDS_DELETE"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_FR_DEL") ?></a></li><?
				elseif (!$arGadgetParams["RELATION"] || ($arGadgetParams["RELATION"] == SONET_RELATIONS_BAN && IsModuleInstalled("im"))):
					?><li class="bx-icon-action bx-icon-friend-add"><a href="<?= $arGadgetParams["URL_FRIENDS_ADD"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_FR_ADD") ?></a></li><?
				endif;
			endif;
		?></ul>
		</div><?
	endif;

	if ($arGadgetParams["CAN_MODIFY_USER"]):
		?><div class="bx-user-control">
		<ul><?
			if ($arGadgetParams["CAN_MODIFY_USER_MAIN"]):
				?><li class="bx-icon-action bx-icon-profile"><a href="<?= $arGadgetParams["URL_EDIT"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_PROFILE") ?></a></li><?
			endif;
			if (
				!isset($arParams["USER_TYPE"])
				|| !in_array($arParams["USER_TYPE"], array('bot', 'email', 'imconnector'))
			):
				if (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()):
					?><li class="bx-icon-action bx-icon-privacy"><a href="<?= $arGadgetParams["URL_SETTINGS"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_SETTINGS") ?></a></li><?
				endif;
				if ($arGadgetParams["SHOW_FEATURES"] == "Y"):
					?><li class="bx-icon-action bx-icon-settings"><a href="<?= $arGadgetParams["URL_FEATURES"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_FEATURES") ?></a></li><?
				endif;
				if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable()):
					?><li class="bx-icon-action bx-icon-subscribe"><a href="<?= $arGadgetParams["URL_EXTMAIL"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_EXTMAIL") ?></a></li><?
				endif;
				?><li class="bx-icon-action bx-icon-requests"><a href="<?= $arGadgetParams["URL_REQUESTS"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_REQUESTS") ?></a></li><?
			endif;
		?></ul>
		</div><?
	endif;

	if ($arGadgetParams["IS_CURRENT_USER"])
	{
		?>
		<div class="bx-user-control">
			<ul>
				<?if ($arGadgetParams["URL_SECURITY"] && $arParams["G_SONET_USER_OTP"]["IS_ENABLED"] !== "N"):
					?><li class="bx-icon-action bx-icon-security"><a href="<?=$arGadgetParams["URL_SECURITY"]?>"><?= GetMessage("GD_SONET_USER_LINKS_SECURITY") ?></a></li><?
				endif;?>
				<?if ($arGadgetParams["URL_PASSWORDS"]):
					?><li class="bx-icon-action bx-icon-passwords"><a href="<?=$arGadgetParams["URL_PASSWORDS"]?>"><?= GetMessage("GD_SONET_USER_LINKS_PASSWORDS") ?></a></li><?
				endif;?>
				<?if ($arGadgetParams["URL_SYNCHRONIZE"]):
					?><li class="bx-icon-action bx-icon-synchronize"><a href="<?=$arGadgetParams["URL_SYNCHRONIZE"]?>"><?= GetMessage("GD_SONET_USER_LINKS_SYNCHRONIZE") ?></a></li><?
				endif;?>
				<?if ($arParams["G_SONET_USER_OTP"]["IS_ACTIVE"] && $arParams["G_SONET_USER_OTP"]["ARE_RECOVERY_CODES_ENABLED"]):
					?><li class="bx-icon-action bx-icon-codes"><a href="<?=$arGadgetParams["URL_CODES"]?>"><?= GetMessage("GD_SONET_USER_LINKS_CODES") ?></a></li><?
				endif?>
			</ul>
		</div>
	<?
	}

	if (
		$arGadgetParams["IS_CURRENT_USER"]
		&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
		&& !CSocNetUser::IsEnabledModuleAdmin()
	)
	{
		?><?
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.admin.set",
			"",
			Array(
				"PROCESS_ONLY" => "Y"
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?><?
		?><div class="bx-user-control">
			<ul>
				<li class="bx-icon-action"><a onclick="__SASSetAdmin(); return false;" href="#"><?= GetMessage("GD_SONET_USER_SONET_ADMIN_ON") ?></a></li>
			</ul>
		</div><?
	}

endif;
?>