<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table width="100%"><tr><td valign="top">
<form method="get" action="<?= $arResult["Urls"]["GroupSearch"] ?>" style="margin:0;padding:0;">
	<input type="hidden" name="<?= $arParams["PAGE_VAR"] ?>" value="group_search">
	<div class="sonet-cntnr-group-list">
	<table class="sonet-user-profile-friends data-table">
		<tr>
			<th colspan="2"><?= GetMessage("SONET_C24_T_SEARCH_TITLE") ?></th>
		</tr>
		<tr>
			<td align="right"><?= GetMessage("SONET_C24_T_SEARCH") ?>:</td>
			<td><input type="text" name="q" style="width:300px" size="40" value="<?= $arResult["q"] ?>"></td>
		</tr>
		<tr>
			<td align="right"><?= GetMessage("SONET_C24_T_SUBJECT") ?>:</td>
			<td>
				<select name="subject" style="width:300px">
					<option value=""><?= GetMessage("SONET_C24_T_ANY") ?></option>
					<?foreach ($arResult["Subjects"] as $k => $v):?>
						<option value="<?= $k ?>"<?= ($k == $arResult["subject"]) ? " selected" : "" ?>><?= $v ?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<tfoot>
		<tr>
			<td colspan="2">
				<input type="submit" value="<?= GetMessage("SONET_C24_T_DO_SEARCH") ?>">
				<input type="button" value="<?= GetMessage("SONET_C24_T_DO_CANCEL") ?>" onclick="window.location='<?= $arResult["Urls"]["GroupSearch"] ?>'">
			</td>
		</tr>
		</tfoot>
	</table>
	</div>
	<?if ($arResult["how"] == "d"):?>
		<input type="hidden" name="how" value="d">
	<?endif;?>
</form>
</td>
<td valign="bottom" align="right">
	<?if ($arResult["ALLOW_CREATE_GROUP"]):?>
		<a href="<?= $arResult["Urls"]["GroupCreate"] ?>" class="sonet-icon-create"><?= GetMessage("SONET_C24_T_CREATE_GROUP") ?></a>
	<?endif;?>
</td>
</tr></table>

<?if (strlen($arResult["ERROR_MESSAGE"]) <= 0):?>
	<?if (count($arResult["SEARCH_RESULT"]) > 0):?>
		<br /><?foreach ($arResult["SEARCH_RESULT"] as $v):?>
		<div class="sonet-cntnr-group-list2">
		<table width="100%" class="sonet-user-profile-friends data-table">
			<tr>
				<td width="105" nowrap valign="top" align="center">
					<?= $v["IMAGE_IMG"] ?>
				</td>
				<td valign="top">
					<a href="<?= $v["URL"] ?>"><b><?= $v["TITLE_FORMATED"] ?></b></a><br />
					<?
					if ($v["ARCHIVE"] == "Y")
					{
						?>
						<br />
						<b><?= GetMessage("SONET_C39_ARCHIVE_GROUP") ?></b>
						<?
					}
					if (strlen($v["BODY_FORMATED"]) > 0)
					{
						?>
						<br />
						<?= $v["BODY_FORMATED"] ?>
						<?
					}
					if (strlen($v["SUBJECT_NAME"]) > 0)
					{
						?>
						<br />
						<?= GetMessage("SONET_C24_T_SUBJ") ?>: <?= $v["SUBJECT_NAME"] ?>
						<?
					}
					if (IntVal($v["NUMBER_OF_MEMBERS"]) > 0)
					{
						?>
						<br />
						<?= GetMessage("SONET_C24_T_MEMBERS") ?>: <?= $v["NUMBER_OF_MEMBERS"] ?>
						<?
					}
					?>
					<br />
					<?= GetMessage("SONET_C24_T_ACTIVITY") ?>: <?= $v["FULL_DATE_CHANGE_FORMATED"]; ?>
				</td>
			</tr>
		</table>
		</div>
		<?endforeach;?>

		<?if (strlen($arResult["NAV_STRING"]) > 0):?>
			<p><?=$arResult["NAV_STRING"]?></p>
		<?endif;?>
			
		<?if (strlen($arResult["ORDER_LINK"]) > 0):?>
			<?if ($arResult["how"] == "d"):?>
				<p><a href="<?= $arResult["ORDER_LINK"] ?>"><?= GetMessage("SONET_C24_T_ORDER_REL") ?></a>&nbsp;|&nbsp;<b><?= GetMessage("SONET_C24_T_ORDER_DATE") ?></b></p>
			<?else:?>
				<p><b><?= GetMessage("SONET_C24_T_ORDER_REL") ?></b>&nbsp;|&nbsp;<a href="<?=$arResult["ORDER_LINK"]?>"><?= GetMessage("SONET_C24_T_ORDER_DATE") ?></a></p>
			<?endif;?>
		<?endif;?>
	<?endif;?>
<?else:?>
	<?= ShowError($arResult["ERROR_MESSAGE"]); ?>
<?endif;?>