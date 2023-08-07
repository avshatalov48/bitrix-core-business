<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

if(!CModule::IncludeModule("socialnetwork"))
{
	return false;
}

?><table width="100%">
<tr>
	<td><?
	if (
		$arGadgetParams["MODERATORS_LIST"]
		&& is_array($arGadgetParams["MODERATORS_LIST"])
	)
	{
		$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/main.user.link/templates/.default/style.css');

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

		?><table width="100%" border="0" class="sonet-user-profile-friend-box"><?
		foreach ($arGadgetParams["MODERATORS_LIST"] as $friend)
		{

			?><tr><?
				?><td align="left"><?
					$arTmpUser = array(
						"ID" => $friend["USER_ID"],
						"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
						"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
						"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
						"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"])
					);

					$link = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER"], array("user_id" => $friend["USER_ID"], "USER_ID" => $friend["USER_ID"], "ID" => $friend["USER_ID"]));

					?><table cellspacing="0" cellpadding="0" border="0" class="bx-user-info-anchor" bx-tooltip-user-id="<?=$friend["USER_ID"]?>"><?
					?><tr><?
						?><td class="bx-user-info-anchor-cell"><?
							?><div class="bx-user-info-thumbnail" align="center" valign="middle" style="width: 30px; height: 32px;"><?
								?><?=$friend["USER_PERSONAL_PHOTO_IMG"]?><?
							?></div><?
						?></td><?
						?><td class="bx-user-info-anchor-cell" valign="top"><?
							?><a class="bx-user-info-name" href="<?=$link?>"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N"))?></a><?
						?></td><?
					?></tr><?
					?></table><?
				?></td><?
			?></tr><?
		}
		?></table>
		<br><?
	}
	else
	{
		?><?= GetMessage("GD_SONET_GROUP_MODS_NO_MODS") ?>
		<br><br><?
	}
	?></td>
</tr>
</table>