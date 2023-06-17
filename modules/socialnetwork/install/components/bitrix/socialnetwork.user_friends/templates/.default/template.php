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
	?>	
	<?if ($arResult["CanViewLog"]):?>
		<a href="<?= $arResult["Urls"]["LogUsers"] ?>"><?= GetMessage("SONET_C33_T_UPDATES") ?></a><br /><br />
	<?endif;?>
	<?
	if ($arResult["CurrentUserPerms"]["IsCurrentUser"]):
		?><form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"><?
	endif;
	?>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<div class="sonet-cntnr-user-friends">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th colspan="2"><?= GetMessage("SONET_C33_T_FRIENDS") ?></th>
		</tr>
		<tr>
			<td>
				<?
				if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewfriends"])
				{
					if ($arResult["Friends"] && $arResult["Friends"]["List"])
					{
						?>
						<table width="100%" border="0" class="sonet-user-profile-friend-box">
						<tr>
							<td align="left" valign="top">								
						<?
						$ind = 0;
						$ind_row = 0;
					
						$colcnt = 2;
						$cnt = count($arResult["Friends"]["List"]);
						$rowcnt = intval(round($cnt / $colcnt));
					
						foreach ($arResult["Friends"]["List"] as $friend)
						{
							if ($ind_row >= $rowcnt)
							{
								echo "</td><td align=\"left\" valign=\"top\" width=\"".intval(100 / $colcnt)."%\">";
								$ind_row = 0;
							}

							?><div class="user-div"><?
							
							if ($arResult["CurrentUserPerms"]["IsCurrentUser"])
							{
								?><table cellspacing="0" cellpadding="0" border="0" class="sonet-user-profile-friend-user">
								<tr>
									<td align="right" class="checkbox-cell"><?
									echo "<input type=\"checkbox\" name=\"checked_".$ind."\" value=\"Y\">";
									echo "<input type=\"hidden\" name=\"id_".$ind."\" value=\"".$friend["USER_ID"]."\">";
									?></td>
									<td><?
							}
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $friend["USER_ID"],
									"HTML_ID" => "user_friends_".$friend["USER_ID"],
									"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
									"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
									"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
									"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"]),
									"PERSONAL_PHOTO_IMG" => $friend["USER_PERSONAL_PHOTO_IMG"],
									"PROFILE_URL" => htmlspecialcharsback($friend["USER_PROFILE_URL"]),
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
									"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								),
								false
								, array("HIDE_ICONS" => "Y")
							);

							if ($friend["REQUEST_GROUP_LINK"] <> '' || $friend["CAN_ADD2FRIENDS"] || $friend["CAN_DELETE_FRIEND"])							
							{
								?><div class="desc-div"><?
								if ($friend["REQUEST_GROUP_LINK"] <> '')
									echo "<br><a href=\"".$friend["REQUEST_GROUP_LINK"]."\" class=\"action-link\"><b>".GetMessage("SONET_C33_T_INVITE")."</b></a>";
								?></div><?
							}
							
							if ($arResult["CurrentUserPerms"]["IsCurrentUser"])
							{
									?></td>
								</tr>
								</table><?
							}
							
							$ind++;
							$ind_row++;						
							?></div><?
						}
						?>
							</td>
						</tr>
						</table>
						<?
					}
					else
						echo GetMessage("SONET_C33_T_NO_FRIENDS");
				}
				else
					echo GetMessage("SONET_C33_T_FR_UNAVAIL");
				?>
				<?if ($arResult["CurrentUserPerms"]["IsCurrentUser"]):?>
					<a href="<?= $arResult["Urls"]["Search"] ?>"><?= ($friend["REQUEST_GROUP_LINK"] <> '') ? GetMessage("SONET_C33_T_ADD_FRIEND1") : GetMessage("SONET_C33_T_ADD_FRIEND") ?></a>
				<?endif;?>
			</td>
		</tr>
	</table>
	</div>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<br><?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?
	if ($arResult["CurrentUserPerms"]["IsCurrentUser"]):
		?><br />
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<input type="submit" name="delete" value="<?= GetMessage("SONET_C33_T_DELETE") ?>">		
		</form><?
	endif;	
}
?>