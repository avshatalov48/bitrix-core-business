<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div style="float: left; padding-top: 2px;"><?=GetMessage("SEARCH_LABEL")?>&nbsp;</div><?$APPLICATION->IncludeComponent(
	"bitrix:search.form",
	"flat",
	Array(
		"PAGE" => "search.php"
	),
	$component
);?>
<br />
<?if (!empty($arResult["COURSES"])):?>
<div class="learning-course-list">
	<?foreach($arResult["COURSES"] as $arCourse):?>
		<?if ($arCourse["PREVIEW_PICTURE_ARRAY"]!==false):?>
			<?echo ShowImage(
				$arCourse["PREVIEW_PICTURE_ARRAY"], 
				200, 
				200, 
				"hspace='6' vspace='6' align='left' border='0'"
					. ' alt="' . htmlspecialcharsbx($arCourse['PREVIEW_PICTURE_ARRAY']['DESCRIPTION']) . '"', 
				"", 
				true);?>
		<?endif;?>

		<a href="<?=$arCourse["COURSE_DETAIL_URL"]?>" target="_blank"><?=$arCourse["NAME"]?></a>
		<?if($arCourse["PREVIEW_TEXT"] <> ''):?>
			<br /><?=$arCourse["PREVIEW_TEXT"]?>
		<?endif?>
		<br clear="all"><br />
	<?endforeach;?>

</div>
	<?=$arResult["NAV_STRING"]?>
<?endif?>