<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult["Groups"]) > 0):?>
	<table width="100%" cellspacing="0" cellpadding="5">
	<?foreach ($arResult["Groups"] as $arGroup):?>
		<tr>
			<td valign="top">
				<?if ($arParams["DISPLAY_IMAGE"] != "N"):?>
					<?= $arGroup["IMAGE_IMG"] ?>
				<?endif;?>
			</td>
			<td valign="top">
				<span class="sonet-date-time"><?= $arGroup["FULL_DATE_CHANGE_FORMATED"] ?></span>
				<a href="<?= $arGroup["GROUP_URL"] ?>"><?= $arGroup["NAME"] ?></a><br />

				<?if ($arParams["DISPLAY_DESCRIPTION"] != "N" && $arGroup["DESCRIPTION"] <> ''):?>
					<?= $arGroup["DESCRIPTION"] ?><br />
				<?endif;?>

				<?if ($arParams["DISPLAY_NUMBER_OF_MEMBERS"] != "N" && intval($arGroup["NUMBER_OF_MEMBERS"]) > 0):?>
					<?= GetMessage("SONET_C68_T_MEMBERS") ?>: <?= $arGroup["NUMBER_OF_MEMBERS"] ?><br />
				<?endif;?>

				<?if ($arParams["DISPLAY_SUBJECT"] != "N" && $arGroup["SUBJECT_NAME"] <> ''):?>
					<?= GetMessage("SONET_C68_T_SUBJ") ?>: <?= $arGroup["SUBJECT_NAME"] ?><br />
				<?endif;?>
			</td>
		</tr>
	<?endforeach;?>
	</table>
	<br /><a href="<?= $arResult["Urls"]["GroupSearch"] ?>"><?= GetMessage("SONET_C68_T_ALL") ?></a>
<?else:?>
	<?= GetMessage("SONET_C68_T_EMPTY") ?>
<?endif;?>