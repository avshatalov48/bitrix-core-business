<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if ($arResult["ALLOW_CREATE_GROUP"]):?>
	<div class="sonet-add-group-button">
	<a class="sonet-add-group-button-left" href="<?= $arResult["Urls"]["GroupCreate"] ?>" title="<?= GetMessage("SONET_C24_T_CREATE_GROUP") ?>"></a>
	<div class="sonet-add-group-button-fill"><a href="<?= $arResult["Urls"]["GroupCreate"] ?>" class="sonet-add-group-button-fill-text"><?= GetMessage("SONET_C24_T_CREATE_GROUP") ?></a></div>
	<a class="sonet-add-group-button-right" href="<?= $arResult["Urls"]["GroupCreate"] ?>" title="<?= GetMessage("SONET_C24_T_CREATE_GROUP") ?>"></a>
	<div class="sonet-add-group-button-clear"></div>
	</div>
<?endif;?>
<?if (strlen($arResult["ERROR_MESSAGE"]) <= 0):?>
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
						if (IntVal($v["NUMBER_OF_MEMBERS"]) > 0)
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
						if (strlen($v["SUBJECT_NAME"]) > 0)
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
						if (strlen($v["BODY_FORMATED"]) > 0)
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