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

	if ($arResult["ShowForm"] == "Input")
	{
	
		if (!$arResult["bIntranet"])
		{
			?><script language="JavaScript">
			<!--
			var bFirstUser = true;
			function AddUser(name)
			{
				if (name.length <= 0)
					return;

				var userDiv = document.getElementById("id_users");

				if (bFirstUser)
				{
					userDiv.innerHTML = "";
					bFirstUser = false;
				}

				userDiv.innerHTML += "<b>" + name.replace("<", "&lt;").replace(">", "&gt;") + "</b><br />";
				document.sonet_form1.users_list.value += name + ",";
			}
			//-->
			</script><?
		}
		?>
		<table>
		<tr>
			<td valign="top" width="75%">
				<form method="post" name="sonet_form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
					<table class="sonet-message-form data-table" cellspacing="0" cellpadding="0">
						<tr>
							<th colspan="3"><?= GetMessage("SONET_C11_SUBTITLE") ?></th>
						</tr>
						<tr>
							<td valign="top" width="10%" align="right" nowrap><span class="required-field">*</span><?= GetMessage("SONET_C11_USER") ?>:</td>
							<td valign="top">
							<?
							if (!IsModuleInstalled('intranet'))
							{
								?>
								<div id="id_users"><i><?= GetMessage("SONET_C33_T_UNOTSET") ?></i></div>
								<input type="hidden" name="users_list" value=""><br />
								<?
							}

							if ($arResult["bExtranet"])
								$bExtranet = true;

							if ($arResult["isCurrentUserIntranet"])
							{
								if (!$arResult["bIntranet"])
									$APPLICATION->IncludeComponent(
										"bitrix:socialnetwork.user_search_input",
										".default",
										array(
											"TEXT" => "size='50'",
											"EXTRANET" => "I",
											"FUNCTION" => "AddUser",
											"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
											"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
										)
									);								
								else
								{
									if ($bExtranet)
										echo "<p>".GetMessage("SONET_C11_USER_INTRANET")."<br>";

									$ControlID = $APPLICATION->IncludeComponent('bitrix:intranet.user.selector', 
										'', 
										array(
											'INPUT_NAME' => $arParams["IUS_INPUT_NAME"],
											'INPUT_NAME_STRING' => $arParams["IUS_INPUT_NAME_STRING"],
											'INPUT_NAME_SUSPICIOUS' => $arParams["IUS_INPUT_NAME_SUSPICIOUS"],
											'TEXTAREA_MIN_HEIGHT' => 50,
											'TEXTAREA_MAX_HEIGHT' => 150,
											'INPUT_VALUE_STRING' => $_REQUEST[$arParams["IUS_INPUT_NAME_STRING"]],
											'EXTERNAL' => 'I'
										)
									);
								}
							}

							if ($bExtranet)
							{
								if ($arResult["isCurrentUserIntranet"])
								{
									$ExtranetUserFilter = "EA";
									echo "<p>".GetMessage("SONET_C11_USER_EXTRANET")."<br>";
								}
								else
									$ExtranetUserFilter = "E";

								$APPLICATION->IncludeComponent('bitrix:intranet.user.selector', '', array(
									'INPUT_NAME' => $arParams["IUS_INPUT_NAME_EXTRANET"],
									'INPUT_NAME_STRING' => $arParams["IUS_INPUT_NAME_STRING_EXTRANET"],										
									'INPUT_NAME_SUSPICIOUS' => $arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"],
									'TEXTAREA_MIN_HEIGHT' => 50,
									'TEXTAREA_MAX_HEIGHT' => 150,
									'INPUT_VALUE_STRING' => $_REQUEST[$arParams["IUS_INPUT_NAME_STRING_EXTRANET"]],
									'EXTERNAL' => $ExtranetUserFilter
									)
								);
								echo GetMessage("SONET_C11_EMAIL");
							}
							?>
							</td>
							<td valign="top" width="2%"></td>
						</tr>
						<tr>
							<td valign="top" align="right" width="10%" nowrap><?= GetMessage("SONET_C11_GROUP") ?>:</td>
							<td valign="top" colspan="2">
								<b><?
								echo "<a href=\"".$arResult["Urls"]["Group"]."\">";
								echo $arResult["Group"]["NAME"];
								echo "</a>";
								?></b>
							</td>
						</tr>
						<?
						// default invitation message
						$message = htmlspecialcharsex($_POST["MESSAGE"]);
						if ($message == '')
							$message = str_replace(
								array("#NAME#"), 
								array($arResult["Group"]["NAME"]), 
								GetMessage('SONET_C11_MESSAGE_DEFAULT')
							);
						?>
						<tr>
							<td valign="top" align="right" nowrap><?= GetMessage("SONET_C11_MESSAGE") ?>:</td>
							<td valign="top"><textarea name="MESSAGE" style="width:100%" rows="5"><?= $message; ?></textarea></td>
							<td valign="top"></td>
						</tr>
					</table>
					<input type="hidden" name="SONET_USER_ID" value="<?= $arResult["User"]["ID"] ?>">
					<input type="hidden" name="SONET_GROUP_ID" value="<?= $arResult["Group"]["ID"] ?>">
					<?=bitrix_sessid_post()?>
					<br />
					<input type="submit" name="save" value="<?= GetMessage("SONET_C11_DO_ACT") ?>">
					<?
					if ($arParams["ALLOW_SKIP"] == "Y"):
						?><input type="submit" name="skip" value="<?= GetMessage("SONET_C11_DO_SKIP") ?>"><?
					endif
					?>
				</form>
			</td>
			<td valign="top" width="25%">
				<?if ($arResult["Friends"] && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite())):?>
					<div class="sonet-cntnr-group-request-search">
					<table width="100%" class="sonet-user-profile-friends data-table">
						<tr>
							<th><?= GetMessage("SONET_C33_T_FRIENDS") ?></th>
						</tr>
						<tr>
							<td>
								<?
								if ($arResult["Friends"] && $arResult["Friends"]["List"])
								{
									?>
									<table width="100%" border="0" class="sonet-user-profile-friend-box">
									<?
									foreach ($arResult["Friends"]["List"] as $friend)
									{
										?>
										<tr>
											<td>
											<?
											if (!$arResult["bIntranet"])
												$href = "javascript:AddUser('".CUtil::JSEscape($friend["USER_NAME_FORMATED"])."')";

											else
												$href = "javascript:jsMLI_".$ControlID.".AddValue(".$friend["USER_ID"].")";

											$APPLICATION->IncludeComponent("bitrix:main.user.link",
												'',
												array(
													"ID" => $friend["USER_ID"],
													"HTML_ID" => "group_request_search_".$friend["USER_ID"],
													"HREF" => $href,
													"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
													"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
													"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
													"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"]),
													"PERSONAL_PHOTO_IMG" => $friend["USER_PERSONAL_PHOTO_IMG"],
													"PERSONAL_PHOTO_FILE" => $friend["USER_PERSONAL_PHOTO_FILE"],
													"PROFILE_URL" => $friend["USER_PROFILE_URL"],
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
												false
												, array("HIDE_ICONS" => "Y")
											);

											?>
											</td>
										</tr>
										<?
									}
									?>
									</table>
									<?
								}
								else
									echo GetMessage("SONET_C33_T_NO_FRIENDS");
								?>
								<br><br><a href="<?= $arResult["Urls"]["Search"] ?>"><?= GetMessage("SONET_C33_T_ADD_FRIEND1") ?></a>
							</td>
						</tr>
					</table>
					</div>
				<?endif;?>
			</td>
		</tr>
		</table>
		<?
	}
	else
	{
		?>

		<?if ($arResult["SuccessUsers"]):?>
			<?= GetMessage("SONET_C11_SUCCESS") ?><br><br>
			<?= GetMessage("SONET_C33_T_SUCCESS_LIST") ?><br>
			<?foreach ($arResult["SuccessUsers"] as $user):?>
				<?if ($user[1] <> ''):?><a href="<?= $user[1] ?>"><?endif;?><?= $user[0] ?><?if ($user[1] <> ''):?></a><?endif;?><br />
			<?endforeach;?>
			<br />
		<?endif;?>
		<?if ($arResult["ErrorUsers"]):?>
			<?= GetMessage("SONET_C33_T_ERROR_LIST") ?><br>
			<?foreach ($arResult["ErrorUsers"] as $user):?>
				<?if ($user[1] <> ''):?><a href="<?= $user[1] ?>"><?endif;?><?= $user[0] ?><?if ($user[1] <> ''):?></a><?endif;?><br />
			<?endforeach;?>
			<br />
		<?endif;?>
		<?
		if($arResult["WarningMessage"] <> '')
		{
			?>
			<br /><span class='errortext'><?=$arResult["WarningMessage"]?></span><br /><br />
			<?
		}
		?>
		<br /><a href="<? echo $arResult["Urls"]["Group"]; ?>"><? echo GetMessage("SONET_C11_MESSAGE_GROUP_LINK"); ?><? echo $arResult["Group"]["NAME"]; ?></a><br />
		<?
	}
}
?>