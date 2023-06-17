<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
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

	?><div style="margin-bottom: 1em;">
	<table width="100%" cellspacing="0" cellpadding="8" border="0" class="sonet-user-short">
	<tr>
		<td valign="top" width="35%" class="sonet-user-avatar"><?
			?><?=$arResult["User"]["PersonalPhotoImg"]?><?
			if ($arResult['IS_ONLINE'] || $arResult['IS_BIRTHDAY'] || $arResult['IS_ABSENT'] || $arResult["IS_HONOURED"])
			{
				?><div class="bx-user-control">
					<ul>
						<?if ($arResult['IS_ONLINE']):?><li class="bx-icon bx-icon-online"><?= GetMessage("SONET_C38_T_ONLINE") ?></li><?endif;?>
						<?if ($arResult['IS_BIRTHDAY']):?><li class="bx-icon bx-icon-birth"><?= GetMessage("SONET_C38_T_BIRTHDAY") ?></li><?endif;?>
						<?if ($arResult["IS_HONOURED"]):?><li class="bx-icon bx-icon-featured"><?= GetMessage("SONET_C38_T_HONOURED") ?></li><?endif;?>
						<?if ($arResult['IS_ABSENT']):?><li class="bx-icon bx-icon-away"><?= GetMessage("SONET_C39_ABSENT") ?></li><?endif;?>
					</ul>
				</div><?
			}
			
			if ($GLOBALS["USER"]->IsAuthorized())
			{
				if (!$arResult["CurrentUserPerms"]["IsCurrentUser"])
				{
					if ($arResult["CurrentUserPerms"]["Operations"]["message"] || $arResult["CurrentUserPerms"]["Operations"]["videocall"])
					{
						?><div class="bx-user-control">
							<ul><?
								if ($arResult["CurrentUserPerms"]["Operations"]["message"] && $arResult["User"]["ACTIVE"] != "N")
								{
									?><li class="bx-icon-action bx-icon-message"><nobr><a href="<?= $arResult["Urls"]["MessageChat"] ?>" onclick="if (typeof(BX) != 'undefined' && BX.IM) { BXIM.openMessenger(<?=$arResult["User"]["ID"]?>); return false; } else { window.open('<?= $arResult["Urls"]["MessageChat"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }"><?= GetMessage("SONET_C39_SEND_MESSAGE") ?></a></nobr></li><?
								}
								if ($arResult["CurrentUserPerms"]["Operations"]["videocall"] && $arResult["User"]["ACTIVE"] != "N")
								{
									?><li class="bx-icon-action bx-icon-video-call"><nobr><a href="<?= $arResult["Urls"]["VideoCall"] ?>" onclick="window.open('<?= $arResult["Urls"]["VideoCall"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;"><?= GetMessage("SONET_C39_VIDEO_CALL") ?></a></nobr></li><?
								}
							?></ul>
						</div><?
					}
				}
			}
		?></td>
		<td valign="top" width="65%" class="sonet-user-text">
			<h4><?=$arResult["User"]["NAME_FORMATTED"]?></h4><?
			if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
			{
				?><table width="100%" cellspacing="2" cellpadding="2"><?
					if ($arResult["UserFieldsMain"]["SHOW"] == "Y")
					{
						foreach ($arResult["UserFieldsMain"]["DATA"] as $fieldName => $arUserField)
						{
							if ($arUserField["VALUE"] <> '')
							{
								?><tr><?
									?><td width="25%"><?= $arUserField["NAME"] ?>:</td>
									<td width="75%"><?= $arUserField["VALUE"] ?></td><?
								?></tr><?
							}
						}
					}

					if ($arResult["UserPropertiesMain"]["SHOW"] == "Y")
					{
						foreach ($arResult["UserPropertiesMain"]["DATA"] as $fieldName => $arUserField)
						{
							if (
								is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 
								|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] <> ''
							)
							{
								?><tr>
									<td width="25%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
									<td width="75%"><?
										if (
											IsModuleInstalled('intranet') 
											&& (
												!IsModuleInstalled("extranet")
												|| !CExtranet::IsExtranetSite()
											)
										)
										{
											$arUserField['SETTINGS']['SECTION_URL'] = $arParams["PATH_TO_CONPANY_DEPARTMENT"];
										}

										$APPLICATION->IncludeComponent(
											"bitrix:system.field.view", 
											$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
											array("arUserField" => $arUserField),
											null,
											array("HIDE_ICONS"=>"Y")
										);
									?></td>
								</tr><?
							}
						}
					}
				?></table><?
			}
			else
			{
				?><?=GetMessage("SONET_C38_TP_NO_PERMS")?><?
			}
		?></td>
	</tr>
	</table>
	</div><?
}
?>