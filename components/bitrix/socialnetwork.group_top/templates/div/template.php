<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="bx-sonet-layout-include">
	<?if (count($arResult["Groups"]) > 0):?>
		<?foreach ($arResult["Groups"] as $arGroup):?>
			<div class="bx-sonet-group-info">
				<div class="bx-sonet-group-info-inner">
					<?if ($arParams["DISPLAY_IMAGE"] != "N"):?>
						<div class="bx-sonet-group-image"><?= $arGroup["IMAGE_IMG"]; ?></div>
					<?endif;?>
					<div class="bx-sonet-group-date intranet-date<?if ($arParams["DISPLAY_IMAGE"] == "N"):?> no-image<?endif;?>"><?= $arGroup["FULL_DATE_CHANGE_FORMATED"] ?></div>
					<div class="bx-user-name<?if ($arParams["DISPLAY_IMAGE"] == "N"):?> no-image<?endif;?>"><a href="<?= $arGroup["GROUP_URL"] ?>"><?= $arGroup["NAME"] ?></a></div>
					<?if ($arParams["DISPLAY_DESCRIPTION"] != "N" && $arGroup["DESCRIPTION"] <> ''):?>
						<div class="bx-user-post<?if ($arParams["DISPLAY_IMAGE"] == "N"):?> no-image<?endif;?>"><?= $arGroup["DESCRIPTION"] ?></div>
					<?endif;?>
					<?if ($arParams["DISPLAY_NUMBER_OF_MEMBERS"] != "N" && intval($arGroup["NUMBER_OF_MEMBERS"]) > 0):?>
						<div class="bx-user-post<?if ($arParams["DISPLAY_IMAGE"] == "N"):?> no-image<?endif;?>"><?= GetMessage("SONET_C68_T_MEMBERS") ?>: <?= $arGroup["NUMBER_OF_MEMBERS"] ?></div>
					<?endif;?>
					<?if ($arParams["DISPLAY_SUBJECT"] != "N" && $arGroup["SUBJECT_NAME"] <> ''):?>
						<div class="bx-user-post<?if ($arParams["DISPLAY_IMAGE"] == "N"):?> no-image<?endif;?>"><?= GetMessage("SONET_C68_T_SUBJ") ?>: <?= $arGroup["SUBJECT_NAME"] ?></div>
					<?endif;?>
					<div class="bx-users-delimiter"></div>
				</div>
			</div>
		<?endforeach;?>
		<br /><a href="<?= $arResult["Urls"]["GroupSearch"] ?>"><?= GetMessage("SONET_C68_T_ALL") ?></a> <a href="<?= $arResult["Urls"]["GroupSearch"] ?>" class="bx-sonet-group-arrows"></a>
	<?else:?>
		<?= GetMessage("SONET_C68_T_EMPTY") ?>
	<?endif;?>
</div>