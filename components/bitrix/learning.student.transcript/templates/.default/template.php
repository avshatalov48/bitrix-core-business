<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["USER"])):?>

	<b>
	<?if($arResult["USER"]["LAST_NAME"] <> '' || $arResult["USER"]["NAME"] <> ''):?>
		<?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"])?>
	<?else:?>
		<?=$arResult["USER"]["LOGIN"]?>
	<?endif?>
	</b><br />

	<?if ($arResult["USER"]["PERSONAL_PHOTO_ARRAY"]!==false):?>
		<br /><?=CFile::ShowImage($arResult["USER"]["PERSONAL_PHOTO_ARRAY"], 200, 200, "border=0", "", true)?><br /><br />
	<?endif;?>

	<?if($arResult["USER"]["EMAIL"] <> ''):?>
		<a href="mailto:<?=$arResult["USER"]["EMAIL"]?>"><?=$arResult["USER"]["EMAIL"]?></a><br />
	<?endif?>

	<?if($arResult["USER"]["PERSONAL_ICQ"] <> ''):?>
		ICQ: <?=$arResult["USER"]["PERSONAL_ICQ"]?><br />
	<?endif?>

	<?if($arResult["USER"]["PERSONAL_WWW"]!="http://" && $arResult["USER"]["PERSONAL_WWW"]!=""):?>
		<a href="<?echo (!preg_match( "#^http://#", $arResult["USER"]["PERSONAL_WWW"])?"http://".$arResult["USER"]["PERSONAL_WWW"]:$arResult["USER"]["PERSONAL_WWW"])?>"><?=$arResult["USER"]["PERSONAL_WWW"]?></a><br />
	<?endif?>

	<?if($arResult["USER"]["PERSONAL_STREET"] <> ''):?>
		<?=$arResult["USER"]["PERSONAL_STREET"]?><br />
	<?endif?>

	<?if($arResult["USER"]["PERSONAL_CITY"] <> '' && $arResult["USER"]["PERSONAL_ZIP"] <> '' && $arResult["USER"]["PERSONAL_STATE"] <> ''):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?>, <?=$arResult["USER"]["PERSONAL_STATE"]?>, <?=$arResult["USER"]["PERSONAL_ZIP"]?><br />
	<?elseif($arResult["USER"]["PERSONAL_CITY"] <> '' && $arResult["USER"]["PERSONAL_ZIP"] <> ''):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?>, <?=$arResult["USER"]["PERSONAL_ZIP"]?><br />
	<?elseif($arResult["USER"]["PERSONAL_CITY"] <> ''):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?><br />
	<?endif?>

	<?if ($arResult["USER"]["PERSONAL_COUNTRY_NAME"] <> ''):?>
		<?=$arResult["USER"]["PERSONAL_COUNTRY_NAME"]?><br />
	<?endif?>

	<?if ($arResult["STUDENT"]["RESUME"] <> ''):?>
		<br /><b><?=GetMessage("LEARNING_TRANSCRIPT_RESUME")?></b><br />
		<?=str_replace("\n", "<br>",$arResult["STUDENT"]["RESUME"])?><br />
	<?endif?>

	<br /><b><?=GetMessage("LEARNING_TRANSCRIPT_CERTIFIFCATIONS")?></b><br />

	<table class="learning-certificate-table data-table">
		<tr>
			<th width="30%"><?=GetMessage("LEARNING_TRANSCRIPT_DATE")?></th>
			<th><?=GetMessage("LEARNING_TRANSCRIPT_NAME")?></th>

		</tr>
	<?if (!empty($arResult["CERTIFICATES"])):?>
		<?foreach ($arResult["CERTIFICATES"] as $arCertificate):?>
			<tr>
				<td><?=$arCertificate["DATE_CREATE"]?></td>
				<td><?=$arCertificate["COURSE_NAME"]?></td>
			</tr>
		<?endforeach?>
	<?else:?>
		<tr>
			<td colspan="2">-&nbsp;<?=GetMessage("LEARNING_TRANSCRIPT_NO_DATA")?>&nbsp;-</td>
		</tr>
	<?endif?>
	</table>

<?endif?>