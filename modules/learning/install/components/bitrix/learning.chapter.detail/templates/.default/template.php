<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["CHAPTER"])):?>

	<?if($arResult["CHAPTER"]["DETAIL_PICTURE_ARRAY"] !== false):?>
		<?=ShowImage(
			$arResult["CHAPTER"]["DETAIL_PICTURE_ARRAY"],
			250,
			250,
			"hspace='8' vspace='1' align='left' border='0'"
				. ' alt="' . htmlspecialcharsbx($arResult["CHAPTER"]["DETAIL_PICTURE_ARRAY"]['DESCRIPTION']) . '"',
			"",
			true);?>
	<?endif?>



	<?if ($arResult["CHAPTER"]["DETAIL_TEXT"] <> ''):?>
		<br /><?=$arResult["CHAPTER"]["DETAIL_TEXT"]?>
	<?endif;?>

	<br clear="all" />

	<?if (!empty($arResult["CONTENTS"])):?>
	<div class="learn-chapter-contents">
		<b><?echo GetMessage("LEARNING_CHAPTER_CONTENTS");?>:</b>
		<?foreach ($arResult["CONTENTS"] as $arContent):?>
			<?=str_repeat("<ul>", $arContent["DEPTH_LEVEL"]);?>
			<li><a href="<?=$arContent["URL"]?>"><?=$arContent["NAME"]?></a></li>
			<?=str_repeat("</ul>", $arContent["DEPTH_LEVEL"]);?>
		<?endforeach?>
	</div>
	<?endif?>

	<?if($arResult["CHAPTER"]["SELF_TEST_EXISTS"]):?>
		<a href="<?=$arResult["CHAPTER"]["SELF_TEST_URL"]?>" title="<?=GetMessage("LEARNING_PASS_SELF_TEST")?>">
			<div title="<?echo GetMessage("LEARNING_PASS_SELF_TEST")?>" class="learn-self-test-icon float-right"></div>
		</a>
	<?endif?>
	<?
	$arParams["SHOW_RATING"] = $arResult["COURSE"]["RATING"];
	CRatingsComponentsMain::GetShowRating($arParams);
	if($arParams["SHOW_RATING"] == 'Y'):
	?>
	<br>
		<div class="learn-rating">
		<?$APPLICATION->IncludeComponent(
			"bitrix:rating.vote", $arResult["COURSE"]["RATING_TYPE"],
			Array(
				"ENTITY_TYPE_ID" => "LEARN_LESSON",
				"ENTITY_ID" => $arResult["CHAPTER"]["LESSON_ID"],
				"OWNER_ID" => $arResult["CHAPTER"]["CREATED_BY"],
				"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);?>
		</div>
	<?endif?>
	<?if($arResult["CHAPTER"]["SELF_TEST_EXISTS"]):?>
		<div class="float-clear"></div>
		<br /><div title="<?echo GetMessage("LEARNING_PASS_SELF_TEST")?>" class="learn-self-test-icon float-left"></div>&nbsp;<a href="<?=$arResult["CHAPTER"]["SELF_TEST_URL"]?>"><?=GetMessage("LEARNING_PASS_SELF_TEST")?></a><br />
	<?endif?>

<?endif?>