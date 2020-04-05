<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}

	if($_REQUEST["invite_sent"] == "Y")
	{
		?>
		<span class='notetext'><?=GetMessage("SONET_C12_REINVITE_SENT")?></span><br /><br />
		<?
	}
	?>	
	<script language="javascript">
	<!--
	function SelectAllRows(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bChecked = checkbox.checked;
		var i;
		var n = tbl.rows.length;
		for (i = 1; i < n; i++)
		{
			var j;
			var m = tbl.rows[i].cells[0].childNodes.length;
			for (j = 0; j < m; j++)
			{
				var box = tbl.rows[i].cells[0].childNodes[j];
				if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
				{
					if (box.checked != bChecked && !box.disabled)
						box.checked = bChecked;
					break;
				}
			}
		}
	}
	//-->
	</script>

	<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
		<?if (StrLen($arResult["NAV_STRING"]) > 0):?>
			<?=$arResult["NAV_STRING"]?><br /><br />
		<?endif;?>
		<div class="sonet-cntnr-group-requests-out">
		<table width="100%" class="sonet-user-profile-friends data-table">
			<tr>
				<th colspan="3"><?= GetMessage("SONET_C12_SUBTITLE") ?></th>
			</tr>
			<tr>
				<th width="0%" align="center"><input type="checkbox" id="check_all" value="" title="<?= GetMessage("SONET_C12_CHECK_ALL") ?>" onclick="SelectAllRows(this);"></th>
				<th><?= GetMessage("SONET_C12_RECIPIENT") ?></th>
				<th><?= GetMessage("SONET_C12_MESSAGE") ?></th>
			</tr>
			<?$ind = 0;?>
			<?if ($arResult["Requests"] && $arResult["Requests"]["List"]):?>
				<?foreach ($arResult["Requests"]["List"] as $friend):?>
					<tr>
						<td valign="top" align="center" width="0%">
							<input type="checkbox" name="checked_<?= $ind ?>" value="Y">
							<input type="hidden" name="id_<?= $ind ?>" value="<?= $friend["ID"] ?>">
						</td>
						<td valign="top">
							<?= $friend["USER_PERSONAL_PHOTO_IMG"]; ?><br />
							<?
							
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $friend["USER_ID"],
									"HTML_ID" => "group_request_out_".$friend["USER_ID"],
									"NAME" => htmlspecialcharsback($friend["USER_NAME"]),
									"LAST_NAME" => htmlspecialcharsback($friend["USER_LAST_NAME"]),
									"SECOND_NAME" => htmlspecialcharsback($friend["USER_SECOND_NAME"]),
									"LOGIN" => htmlspecialcharsback($friend["USER_LOGIN"]),
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $friend["USER_PERSONAL_PHOTO_IMG"],
									"PERSONAL_PHOTO_FILE" => $friend["USER_PERSONAL_PHOTO_FILE"],
									"PROFILE_URL" => $friend["USER_PROFILE_URL"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
									"SHOW_FIELDS" => $arParams["SHOW_FIELDS_TOOLTIP"],
									"USER_PROPERTY" => $arParams["USER_PROPERTY_TOOLTIP"],
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
							
							?><br />
							<?= $friend["DATE_CREATE"] ?>
						</td>
						<td valign="top">
							<?= $friend["MESSAGE"]; 
							
							if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite()):

								$rsInvitedUser = CUser::GetByID($friend["USER_ID"]);
								if (($arInvitedUser = $rsInvitedUser->Fetch()) && (!is_array($arInvitedUser["UF_DEPARTMENT"]) || intval($arInvitedUser["UF_DEPARTMENT"][0]) <= 0))
								{
									if (strlen($arInvitedUser["LAST_LOGIN"]) <= 0 && strlen($arInvitedUser["LAST_ACTIVITY_DATE"]) <= 0)
										echo "<br><br><a href='".$APPLICATION->GetCurPageParam("invite_user_id=".$arInvitedUser["ID"], array("invite_user_id", "employee"))."'>".GetMessage("SONET_C12_REINVITE_TO_SITE")."</a>";
								}

							endif;
							?>
						</td>
					</tr>
					<?$ind++;?>
				<?endforeach;?>
			<?else:?>
				<tr><td colspan="3"><?= GetMessage("SONET_C12_NO_REQUESTS") ?><br /><?= GetMessage("SONET_C12_NO_REQUESTS_DESCR") ?></td></tr>
			<?endif;?>
		</table>
		</div>
		<?if (StrLen($arResult["NAV_STRING"]) > 0):?>
			<?=$arResult["NAV_STRING"]?><br /><br />
		<?endif;?>
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<input type="submit" name="reject" value="<?= GetMessage("SONET_C12_DO_REJECT") ?>">
	</form>

	<a href="<?= $arResult["Urls"]["RequestSearch"] ?>"><?= GetMessage("SONET_C12_INVITE") ?></a>
	<?
}
?>