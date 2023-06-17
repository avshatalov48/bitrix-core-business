<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>
	<?if ($arResult["Events"]):?>
		<div class="sonet-cntnr-messages-requests">
		<table width="100%" class="sonet-user-profile-friends data-table">
			<tr>
				<th width="10%"><?= GetMessage("SONET_C29_T_SENDER") ?></th>
				<th width="90%"><?= GetMessage("SONET_C29_T_MESSAGE") ?></th>
				<th width="0%"><?= GetMessage("SONET_C29_T_ACTIONS") ?></th>
			</tr>
			<?foreach ($arResult["Events"] as $event):?>
				<tr>
					<td valign="top" width="10%" nowrap>
						<?if ($event["EventType"] == "FriendRequest"):?>
							<?= $event["Event"]["USER_PERSONAL_PHOTO_IMG"]; ?><br>
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $event["Event"]["USER_ID"],
									"NAME" => htmlspecialcharsback($event["Event"]["USER_NAME"]),
									"LAST_NAME" => htmlspecialcharsback($event["Event"]["USER_LAST_NAME"]),
									"SECOND_NAME" => htmlspecialcharsback($event["Event"]["USER_SECOND_NAME"]),
									"LOGIN" => htmlspecialcharsback($event["Event"]["USER_LOGIN"]),
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $event["Event"]["USER_PERSONAL_PHOTO_IMG"],
									"PERSONAL_PHOTO_FILE" => $event["Event"]["USER_PERSONAL_PHOTO_FILE"],
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
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							
							?>
						<?else:?>
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
						<?if ($event["EventType"] == "FriendRequest"):?>
							<?= GetMessage("SONET_C29_T_FRIEND_REQUEST") ?>:<br /><br />
							<?= $event["Event"]["MESSAGE"]; ?><br /><br />
							<i><?= $event["Event"]["DATE_UPDATE"]; ?></i>
						<?else:?>
							<?= GetMessage("SONET_C29_T_USER") ?>
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $event["Event"]["USER_ID"],
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
							<?= GetMessage("SONET_C29_T_INVITE") ?>:<br /><br />
							<?= $event["Event"]["MESSAGE"]; ?><br /><br />
							<i><?= $event["Event"]["DATE_CREATE"]; ?></i>
						<?endif;?>
					</td>
					<td valign="top" width="0%" nowrap>
						<?if ($event["EventType"] == "FriendRequest"):?>
							<a href="<?= $event["Urls"]["FriendAdd"] ?>"><?= GetMessage("SONET_C29_T_DO_FRIEND") ?></a><br><br>
							<a href="<?= $event["Urls"]["FriendReject"] ?>"><?= GetMessage("SONET_C29_T_DO_DENY") ?></a>
						<?else:?>
							<a href="<?= $event["Urls"]["FriendAdd"] ?>"><?= GetMessage("SONET_C29_T_DO_AGREE") ?></a><br><br>
							<a href="<?= $event["Urls"]["FriendReject"] ?>"><?= GetMessage("SONET_C29_T_DO_DENY") ?></a>
						<?endif;?>
					</td>
				</tr>
			<?endforeach;?>
		</table>
		</div>
		<br /><br />
	<?endif;?>
	<?
}
?>