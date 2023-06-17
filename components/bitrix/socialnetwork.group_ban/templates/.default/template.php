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
	<?if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"]):?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
	<?endif;?>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<div class="sonet-cntnr-group-ban">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th><?= GetMessage("SONET_C7_SUBTITLE") ?></th>
		</tr>
		<tr>
			<td>
				<?
				if ($arResult["Users"] && $arResult["Users"]["List"])
				{
					?>
					<table width="100%" border="0" class="sonet-user-profile-friend-box">
					<tr>
						<td align="left" valign="top">							
					<?
					$ind = 0;
					$ind_row = 0;
					
					$colcnt = 2;
					$cnt = count($arResult["Users"]["List"]);
					$rowcnt = intval(round($cnt / $colcnt));
					
					foreach ($arResult["Users"]["List"] as $friend)
					{
						if ($ind_row >= $rowcnt)
						{
							echo "</td><td align=\"left\" valign=\"top\" width=\"".intval(100 / $colcnt)."%\">";
							$ind_row = 0;
						}
						
						?><div class="user-div"><?						
						
						if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"])
						{
							?>
							<table cellspacing="0" cellpadding="0" border="0" class="sonet-user-profile-friend-user">
							<tr>
								<td align="right" class="checkbox-cell">
								<?
								echo "<input type=\"checkbox\" name=\"checked_".$ind."\" value=\"Y\">";
								echo "<input type=\"hidden\" name=\"id_".$ind."\" value=\"".$friend["ID"]."\">";								?>
								</td>
								<td>
							<?
						}

						$APPLICATION->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $friend["USER_ID"],
								"HTML_ID" => "group_ban_".$friend["USER_ID"],
								"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
								"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
								"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
								"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"]),
								"PERSONAL_PHOTO_IMG" => $friend["USER_PERSONAL_PHOTO_IMG"],
								"PROFILE_URL" => $friend["USER_PROFILE_URL"],
								"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
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
						
						if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"])
						{
							?>
								</td>
							</tr>
							</table>
							<?
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
				{
					echo GetMessage("SONET_C7_NO_USERS")."<br>".GetMessage("SONET_C7_NO_USERS_DESCR");
				}
				?>

				<?if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"]):?>
					<a href="<?= $arResult["Urls"]["GroupUsers"] ?>"><?= GetMessage("SONET_C7_ACT_IN_BAN") ?></a>
				<?endif;?>
			</td>
		</tr>
	</table>
	</div>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<br><?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"]):?>
		<br />
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<input type="submit" name="save" value="<?= GetMessage("SONET_C7_ACT_SAVE") ?>">
		</form>
	<?endif;?>
	<?
}
?>