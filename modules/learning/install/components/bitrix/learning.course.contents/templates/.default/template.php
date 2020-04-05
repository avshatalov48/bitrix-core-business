<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["COURSE"])):
	$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/mediaplayer/flvscript.js")
?>
<div class="learning-course-contents">

	<div class="learning-course-toc">
		<b><?=GetMessage('LEARNING_COURSE_TOC')?></b>

		<?if (strlen($arResult["COURSE"]["DESCRIPTION"])>0):?>
			<ul><li><a href="#TOC"><?=GetMessage('LEARNING_COURSE_DESCRIPTION')?></a></li></ul>
		<?endif?>

		<?foreach ($arResult["CONTENTS"] as $arElement):?>
			<?=str_repeat("<ul>", $arElement["DEPTH_LEVEL"]);?>
			<li><a href="#<?=$arElement["TYPE"].$arElement["ID"]?>"><?=$arElement["NAME"]?></a></li>
			<?=str_repeat("</ul>", $arElement["DEPTH_LEVEL"]);?>
		<?endforeach?>
	</div>

	<div class="page-break"></div>

	<?if (strlen($arResult["COURSE"]["DESCRIPTION"])>0):?>
		<a name="TOC"></a>
		<h2><?=GetMessage('LEARNING_COURSE_DESCRIPTION')?></h2>
		<?=$arResult["COURSE"]["DESCRIPTION"]?>
	<?endif?>

	<div class="page-break"></div>

	<?foreach($arResult["CONTENTS"] as $arElement):?>
		<a name="<?=$arElement["TYPE"].$arElement["ID"]?>"></a>
		<h2><?=$arElement["NAME"]?></h2>
		<?if($arElement["DETAIL_PICTURE_ARRAY"] !== false):?>
			<br /><img src="<?=$arElement["DETAIL_PICTURE_ARRAY"]["SRC"]?>" width="<?=$arElement["DETAIL_PICTURE_ARRAY"]["WIDTH"]?>" height="<?=$arElement["DETAIL_PICTURE_ARRAY"]["HEIGHT"]?>" /><br />
		<?endif?>
		<?php if ($arElement["TYPE"] == "LE" && $arElement["DETAIL_TEXT_TYPE"] == "file"):?>
			<iframe width="100%" height="500" src="<?php echo $arElement["LAUNCH"]?>" frameborder="0"></iframe>
		<?php else:?>
			<?=$arElement["DETAIL_TEXT"]?>
		<?php endif?>
		<div class="page-break"></div>
	<?endforeach;?>

</div>
<?endif?>