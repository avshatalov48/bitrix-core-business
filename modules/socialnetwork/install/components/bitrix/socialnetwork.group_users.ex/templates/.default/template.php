<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load("ui.buttons.icons");
UI\Extension::load("ui.alerts");
UI\Extension::load("socialnetwork.common");

if(strlen($arResult["FatalError"]) > 0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CUtil::InitJSCore(array("tooltip", "popup", "sidepanel"));

	if(strlen($arResult["ErrorMessage"])>0)
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
			GUEAddToUsersTitle: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_ACTION_ADDTOUSERS"))?>',
			GUEAddToModeratorsTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_ADDTOMODERATORS_PROJECT" : "SONET_GUE_T_ACTION_ADDTOMODERATORS"))?>',
			GUEExcludeFromGroupTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_EXCLUDEFROMGROUP_PROJECT" : "SONET_GUE_T_ACTION_EXCLUDEFROMGROUP"))?>',
			GUEExcludeFromModeratorsTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_EXCLUDEFROMMODERATORS_PROJECT" : "SONET_GUE_T_ACTION_EXCLUDEFROMMODERATORS"))?>',
			GUEExcludeFromGroupConfirmTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_EXCLUDEFROMGROUP_CONFIRM_PROJECT" : "SONET_GUE_T_ACTION_EXCLUDEFROMGROUP_CONFIRM"))?>',
			GUEUnBanFromGroupTitle: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_ACTION_UNBANFROMGROUP"))?>',
			GUESetGroupOwnerTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_SETGROUPOWNER_PROJECT" : "SONET_GUE_T_ACTION_SETGROUPOWNER"))?>',
			GUESetGroupOwnerConfirmTitle: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_ACTION_SETGROUPOWNER_CONFIRM_PROJECT" : "SONET_GUE_T_ACTION_SETGROUPOWNER_CONFIRM"))?>',
			GUESetGroupUnconnectDeptTitle: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_ACTION_UNCONNECT_DEPT"))?>',
			GUEErrorUserIDNotDefined: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_USER_ID_NOT_DEFINED"))?>',
			GUEErrorDepartmentIDNotDefined: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_DEPARTMENT_ID_NOT_DEFINED"))?>',
			GUEErrorUserIDIncorrect: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_USER_ID_INCORRECT"))?>',
			GUEErrorGroupIDNotDefined: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_GROUP_ID_NOT_DEFINED"))?>',
			GUEErrorCurrentUserNotAuthorized: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_NOT_ATHORIZED"))?>',
			GUEErrorModuleNotInstalled: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_MODULE_NOT_INSTALLED"))?>',
			GUEErrorOwnerCantExcludeHimself: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF_PROJECT" : "SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF"))?>',
			GUEErrorCantExcludeAutoMember: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER_PROJECT" : "SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER"))?>',
			GUEErrorNoPerms: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_NO_PERMS_PROJECT" : "SONET_GUE_T_NO_PERMS"))?>',
			GUEErrorSessionWrong: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_SESSION_WRONG"))?>',
			GUEErrorActionFailedPattern: '<?=CUtil::JSEscape(GetMessage("SONET_GUE_T_ACTION_FAILED"))?>',
			GUEErrorSameOwner: '<?=CUtil::JSEscape(GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_SAME_OWNER_PROJECT" : "SONET_GUE_T_SAME_OWNER"))?>',
			GUEGroupId: <?=intval($arParams["GROUP_ID"])?>,
			GUEGroupName: '<?=CUtil::JSEscape($arResult["Group"]["NAME"])?>',
			GUEUseBan: '<?=CUtil::JSEscape($arParams["GROUP_USE_BAN"])?>',
			GUEUseDepts: '<?=(IsModuleInstalled('intranet') ? 'Y' : 'N')?>',
			GUEIsB24: '<?=(SITE_TEMPLATE_ID == "bitrix24" ? "Y" : "N")?>',
			GUEUserCanViewGroup: <?=($arResult["CurrentUserPerms"]["UserCanViewGroup"] ? "true" : "false")?>,
			GUEUserCanModerateGroup: <?=($arResult["CurrentUserPerms"]["UserCanModerateGroup"] ? "true" : "false")?>,
			GUEUserCanModifyGroup: <?=($arResult["CurrentUserPerms"]["UserCanModifyGroup"] ? "true" : "false")?>,
			GUEUserCanInitiate: <?=($arResult["CurrentUserPerms"]["UserCanInitiate"] ? "true" : "false")?>,
			GUEPathToGroupInvite: '<?=(
				!empty($arResult["Urls"]["GroupInvite"])
					? htmlspecialcharsback($arResult["Urls"]["GroupInvite"])
					: htmlspecialcharsback($arResult["Urls"]["GroupEdit"]).(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=invite"
			)?>'
		});

		var actionUsers = false;

		BX.ready(function() {

			BX.BXGUE.init({
				groupId: <?=intval($arResult["Group"]["ID"])?>,
				errorBlockName: 'sonet_group_users_error_block',
				styles: {
					memberClass: 'sonet-members-member-block',
					memberClassOver: 'sonet-members-member-block-over',
					memberClassDelete: 'sonet-members-close'
				}
			});
		});
	</script><?

	if ($arResult["CurrentUserPerms"]["UserCanInitiate"])
	{
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.group.iframe.popup",
			".default",
			array(
				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
				"PATH_TO_GROUP_INVITE" => (
					!empty($arResult["Urls"]["GroupInvite"])
						? htmlspecialcharsback($arResult["Urls"]["GroupInvite"])
						: htmlspecialcharsback($arResult["Urls"]["GroupEdit"]).(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=invite"
				),
				"PATH_TO_GROUP_EDIT" => htmlspecialcharsback($arResult["Urls"]["GroupEdit"]).(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=edit",
				"PATH_TO_GROUP_FEATURES" => htmlspecialcharsback($arResult["Urls"]["GroupEdit"]).(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=features",
				"ON_GROUP_ADDED" => "BX.DoNothing",
				"ON_GROUP_CHANGED" => "BX.DoNothing",
				"ON_GROUP_DELETED" => "BX.DoNothing"
			),
			null,
			array("HIDE_ICONS" => "Y")
		);
	}

	?><div class="socialnetwork-group-users-content"><?

		?><div id="sonet_group_users_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(strlen($arResult["ErrorMessage"]) > 0 ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?

		if (!empty($arResult["Owner"]))
		{
			$canChangeOwner = (
				$arResult["CurrentUserPerms"]
				&& $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
			);

			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?=GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_OWNER_SUBTITLE_PROJECT" : "SONET_GUE_T_OWNER_SUBTITLE")?></span><?
				?><div class="sonet-members-separator"></div><?

				if ($canChangeOwner)
				{
					?><div class="sonet-members-item-menu" id="sonet-members-actionlink-changeowner"><?
						?><span class="sonet-members-item-menu-title" id="sonet-members-container-changeowner"><?=Loc::getMessage("SONET_GUE_T_ACTIONLINK_CHANGE")?></span><?
					?></div><?

					$selectorID = 'changeowner';

					$APPLICATION->IncludeComponent(
						"bitrix:main.ui.selector",
						".default",
						array(
							'ID' => $selectorID,
							'BIND_ID' => 'sonet-members-actionlink-changeowner',
							'ITEMS_SELECTED' => array(
								'U'.$arResult["Owner"]["USER_ID"] => 'users'
							),
							'CALLBACK' => array(
								'select' => 'BX.BXGUEDestinationSelectorManager.onSelect',
								'unSelect' => '',
								'openDialog' => 'BX.BXGUEDestinationSelectorManager.onDialogOpen',
								'closeDialog' => 'BX.BXGUEDestinationSelectorManager.onDialogClose',
								'openSearch' => ''
							),
							'OPTIONS' => array(
								'useNewCallback' => 'Y',
								'eventInit' => 'BX.SonetGroupUsers:openInit',
								'eventOpen' => 'BX.SonetGroupUsers:open',
								'context' => 'GROUP_SET_OWNER',
								'contextCode' => 'U',
								'useSearch' => 'Y',
								'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
								'useClientDatabase' => 'Y',
								'allowEmailInvitation' => 'N',
								'enableAll' => 'N',
								'enableDepartments' => 'Y',
								'enableSonetgroups' => 'N',
								'departmentSelectDisable' => 'Y',
								'allowAddUser' => 'N',
								'allowAddCrmContact' => 'N',
								'allowAddSocNetGroup' => 'N',
								'allowSearchEmailUsers' => 'N',
								'allowSearchCrmEmailUsers' => 'N',
								'allowSearchNetworkUsers' => 'N',
								'allowSonetGroupsAjaxSearchFeatures' => 'N'
							)
						),
						false,
						array("HIDE_ICONS" => "Y")
					);

					?><script>
						BX.ready(function() {
							BX.BXGUEDestinationSelector.create(
								"<?=CUtil::JSEscape($selectorID)?>",
								{}
							);
						});
					</script><?
				}

				?><div class="sonet-members-member-block-shift"><?

					$tooltip_id = randString(8);
					$arUserTmp = array(
						"ID" => $arResult["Owner"]["USER_ID"],
						"NAME" => htmlspecialcharsback($arResult["Owner"]["USER_NAME"]),
						"LAST_NAME" => htmlspecialcharsback($arResult["Owner"]["USER_LAST_NAME"]),
						"SECOND_NAME" => htmlspecialcharsback($arResult["Owner"]["USER_SECOND_NAME"]),
						"LOGIN" => htmlspecialcharsback($arResult["Owner"]["USER_LOGIN"])
					);

					?><span class="sonet-members-member-block" id="sonet-members-member-block-owner"><?
						?><span class="sonet-members-member-img-wrap"><?
							?><span class="sonet-members-member-img" style="<?=(is_array($arResult["Owner"]["USER_PERSONAL_PHOTO_IMG"]) && strlen($arResult["Owner"]["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arResult["Owner"]["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
						?></span><?

						?><span class="sonet-members-member-text"><?
							?><span class="sonet-members-member-title<?=($arResult["Owner"]["USER_IS_EXTRANET"] == "Y" ? " sonet-members-member-title-extranet" : "")?>"><?
								if ($arResult["Owner"]["SHOW_PROFILE_LINK"])
								{
									?><a id="anchor_<?=$tooltip_id?>" href="<?=htmlspecialcharsback($arResult["Owner"]["USER_PROFILE_URL"])?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
								}
								else
								{
									?><span id="anchor_<?=$tooltip_id?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
								}
							?></span><?

							if ($arResult["bIntranetInstalled"])
							{
								?><span class="sonet-members-member-description"><?=$arResult["Owner"]["USER_WORK_POSITION"]?><?
									if ($arResult["Owner"]["USER_ACTIVE"] != "Y")
									{
										?><?=(strlen($arResult["Owner"]["USER_WORK_POSITION"]) > 0 ? ", " : "").Loc::getMessage("SONET_GUE_T_FIRED".(in_array($arResult["Owner"]["USER_PERSONAL_GENDER"], array("M", "F")) ? "_".$arResult["Owner"]["USER_PERSONAL_GENDER"] : ""))?><?
									}
								?></span><?
							}

							?><script>
								BX.tooltip(<?=$arResult["Owner"]["USER_ID"]?>, "anchor_<?=$tooltip_id?>");
							</script><?
						?></span><?
					?></span><?
				?></div><?
			?></div><?
		}

		if (
			is_array($arResult["Moderators"])
			&& is_array($arResult["Moderators"]["List"])
		)
		{
			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?=GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_MODS_SUBTITLE_PROJECT" : "SONET_GUE_T_MODS_SUBTITLE")?></span><?
				?><div class="sonet-members-separator"></div><?

				if (
					$arResult["CurrentUserPerms"]
					&& $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
				)
				{
					?><div class="sonet-members-item-menu" id="sonet-members-actionlink-addmoderator"><?
						?><span class="sonet-members-item-menu-title" id="sonet-members-container-addmoderator">+&nbsp;<?=Loc::getMessage("SONET_GUE_T_ACTIONLINK_ADD")?></span>
					</div><?

					$selectorID = 'addmoderator';

					$arModeratorCodeList = array();
					foreach ($arResult["Moderators"]["List"] as $arMember)
					{
						$arModeratorCodeList['U'.$arMember['USER_ID']] = 'users';
					}
					$APPLICATION->IncludeComponent(
						"bitrix:main.ui.selector",
						".default",
						array(
							'ID' => $selectorID,
							'BIND_ID' => 'sonet-members-actionlink-addmoderator',
							'ITEMS_SELECTED' => $arModeratorCodeList,
							'CALLBACK' => array(
								'select' => 'BX.BXGUEDestinationSelectorManager.onSelect',
								'unSelect' => '',
								'openDialog' => 'BX.BXGUEDestinationSelectorManager.onDialogOpen',
								'closeDialog' => 'BX.BXGUEDestinationSelectorManager.onDialogClose',
								'openSearch' => ''
							),
							'OPTIONS' => array(
								'useNewCallback' => 'Y',
								'eventInit' => 'BX.SonetGroupUsers:openInit',
								'eventOpen' => 'BX.SonetGroupUsers:open',
								'context' => 'GROUP_ADD_MODERATOR',
								'contextCode' => 'U',
								'useSearch' => 'Y',
								'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
								'useClientDatabase' => 'Y',
								'allowEmailInvitation' => 'N',
								'enableAll' => 'N',
								'enableDepartments' => 'Y',
								'enableSonetgroups' => 'N',
								'departmentSelectDisable' => 'Y',
								'allowAddUser' => 'N',
								'allowAddCrmContact' => 'N',
								'allowAddSocNetGroup' => 'N',
								'allowSearchEmailUsers' => 'N',
								'allowSearchCrmEmailUsers' => 'N',
								'allowSearchNetworkUsers' => 'N',
								'allowSonetGroupsAjaxSearchFeatures' => 'N'
							)
						),
						false,
						array("HIDE_ICONS" => "Y")
					);

					?><script>
						BX.ready(function() {
							BX.BXGUEDestinationSelector.create(
								"<?=CUtil::JSEscape($selectorID)?>",
								{}
							);
						});
					</script><?
				}
				?><div class="sonet-members-member-block-shift"><?
					foreach ($arResult["Moderators"]["List"] as $arMember)
					{
						$canExclude = (
							$arResult["CurrentUserPerms"]
							&& $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
							&& $arMember["USER_ID"] != $USER->getId()
							&& $arMember["IS_OWNER"] != 'Y'
						);

						$tooltip_id = randString(8);
						$arUserTmp = array(
							"ID" => $arMember["USER_ID"],
							"NAME" => htmlspecialcharsback($arMember["USER_NAME"]),
							"LAST_NAME" => htmlspecialcharsback($arMember["USER_LAST_NAME"]),
							"SECOND_NAME" => htmlspecialcharsback($arMember["USER_SECOND_NAME"]),
							"LOGIN" => htmlspecialcharsback($arMember["USER_LOGIN"])
						);

						?><span class="sonet-members-member-block" bx-action="removemod" id="sonet-members-member-block-mod-<?=intval($arMember["USER_ID"])?>"><?
							if ($canExclude)
							{
								?><span class="sonet-members-close"><span class="sonet-members-close-item"></span></span><?
							}

							?><span class="sonet-members-member-img-wrap"><?
								?><span class="sonet-members-member-img" style="<?=(is_array($arMember["USER_PERSONAL_PHOTO_IMG"]) && strlen($arMember["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arMember["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
							?></span><?

							if ($canExclude)
							{
								?><span class="ui-btn ui-btn-sm ui-btn-danger sonet-members-member-button" bx-entity-id="<?=intval($arMember["USER_ID"])?>"><?=Loc::getMessage('SONET_GUE_T_BUTTON_REMOVEMOD')?></span><?
							}

							?><span class="sonet-members-member-text"><?
								?><span class="sonet-members-member-title<?=($arMember["USER_IS_EXTRANET"] == "Y" ? " sonet-members-member-title-extranet" : "")?>"><?
								if ($arMember["SHOW_PROFILE_LINK"])
								{
									?><a id="anchor_<?=$tooltip_id?>" href="<?=htmlspecialcharsback($arMember["USER_PROFILE_URL"])?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
								}
								else
								{
									?><span id="anchor_<?=$tooltip_id?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
								}
								?></span><?
								if ($arResult["bIntranetInstalled"])
								{
									?><span class="sonet-members-member-description"><?=$arMember["USER_WORK_POSITION"]?><?
									if ($arMember["USER_ACTIVE"] != "Y")
									{
										?><?=(strlen($arMember["USER_WORK_POSITION"]) > 0 ? ", " : "").GetMessage("SONET_GUE_T_FIRED".(in_array($arMember["USER_PERSONAL_GENDER"], array("M", "F")) ? "_".$arMember["USER_PERSONAL_GENDER"] : ""))?><?
									}
									?></span><?
								}
								if ($arMember["IS_OWNER"])
								{
									?><span class="sonet-members-caption"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_OWNER_PROJECT" : "SONET_GUE_T_OWNER")?></span><?
								}
								?><script type="text/javascript">
									BX.tooltip(<?=$arMember["USER_ID"]?>, "anchor_<?=$tooltip_id?>");
								</script><?
							?></span><?
						?></span><?
					}
				?></div><?

				if (StrLen($arResult["Moderators"]["NAV_STRING"]) > 0):
					?><div class="sonet-members-nav"><?=$arResult["Moderators"]["NAV_STRING"]?></div><?
				endif;

			?></div><?
		}

		if (
			is_array($arResult["Ban"])
			&& is_array($arResult["Ban"]["List"])
		)
		{
			$canUnban = (
				$arResult["CurrentUserPerms"]
				&& $arResult["CurrentUserPerms"]["UserCanModerateGroup"]
			);

			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?=GetMessage("SONET_GUE_T_BAN_SUBTITLE")?></span><?
				?><div class="sonet-members-separator"></div><?
				?><div class="sonet-members-member-block-shift"><?
					foreach ($arResult["Ban"]["List"] as $arMember)
					{
						$tooltip_id = randString(8);
						$arUserTmp = array(
							"ID" => $arMember["USER_ID"],
							"NAME" => htmlspecialcharsback($arMember["USER_NAME"]),
							"LAST_NAME" => htmlspecialcharsback($arMember["USER_LAST_NAME"]),
							"SECOND_NAME" => htmlspecialcharsback($arMember["USER_SECOND_NAME"]),
							"LOGIN" => htmlspecialcharsback($arMember["USER_LOGIN"])
						);

						?><span class="sonet-members-member-block" bx-action="unban"><?
							if ($canUnban)
							{
								?><span class="sonet-members-close"><span class="sonet-members-close-item"></span></span><?
							}
							?><span class="sonet-members-member-img-wrap"><?
								?><span class="sonet-members-member-img" style="<?=(is_array($arMember["USER_PERSONAL_PHOTO_IMG"]) && strlen($arMember["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arMember["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
							?></span><?
							if ($canUnban)
							{
								?><span class="ui-btn ui-btn-sm ui-btn-danger sonet-members-member-button" bx-entity-id="<?=intval($arMember["USER_ID"])?>"><?=Loc::getMessage('SONET_GUE_T_BUTTON_UNBAN')?></span><?
							}
							?><span class="sonet-members-member-text"><?
								?><span class="sonet-members-member-title<?=($arMember["USER_IS_EXTRANET"] == "Y" ? " sonet-members-member-title-extranet" : "")?>"><?
								if ($arMember["SHOW_PROFILE_LINK"])
								{
									?><a id="anchor_<?=$tooltip_id?>" href="<?=htmlspecialcharsback($arMember["USER_PROFILE_URL"])?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
								}
								else
								{
									?><span id="anchor_<?=$tooltip_id?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
								}
								?></span><?
								if ($arResult["bIntranetInstalled"])
								{
									?><span class="sonet-members-member-description"><?=$arMember["USER_WORK_POSITION"]?><?
									if ($arMember["USER_ACTIVE"] != "Y")
									{
										?><?=(strlen($arMember["USER_WORK_POSITION"]) > 0 ? ", " : "").GetMessage("SONET_GUE_T_FIRED".(in_array($arMember["USER_PERSONAL_GENDER"], array("M", "F")) ? "_".$arMember["USER_PERSONAL_GENDER"] : ""))?><?
									}
									?></span><?
								}
							?></span><?
							?><script type="text/javascript">
								BX.tooltip(<?=$arMember["USER_ID"]?>, "anchor_<?=$tooltip_id?>");
							</script><?
						?></span><?
					}
				?></div><?

				if (StrLen($arResult["Ban"]["NAV_STRING"]) > 0):
					?><div class="sonet-members-nav"><?=$arResult["Ban"]["NAV_STRING"]?></div><?
				endif;

			?></div><?
		}

		if (
			is_array($arResult["Departments"])
			&& is_array($arResult["Departments"]["List"])
		)
		{
			$canUnconnect = (
				$arResult["CurrentUserPerms"]
				&& $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
			);

			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?=GetMessage("SONET_GUE_T_DEPARTMENTS_SUBTITLE")?></span><?
				?><div class="sonet-members-separator"></div><?

				if (
					false &&
					$canUnconnect
				)
				{
					?><div class="sonet-members-item-menu" id="sonet-members-action-dept-add"><?
						?><span class="sonet-members-item-menu-title">+&nbsp;<?=Loc::getMessage("SONET_GUE_T_ACTIONLINK_ADD")?></span>
					</div><?
				}

				?><div class="sonet-members-member-block-shift"><?

					foreach ($arResult["Departments"]["List"] as $arDepartment)
					{
						?><span class="sonet-members-member-block" bx-action="unconnect"><?

							if ($canUnconnect)
							{
								?><span class="sonet-members-close"><span class="sonet-members-close-item"></span></span><?
							}

							?><span class="sonet-members-member-img-wrap"><span class="sonet-members-member-img"></span></span><?

							if ($canUnconnect)
							{
								?><span class="ui-btn ui-btn-sm ui-btn-danger sonet-members-member-button" bx-entity-id="<?=intval($arDepartment["ID"])?>"><?=Loc::getMessage('SONET_GUE_T_BUTTON_UNCONNECT')?></span><?
							}

							?><span class="sonet-members-member-text"><?
								?><span class="sonet-members-member-title"><?
									?><a href="<?=$arDepartment["URL"]?>" class="sonet-members-member-link"><?=$arDepartment["NAME"]?></a><?
								?></span><?
							?></span><?
						?></span><?
					}
				?></div><?
			?></div><?
		}

		if (
			is_array($arResult["Users"])
			&& is_array($arResult["Users"]["List"])
		)
		{
			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?=GetMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_USERS_SUBTITLE_PROJECT" : "SONET_GUE_T_USERS_SUBTITLE")?></span><?
				?><div class="sonet-members-separator"></div><?

				if (
					$arResult["CurrentUserPerms"]
					&& (
						$arResult["CurrentUserPerms"]["UserCanInitiate"]
						|| $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
					)
					&& !empty($arResult["Urls"])
					&& !empty($arResult["Urls"]["GroupEdit"])
				)
				{
					?><div class="sonet-members-item-menu" id="sonet-members-action-user-invite"><?
						?><a href="<?=
							!empty($arResult["Urls"]["GroupInvite"])
								? htmlspecialcharsbx($arResult["Urls"]["GroupInvite"])
								: htmlspecialcharsback($arResult["Urls"]["GroupEdit"]).(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=invite"
						?>" class="sonet-members-item-menu-title">+&nbsp;<?=Loc::getMessage("SONET_GUE_T_ACTIONLINK_INVITE")?></a>
					</div><?
				}

				?><div class="sonet-members-member-block-shift"><?

					foreach ($arResult["Users"]["List"] as $arMember)
					{
						$canExclude = (
							$arResult["CurrentUserPerms"]
							&& $arResult["CurrentUserPerms"]["UserCanModifyGroup"]
							&& $arMember["USER_ID"] != $USER->getId()
							&& $arMember["IS_OWNER"] != 'Y'
						);

						$tooltip_id = randString(8);
						$arUserTmp = array(
							"ID" => $arMember["USER_ID"],
							"NAME" => htmlspecialcharsback($arMember["USER_NAME"]),
							"LAST_NAME" => htmlspecialcharsback($arMember["USER_LAST_NAME"]),
							"SECOND_NAME" => htmlspecialcharsback($arMember["USER_SECOND_NAME"]),
							"LOGIN" => htmlspecialcharsback($arMember["USER_LOGIN"])
						);

						?><span class="sonet-members-member-block" bx-action="exclude"><?

							if ($canExclude)
							{
								?><span class="sonet-members-close"><span class="sonet-members-close-item"></span></span><?
							}

							?><span class="sonet-members-member-img-wrap"><?
								?><span class="sonet-members-member-img" style="<?=(is_array($arMember["USER_PERSONAL_PHOTO_IMG"]) && strlen($arMember["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arMember["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span><?
							?></span><?

							if ($canExclude)
							{
								?><span class="ui-btn ui-btn-sm ui-btn-danger sonet-members-member-button" bx-entity-id="<?=intval($arMember["USER_ID"])?>"><?=Loc::getMessage('SONET_GUE_T_BUTTON_EXCLUDE')?></span><?
							}

							?><span class="sonet-members-member-text"><?
								?><span class="sonet-members-member-title<?=($arMember["USER_IS_EXTRANET"] == "Y" ? " sonet-members-member-title-extranet" : "")?>"><?
								if ($arMember["SHOW_PROFILE_LINK"])
								{
									?><a id="anchor_<?=$tooltip_id?>" href="<?=htmlspecialcharsback($arMember["USER_PROFILE_URL"])?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
								}
								else
								{
									?><span id="anchor_<?=$tooltip_id?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
								}
								?></span><?
								if ($arResult["bIntranetInstalled"])
								{
									?><span class="sonet-members-member-description"><?=$arMember["USER_WORK_POSITION"]?><?
									if ($arMember["USER_ACTIVE"] != "Y")
									{
										?><?=(strlen($arMember["USER_WORK_POSITION"]) > 0 ? ", " : "").GetMessage("SONET_GUE_T_FIRED".(in_array($arMember["USER_PERSONAL_GENDER"], array("M", "F")) ? "_".$arMember["USER_PERSONAL_GENDER"] : ""))?><?
									}
									?></span><?
								}
								if ($arMember["IS_OWNER"])
								{
									?><span class="sonet-members-caption"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_OWNER_PROJECT" : "SONET_GUE_T_OWNER")?></span><?
								}
							?></span><?
							?><script type="text/javascript">
								BX.tooltip(<?=$arMember["USER_ID"]?>, "anchor_<?=$tooltip_id?>");
							</script><?
						?></span><?
					}
				?></div><?

				if (StrLen($arResult["Users"]["NAV_STRING"]) > 0):
					?><div class="sonet-members-nav"><?=$arResult["Users"]["NAV_STRING"]?></div><?
				endif;

			?></div><?
		}

		if (
			is_array($arResult["UsersAuto"])
			&& is_array($arResult["UsersAuto"]["List"])
		)
		{
			?><div class="sonet-members-item"><?
				?><span class="sonet-members-item-name"><?
					?><?=Loc::getMessage("SONET_GUE_T_USERS_AUTO_SUBTITLE")?><?
					?><span class="sonet-members-hint" id="sonet-members-auto-subtitle-hint" data-text="<?=htmlspecialcharsbx(Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_GUE_T_USERS_AUTO_SUBTITLE_HINT_PROJECT" : "SONET_GUE_T_USERS_AUTO_SUBTITLE_HINT"))?>">?</span><?
				?></span><?
				?><div class="sonet-members-separator"></div><?
				?><div class="sonet-members-member-block-shift"><?
					foreach ($arResult["UsersAuto"]["List"] as $arMember)
					{
						$tooltip_id = randString(8);
						$arUserTmp = array(
							"ID" => $arMember["USER_ID"],
							"NAME" => htmlspecialcharsback($arMember["USER_NAME"]),
							"LAST_NAME" => htmlspecialcharsback($arMember["USER_LAST_NAME"]),
							"SECOND_NAME" => htmlspecialcharsback($arMember["USER_SECOND_NAME"]),
							"LOGIN" => htmlspecialcharsback($arMember["USER_LOGIN"])
						);

						?><span class="sonet-members-member-block"><?
							?><span class="sonet-members-member-img-wrap"><?
							?><span class="sonet-members-member-img" style="<?=(is_array($arMember["USER_PERSONAL_PHOTO_IMG"]) && strlen($arMember["USER_PERSONAL_PHOTO_IMG"]["src"]) > 0 ? "background: url('".$arMember["USER_PERSONAL_PHOTO_IMG"]["src"]."') no-repeat 0 0;" : "")?>"></span></span><?
							?><span class="sonet-members-member-text"><?
								?><span class="sonet-members-member-title<?=($arMember["USER_IS_EXTRANET"] == "Y" ? " sonet-members-member-title-extranet" : "")?>"><?
									if ($arMember["SHOW_PROFILE_LINK"])
									{
										?><a id="anchor_<?=$tooltip_id?>" href="<?=htmlspecialcharsback($arMember["USER_PROFILE_URL"])?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></a><?
									}
									else
									{
										?><span id="anchor_<?=$tooltip_id?>" class="sonet-members-member-link"><?=CUser::FormatName(str_replace(array("#NOBR#", "#/NOBR#"), array("", ""), $arParams["NAME_TEMPLATE"]), $arUserTmp, $arParams["SHOW_LOGIN"] != "N")?></span><?
									}
								?></span><?
								if ($arResult["bIntranetInstalled"])
								{
									?><span class="sonet-members-member-description"><?=$arMember["USER_WORK_POSITION"]?><?
									if ($arMember["USER_ACTIVE"] != "Y")
									{
										?><?=(strlen($arMember["USER_WORK_POSITION"]) > 0 ? ", " : "").GetMessage("SONET_GUE_T_FIRED".(in_array($arMember["USER_PERSONAL_GENDER"], array("M", "F")) ? "_".$arMember["USER_PERSONAL_GENDER"] : ""))?><?
									}
									?></span><?
								}
							?></span><?
							?><script type="text/javascript">
								BX.tooltip(<?=$arMember["USER_ID"]?>, "anchor_<?=$tooltip_id?>");
							</script><?
						?></span><?
					}
				?></div><?

				if (StrLen($arResult["UsersAuto"]["NAV_STRING"]) > 0):
					?><div class="sonet-members-nav"><?=$arResult["UsersAuto"]["NAV_STRING"]?></div><?
				endif;

			?></div><?
		}

	?></div><? // socialnetwork-group-users-content
}
?>