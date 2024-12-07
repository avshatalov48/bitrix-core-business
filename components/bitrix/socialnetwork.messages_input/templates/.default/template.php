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
	<script>
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
		<?if ($arResult["NAV_STRING"] <> ''):?>
			<?=$arResult["NAV_STRING"]?><br /><br />
		<?endif;?>
		<div class="sonet-cntnr-messages-input">
		<table width="100%" class="sonet-user-profile-friends data-table">
			<tr>
				<th><input type="checkbox" id="check_all" value="" title="<?= GetMessage("SONET_C27_T_SELECT_ALL") ?>" onclick="SelectAllRows(this);"></th>
				<th><?= GetMessage("SONET_C27_T_SENDER") ?></th>
				<th><?= GetMessage("SONET_C27_T_MESSAGE") ?></th>
				<th><?= GetMessage("SONET_C27_T_ACTIONS") ?></th>
			</tr>
			<?$ind = 0;?>
			<?if ($arResult["Events"]):?>
				<?foreach ($arResult["Events"] as $event):?>
					<tr>
						<td valign="top" align="center"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?>>
							<input type="checkbox" name="checked_<?= $ind ?>" value="Y">
							<input type="hidden" name="id_<?= $ind ?>" value="<?= $event["ID"] ?>">
						</td>
						<td valign="top"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?> nowrap>
							<?= $event["USER_PERSONAL_PHOTO_IMG"]; ?><br>
							<?
								
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $event["USER_ID"],
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
								
							?>
						</td>
						<td valign="top"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?>>
							<?= $event["DATE_CREATE"]; ?><br>
							<?if ($event["TITLE"] <> ''):?>
								<b><?= $event["TITLE"]; ?></b><br><br>
							<?endif;?>
							<?= $event["MESSAGE"]; ?>
						</td>
						<td valign="top"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?> nowrap>
							<a href="<?= $event["ALL_USER_MESSAGES_LINK"] ?>"><?= GetMessage("SONET_C27_T_ALL_MSGS") ?></a><br><br>
							<?if ($event["SHOW_ANSWER_LINK"]):?>
								<a href="<?= $event["ANSWER_LINK"] ?>" onclick="window.open('<?= $event["ANSWER_LINK"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false;"><?= GetMessage("SONET_C27_T_ANSWER") ?></a><br><br>
							<?endif;?>
							<?if (!$event["IS_READ"]):?>
								<a href="<?= $event["READ_LINK"] ?>"><?= GetMessage("SONET_C27_T_MARK_READ") ?></a><br><br>
							<?endif;?>
							<a href="<?= $event["DELETE_LINK"] ?>"><?= GetMessage("SONET_C27_T_DELETE") ?></a><br><br>
							<?if ($event["SHOW_BAN_LINK"]):?>
								<a href="<?= $event["BAN_LINK"] ?>"><?= GetMessage("SONET_C27_T_BAN") ?></a>
							<?endif;?>
						</td>
					</tr>
					<?$ind++;?>
				<?endforeach;?>
			<?else:?>
				<tr>
					<td colspan="4"><?= GetMessage("SONET_C27_T_EMPTY") ?></td>
				</tr>
			<?endif;?>
		</table>
		</div>
		<?if ($arResult["NAV_STRING"] <> ''):?>
			<br /><?=$arResult["NAV_STRING"]?><br />
		<?endif;?>
		<br />
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<input type="submit" name="do_read" value="<?= GetMessage("SONET_C27_T_DO_READ") ?>">
		<input type="submit" name="do_delete" value="<?= GetMessage("SONET_C27_T_DO_DELETE") ?>">
	</form>
	<?
}
?>