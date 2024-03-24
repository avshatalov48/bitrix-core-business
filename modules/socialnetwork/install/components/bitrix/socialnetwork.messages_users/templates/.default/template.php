<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
	$APPLICATION->AuthForm("");
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
	?><div class="sonet-cntnr-messages-users">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th colspan="2"><?= GetMessage("SONET_C30_T_USER") ?></th>
			<th><?= GetMessage("SONET_C30_T_TOTAL") ?></th>
			<th><?= GetMessage("SONET_C30_T_NEW") ?></th>
			<th><?= GetMessage("SONET_C30_T_ACTIONS") ?></th>
		</tr><?
		$ind = 0;
		if ($arResult["Events"]):
			foreach ($arResult["Events"] as $event):
				?><tr>
					<td valign="top"<?= ($event["UNREAD"] > 0 ? " class=\"selected\"" : "") ?> width="1%" nowrap><?
						?><?=$event["USER_PERSONAL_PHOTO_IMG"]; ?><?
					?></td>
					<td valign="top"<?= ($event["UNREAD"] > 0 ? " class=\"selected\"" : "") ?>><?
						$APPLICATION->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $event["USER_ID"],
								"HTML_ID" => "messages_users_".$event["USER_ID"],
								"NAME" => htmlspecialcharsback($event["USER_NAME"]),
								"LAST_NAME" => htmlspecialcharsback($event["USER_LAST_NAME"]),
								"SECOND_NAME" => htmlspecialcharsback($event["USER_SECOND_NAME"]),
								"LOGIN" => htmlspecialcharsback($event["USER_LOGIN"]),
								"USE_THUMBNAIL_LIST" => "N",
								"PERSONAL_PHOTO_IMG" => $event["USER_PERSONAL_PHOTO_IMG"],
								"PERSONAL_PHOTO_FILE" => $event["USER_PERSONAL_PHOTO_FILE"],
								"PROFILE_URL" => $event["USER_PROFILE_URL"],
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
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?><br><br>
						<i><?= $event["MAX_DATE_FORMAT"] ?></i>
					</td>
					<td align="center" valign="top"<?= ($event["UNREAD"] > 0 ? " class=\"selected\"" : "") ?>><?
						if (intval($event["TOTAL"]) > 0):
							?><a href="<?= $event["USER_MESSAGES_LINK"] ?>"><b><?= $event["TOTAL"]; ?></b></a><?
						endif;
					?></td>
					<td align="center" valign="top"<?= ($event["UNREAD"] > 0 ? " class=\"selected\"" : "") ?>><?
						if (intval($event["UNREAD"]) > 0):
							?><a href="<?= $event["USER_MESSAGES_LINK"] ?>"><b><?= $event["UNREAD"]; ?></b></a><?
						endif;
					?></td>
					<td valign="top"<?= ($event["UNREAD"] > 0 ? " class=\"selected\"" : "") ?> width="0%" nowrap>
						<a href="<?= $event["USER_MESSAGES_LINK"] ?>"><?= GetMessage("SONET_C30_T_MESSAGES") ?></a><br><br><?
						if ($event["SHOW_ANSWER_LINK"]):
							?><a href="<?= $event["CHAT_LINK"] ?>" onclick="if (typeof BXIM !== 'undefined') { BXIM.openMessenger(<?=$event["USER_ID"]?>); return false; } else { window.open('<?= $event["CHAT_LINK"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }"><?= GetMessage("SONET_C30_T_WRITE_MESSAGE") ?></a><br><br><?
						endif;
						if ($event["SHOW_BAN_LINK"]):
							?><a href="<?= $event["BAN_LINK"] ?>"><?= GetMessage("SONET_C30_T_BAN") ?></a><?
						endif;
					?></td>
				</tr><?
				$ind++;
			endforeach;
		else:
			?><tr>
				<td colspan="4"><?= GetMessage("SONET_C30_T_EMPTY") ?></td>
			</tr><?
		endif;
	?></table>
	</div><?
	if ($arResult["NAV_STRING"] <> ''):
		?><?=$arResult["NAV_STRING"]?><br /><br /><?
	endif;
}
?>