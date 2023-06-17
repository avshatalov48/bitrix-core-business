<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult["FatalError"]))
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

	if ($arResult["EventType"] == "FriendRequest")
	{
		?>
		<div class="sonet-cntnr-events">
		<table class="sonet-user-profile-friends data-table" width="100%">
			<tr>
				<th align="center"><?= GetMessage("SONET_C1_FR_TITLE") ?></th>
			</tr>
			<tr>
				<td>
					<table width="100%" border="0" class="sonet-user-profile-friend-box">
					<tr>
						<td align="center">
							<?= $arResult["Event"]["USER_PERSONAL_PHOTO_IMG"]; ?><br>
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $arResult["Event"]["USER_ID"],
									"HTML_ID" => "group_requests_".$arResult["Event"]["USER_ID"],
									"NAME" => $arResult["Event"]["USER_NAME"],
									"LAST_NAME" => $arResult["Event"]["USER_LAST_NAME"],
									"SECOND_NAME" => $arResult["Event"]["USER_SECOND_NAME"],
									"LOGIN" => $arResult["Event"]["USER_LOGIN"],
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $arResult["Event"]["USER_PERSONAL_PHOTO_IMG"],
									"PERSONAL_PHOTO_FILE" => $arResult["Event"]["USER_PERSONAL_PHOTO_FILE"],
									"PROFILE_URL" => $arResult["Event"]["USER_PROFILE_URL"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
									"SHOW_FIELDS" => $arParams["SHOW_FIELDS_TOOLTIP"],
									"USER_PROPERTY" => $arParams["USER_PROPERTY_TOOLTIP"],
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							
							?>
						</td>
					</tr>
					<tr>
						<td align="center">
							<?= $arResult["Event"]["DATE_UPDATE"]; ?>
						</td>
					</tr>
					<tr>
						<td align="center">
							<?= GetMessage("SONET_C1_FR_TEXT") ?>.
						</td>
					</tr>
					<tr>
						<td>
							<?= $arResult["Event"]["MESSAGE"]; ?>
						</td>
					</tr>
					<tr>
						<td>
							<input type="button" name="do_friend_add" value="<?= GetMessage("SONET_C1_FR_ADD") ?>" onclick="window.location='<?= $arResult["Urls"]["FriendAdd"] ?>'">
							<input type="button" name="do_friend_reject" value="<?= GetMessage("SONET_C1_REJECT") ?>" onclick="window.location='<?= $arResult["Urls"]["FriendReject"] ?>'">
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		</div>
		<?
	}
	elseif ($arResult["EventType"] == "GroupRequest")
	{
		?>
		<div class="sonet-cntnr-events">
		<table class="sonet-user-profile-friends data-table" width="100%">
			<tr>
				<th align="center"><?= GetMessage("SONET_C1_GR_TITLE") ?></th>
			</tr>
			<tr>
				<td>
					<table width="100%" border="0" class="sonet-user-profile-friend-box">
					<tr>
						<td align="center">
							<?= $arResult["Event"]["GROUP_IMAGE_ID_IMG"]; ?><br>
							<?
							if ($arResult["Event"]["SHOW_GROUP_LINK"])
								echo "<a href=\"".$arResult["Event"]["GROUP_PROFILE_URL"]."\">";
							echo $arResult["Event"]["GROUP_NAME"];
							if ($arResult["Event"]["SHOW_GROUP_LINK"])
								echo "</a>";
							?>
						</td>
					</tr>
					<tr>
						<td align="center">
							<?= $arResult["Event"]["DATE_CREATE"]; ?>
						</td>
					</tr>
					<tr>
						<td align="center">
							<?= GetMessage("SONET_C1_GR_INV") ?>:
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $arResult["Event"]["USER_ID"],
									"HTML_ID" => "group_requests_".$arResult["Event"]["USER_ID"],
									"NAME" => $arResult["Event"]["USER_NAME"],
									"LAST_NAME" => $arResult["Event"]["USER_LAST_NAME"],
									"SECOND_NAME" => $arResult["Event"]["USER_SECOND_NAME"],
									"LOGIN" => $arResult["Event"]["USER_LOGIN"],
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $arResult["Event"]["USER_PERSONAL_PHOTO_IMG"],
									"PERSONAL_PHOTO_FILE" => $arResult["Event"]["USER_PERSONAL_PHOTO_FILE"],
									"PROFILE_URL" => $arResult["Event"]["USER_PROFILE_URL"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
									"SHOW_FIELDS" => array(
										"EMAIL",
										"PERSONAL_MOBILE",
										"WORK_PHONE",
										"PERSONAL_ICQ",
										"PERSONAL_PHOTO",
										"PERSONAL_CITY",
										"WORK_COMPANY",
										"WORK_POSITION"
									),
									"USER_PROPERTY" => array(
										"UF_DEPARTMENT",
									),
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							
							?>
						</td>
					</tr>
					<tr>
						<td>
							<?= $arResult["Event"]["MESSAGE"]; ?>
						</td>
					</tr>
					<tr>
						<td>
							<input type="button" name="do_friend_add" value="<?= GetMessage("SONET_C1_GR_ADD") ?>" onclick="window.location='<?= $arResult["Urls"]["FriendAdd"] ?>'">
							<input type="button" name="do_friend_reject" value="<?= GetMessage("SONET_C1_REJECT") ?>" onclick="window.location='<?= $arResult["Urls"]["FriendReject"] ?>'">
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		</div>
		<?
	}
	elseif ($arResult["EventType"] == "Message")
	{
		?>
		<div class="sonet-cntnr-events">
		<table class="sonet-user-profile-friends data-table" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<th align="center"><?= GetMessage("SONET_C1_MS_TITLE") ?></th>
			</tr>
			<tr>
				<td>
					<table width="100%" border="0" class="sonet-user-profile-friend-box">
					<tr>
						<td align="center">
							<?= $arResult["Event"]["USER_PERSONAL_PHOTO_IMG"]; ?><br>
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $arResult["Event"]["USER_ID"],
									"HTML_ID" => "group_requests_".$arResult["Event"]["USER_ID"],
									"NAME" => $arResult["Event"]["USER_NAME"],
									"LAST_NAME" => $arResult["Event"]["USER_LAST_NAME"],
									"SECOND_NAME" => $arResult["Event"]["USER_SECOND_NAME"],
									"LOGIN" => $arResult["Event"]["USER_LOGIN"],
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $arResult["Event"]["USER_PERSONAL_PHOTO_IMG"],
									"PERSONAL_PHOTO_FILE" => $arResult["Event"]["USER_PERSONAL_PHOTO_FILE"],
									"PROFILE_URL" => $arResult["Event"]["USER_PROFILE_URL"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
									"SHOW_FIELDS" => array(
										"EMAIL",
										"PERSONAL_MOBILE",
										"WORK_PHONE",
										"PERSONAL_ICQ",
										"PERSONAL_PHOTO",
										"PERSONAL_CITY",
										"WORK_COMPANY",
										"WORK_POSITION"
									),
									"USER_PROPERTY" => array(
										"UF_DEPARTMENT",
									),
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							
							?>
						</td>
					</tr>
					<tr>
						<td align="center">
							<?= $arResult["Event"]["DATE_CREATE"]; ?>
						</td>
					</tr>
					<?if ($arResult["Event"]["TITLE"] <> ''):?>
					<tr>
						<td align="center">
							<?= $arResult["Event"]["TITLE"]; ?>
						</td>
					</tr>
					<?endif;?>
					<tr>
						<td>
							<?= $arResult["Event"]["MESSAGE"]; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?if ($arResult["Urls"]["Reply"]["Show"]):?>
								<input type="button" name="do_message_answer" value="<?= GetMessage("SONET_C1_ANSWER") ?>" onclick="window.location='<?= $arResult["Urls"]["Reply"]["Link"] ?>'">
							<?endif;?>
							<input type="button" name="do_message_close" value="<?= GetMessage("SONET_C1_CLOSE") ?>" onclick="window.location='<?= $arResult["Urls"]["Close"] ?>'">
							<?if ($arResult["Urls"]["Reply"]["Show"]):?>
								<br><br>
								<a href="<?= $arResult["Urls"]["Chat"] ?>"><?= GetMessage("SONET_C1_CHAT") ?></a>
							<?endif;?>
							<?if ($arResult["Urls"]["Ban"]["Show"]):?>
								<br><br>
								<a href="<?= $arResult["Urls"]["Ban"]["Link"] ?>"><?= GetMessage("SONET_C1_BAN") ?></a>
							<?endif;?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		</div>
		<?
	}
}
?>