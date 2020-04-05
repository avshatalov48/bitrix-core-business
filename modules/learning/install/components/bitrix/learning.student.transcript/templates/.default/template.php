<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["USER"])):?>

	<b>
	<?if(strlen($arResult["USER"]["LAST_NAME"])>0 || strlen($arResult["USER"]["NAME"])>0):?>
		<?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"])?>
	<?else:?>
		<?=$arResult["USER"]["LOGIN"]?>
	<?endif?>
	</b><br />

	<?if ($arResult["USER"]["PERSONAL_PHOTO_ARRAY"]!==false):?>
		<br /><?=CFile::ShowImage($arResult["USER"]["PERSONAL_PHOTO_ARRAY"], 200, 200, "border=0", "", true)?><br /><br />
	<?endif;?>

	<?if(strlen($arResult["USER"]["EMAIL"])>0):?>
		<a href="mailto:<?=$arResult["USER"]["EMAIL"]?>"><?=$arResult["USER"]["EMAIL"]?></a><br />
	<?endif?>

	<?if(strlen($arResult["USER"]["PERSONAL_ICQ"])>0):?>
		ICQ: <?=$arResult["USER"]["PERSONAL_ICQ"]?><br />
	<?endif?>

	<?if($arResult["USER"]["PERSONAL_WWW"]!="http://" && $arResult["USER"]["PERSONAL_WWW"]!=""):?>
		<a href="<?echo (!preg_match( "#^http://#", $arResult["USER"]["PERSONAL_WWW"])?"http://".$arResult["USER"]["PERSONAL_WWW"]:$arResult["USER"]["PERSONAL_WWW"])?>"><?=$arResult["USER"]["PERSONAL_WWW"]?></a><br />
	<?endif?>

	<?if(strlen($arResult["USER"]["PERSONAL_STREET"])>0):?>
		<?=$arResult["USER"]["PERSONAL_STREET"]?><br />
	<?endif?>

	<?if(strlen($arResult["USER"]["PERSONAL_CITY"])>0 && strlen($arResult["USER"]["PERSONAL_ZIP"])>0 && strlen($arResult["USER"]["PERSONAL_STATE"])>0):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?>, <?=$arResult["USER"]["PERSONAL_STATE"]?>, <?=$arResult["USER"]["PERSONAL_ZIP"]?><br />
	<?elseif(strlen($arResult["USER"]["PERSONAL_CITY"])>0 && strlen($arResult["USER"]["PERSONAL_ZIP"])>0):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?>, <?=$arResult["USER"]["PERSONAL_ZIP"]?><br />
	<?elseif(strlen($arResult["USER"]["PERSONAL_CITY"])>0):?>
		<?=$arResult["USER"]["PERSONAL_CITY"]?><br />
	<?endif?>

	<?if (strlen($arResult["USER"]["PERSONAL_COUNTRY_NAME"])>0):?>
		<?=$arResult["USER"]["PERSONAL_COUNTRY_NAME"]?><br />
	<?endif?>

	<?if (strlen($arResult["STUDENT"]["RESUME"])>0):?>
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