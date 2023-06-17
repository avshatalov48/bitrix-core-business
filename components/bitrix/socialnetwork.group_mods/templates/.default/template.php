<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

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

	<?if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
	<?endif;?>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<div class="sonet-cntnr-group-mods">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th><?= GetMessage("SONET_C10_SUBTITLE") ?></th>
		</tr>
		<tr>
			<td>
				<?
				if ($arResult["Moderators"] && $arResult["Moderators"]["List"])
				{
					?><br />
					<div class="bx-links-container"><?
					if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):
						?><script>
							var arUsers = [];
							var bx_owner_menu = new PopupMenu('bx_owner_menu');

							function ShowOwnerMenu(el)
							{
								if (arUsers.length > 0)
								{
									var items = [];

									for (var i = 0; i < arUsers.length; i++)
									{
										items[i] = {
											ICONCLASS: !arUsers[i].CURRENT ? '' : 'checked1',
											TEXT: arUsers[i].NAME,
											ONCLICK: 'SetOwner(' + arUsers[i].ID + ')'
										}
									}

									bx_owner_menu.ShowMenu(el, items);
								}
							}

							function SetOwner(id)
							{
								var url = '/bitrix/tools/sonet_group_set_owner.php?GROUP_ID=<?echo $arResult["Group"]["ID"]?>&USER_ID=' + parseInt(id) + '&<?echo bitrix_sessid_get()?>';
								jsUtils.LoadPageToDiv(url, 'blank')
							}

							window.onload = function() {if (arUsers.length > 0) document.getElementById('bx_owner_link').style.display = 'inline';}
						</script><span id="blank"></span>
						<a href="javascript:void(0)" class="bx-owner-link" id="bx_owner_link" onclick="ShowOwnerMenu(this);" style="display: none;"><span><?echo GetMessage('SONET_C10_T_OWNER')?></span></a><?
					endif;
					?></div>

					<table width="100%" border="0" class="sonet-user-profile-friend-box">
					<tr>
						<td align="left" valign="top">
					<?
					$ind = 0;
					$ind_row = 0;

					$colcnt = 2;
					$cnt = count($arResult["Moderators"]["List"]);
					$rowcnt = intval(round($cnt / $colcnt));

					foreach ($arResult["Moderators"]["List"] as $friend)
					{
						if ($ind_row >= $rowcnt)
						{
							echo "</td><td align=\"left\" valign=\"top\" width=\"".intval(100 / $colcnt)."%\">";
							$ind_row = 0;
						}

						?><div class="user-div"><?

						if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"])
						{
							?>
							<table cellspacing="0" cellpadding="0" border="0" class="sonet-user-profile-friend-user">
							<tr>
								<td align="right" class="checkbox-cell">
								<?
								if ($friend["USER_ID"] != $arResult["Group"]["OWNER_ID"])
									echo "<input type=\"checkbox\" name=\"checked_".$ind."\" value=\"Y\">";
								echo "<input type=\"hidden\" name=\"id_".$ind."\" value=\"".$friend["ID"]."\">";
								?>
								</td>
								<td>
							<?
						}

						if ($friend["USER_ID"] == $arResult["Group"]["OWNER_ID"])
							$strUserDesc = GetMessage("SONET_C10_OWNER");
						else
							$strUserDesc = "";

						$APPLICATION->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $friend["USER_ID"],
								"HTML_ID" => "group_mods_".$friend["USER_ID"],
								"DESCRIPTION" => $strUserDesc,
								"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
								"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
								"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
								"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"]),
								"PERSONAL_PHOTO_IMG" => $friend["USER_PERSONAL_PHOTO_IMG"],
								"PROFILE_URL" => htmlspecialcharsback($friend["USER_PROFILE_URL"]),
								"THUMBNAIL_LIST_SIZE" => $arParams["THUMBNAIL_LIST_SIZE"],
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

						if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"])
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
						if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):
							?><script>arUsers[arUsers.length] = {ID:<?echo $friend["USER_ID"]?>,NAME:'<?echo CUtil::JSEscape(htmlspecialcharsback($friend["USER_NAME_FORMATTED"]))?>',CURRENT:<?echo $friend["IS_OWNER"] ? 'true' : 'false'?>}</script><?
						endif;
					}
					?>
						</td>
					</tr>
					</table>
					<?
				}
				else
				{
					echo GetMessage("SONET_C10_NO_MODS")."<br>".GetMessage("SONET_C10_NO_MODS_DESCR");
				}
				?>

				<?if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):?>
					<a href="<?= $arResult["Urls"]["GroupUsers"] ?>"><?= GetMessage("SONET_C10_DO_SET") ?></a>
				<?endif;?>
			</td>
		</tr>
	</table>
	</div>
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<br><?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):?>
		<br />
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<input type="submit" name="save" value="<?= GetMessage("SONET_C10_DO_SAVE") ?>">
		</form>
	<?endif;?>
	<?
}
?>