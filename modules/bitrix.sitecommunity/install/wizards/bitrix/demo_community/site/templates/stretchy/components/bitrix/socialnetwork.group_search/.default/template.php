<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if ($arResult["ALLOW_CREATE_GROUP"]):?>
	<div class="sonet-add-group-button">
	<a class="sonet-add-group-button-left" href="<?= $arResult["Urls"]["GroupCreate"] ?>" title="<?= GetMessage("SONET_C24_T_CREATE_GROUP") ?>"></a>
	<div class="sonet-add-group-button-fill"><a href="<?= $arResult["Urls"]["GroupCreate"] ?>" class="sonet-add-group-button-fill-text"><?= GetMessage("SONET_C24_T_CREATE_GROUP") ?></a></div>
	<a class="sonet-add-group-button-right" href="<?= $arResult["Urls"]["GroupCreate"] ?>" title="<?= GetMessage("SONET_C24_T_CREATE_GROUP") ?>"></a>
	<div class="sonet-add-group-button-clear"></div>
	</div>
<?endif;?>
<?if ($arResult["ERROR_MESSAGE"] == ''):?>
	<?if (count($arResult["SEARCH_RESULT"]) > 0):?>
		<br /><?foreach ($arResult["SEARCH_RESULT"] as $v):?>
		<table width="100%" class="sonet-user-profile-friends data-table">
			
				<tr>
					<td width="105" nowrap valign="top" align="center">
						<?= $v["IMAGE_IMG"] ?>
					</td>
					<td valign="top">
						<div class="content-sidebar">
						<div class="content-change"><?= GetMessage("SONET_C24_T_ACTIVITY") ?>: <?= $v["FULL_DATE_CHANGE_FORMATED"]; ?></div>
						<?
						if (intval($v["NUMBER_OF_MEMBERS"]) > 0)
						{
							?>
							<div class="content-members">
							<?= GetMessage("SONET_C24_T_MEMBERS") ?>: <?= $v["NUMBER_OF_MEMBERS"] ?>
							</div>
							<?
						}
						?>
						</div>		
						<a href="<?= $v["URL"] ?>"><b><?= $v["TITLE_FORMATED"] ?></b></a>
						<?
						if ($v["SUBJECT_NAME"] <> '')
						{
							?>
							<div class="content-subject"><?= GetMessage("SONET_C24_T_SUBJ") ?>: <?= $v["SUBJECT_NAME"] ?></div>
							<?
						}
						?>
						<?
						if ($v["ARCHIVE"] == "Y")
						{
							?>
							<br />
							<b><?= GetMessage("SONET_C39_ARCHIVE_GROUP") ?></b>
							<?
						}
						if ($v["BODY_FORMATED"] <> '')
						{
							?>
							<br />
							<?= $v["BODY_FORMATED"] ?>
							<?
						}

						?>
										
						
					</td>
				</tr>
			
		</table>
		<br />
		<?endforeach;?>

		<?if ($arResult["NAV_STRING"] <> ''):?>
			<p><?=$arResult["NAV_STRING"]?></p>
		<?endif;?>
			
		<?if ($arResult["ORDER_LINK"] <> ''):?>
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