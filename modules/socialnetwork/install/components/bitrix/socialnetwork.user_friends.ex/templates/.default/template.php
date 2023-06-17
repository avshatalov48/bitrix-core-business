<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

if(!empty($arResult["FatalError"]))
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CUtil::InitJSCore(array("popup"));

	if(!empty($arResult["ErrorMessage"]))
	{
		?><span class="errortext"><?=$arResult["ErrorMessage"]?></span><br /><br /><?
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

		BX.message({

			UFEAddToFriendsTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_ADDTOFRIENDS"))?>',
			UFEExcludeFromFriendsTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_EXCLUDEFROMFRIENDS"))?>',
			UFEBanTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_BAN"))?>',
			UFEExcludeFromFriendsConfirmTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_EXCLUDEFROMFRIENDS_CONFIRM"))?>',
			UFEUnBanTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_UNBAN"))?>',
			UFEErrorFriendIDNotDefined: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_FRIEND_ID_NOT_DEFINED"))?>',
			UFEErrorFriendIDIncorrect: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_FRIEND_ID_INCORRECT"))?>',
			UFEErrorFriendIDIncorrect2: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_FRIEND_ID_INCORRECT_2"))?>',
			UFEErrorUserIDNotDefined: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_USER_ID_NOT_DEFINED"))?>',
			UFErrorCurrentUserNotAuthorized: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_NOT_ATHORIZED"))?>',
			UFEErrorModuleNotInstalled: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_MODULE_NOT_INSTALLED"))?>',
			UFEErrorNoPerms: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_NO_PERMS"))?>',
			UFEErrorSessionWrong: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_SESSION_WRONG"))?>',
			UFEErrorActionFailedPattern: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_ACTION_FAILED"))?>',			
			UFESiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
			UFEUserId: <?=intval($arParams["ID"])?>,
			UFEUseBan: '<?=(IsModuleInstalled("im") ? "N" : "Y")?>',
			UFEIsB24: '<?=(SITE_TEMPLATE_ID == "bitrix24" ? "Y" : "N")?>',
			UFEIsCurrentUser: <?=($arResult["CurrentUserPerms"]["IsCurrentUser"] ? "true" : "false")?>,
			UFEWaitTitle: '<?=CUtil::JSEscape(GetMessage("SONET_UFE_T_WAIT"))?>',
			UFEPathToUserSearch: '<?=CUtil::JSEscape($arResult["Urls"]["Search"])?>'
		});

		var actionUsers = false;
		var oUFEWaitWindow = false;

		BX.ready(
			function()	
			{
				var userBlockArr = BX.findChildren(document, { className: 'sonet-members-member-block' }, true);
				if (userBlockArr)
				{
					for (var i = userBlockArr.length - 1; i >= 0; i--)
					{
						BX.bind(userBlockArr[i], 'mouseover', function() {
							BX.addClass(this, 'sonet-members-member-block-over');
						});

						BX.bind(userBlockArr[i], 'mouseout', function() {
							BX.removeClass(this, 'sonet-members-member-block-over');
						});
					}
				}

				
				actionUsers = { 'Friends': new Array() };
				if (BX.message("UFEUseBan") == "Y")
					actionUsers['Banned'] = new Array();
			}
		);

	</script><?

	?><div class="sonet-members-item"><?
		if (is_array($arResult["Baned"]["List"]))
		{
			?><span class="sonet-members-item-name"><?=GetMessage("SONET_UFE_T_FRIENDS_SUBTITLE")?></span><?
			?><div class="sonet-members-separator"></div><?
		}
		if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["IsCurrentUser"])
		{
			?><div class="sonet-members-item-menu"><?
				?><span class="sonet-members-item-menu-title" onclick="__UFEShowMenu(this, 'friends');"><?
					?><?=GetMessage("SONET_UFE_T_ACTIONS_TITLE")?>&nbsp;<?
					?><span class="sonet-members-item-menu-arrow"></span><?
				?></span>
			</div><?
		}
		if (is_array($arResult["Friends"]) && is_array($arResult["Friends"]["List"]))
		{			
			?><div class="sonet-members-member-block-shift"><?
				foreach ($arResult["Friends"]["List"] as $arFriend)
				{
					$arUserTmp = array(
						"ID" => $arFriend["USER_ID"],
						"NAME" => htmlspecialcharsback($arFriend["USER_NAME"]),
						"LAST_NAME" => htmlspecialcharsback($arFriend["USER_LAST_NAME"]),
						"SECOND_NAME" => htmlspecialcharsback($arFriend["USER_SECOND_NAME"]),
						"LOGIN" => htmlspecialcharsback($arFriend["USER_LOGIN"])
					);

					?><span class="sonet-members-member-block"><?
						?><span class="sonet-members-member-img-wrap" <?
						if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["IsCurrentUser"])
						{
							?>onclick="__UFEtoggleCheckbox(event, this, 'F<?=intval($arFriend["USER_ID"])?>');"<?
						}
						?>><?
							?><span class="sonet-members-member-img" style="<?=(is_array($arFriend["USER_PERSONAL_PHOTO_IMG"]) && $arFriend["USER_PERSONAL_PHOTO_IMG"]["src"] <> '' ? "background: url('".$arFriend["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
							if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["IsCurrentUser"])
							{
								?><input class="sonet-members-checkbox" type="checkbox"/><?
							}
						?></span><?
						?><span class="sonet-members-member-text"><?
							?><span class="sonet-members-member-title"><?
							if ($arFriend["SHOW_PROFILE_LINK"])
							{
								?><a href="<?=htmlspecialcharsback($arFriend["USER_PROFILE_URL"])?>" class="sonet-members-membet-link" bx-tooltip-user-id="<?=$arFriend["USER_ID"]?>"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
							}
							else
							{
								?><span class="sonet-members-membet-link" bx-tooltip-user-id="<?=$arFriend["USER_ID"]?>"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
							}
							?></span><?
							if (IsModuleInstalled("intranet"))
							{
								?><span class="sonet-members-member-description"><?=$arFriend["USER_WORK_POSITION"]?></span><?
							}
						?></span><?
					?></span><?
				}
			?></div><?
		}
		else
			echo GetMessage("SONET_UFE_T_NO_FRIENDS");

		if ($arResult["Friends"]["NAV_STRING"] <> ''):
			?><div class="sonet-members-nav"><?=$arResult["Friends"]["NAV_STRING"]?></div><?
		endif;

	?></div><?

	if (is_array($arResult["Banned"]) && is_array($arResult["Banned"]["List"]))
	{
		?><div class="sonet-members-item"><?
			?><span class="sonet-members-item-name"><?=GetMessage("SONET_UFE_T_BAN_SUBTITLE")?></span><?
			?><div class="sonet-members-separator"></div><?
			if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["IsCurrentUser"])
			{
				?><div class="sonet-members-item-menu"><?
					?><span class="sonet-members-item-menu-title" onclick="__UFEShowMenu(this, 'ban');"><?
						?><?=GetMessage("SONET_UFE_T_ACTIONS_TITLE")?>&nbsp;<?
						?><span class="sonet-members-item-menu-arrow"></span><?
					?></span>
				</div><?
			}
			?><div class="sonet-members-member-block-shift"><?
				foreach ($arResult["Banned"]["List"] as $arBanned)
				{
					$arUserTmp = array(
						"ID" => $arBanned["USER_ID"],
						"NAME" => htmlspecialcharsback($arBanned["USER_NAME"]),
						"LAST_NAME" => htmlspecialcharsback($arBanned["USER_LAST_NAME"]),
						"SECOND_NAME" => htmlspecialcharsback($arBanned["USER_SECOND_NAME"]),
						"LOGIN" => htmlspecialcharsback($arBanned["USER_LOGIN"])
					);

					?><span class="sonet-members-member-block"><?
						?><span class="sonet-members-member-img-wrap" id="sonet-members-owner" <?
						if ($arBanned["CAN_UNBAN"])
						{
							?>onclick="__UFEtoggleCheckbox(event, this, 'B<?=intval($arBanned["USER_ID"])?>');"<?
						}
						?>><?
							?><span class="sonet-members-member-img" style="<?=(is_array($arBanned["USER_PERSONAL_PHOTO_IMG"]) && $arBanned["USER_PERSONAL_PHOTO_IMG"]["src"] <> '' ? "background: url('".$arBanned["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
							if ($arBanned["CAN_UNBAN"])
							{
								?><input class="sonet-members-checkbox" type="checkbox"/><?
							}
						?></span><?
						?><span class="sonet-members-member-text"><?
							?><span class="sonet-members-member-title"><?
							if ($arBanned["SHOW_PROFILE_LINK"])
							{
								?><a href="<?=htmlspecialcharsback($arBanned["USER_PROFILE_URL"])?>" class="sonet-members-membet-link" bx-tooltip-user-id="<?=$arBanned["USER_ID"]?>"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
							}
							else
							{
								?><span class="sonet-members-membet-link" bx-tooltip-user-id="<?=$arBanned["USER_ID"]?>"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
							}
							?></span><?
							if (IsModuleInstalled("intranet"))
							{
								?><span class="sonet-members-member-description"><?=$arBanned["USER_WORK_POSITION"]?></span><?
							}
						?></span><?
					?></span><?
				}
			?></div><?

			if ($arResult["Banned"]["NAV_STRING"] <> ''):
				?><div class="sonet-members-nav"><?=$arResult["Banned"]["NAV_STRING"]?></div><?
			endif;

		?></div><?
	}	
	
}
?>